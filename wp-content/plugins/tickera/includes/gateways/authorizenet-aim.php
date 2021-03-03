<?php
/*
  Authorize.net (AIM) - Payment Gateway
 */

class TC_Gateway_AuthorizeNet_AIM extends TC_Gateway_API {

    const API_PRODUCTION = 'https://api.authorize.net/xml/v1/request.api';
    const API_TEST = 'https://apitest.authorize.net/xml/v1/request.api';

    var $plugin_name				= 'authorizenet-aim';
    var $admin_name				    = '';
    var $public_name				= '';
    var $method_img_url			    = '';
    var $admin_img_url			    = '';
    var $currencies				    = array();
    var $automatically_activated    = false;
    var $skip_payment_screen		= false;
    var $API_Login_ID, $API_Transaction_Key, $API_Endpoint, $currency, $force_ssl, $ipn_url, $additional_fields;

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

        $this->admin_name	 = __( 'Authorize.Net API', 'tc' );
        $this->public_name	 = __( 'Authorize.net', 'tc' );

        $this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/authorize.png', $this->plugin_name );
        $this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-authorize.png', $this->plugin_name );

        $this->API_Login_ID	        = $this->get_option( 'api_user' );
        $this->API_Transaction_Key	= $this->get_option( 'api_key' );
        $this->currency			    = $this->get_option( 'currency', 'USD' );
        $this->additional_fields    = $this->get_option( 'additional_fields', 'no' );
        $this->force_ssl            = ( $this->get_option( 'mode', 'sandbox' ) == 'sandbox' ) ? false : true;
        $this->API_Endpoint         = ( $this->force_ssl ) ? self::API_PRODUCTION : self::API_TEST;

        $currencies = array(
            'USD'	 => __( 'USD - U.S. Dollar', 'tc' ),
            'CAD'	 => __( 'CAD - Canadian Dollar', 'tc' ),
            'AUD'    => __( 'AUD - Australian Dollar', 'tc'),
            'NZD'    => __( 'NZD - New Zealand Dollar', 'tc'),
            'CHF'    => __( 'CHF - Swiss Franc', 'tc'),
            'DKK'    => __( 'DKK - Danish Krone', 'tc'),
            'EUR'    => __( 'EUR - Euro', 'tc'),
            'GBP'    => __( 'GBP - Pound Sterling', 'tc'),
            'NOK'    => __( 'NOK - Norwegian Krone', 'tc'),
            'PLN'    => __( 'PLN - Poland Złoty', 'tc'),
            'SEK'    => __( 'SEK - Swedish Krona', 'tc'),
        );

        $this->currencies = $currencies;

        add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
    }

    /**
     * Load CSS and JS Files
     */
    function enqueue_scripts() {
        if ( $this->is_payment_page() && $this->is_active() ) {
            wp_register_script( 'js-authorizenet',plugins_url('/authorizenet-aim/assets/js/authorizenet.js',__FILE__), array( 'jquery' ) );
            wp_enqueue_style('css-authorizenet', plugins_url( '/authorizenet-aim/assets/css/authorizenet.css',__FILE__));
        }
    }

    /**
     * Generate Payment Form
     * @param $cart
     * @return string|void
     */
    function payment_form( $cart ) {
        global $tc;

        $content = '';

        // IF: Additional fields is enabled
        if ( 'yes' == $this->additional_fields ) {
            $content .= $this->get_additional_fields();
        }

        $content .= '<table class="tc_cart_billing tbl_authorizenet">';
        $content .= '<thead>';
        $content .= '<tr>';
        $content .= '<th colspan="2">' . __( 'Pay with card:', 'tc' ) . '</th>';
        $content .= '</tr>';
        $content .= '</thead>';
        $content .= '<tbody>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-card-num">' . __( 'Credit Card Number:', 'tc' ) . '</label></td>';
        $content .= '<td><input name="authorize-card-num"  id="authorize-card-num" class="credit_card_number input_field noautocomplete" type="text" size="22" maxlength="22"/><span id="help-authorize-card-num" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label>' . __( 'Expiration Date:', 'tc' ) . '</label></td>';
        $content .= '<td>';
        $content .= '<div><label for="authorize-exp-month">' . __( 'Month', 'tc' ) . '</label><select name="authorize-exp-month" id="authorize-exp-month">' . tc_months_dropdown() . '</select><br><span id="help-authorize-exp-month" class="help-block"></span></div>';
        $content .= '<div><label for="authorize-exp-year">' . __( 'Year', 'tc' ) . '</label><select name="authorize-exp-year" id="authorize-exp-year">' . tc_years_dropdown( '', true ) . '</select><br><span id="help-authorize-exp-year" class="help-block"></span></div>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-card-code">' . __( 'CCV:', 'tc' ) . '</label></td>';
        $content .= '<td><input id="authorize-card-code" name="authorize-card-code" class="input_field noautocomplete" type="text" size="4" maxlength="4"/><span id="help-authorize-card-code" class="help-block"></td>';
        $content .= '</tr>';
        $content .= '</tbody></table>';

        return $content;
    }

    /**
     * Generate HTML for additional fields
     * @return string
     */
    function get_additional_fields() {

        $content = '<table class="tbl_authorizenet">';
        $content .= '<thead>';
        $content .= '<tr>';
        $content .= '<th colspan="2">' . __( 'Billing Information:', 'tc' ) . '</th>';
        $content .= '</tr>';
        $content .= '</thead><tbody>';
        $content .= '<tr>';
        $content .= '<td><label>'. __('First Name', 'tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('first_name') . '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label>'. __('Last Name', 'tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('last_name') . '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label>'. __('Email address','tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('email') .'</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-billing-address">' . __( 'Address', 'tc' ) . '</label></td>';
        $content .= '<td><input type="authorize-billing-address" id="authorize-billing-address" name="billing_address" class="input_field noautocomplete" type="text"/><span id="help-authorize-billing-address" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-billing-city">' . __( 'City', 'tc' ) . '</label></td>';
        $content .= '<td><input type="authorize-billing-city" id="authorize-billing-city" name="billing_city" class="input_field noautocomplete" type="text"/><span id="help-authorize-billing-city" class="help-block"></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-billing-country">' . __( 'Country', 'tc' ) . '</label></td>';
        $content .= '<td><select type="authorize-billing-country" id="authorize-billing-country" name="billing_country" class="input_field noautocomplete authorizenet_billing_country" type="text"><option></option></select><span id="help-authorize-billing-country" class="help-block"></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-billing-state">' . __( 'State', 'tc' ) . '</label></td>';
        $content .= '<td><select type="authorize-billing-state" id="authorize-billing-state" name="billing_state" class="input_field noautocomplete authorizenet_billing_state" type="text"><option></option></select><span id="help-authorize-billing-state" class="help-block"></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-billing-postal_code">' . __( 'Zip Code', 'tc' ) . '</label></td>';
        $content .= '<td><input type="authorize-billing-postal_code" id="authorize-billing-postal-code" name="billing_postal_code" class="input_field noautocomplete" type="text"/><span id="help-authorize-billing-postal-code" class="help-block"></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="authorize-billing-phone">' . __( 'Phone Number', 'tc' ) . '</td>';
        $content .= '<td><input type="authorize-billing-phone" id="authorize-billing-phone" name="billing_phone" class="input_field noautocomplete" type="text" onkeypress="return isNumeric(event)"/><span id="help-authorize-billing-phone" class="help-block"></td>';
        $content .= '</tr>';
        $content .= '</tbody></table>';

        // Retrieve json file for country code field
        $jsonFileUrl = plugins_url('authorizenet-aim/assets/json/country-code.json',__FILE__);
        $jsonFileData = $this->retrieve_json_file($jsonFileUrl);

        $country_data = [];
        foreach($jsonFileData as $key => $val ) {
            $country_data[$key]['id'] = $val['countryShortCode'];
            $country_data[$key]['text'] = $val['countryName'] . " | " . $val['countryShortCode'];
        }

        $formData= array(
            'country_data' => $country_data,
            'billing_error' => __('Field cannot be blank.', 'tc'),
        );
        $params = json_encode($formData);

        // Load script when payment method is selected
        wp_localize_script('js-authorizenet',  'authorizenet_params', $params);
        wp_enqueue_script('js-authorizenet');

        return $content;
    }

    /**
     * Process Payment and Create Tickera Order
     * @param $cart
     * @return bool|void
     */
    function process_payment( $cart ) {
        global $tc;

        tc_final_cart_check($cart);

        $this->maybe_start_session();
        $this->save_cart_info();

        $transaction = [];
        $transaction["createTransactionRequest"]["merchantAuthentication"]  = self::authorize_merchant(); // Authorize Merchant
        $transaction["createTransactionRequest"]["refId"]                   = 'ref' . time(); // Set the transaction's refId
        $transaction["createTransactionRequest"]["transactionRequest"]      = self::request_transaction();

        $args[ 'user-agent' ]	 = $tc->title;
        $args[ 'body' ]			 = json_encode($transaction);
        $args[ 'sslverify' ]	 = false;
        $args[ 'timeout' ]		 = 30;

        $response = wp_remote_post( $this->API_Endpoint, $args );

        // IF: Request successfully delivered to server
        if ( $response != null && !isset( $response->errors ) && isset( $response['body'] ) ) {

            $responseBody = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response['body']); // Removing BOM
            $responseBodyDecoded = json_decode($responseBody, true);

            // IF: Gateway successfully responded
            if ( $responseBodyDecoded != null && 'Ok' == $responseBodyDecoded['messages']['resultCode'] ) {

                // Create Tickera Order
                $order_id = $tc->generate_order_id();
                self::tc_create_order( $order_id, $responseBodyDecoded );

                // Redirects to success page
                ob_start();
                @wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
                tc_js_redirect( $tc->get_confirmation_slug( true, $order_id ) );

            // IF: Gateway unable to respond
            } else {
                $_SESSION['tc_gateway_error'] = sprintf(__('Sorry, something went wrong. %sPlease try again%s.', 'tc'), '<a href="' . $tc->get_cart_slug(true) . '">', '</a>');
                ob_start();
                @wp_redirect( $tc->get_payment_slug( true ) );
                tc_js_redirect( $tc->get_payment_slug( true ) );
            }

        // IF: Request failed
        } else {
            $_SESSION['tc_gateway_error'] = sprintf(__('Sorry, something went wrong. %sPlease try again%s.', 'tc'), '<a href="' . $tc->get_cart_slug(true) . '">', '</a>');
            ob_start();
            @wp_redirect( $tc->get_payment_slug( true ) );
            tc_js_redirect( $tc->get_payment_slug( true ) );
        }
        exit;
    }

    /**
     * Authorize Merchant before request transactions
     * @return array
     */
    function authorize_merchant() {
        return array( "name" => $this->API_Login_ID, "transactionKey" => $this->API_Transaction_Key );
    }

    /**
     * Prepare Object for a transaction request
     * @return array
     */
    function request_transaction() {
        global $tc;
        return array (
            "transactionType"   => "authCaptureTransaction",
            "amount"            =>  $this->total(),
            "currencyCode"      =>  $this->currency,
            "payment"           =>  self::create_payment_data(),
            "order"             =>  self::create_invoice(),
            "customer"          =>  self::create_customer(),
            "billTo"            =>  self::customer_bill_to_address(),
            "customerIP"        =>  $_SERVER[ 'REMOTE_ADDR' ],
        );
    }

    /**
     * Prepare Card Object
     * @return array
     */
    function create_payment_data() {
        $card_number = sanitize_text_field( $_POST['authorize-card-num'] );
        $expiration = sanitize_text_field( $_POST['authorize-exp-year'] ) . '-' . sanitize_text_field( $_POST['authorize-exp-month'] );
        $card_cvv = sanitize_text_field( $_POST['authorize-card-code'] );
        return array( "creditCard" => array( "cardNumber" => $card_number, "expirationDate" => $expiration, "cardCode" => $card_cvv ) );
    }

    /**
     * Prepare Billing To Object
     * @return array
     */
    function customer_bill_to_address()
    {
        $customerAddress = array ( "firstName" => $this->buyer_info("first_name"), "lastName" => $this->buyer_info("last_name") );
        if ('yes' == $this->additional_fields) {
            $billing_address = sanitize_text_field($_POST['billing_address']);
            $billing_state = sanitize_text_field($_POST['billing_state']);
            $billing_city = sanitize_text_field($_POST['billing_city']);
            $billing_postal_code = sanitize_text_field($_POST['billing_postal_code']);
            $billing_country = sanitize_text_field($_POST['billing_country']);
            $billing_phone = sanitize_text_field($_POST['billing_phone']);

            $customerAddress["address"] = $billing_address;
            $customerAddress["city"] = $billing_city;
            $customerAddress["state"] = $billing_state;
            $customerAddress["zip"] = $billing_postal_code;
            $customerAddress["country"] = $billing_country;
            $customerAddress["phoneNumber"] = $billing_phone;
        }
        return $customerAddress;
    }

    /**
     * Prepare Order Invoice Object
     * @return array
     */
    function create_invoice() {
        global $tc;
        return array ( "invoiceNumber" => apply_filters( 'tc_authorize_invoice_name', $tc->generate_order_id() ), "description" => $this->cart_items() );
    }

    /**
     * Prepare Customer Object
     * @return array
     */
    function create_customer() {
        return array ( "type" => "individual", "email" => $this->buyer_info('email') );
    }

    /**
     * Create Tickera Order if payment request is valid
     * @param $order_id
     * @param $response
     * @param bool $paid
     */
    function tc_create_order( $order_id, $response ) {
        global $tc;

        $tresponse = $response['transactionResponse'];

        // Create Tickera Order
        $payment_info = array();
        $payment_info['method'] = __( 'Credit Card', 'tc' );
        $payment_info['transaction_id']	 = $response['transactionResponse']['transId'];
        $payment_info = $this->save_payment_info($payment_info);

        // Create Tickera Order
        $tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, false );
        $order_id = tc_get_order_id_by_name($order_id)->ID;

        if ( $tresponse != null && isset($tresponse['messages']) ) {
            // Update Order Status to paid
            $tc->update_order_payment_status($order_id, true);

        } else {

            // Insert Order Note
            if ( $tresponse != null && isset($tresponse['errors']) ) {
                $note = $tresponse['errors'][0]['errorText'];
            } else {
                $note = $response['messages']['message'][0]['text'];
            }
            TC_Order::add_order_note( $order_id,  $note );
        }

    }

    /**
     * Generate view for Admin Setting
     * @param $settings
     * @param $visible
     */
    function gateway_admin_settings( $settings, $visible ) {
        global $tc;
        ?>
        <div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
            <h3>
                <span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span>
                <span class="description"><?php _e( 'A SSL certificate is required for live transactions.', 'tc' ) ?></span>
            </h3>
            <div class="inside">
                <?php
                $fields	 = array(
                    'mode'				 => array(
                        'title'		 => __( 'Mode', 'tc' ),
                        'type'		 => 'select',
                        'options'	 => array(
                            'sandbox'	 => __( 'Sandbox / Test', 'tc' ),
                            'live'		 => __( 'Live', 'tc' )
                        ),
                        'default'	 => 'sandbox',
                    ),
                    'api_user'			 => array(
                        'title'	 => __( 'Login ID', 'tc' ),
                        'type'	 => 'text',
                    ),
                    'api_key'			 => array(
                        'title'			 => __( 'Transaction Key', 'tc' ),
                        'type'			 => 'text',
                        'description'	 => '',
                        'default'		 => ''
                    ),
                    'additional_fields'	 => array(
                        'title'			 => __( 'Show Additional Fields (required by European merchants)', 'tc' ),
                        'type'			 => 'select',
                        'default'		 => 'no',
                        'options'		 => array(
                            'yes'	 => __( 'Yes', 'tc' ),
                            'no'	 => __( 'No', 'tc' )
                        ),
                        'description'	 => 'Fields added to checkout are billing information: Address, City, State, Zip Code, Country',
                        'default'		 => 'no'
                    ),
                    'currency'			 => array(
                        'title'		 => __( 'Currency', 'tc' ),
                        'type'		 => 'select',
                        'options'	 => $this->currencies,
                        'default'	 => 'USD',
                    ),
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

    /**
     * Retrive JSON file
     * @param $file
     * @return array|mixed
     */
    function retrieve_json_file($file) {
        $jsonFileRequest = wp_remote_get( $file );

        $jsonFileData = [];
        if( !is_wp_error( $jsonFileRequest ) ) {
            $jsonFileBody = wp_remote_retrieve_body( $jsonFileRequest );
            $jsonFileData = json_decode( $jsonFileBody, true );
        }
        return $jsonFileData;
    }
}

// Register payment gateway plugin
tc_register_gateway_plugin( 'TC_Gateway_AuthorizeNet_AIM', 'authorizenet-aim', __( 'Authorize.Net API', 'tc' ) );
?>