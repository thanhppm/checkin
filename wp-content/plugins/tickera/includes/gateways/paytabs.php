<?php
/*
  PayTabs - Payment Gateway
 */

class TC_Gateway_PayTabs extends TC_Gateway_API {

	var $plugin_name                = 'paytabs';
	var $admin_name                 = '';
	var $public_name                = '';
	var $method_img_url             = '';
	var $admin_img_url              = '';
	var $force_ssl                  = false;
	var $ipn_url;
	var $currencies                 = array();
	var $automatically_activated    = false;
	var $skip_payment_screen        = false;
	var $currency                   = '';
	var $language                   = 'English';
	var $merchant_email             = '';
	var $secret_key                 = '';
	var $site_url                   = '';
	var $gateway_url                = 'https://www.paytabs.com/apiv2/create_pay_page';
	var $verify_payment_url         = 'https://www.paytabs.com/apiv2/verify_payment';


    /**
     * Support for older payment gateway API
     */
	function on_creation() {
		$this->init();
	}


    /**
     * Initialize Variables
     */
	function init() {
		global $tc;

        // Register Ajax Actions
        add_action('wp_ajax_request_paytabs_paypage_ajax', array( $this, 'request_paytabs_paypage_ajax' ) );
        add_action('wp_ajax_nopriv_request_paytabs_paypage_ajax', array( $this, 'request_paytabs_paypage_ajax' ) );

		$this->admin_name       = __( 'PayTabs PayPage', 'tc' );
		$this->public_name      = __( 'PayTabs PayPage', 'tc' );
		$this->site_url         = get_site_url();

		$this->method_img_url   = apply_filters( 'tc_gatew$this->merchant_iday_method_img_url', $tc->plugin_url . 'images/gateways/paytabs.png', $this->plugin_name );
		$this->admin_img_url    = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paytabs.png', $this->plugin_name );

		$this->merchant_email   = $this->get_option( 'merchant_email' );
		$this->secret_key       = $this->get_option( 'secret_key' );
		$this->language		    = $this->get_option( 'language', 'English' );

        $this->currency         = $tc->get_store_currency();

        add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
	}


    /**
     * Load CSS and JS Files
     */
    function enqueue_scripts() {
        if ( $this->is_payment_page() && $this->is_active() ) {
            wp_enqueue_style('css-paytabs', plugins_url('paytabs/assets/css/paytabs.css',__FILE__));
            wp_register_script( 'js-paytabs',plugins_url('/paytabs/paytabs.js',__FILE__), array( 'jquery' ) );
        }
    }


    /**
     * Generate Payment Form
     *
     * @param $cart
     * @return string|void
     */
	function payment_form( $cart ) {
		global $tc;

        $this->maybe_start_session();
        $this->save_cart_info();

        // Redirects to Cart Page if First and last name and email are missing.
        if ( !$this->buyer_info('first_name') || !$this->buyer_info('last_name') || !$this->buyer_info('email') ) {
            $_SESSION[ 'tc_gateway_error' ] = __('Missing required fields. e.g. first name, last name, email');

            ob_start();
            @wp_redirect( $tc->get_cart_slug( true ) );
            @tc_js_redirect( $tc->get_cart_slug( true ) );
            exit;
        }

        $content = '';
        $content .= '<div id="paytabs_errors" class="paytabs_errors"></div>';
        $content .= '<table id="tbl_paytabs" class="tc_cart_billing"><thead><tr><th colspan="2">' . __( 'Billing Information', 'tc' ) . '</th></tr></thead>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-first-name">'. __('First Name', 'tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('first_name') .'<input type="paytabs-billing-first-name" class="form-control hd-hidden" id="paytabs-billing-first-name" value="'. $this->buyer_info('first_name') .'"><span id="help-paytabs-billing-first-name" class="help-block"></span>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-last-name">'. __('Last Name', 'tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('last_name') .'<input type="paytabs-billing-last-name" class="form-control hd-hidden" id="paytabs-billing-last-name" value="'. $this->buyer_info('last_name') .'"><span id="help-paytabs-billing-last-name" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-email">'. __('Email address','tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('email') .'<input type="paytabs-billing-email" class="form-control hd-hidden" id="paytabs-billing-email" value="'. $this->buyer_info('email') .'"><span id="help-paytabs-billing-email" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-phone">'. __('Phone Number','tc') .'</label></td>';
        $content .= '<td><input type="paytabs-billing-phone" class="form-control" id="paytabs-billing-phone" onkeypress="return isNumeric(event)"><span id="help-paytabs-billing-phone" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-street-address">'. __('Address Line 1','tc') .'</label></td>';
        $content .= '<td><input type="paytabs-billing-street-address" class="form-control" id="paytabs-billing-street-address"><span id="help-paytabs-billing-street-address" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-extended-address">'. __('Address Line 2','tc') .'</label></td>';
        $content .= '<td><input type="paytabs-billing-extended-address" class="form-control" id="paytabs-billing-extended-address"><span id="help-paytabs-billing-extended-address" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-city">'. __('City','tc') .'</label></td>';
        $content .= '<td><input type="paytabs-billing-city" class="form-control" id="paytabs-billing-city"><span id="help-paytabs-billing-city" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-country-code">'. __('Country Code','tc') .'</label></td><td>';
        $content .= '<select type="paytabs-billing-country-code" id="paytabs-billing-country-code" class="form-control"><option></option></select>';
        $content .= '<span id="help-paytabs-billing-country-code" class="help-block"></span>';
        $content .= '</td></tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-region">'. __('Region','tc') .'</label></td><td>';
        $content .= '<select type="paytabs-billing-region" id="paytabs-billing-region" class="form-control"><option></option></select>';
        $content .= '<span id="help-paytabs-billing-region" class="help-block"></span>';
        $content .= '</td></tr>';
        $content .= '<tr>';
        $content .= '<td><label for="paytabs-billing-postal-code">'. __('Postal Code','tc') .'</label></td>';
        $content .= '<td><input type="paytabs-billing-postal-code" class="form-control" id="paytabs-billing-postal-code"><span id="help-paytabs-billing-postal-code" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<div id="paytabs_overlay" style="display:none;"><img title="'. __('Loading...','tc') .'" src="'. plugins_url('paytabs/assets/images/loading.gif',__FILE__) . '"/></div>';

        // Define Country and Region data
        $country_data = $this->get_country_data();
        $region_data = $this->get_region_data();

        // Pass data to paytabs.js script
        $formData= array(
            'country_data' => json_decode( $country_data, true ),
            'region_data' => json_decode( $region_data, true ),
            'billing_error' => __('Field cannot be blank.', 'tc'),
        );

        $jsonData = json_encode($formData);
        $params = array($jsonData);

        wp_localize_script( 'js-paytabs',  'paytabs_params', $params );
        wp_enqueue_script( 'js-paytabs' );

        return $content;
    }


    /**
     * Request Paytabs' Paypage
     *
     * @return bool|void
     */
    function request_paytabs_paypage_ajax() {

        if ( isset( $_POST['action'] ) && 'request_paytabs_paypage_ajax' == $_POST['action'] ) {

            global $tc;

            $this->maybe_start_session();

            $post_data = ( isset( $_POST['paytabs_arguments'] ) ) ? (Array) $_POST['paytabs_arguments'] : null;
            $result = json_decode( self::sendRequest( $this->gateway_url, self::prepare_paytabs_arguments( $post_data ) ) , true );

            if ( $result ) {

                switch ( $result['response_code'] ) {

                    case '4012':

                        /*
                         * The Pay Page is created
                         * Order Note: Pay Page request result
                         */

                        $order_id = self::tc_create_order();
                        update_post_meta( $order_id, 'paytabs_p_id', $result['p_id'] );
                        TC_Order::add_order_note( $order_id, sprintf( __('%s %sView PayPage%s', 'tc'), $result['result'], '<a href="' . $result['payment_url'] . '" target="_blank">', '</a>' ) );
                        break;
                }

            } else {
                $result = [ 'result' => __( 'Sorry, something went wrong. Please try again.', 'tc') ];
            }

            wp_send_json( $result );
        }
    }


    /**
     * Verify Paytabs payment.
     * Set Order statuses base on result response
     *
     * @param $order
     * @param string $payment_info
     * @param string $cart_info
     */
    function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {

        global $tc;

        $order_id = tc_get_order_id_by_name( $order )->ID;
        $paytabs_p_id = get_post_meta( $order_id, 'paytabs_p_id', true );

        $verify_payment_arguments = array( 'merchant_email' => $this->merchant_email, 'secret_key' => $this->secret_key, 'payment_reference' => $paytabs_p_id );
        $verify_payment = json_decode( self::sendRequest( $this->verify_payment_url, $verify_payment_arguments  ), true );

        if ( $verify_payment ) {

            /*
             * Payment verification response codes
             * https://dev.paytabs.com/docs/verify/
             */
            $order_received = [ '115', '112', '1004' ];
            $order_refunded = [ '12' ];
            $order_cancelled = [ '116', '114', '110', '1100', '1003', '1000', '0404', '4003', '4002', '4001' ];
            $order_completed = [ '6', '11', '113', '111', '100' ];
            $order_fraud = [ '481', '482' ];

            if ( in_array( $verify_payment['response_code'], $order_completed ) ) {

                /* The payment is completed successfully! */
                $tc->update_order_payment_status( $order_id, true );

            } elseif ( in_array( $verify_payment['response_code'], $order_cancelled ) ) {

                /* Set order status to cancelled */
                wp_update_post( [ 'ID' => $order_id, 'post_status' => 'order_cancelled' ] );

            } elseif ( in_array( $verify_payment['response_code'], $order_received ) ) {

                /* Set order status as received */
                wp_update_post( [ 'ID' => $order_id, 'post_status' => 'order_received' ] );

            } elseif ( in_array( $verify_payment['response_code'], $order_refunded ) ) {

                /* The payment is refunded */
                wp_update_post( [ 'ID' => $order_id, 'post_status' => 'order_refunded' ] );

            } elseif ( in_array( $verify_payment['response_code'], $order_fraud ) ) {

                /* Payment Rejected (by fraud monitoring tools) */
                wp_update_post( [ 'ID' => $order_id, 'post_status' => 'order_fraud' ] );

            }

            $note = $verify_payment['result'];

        } else {
            $note = __( 'Unsuccessful order confirmation. Please visit PayPage to confirm.', 'tc' );
        }

        // Order Note: Payment verification result
        TC_Order::add_order_note( $order_id, $note );
    }


    /**
     * Create Tickera Order and Ticket Instances
     *
     * @return int
     */
    function tc_create_order() {

        global $tc;

        $this->maybe_start_session();
        $this->save_cart_info();

        tc_final_cart_check( $this->cart_contents() );
        $order_id = $tc->generate_order_id();

        $payment_info = array();
        $payment_info['method'] = __('Credit Card', 'tc');
        $payment_info = $this->save_payment_info( $payment_info );

        // Crate Tickera Order
        $tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, false );
        $order_id = tc_get_order_id_by_name( $order_id )->ID;

        // Initially set order to cancelled in order to avoid unnecessary committed stocks if the customer failed to send payment
        wp_update_post( [ 'ID' => $order_id, 'post_status' => 'order_cancelled' ] );

        return $order_id;
    }


    /**
     * Prepare the fields to be sent on paytabs
     *
     * @param $post_data
     * @return array
     */
    function prepare_paytabs_arguments( $post_data ) {
        global $tc;

        $this->maybe_start_session();
        $this->save_cart_info();
        $order_id = $tc->generate_order_id();

        $ticket_meta = [];
        foreach ( $this->cart_contents() as $ticket_type_id => $quantity ) {

            $post_meta = get_post_meta( $ticket_type_id );

            $ticket_meta['ticket'][] = get_the_title( $ticket_type_id );
            $ticket_meta['unit_price'][] = reset( $post_meta['price_per_ticket'] );
            $ticket_meta['quantity'][] = $quantity;
        }

        // Retrieve Client IP
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $client_ip = $_SERVER['HTTP_CLIENT_IP'];

        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }

        $other_charges = tc_is_tax_inclusive() ? $this->total_fees() : ( $this->total_fees() + $this->total_taxes() );

        return array (
            "merchant_email" => $this->merchant_email,
            "secret_key" => $this->secret_key,
            "site_url" => $this->site_url,
            "return_url" => $tc->get_confirmation_slug( true, $order_id ),
            "title" => $this->buyer_info( 'first_name' ) . ' ' . $this->buyer_info( 'last_name' ),
            "cc_first_name" => $this->buyer_info( 'first_name' ),
            "cc_last_name" => $this->buyer_info( 'last_name' ),
            "cc_phone_number" => $post_data['billing_phone'],
            "phone_number" => $post_data['billing_phone'],
            "email" => $this->buyer_info('email' ),
            "products_per_title" => implode( ' || ', $ticket_meta['ticket'] ),
            "unit_price" => implode( ' || ', $ticket_meta['unit_price'] ),
            "quantity" => implode( ' || ', $ticket_meta['quantity'] ),
            "other_charges" => $other_charges,
            "amount" => $this->subtotal() + $other_charges,
            "discount" => $this->discount(),
            "currency" => $this->currency,
            "reference_no" => $order_id,
            "ip_customer" => $client_ip,
            "ip_merchant" =>"1.1.1.0",
            "billing_address" => $post_data['billing_street_address'] . $post_data['billing_extended_address'],
            "city" => $post_data['billing_city'],
            "state" => $post_data['billing_region'],
            "postal_code" => $post_data['billing_postal_code'],
            "country" => $post_data['billing_country_code'],
            "shipping_first_name" => $this->buyer_info( 'first_name' ),
            "shipping_last_name" => $this->buyer_info( 'last_name' ),
            "address_shipping" => $post_data['billing_street_address'] . $post_data['billing_extended_address'],
            "state_shipping" => $post_data['billing_region'],
            "city_shipping" => $post_data['billing_city'],
            "postal_code_shipping" => $post_data['billing_postal_code'],
            "country_shipping" => $post_data['billing_country_code'],
            "msg_lang" => $this->language,
            "cms_with_version" => "WordPress " . get_bloginfo( 'version' ) . " - Tickera " . get_option( 'tc_version' )
        );
    }


    /**
     * Send Request
     *
     * @param $request_string
     * @return bool|string
     */
    function sendRequest( $gateway_url, $request_string ) {

        $ch	= @curl_init();
        @curl_setopt( $ch, CURLOPT_URL, $gateway_url );
        @curl_setopt( $ch, CURLOPT_POST, true );
        @curl_setopt( $ch, CURLOPT_POSTFIELDS, $request_string );
        @curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        @curl_setopt( $ch, CURLOPT_HEADER, false );
        @curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        @curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        @curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        @curl_setopt( $ch, CURLOPT_VERBOSE, true );
        $result	 = curl_exec( $ch );
        if ( !$result )
            die( curl_error( $ch ) );

        curl_close( $ch );

        return $result;
    }


    /**
     * Generate view for Admin Setting
     *
     * @param $settings
     * @param $visible
     */
	function gateway_admin_settings( $settings, $visible ) { ?>

		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3>
                <span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span>
                <span class="description"> <?php echo __( 'PayTabs works by sending the user to PayTabs to enter their payment information.', 'tc' ); ?> </span>
            </h3>
			<div class="inside">

				<?php
				$fields	 = array (
					'merchant_email' => array (
						'title'	 => __( 'Merchant email', 'tc' ),
						'type'	 => 'text',
					),
					'secret_key'	 => array (
						'title'	 => __( 'Secret Key', 'tc' ),
						'type'	 => 'text',
					),
					'language'	 => array (
						'title'		 => __( 'Language', 'tc' ),
						'type'		 => 'select',
						'options'	 => array ( 'English' => 'English', 'Arabic' => 'Arabic' ),
						'default'	 => 'English',
					)
				);
				$form	 = new TC_Form_Fields_API( $fields, 'tc', 'gateways', $this->plugin_name );
				?>
				<table class="form-table">
					<?php $form->admin_options(); ?>
				</table>
			</div>
		</div>
		<?php
	}

}

tc_register_gateway_plugin( 'TC_Gateway_PayTabs', 'paytabs', __( 'PayTabs', 'tc' ) );
?>