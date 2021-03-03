<?php
/*
    Braintree 3D Secure 2 - Payment Gateway
 */

class TC_Gateway_Braintree_3ds2 extends TC_Gateway_API {

    var $plugin_name		     = 'braintree_3ds2';
    var $admin_name				 = '';
    var $public_name		     = '';
    var $method_img_url			 = '';
    var $admin_img_url			 = '';
    var $force_ssl;
    var $ipn_url;
    var $merchant_key			 = '';
    var $public_key;
    var $private_key;
    var $cse_key;
    var $environment;
    var $clientToken;
    var $currency;
    var $totalAmount;
    var $currencies				 = array();
    var $automatically_activated	 = false;
    var $skip_payment_screen		 = false;

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

        require_once($tc->plugin_dir . "includes/gateways/braintree/lib/Braintree.php");

        // Register Ajax Actions
        add_action('wp_ajax_collect_regions_ajax', array(&$this, 'collect_regions_ajax') );
        add_action('wp_ajax_nopriv_collect_regions_ajax', array(&$this, 'collect_regions_ajax') );

        // Register API Route
        add_action( 'rest_api_init', function () {
            register_rest_route( 'tc-braintree-3ds2/v1', '/callback/', array(
                'methods' => 'POST',
                'callback' =>  array($this, 'process_payment'),
            ) );
        } );

        $this->admin_name	 = __( 'Braintree 3DS2', 'tc' );
        $this->public_name	 = __( 'Braintree 3D Secure 2', 'tc' );

        $this->method_img_url	 = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/braintree.png', $this->plugin_name );
        $this->admin_img_url	 = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-braintree-3ds2.png', $this->plugin_name );

        $this->merchant_key	 = $this->get_option( 'merchant_key' );
        $this->public_key	 = $this->get_option( 'public_key' );
        $this->private_key	 = $this->get_option( 'private_key' );
        $this->cse_key		 = $this->get_option( 'cse_key' );
        $this->force_ssl	 = $this->get_option( 'is_ssl', '0' );
        $this->environment	 = ($this->force_ssl == '1' ? 'production' : 'sandbox');
        $this->currency		 = $this->get_option( 'currency', 'USD' );

        $currencies = array(
            "AFA"	 => __( 'AFA - Afghanistan Afghani', 'tc' ),
            "ALL"	 => __( 'ALL - Albanian Lek', 'tc' ),
            "DZD"	 => __( 'DZD - Algerian dinar', 'tc' ),
            "ARS"	 => __( 'ARS - Argentine Peso', 'tc' ),
            "AMD"	 => __( 'AMD - Armenian dram', 'tc' ),
            "AWG"	 => __( 'AWG - Aruban Guilder', 'tc' ),
            "AUD"	 => __( 'AUD - Australian Dollar', 'tc' ),
            "AZN"	 => __( 'AZN - Azerbaijani an Manat', 'tc' ),
            "BSD"	 => __( 'BSD - Bahamian Dollar', 'tc' ),
            "BHD"	 => __( 'BHD - Bahraini Dinar', 'tc' ),
            "BDT"	 => __( 'BDT - Bangladeshi Taka', 'tc' ),
            "BBD"	 => __( 'BBD - Barbados Dollar', 'tc' ),
            "BYR"	 => __( 'BYR - Belarussian ruble', 'tc' ),
            "BZD"	 => __( 'BZD - Belizean dollar', 'tc' ),
            "BMD"	 => __( 'BMD - Bermudian Dollar', 'tc' ),
            "BOB"	 => __( 'BOB - Bolivian Boliviano', 'tc' ),
            "BWP"	 => __( 'BWP - Botswana Pula', 'tc' ),
            "BRL"	 => __( 'BRL - Brazilian Real', 'tc' ),
            "BND"	 => __( 'BND - Brunei Dollar', 'tc' ),
            "BGN"	 => __( 'BGN - Bulgarian Lev', 'tc' ),
            "BIF"	 => __( 'BIF - Burundi Franc', 'tc' ),
            "KHR"	 => __( 'KHR - Cambodian Riel', 'tc' ),
            "CAD"	 => __( 'CAD - Canadian Dollar', 'tc' ),
            "CVE"	 => __( 'CVE - Cape Verde Escudo', 'tc' ),
            "KYD"	 => __( 'KYD - Cayman Islands Dollar', 'tc' ),
            "XAF"	 => __( 'XAF - Central African Republic Franc BCEAO', 'tc' ),
            "XPF"	 => __( 'XPF - CFP Franc', 'tc' ),
            "CLP"	 => __( 'CLP - Chilean Peso', 'tc' ),
            "CNY"	 => __( 'CNY - Chinese Yuan Renminbi', 'tc' ),
            "COP"	 => __( 'COP - Colombian Peso', 'tc' ),
            "KMF"	 => __( 'KMF - Comoroan franc', 'tc' ),
            "BAM"	 => __( 'BAM - Convertible Marks', 'tc' ),
            "CRC"	 => __( 'CRC - Costa Rican Colon', 'tc' ),
            "HRK"	 => __( 'HRK - Croatian Kuna', 'tc' ),
            "CUP"	 => __( 'CUP - Cuban Peso', 'tc' ),
            "CYP"	 => __( 'CYP - Cyprus Pound', 'tc' ),
            "CZK"	 => __( 'CZK - Czech Republic Koruna', 'tc' ),
            "DKK"	 => __( 'DKK - Danish Krone', 'tc' ),
            "DJF"	 => __( 'DJF - Djiboutian franc', 'tc' ),
            "DOP"	 => __( 'DOP - Dominican Peso', 'tc' ),
            "XCD"	 => __( 'XCD - East Caribbean Dollar', 'tc' ),
            "ECS"	 => __( 'ECS - Ecuador', 'tc' ),
            "EGP"	 => __( 'EGP - Egyptian Pound', 'tc' ),
            "SVC"	 => __( 'SVC - El Salvador Colon', 'tc' ),
            "ERN"	 => __( 'ERN - Eritrea Nakfa', 'tc' ),
            "EEK"	 => __( 'EEK - Estonian Kroon', 'tc' ),
            "ETB"	 => __( 'ETB - Ethiopian Birr', 'tc' ),
            "EUR"	 => __( 'EUR - European Union Euro', 'tc' ),
            "FKP"	 => __( 'FKP - Falkland Islands Pound', 'tc' ),
            "FJD"	 => __( 'FJD - Fiji Dollar', 'tc' ),
            "CDF"	 => __( 'CDF - Franc Congolais', 'tc' ),
            "GMD"	 => __( 'GMD - Gambian Delasi', 'tc' ),
            "GEL"	 => __( 'GEL - Georgian Lari', 'tc' ),
            "GHS"	 => __( 'GHS - Ghanan Cedi', 'tc' ),
            "GIP"	 => __( 'GIP - Gibraltar Pound', 'tc' ),
            "GTQ"	 => __( 'GTQ - Guatemala Quetzal', 'tc' ),
            "GNF"	 => __( 'GNF - Guinea Franc', 'tc' ),
            "GWP"	 => __( 'GWP - Guinea-Bissau Peso', 'tc' ),
            "GYD"	 => __( 'GYD - Guyanese dollar', 'tc' ),
            "HTG"	 => __( 'HTG - Haitian Gourde', 'tc' ),
            "HNL"	 => __( 'HNL - Honduras Lempira', 'tc' ),
            "HKD"	 => __( 'HKD - Hong Kong Dollar', 'tc' ),
            "HUF"	 => __( 'HUF - Hungarian Forint', 'tc' ),
            "ISK"	 => __( 'ISK - Iceland Krona', 'tc' ),
            "INR"	 => __( 'INR - Indian Rupee', 'tc' ),
            "IDR"	 => __( 'IDR - Indonesian Rupiah', 'tc' ),
            "IRR"	 => __( 'IRR - Iranian Rial', 'tc' ),
            "IQD"	 => __( 'IQD - Iraqi Dinar', 'tc' ),
            "ILS"	 => __( 'ILS - Israeli shekel', 'tc' ),
            "JMD"	 => __( 'JMD - Jamaican Dollar', 'tc' ),
            "JPY"	 => __( 'JPY - Japanese Yen', 'tc' ),
            "JOD"	 => __( 'JOD - Jordanian Dinar', 'tc' ),
            "KZT"	 => __( 'KZT - Kazakhstan Tenge', 'tc' ),
            "KES"	 => __( 'KES - Kenyan Shilling', 'tc' ),
            "KWD"	 => __( 'KWD - Kuwaiti Dinar', 'tc' ),
            "AOA"	 => __( 'AOA - Kwanza', 'tc' ),
            "KGS"	 => __( 'KGS - Kyrgyzstan Som', 'tc' ),
            "KIP"	 => __( 'KIP - Laos Kip', 'tc' ),
            "LAK"	 => __( 'LAK - Laosian kip', 'tc' ),
            "LVL"	 => __( 'LVL - Latvia Lat', 'tc' ),
            "LBP"	 => __( 'LBP - Lebanese Pound', 'tc' ),
            "LRD"	 => __( 'LRD - Liberian Dollar', 'tc' ),
            "LYD"	 => __( 'LYD - Libyan Dinar', 'tc' ),
            "LTL"	 => __( 'LTL - Lithuania Litas', 'tc' ),
            "LSL"	 => __( 'LSL - Loti', 'tc' ),
            "MOP"	 => __( 'MOP - Macanese Pataca', 'tc' ),
            "MOP"	 => __( 'MOP - Macao', 'tc' ),
            "MKD"	 => __( 'MKD - Macedonian Denar', 'tc' ),
            "MGF"	 => __( 'MGF - Madagascar Malagasy Franc', 'tc' ),
            "MGA"	 => __( 'MGA - Malagasy Ariary', 'tc' ),
            "MWK"	 => __( 'MWK - Malawi Kwacha', 'tc' ),
            "MYR"	 => __( 'MYR - Malaysia Ringgit', 'tc' ),
            "MVR"	 => __( 'MVR - Maldiveres Rufiyaa', 'tc' ),
            "MTL"	 => __( 'MTL - Maltese Lira', 'tc' ),
            "MRO"	 => __( 'MRO - Mauritanian Ouguiya', 'tc' ),
            "MUR"	 => __( 'MUR - Mauritius Rupee', 'tc' ),
            "MXN"	 => __( 'MXN - Mexican Peso', 'tc' ),
            "MDL"	 => __( 'MDL - Moldova Leu', 'tc' ),
            "MNT"	 => __( 'MNT - Mongolia Tugrik', 'tc' ),
            "MAD"	 => __( 'MAD - Moroccan Dirham', 'tc' ),
            "MZM"	 => __( 'MZM - Mozambique Metical', 'tc' ),
            "MMK"	 => __( 'MMK - Myanmar Kyat', 'tc' ),
            "NAD"	 => __( 'NAD - Namibia Dollar', 'tc' ),
            "NPR"	 => __( 'NPR - Nepalese Rupee', 'tc' ),
            "ANG"	 => __( 'ANG - Netherlands Antillean Guilder', 'tc' ),
            "PGK"	 => __( 'PGK - New Guinea kina', 'tc' ),
            "TWD"	 => __( 'TWD - New Taiwan Dollar', 'tc' ),
            "TRY"	 => __( 'TRY - New Turkish Lira', 'tc' ),
            "NZD"	 => __( 'NZD - New Zealand Dollar', 'tc' ),
            "NIO"	 => __( 'NIO - Nicaraguan Cordoba', 'tc' ),
            "NGN"	 => __( 'NGN - Nigeria Naira', 'tc' ),
            "KPW"	 => __( 'KPW - North Korea Won', 'tc' ),
            "NOK"	 => __( 'NOK - Norway Krone', 'tc' ),
            "PKR"	 => __( 'PKR - Pakistan Rupee', 'tc' ),
            "PAB"	 => __( 'PAB - Panama Balboa', 'tc' ),
            "PYG"	 => __( 'PYG - Paraguayan guarani', 'tc' ),
            "PEN"	 => __( 'PEN - Peru Nuevo Sol', 'tc' ),
            "PHP"	 => __( 'PHP - Philippine Peso', 'tc' ),
            "PLN"	 => __( 'PLN - Poland Zloty', 'tc' ),
            "QAR"	 => __( 'QAR - Qatari Rial', 'tc' ),
            "OMR"	 => __( 'OMR - Rial Omani', 'tc' ),
            "RON"	 => __( 'RON - Romanian leu', 'tc' ),
            "RUB"	 => __( 'RUB - Russian Ruble', 'tc' ),
            "RWF"	 => __( 'RWF - Rwanda Franc', 'tc' ),
            "WST"	 => __( 'WST - Samoan Tala', 'tc' ),
            "STD"	 => __( 'STD - Sao Tome &amp;amp; Principe Dobra', 'tc' ),
            "SAR"	 => __( 'SAR - Saudi Arabian riyal', 'tc' ),
            "RSD"	 => __( 'RSD - Serbian Dinar', 'tc' ),
            "SCR"	 => __( 'SCR - Seychelles Rupee', 'tc' ),
            "SLL"	 => __( 'SLL - Sierra Leone Leone', 'tc' ),
            "SGD"	 => __( 'SGD - Singapore Dollar', 'tc' ),
            "SKK"	 => __( 'SKK - Slovak Koruna Euro', 'tc' ),
            "SIT"	 => __( 'SIT - Slovenian Tolar', 'tc' ),
            "SBD"	 => __( 'SBD - Solomon Islands Dollar', 'tc' ),
            "SOS"	 => __( 'SOS - Somalia Shilling', 'tc' ),
            "ZAR"	 => __( 'ZAR - South Africa Rand', 'tc' ),
            "KRW"	 => __( 'KRW - South Korean Won', 'tc' ),
            "LKR"	 => __( 'LKR - Sri Lanka Rupee', 'tc' ),
            "SHP"	 => __( 'SHP - St. Helena Pound', 'tc' ),
            "SDD"	 => __( 'SDD - Sudanese Dollar', 'tc' ),
            "SRD"	 => __( 'SRD - Suriname Dollar', 'tc' ),
            "SZL"	 => __( 'SZL - Swaziland Lilangeni', 'tc' ),
            "SEK"	 => __( 'SEK - Sweden Krona', 'tc' ),
            "CHF"	 => __( 'CHF - Switzerland Franc', 'tc' ),
            "SYP"	 => __( 'SYP - Syrian Arab Republic Pound', 'tc' ),
            "TJS"	 => __( 'TJS - Tajikistani Somoni', 'tc' ),
            "TZS"	 => __( 'TZS - Tanzanian Shilling', 'tc' ),
            "THB"	 => __( 'THB - Thailand Baht', 'tc' ),
            "TOP"	 => __( 'TOP - Tonga Pa&#x27;anga', 'tc' ),
            "TTD"	 => __( 'TTD - Trinidad and Tobago Dollar', 'tc' ),
            "TMM"	 => __( 'TMM - Turkmenistan Manat', 'tc' ),
            "TND"	 => __( 'TND - Tunisian Dinar', 'tc' ),
            "UGX"	 => __( 'UGX - Uganda Shilling', 'tc' ),
            "UAH"	 => __( 'UAH - Ukraine Hryvnia', 'tc' ),
            "AED"	 => __( 'AED - United Arab Emirates Dirham', 'tc' ),
            "GBP"	 => __( 'GBP - United Kingdom Sterling Pound', 'tc' ),
            "USD"	 => __( 'USD - United States Dollar', 'tc' ),
            "UYU"	 => __( 'UYU - Uruguayo Peso', 'tc' ),
            "UZS"	 => __( 'UZS - Uzbekistan Som', 'tc' ),
            "VUV"	 => __( 'VUV - Vanuatu Vatu', 'tc' ),
            "VEF"	 => __( 'VEF - Venezuela Bolivar Fuerte', 'tc' ),
            "VND"	 => __( 'VND - Vietnam Dong', 'tc' ),
            "XOF"	 => __( 'XOF - West African CFA Franc BCEAO', 'tc' ),
            "YER"	 => __( 'YER - Yemeni Rial', 'tc' ),
            "ZMK"	 => __( 'ZMK - Zambian Kwacha', 'tc' ),
            "ZWD"	 => __( 'ZWD - Zimbabwean dollar', 'tc' ),
        );

        $this->currencies = $currencies;

        add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

    }

    /**
     * Load CSS and JS Files
     */
    function enqueue_scripts() {
        if ( $this->is_payment_page() && $this->is_active() ) {
            wp_register_script( 'js-dropin', 'https://js.braintreegateway.com/web/dropin/1.21.0/js/dropin.min.js', array( 'jquery' ) );
            wp_register_script( 'js-braintree-3ds2',plugins_url('/braintree/braintree.js',__FILE__), array( 'jquery' ) );
            wp_enqueue_style('css-braintree-3ds2', plugins_url('braintree/assets/css/braintree.css',__FILE__));
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
        $content .= '<div id="payment_method_errors"></div>';
        $content .= '<div id="braintree_preload"><img src="'. plugins_url('braintree/assets/images/loading_small.gif',__FILE__) .'" title="'. __('loading...','tc') .'" /></div>';
        $content .= '<table id="tbl_braintree" class="tc_cart_billing"><thead><tr><th colspan="2">' . __( 'Billing Information', 'tc' ) . '</th></tr></thead>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-first-name">'. __('First Name', 'tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('first_name') .'<input type="billing-first-name" class="form-control hd-hidden" id="billing-first-name" value="'. $this->buyer_info('first_name') .'"><span id="help-billing-first-name" class="help-block"></span>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-last-name">'. __('Last Name', 'tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('last_name') .'<input type="billing-last-name" class="form-control hd-hidden" id="billing-last-name" value="'. $this->buyer_info('last_name') .'"><span id="help-billing-last-name" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-email">'. __('Email address','tc') .'</label></td>';
        $content .= '<td>'. $this->buyer_info('email') .'<input type="billing-email" class="form-control hd-hidden" id="billing-email" value="'. $this->buyer_info('email') .'"><span id="help-billing-email" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-phone">'. __('Phone Number','tc') .'</label></td>';
        $content .= '<td><input type="billing-phone" class="form-control" id="billing-phone" onkeypress="return isNumeric(event)"><span id="help-billing-phone" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-street-address">'. __('Address Line 1','tc') .'</label></td>';
        $content .= '<td><input type="billing-street-address" class="form-control" id="billing-street-address"><span id="help-billing-street-address" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-extended-address">'. __('Address Line 2','tc') .'</label></td>';
        $content .= '<td><input type="billing-extended-address" class="form-control" id="billing-extended-address"><span id="help-billing-extended-address" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-city">'. __('City','tc') .'</label></td>';
        $content .= '<td><input type="billing-city" class="form-control" id="billing-city"><span id="help-billing-city" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-country-code">'. __('Country Code','tc') .'</label></td><td>';
        $content .= '<select type="billing-country-code" id="billing-country-code" class="form-control"><option></option></select>';
        $content .= '<span id="help-billing-country-code" class="help-block"></span>';
        $content .= '</td></tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-region">'. __('Region','tc') .'</label></td><td>';
        $content .= '<select type="billing-region" id="billing-region" class="form-control"><option></option></select>';
        $content .= '<span id="help-billing-region" class="help-block"></span>';
        $content .= '</td></tr>';
        $content .= '<tr>';
        $content .= '<td><label for="billing-postal-code">'. __('Postal Code','tc') .'</label></td>';
        $content .= '<td><input type="billing-postal-code" class="form-control" id="billing-postal-code"><span id="help-billing-postal-code" class="help-block"></span></td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<div class="input-group pay-group bt-drop-in-container">';
        $content .= '<div class="row">';
        $content .= '<div class="col-md-12"><div id="drop-in"></div></div>';
        $content .= '</div>';
        $content .= '<div class="row">';
        $content .= '<input disabled id="pay-btn-3ds2" class="btn btn-success" type="submit" value="'. __('Loading...','tc') .'" style="display:none;">';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '<div id="braintree_overlay" style="display:none;"><img title="'. __('Loading...','tc') .'" src="'. plugins_url('braintree/assets/images/loading.gif',__FILE__) . '"/></div>';

        // Check Authentication
        try {
            $gateway = $this->braintree_gateway();
            $this->clientToken = $gateway->clientToken()->generate();
        } Catch (Exception $e) {
            $_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'Error: "%s".', 'tc' ), $e->getMessage());
        }

        // Retrieve json file for country code field
        $jsonFileUrl = plugins_url('braintree/assets/json/country-code.json',__FILE__);
        $jsonFileData = $this->retrieve_json_file($jsonFileUrl);

        $country_data = [];
        foreach($jsonFileData as $key => $val ) {
            $country_data[$key]['id'] = $val['countryShortCode'];
            $country_data[$key]['text'] = $val['countryName'] . " | " . $val['countryShortCode'];
        }

        // Pass data to braintree.js script
        $total = $_SESSION['cart_info']['total'];
        $cart_total = (intval($total) || floatval($total)) ? $total : null;
        $formData= array(
            'country_data' => $country_data,
            'token' => $this->clientToken,
            'amount' => $cart_total,
            'callback' => get_site_url() . '/wp-json/tc-braintree-3ds2/v1/callback/',
            'billing_error' => __('Field cannot be blank.', 'tc'),
            'process_error' => __('Invalid transaction: Liability did not shift. Please contact your payment provider for more details', 'tc'),
            'verification_success' => __('verification success:', 'tc'),
            'tokenization_error' => __('Tokenization error: ', 'tc'),
            'liability_shifted' => __('Liability shifted: ', 'tc'),
            'component_error' => __('component error:', 'tc'),
            'processing' => __('Processing...', 'tc'),
            'pay_now' => __('Pay Now', 'tc')
        );
        $jsonData = json_encode($formData);
        $params = array($jsonData);

        // Load script when payment method is selected
        wp_enqueue_script('js-dropin');
        wp_localize_script('js-braintree-3ds2',  'braintree_params', $params);
        wp_enqueue_script('js-braintree-3ds2');

        return $content;
    }

    /**
     * Process Payment and Create Tickera Order
     * @param $cart
     * @return bool|void
     */
    function process_payment( $cart )
    {
        global $tc;

        tc_final_cart_check($cart);

        $payment_method_nonce = $_POST['nonce'];

        $gateway = $this->braintree_gateway();

        $this->maybe_start_session();
        $this->save_cart_info();

        $order_id = $tc->generate_order_id();

        $total = $_SESSION['cart_info']['total'];
        $result = $gateway->transaction()->sale([
            'amount' => (intval($total) || floatval($total)) ? $total : null, // Set as null to return a Transaction Error
            'orderId' => $order_id,
            'customer' => array(
                'firstName' => $this->buyer_info('first_name'),
                'lastName' => $this->buyer_info('last_name'),
                'email' => $this->buyer_info('email')
            ),
            'paymentMethodNonce' => $payment_method_nonce,
            'options' => ['submitForSettlement' => apply_filters('tc_braintree_settle_payment', true)],
            'channel' => 'Tickera_SP'
        ]);

        if ($result->success) {
            $payment_info = array();
            $payment_info['method'] = __('Credit Card', 'tc');
            $payment_info['transaction_id'] = $result->transaction->id;
            $payment_info = $this->save_payment_info($payment_info);
            $paid = true;
            $order = $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

            return $tc->get_confirmation_slug( true, $order_id );
        } else {
            $_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'Error processing transaction: "%s".', 'tc' ), $result->message );
            return false;
        }
    }

    /**
     * Initialize Gateway Connection
     * @return Braintree_Gateway
     */
    function braintree_gateway() {

        $gateway = new Braintree_Gateway([
            'environment' => $this->environment,
            'merchantId' => $this->merchant_key,
            'publicKey' => $this->public_key,
            'privateKey' => $this->private_key
        ]);
        return $gateway;
    }

    /**
     * Collect Region data based on selected country
     */
    function collect_regions_ajax() {

        $selected_country = sanitize_text_field($_POST['selected_country']);

        // Retrieve json file for country code field
        $jsonFileUrl = plugins_url('braintree/assets/json/country-code.json',__FILE__);
        $country_data = $this->retrieve_json_file($jsonFileUrl);

        $regions = [];
        foreach ( $country_data as $key => $val ) {
            if ( $selected_country == $val['countryShortCode'] ) {
                foreach ( $val['regions'] as $inner_key => $inner_value) {
                    $regions[$inner_key]['id'] = $inner_value['shortCode'];
                    $regions[$inner_key]['text'] = $inner_value['name'] . " | " . $inner_value['shortCode'];
                }
            }
        }
        wp_send_json($regions);
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
     * Generate view for Admin Setting
     * @param $settings
     * @param $visible
     */
    function gateway_admin_settings( $settings, $visible ) {
        global $tc;
        ?>
        <div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
            <h3><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span>
                <span class="description"><?php _e( 'Accept credit and debit cards (Visa, MasterCard, AmEx, Discover, JCB, Maestro and UnionPay)', 'tc' ) ?></span>
            </h3>
            <div class="inside">
                <?php
                $fields = array(
                    'is_ssl'		 => array(
                        'title'		 => __( 'Mode', 'tc' ),
                        'type'		 => 'select',
                        'options'	 => array(
                            '0'	 => __( 'Sandbox / Test', 'tc' ),
                            '1'	 => __( 'Live (Force SSL)', 'tc' )
                        ),
                        'default'	 => '0',
                    ),
                    'merchant_key'	 => array(
                        'title'	 => __( 'Merchant Key', 'tc' ),
                        'type'	 => 'text',
                    ),
                    'private_key'	 => array(
                        'title'	 => __( 'Private Key', 'tc' ),
                        'type'	 => 'text',
                    ),
                    'public_key'	 => array(
                        'title'	 => __( 'Public Key', 'tc' ),
                        'type'	 => 'text',
                    ),
                    'cse_key'		 => array(
                        'title'	 => __( 'CSE Key', 'tc' ),
                        'type'	 => 'text',
                    ),
                    'currency'		 => array(
                        'title'		 => __( 'Currency', 'tc' ),
                        'type'		 => 'select',
                        'options'	 => $this->currencies,
                        'default'	 => 'USD',
                    ),
                );

                $form = new TC_Form_Fields_API( $fields, 'tc', 'gateways', $this->plugin_name );
                ?>
                <table class="form-table">
                    <?php $form->admin_options(); ?>
                </table>

            </div>
        </div>
        <?php
    }
}

tc_register_gateway_plugin( 'TC_Gateway_Braintree_3ds2', 'braintree_3ds2', __( 'Braintree 3DS2', 'tc' ) );
?>