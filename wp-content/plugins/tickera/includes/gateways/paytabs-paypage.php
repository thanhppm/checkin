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
	var $skip_payment_screen        = true;
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
        add_action('wp_ajax_paytabs_collect_regions_ajax', array( &$this, 'paytabs_collect_regions_ajax' ) );
        add_action('wp_ajax_nopriv_paytabs_collect_regions_ajax', array( &$this, 'paytabs_collect_regions_ajax' ) );

        add_action('wp_ajax_request_paytabs_paypage_ajax', array( &$this, 'request_paytabs_paypage_ajax' ) );
        add_action('wp_ajax_nopriv_request_paytabs_paypage_ajax', array( &$this, 'request_paytabs_paypage_ajax' ) );

        // Register API Route
        add_action( 'rest_api_init', function () {
            register_rest_route( 'tc-paytabs-gateway/v1', '/callback/', array(
                'methods' => 'POST',
                'callback' =>  array($this, 'process_payment_api'),
            ) );
        } );

		$this->admin_name       = __( 'PayTabs', 'tc' );
		$this->public_name      = __( 'PayTabs', 'tc' );
		$this->site_url         = get_site_url();

		$this->method_img_url   = apply_filters( 'tc_gatew$this->merchant_iday_method_img_url', $tc->plugin_url . 'images/gateways/paytabs.png', $this->plugin_name );
		$this->admin_img_url    = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paytabs.png', $this->plugin_name );

		$this->merchant_email   = $this->get_option( 'merchant_email' );
		$this->secret_key       = $this->get_option( 'secret_key' );
		$this->language		    = $this->get_option( 'language', 'English' );

        $this->currency         = $tc->get_store_currency();

		$paytabs_languages = array(
			'English'	 => 'English',
			'Arabic'	 => 'Arabic'
		);

		$this->paytabs_languages = $paytabs_languages;

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


        // Retrieve json file for country code field
        $jsonFileUrl = plugins_url('paytabs/assets/json/country-code.json',__FILE__);
        $jsonFileData = self::retrieve_json_file($jsonFileUrl);

        $country_data = [];
        foreach($jsonFileData as $key => $val ) {
            $country_data[$key]['id'] = $val['countryShortCode'];
            $country_data[$key]['text'] = $val['countryName'] . " | " . $val['countryShortCode'];
        }

        // Pass data to paytabs.js script
        $formData= array(
            'country_data' => $country_data,
            'billing_error' => __('Field cannot be blank.', 'tc'),
        );
        $jsonData = json_encode($formData);
        $params = array($jsonData);

        wp_localize_script( 'js-paytabs',  'paytabs_params', $params );
        wp_enqueue_script( 'js-paytabs' );

        return $content;
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

    /**
     * Collect Region data based on selected country
     */
    function paytabs_collect_regions_ajax() {

        $selected_country = sanitize_text_field($_POST['paytabs_selected_country']);

        // Retrieve json file for country code field
        $jsonFileUrl = plugins_url('paytabs/assets/json/country-code.json',__FILE__);
        $country_data = $this->retrieve_json_file($jsonFileUrl);

        $regions = [];
        foreach ( $country_data as $key => $val ) {
            if ( $selected_country == $val['countryShortCode'] ) {
                foreach ( $val['regions'] as $inner_key => $inner_value) {
                    $regions[$inner_key]['id'] = $inner_value['name'];
                    $regions[$inner_key]['text'] = $inner_value['name'] . " | " . $inner_value['shortCode'];
                }
            }
        }
        wp_send_json($regions);
    }

    /**
     * Create an order and marked the status based on Paytabs request results
     */
    function process_payment_api() {
        global $tc;

        $this->maybe_start_session();
        $this->save_cart_info();

        $verify_payment_arguments = array( 'merchant_email' => $this->merchant_email, 'secret_key' => $this->secret_key, 'payment_reference' => $_SESSION['paytabs_p_id'] );
        $verify_payment = json_decode( self::sendRequest( $this->verify_payment_url, $verify_payment_arguments  ), true );

        if ( $verify_payment ) {

            tc_final_cart_check( $this->cart_contents() );
            $order_id = $tc->generate_order_id();

            $payment_info = array();
            $payment_info['method'] = __('Credit Card', 'tc');
            $payment_info['transaction_id'] = $verify_payment['result'];
            $payment_info = $this->save_payment_info($payment_info);

            // Crate Tickera Order
            $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, false);

            // The payment is completed successfully
            if ( $verify_payment['response_code'] == 100 ) {
                $tc->update_order_payment_status( tc_get_order_id_by_name( $order_id )->ID, true );
                unset( $_SESSION['paytabs_p_id'] );
            }

            // Reidrect to Confirmation Page upon transaction request completed
            ob_start();
            @wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
            @tc_js_redirect( $tc->get_confirmation_slug( true, $order_id ) );

            // Insert Order Note
            TC_Order::add_order_note( tc_get_order_id_by_name( $order_id )->ID,  $verify_payment['result'] );

        } else {

            $_SESSION[ 'tc_gateway_error' ] = __( 'Something went wrong. Please try again.', 'tc' );
            TC_Order::add_order_note( tc_get_order_id_by_name( $tc->generate_order_id() )->ID,  __( 'Something went wrong. Please check payment in your Paytabs account.', 'tc' ) );

            ob_start();
            @wp_redirect( $tc->get_cart_slug( true ) );
            tc_js_redirect( $tc->get_cart_slug( true ) );
            exit;
        }
    }

    /**
     * Request Paytabs a Paypage link
     * @param $cart
     * @return bool|void
     */
    function request_paytabs_paypage_ajax() {
        if ( isset( $_POST['action'] ) && 'request_paytabs_paypage_ajax' == $_POST['action'] ) {
            $post_data = ( isset( $_POST['paytabs_arguments'] ) ) ? (Array) $_POST['paytabs_arguments'] : null;
            $result = json_decode( self::sendRequest( $this->gateway_url, self::prepare_paytabs_arguments( $post_data ) ) , true );
            $_SESSION['paytabs_p_id'] = $result['p_id'];
            wp_send_json( $result );
        }
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

        $ticket_meta = [];
        foreach ( $this->cart_contents() as $ticket_type_id => $quantity ) {

            $post_meta = get_post_meta( $ticket_type_id );

            $ticket_meta['ticket'][] = get_the_title( $ticket_type_id );
            $ticket_meta['unit_price'][] = reset( $post_meta['price_per_ticket'] );
            $ticket_meta['quantity'][] = $quantity;
        }

        $client_ip = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $other_charges = tc_is_tax_inclusive() ? ( $this->total_fees() ) : ( $this->total_fees() + $this->total_taxes() );

        return array (
            "merchant_email" => $this->merchant_email,
            "secret_key" => $this->secret_key,
            "site_url" => $this->site_url,
            "return_url" => $this->site_url . '/wp-json/tc-paytabs-gateway/v1/callback/',
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
            "reference_no" => $tc->generate_order_id(),
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
	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span>
                        
                            <span class="description">
                                    <?php echo __( 'PayTabs works by sending the user to PayTabs to enter their payment information.', 'tc' ); ?>
                            </span>

                        </h3>
			<div class="inside">

				<?php
				$fields	 = array(
					'merchant_email' => array(
						'title'	 => __( 'Merchant email', 'tc' ),
						'type'	 => 'text',
					),
					'secret_key'	 => array(
						'title'	 => __( 'Secret Key', 'tc' ),
						'type'	 => 'text',
					),
					'language'	 => array(
						'title'		 => __( 'Language', 'tc' ),
						'type'		 => 'select',
						'options'	 => $this->paytabs_languages,
						'default'	 => 'English',
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

}

tc_register_gateway_plugin( 'TC_Gateway_PayTabs', 'paytabs', __( 'PayTabs', 'tc' ) );
?>