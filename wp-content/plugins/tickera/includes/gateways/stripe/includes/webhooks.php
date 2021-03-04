<?php

use TCStripe\Event;
use TCStripe\Exception\ApiErrorException;
use TCStripe\PaymentIntent;
use TCStripe\StripeClient;
use TCStripe\WebhookEndpoint;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if ( !class_exists('TC_Stripe_Webhooks') ) {

    class TC_Stripe_Webhooks {

        var $plugin_name = 'stripe-elements-3d-secure';
        var $force_ssl;
        var $private_key;

        function __construct( $private_key = '' ) {
            $this->private_key = $private_key;
        }

        /**
         * Register a Tickera Endpoints via REST API
         */
        public static function register_tickera_endpoints() {

            add_action( 'rest_api_init', function () {

                // Capture payments when review has closed
                register_rest_route( 'tickera-stripe/v1', '/payment-intent/capture', array(
                    'methods' => 'POST',
                    'callback' => array( new TC_Stripe_Webhooks, 'capture_payment_intent_endpoint' ),
                    'permission_callback' => '__return_true'
                ));
            });
        }


        /**
         * Register a Stripe Webhook for later automation.
         * TODO: Optimize Codes
         *
         * @throws ApiErrorException
         */
        function register_stripe_webhook_endpoints() {

            $webhook_exists = false;
            $webhook_url = stripslashes( site_url() . '/wp-json/tickera-stripe/v1/payment-intent/capture' );

            try {

                /*
                 * Retrieve all stripe webhooks and check if Tickera Stripe Exists
                 */
                $stripe_webhook = new StripeClient( $this->private_key );
                $stripe_webhook_obj = $stripe_webhook->webhookEndpoints->all();

                foreach ( $stripe_webhook_obj['data'] as $key => $value ) {
                    if ( $value['url'] == $webhook_url ) {
                        $webhook_exists = true;
                        break;
                    }
                }

                /*
                 * Create webhook if doesn't exist
                 */
                if ( !$webhook_exists ) {
                    $webhook_obj = WebhookEndpoint::create([
                        'url' => stripslashes(site_url() . '/wp-json/tickera-stripe/v1/payment-intent/capture'),
                        'description' => 'TC_Endpoint_01 - Charge Captured Event',
                        'enabled_events' => [ 'charge.captured', 'charge.refunded', 'payment_intent.succeeded', 'payment_intent.payment_failed' ],
                    ]);
                }

            } Catch( Exception $e ) {
                // Nothing to do here for now
            }
        }


        /**
         * Second Step. Charge Capture
         * Tickera Endpoint REST API
         * Capture Charge via Stripe account and mark order as paid
         *
         * @param $request
         * @throws ApiErrorException
         */
        function capture_payment_intent_endpoint( $request ) {
            global $tc;

            $request_body = $request->get_json_params();

            $event_id = $request_body['id'];
            $event_obj = self::tc_validate_stripe_event( $event_id );

            /*
             * payment_intent.payment_failed - No action needed for now
             */
            $enabled_events = [ 'charge.captured', 'charge.refunded', 'payment_intent.succeeded' ];

            if ( $event_obj ) {

                if ( in_array( $event_obj['type'], $enabled_events ) ) {

                    $order_statuses = [ 'charge.refunded' => 'order_refunded' ];

                    // Retrieve Payment Intent ID
                    if ( isset( $event_obj['data']['object']['payment_intent'] ) && $event_obj['data']['object']['payment_intent'] ) {
                        $payment_intent_id = $event_obj['data']['object']['payment_intent'];

                    } else {
                        $payment_intent_id = $event_obj['data']['object']['id'];
                    }

                    $payment_intent = PaymentIntent::retrieve( $payment_intent_id );

                    $order_title = $payment_intent['metadata']['order_id'];
                    $order_id = tc_get_order_id_by_name( $order_title )->ID;

                    /*
                     * Check if the cancelled/refund reason is fraud.
                     * Update order status to fraud
                     */
                    if ( 'charge.refunded' == $event_obj['type'] ) {

                        $is_fraud = false;
                        $refund_obj = @$event_obj['data']['object']['refunds']['data'];

                        foreach ( $refund_obj as $key => $val ) {
                            if ( 'fraudulent' == $val['reason'] ) {
                                $is_fraud = true;
                                break;
                            }
                        }

                        if ( $is_fraud )
                            $order_statuses['charge.refunded'] = 'order_fraud';
                    }

                    if ( 'charge.captured' == $event_obj['type'] || 'payment_intent.succeeded' == $event_obj['type']) {
                        $tc->update_order_payment_status( $order_id, true );
                        unset( $_SESSION['stripe_payment_gateway'] );

                    } else {
                        wp_update_post( [ 'ID' => $order_id, 'post_status' => $order_statuses[ $event_obj['type'] ] ] );
                    }

                    TC_Order::add_order_note( $order_id,  $event_obj['type'] );
                }
            }

            echo http_response_code( 200 );
            exit;
        }


        /**
         * Validate Stripe Event. If the event is not found, it is an indication that the event is invalid.
         *
         * @param $event_id
         * @return Event
         */
        function tc_validate_stripe_event( $event_id ) {

            try {
                return Event::retrieve( $event_id );

            } Catch ( Exception $e ) {
                // Invalid Event
            }
        }
    }
}

