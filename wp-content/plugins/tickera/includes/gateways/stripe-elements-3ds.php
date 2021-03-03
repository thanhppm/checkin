<?php
/*
Stripe Elements 3D Secure - Payment Gateway
*/
global $tc;

class TC_Gateway_Stripe_Elements_3DS extends TC_Gateway_API {

  var $plugin_name = 'stripe-elements-3d-secure';
  var $admin_name = '';
  var $public_name = '';
  var $method_img_url = '';
  var $admin_img_url = '';
  var $force_ssl;
  var $ipn_url;
  var $publishable_key, $private_key, $currency;
  var $currencies = array();
  var $automatically_activated = false;
  var $skip_payment_screen = false;

  /**
   * Support for older payment gateway API
   */
  public function on_creation() {
    $this->init();
  }

  /**
   * Initialize Stripe Files
   */
  public function init_stripe(){
    global $tc;

    if (!class_exists('TCStripe\Stripe') && !class_exists('Stripe') && !class_exists('TCStripe\\Stripe')) { // Load Stripe classes only if it doesn't exist already
      require_once $tc->plugin_dir . 'includes/gateways/stripe/init.php';
    }
    \TCStripe\Stripe::setApiKey($this->private_key);
  }

  /**
   * Initialize Variables
   */
  public function init(){
    global $tc;

    // Register Ajax Actions
    add_action('wp_ajax_process_payment', array(&$this, 'process_payment_ajax') );
    add_action('wp_ajax_nopriv_process_payment', array(&$this, 'process_payment_ajax') );
    add_action('wp_ajax_order_confirmation', array(&$this, 'order_confirmation_ajax') );
    add_action('wp_ajax_nopriv_order_confirmation', array(&$this, 'order_confirmation_ajax') );

    $this->admin_name = __('Stripe Elements 3D Secure', 'tc');
    $this->public_name = __('Stripe Elements 3D Secure', 'tc');

    $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/stripe.png', $this->plugin_name);
    $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-stripe-elements-3ds.png', $this->plugin_name);

    $this->publishable_key = $this->get_option('publishable_key');
    $this->private_key = $this->get_option('private_key');
    $this->force_ssl = $this->get_option('is_ssl', '0') === '1';
    $this->currency = $this->get_option('currency', 'USD');
    $this->zero_decimal_currencies = array('MGA', 'BIF', 'CLP', 'PYG', 'DJF', 'RWF', 'GNF', 'UGX', 'JPY', 'VND', 'VUV', 'XAF', 'KMF', 'KRW', 'XOF', 'XPF');

    $this->send_receipt = $this->get_option('send_receipt', '0');

    $currencies = array(
      'AED' => __('AED - United Arab Emirates Dirham', 'tc'),
      'AFN' => __('AFN - Afghan Afghani', 'tc'),
      'ALL' => __('ALL - Albanian Lek', 'tc'),
      'AMD' => __('AMD - Armenian Dram', 'tc'),
      'ANG' => __('ANG - Netherlands Antillean Gulden', 'tc'),
      'AOA' => __('AOA - Angolan Kwanza', 'tc'),
      'ARS' => __('ARS - Argentine Peso', 'tc'),
      'AUD' => __('AUD - Australian Dollar', 'tc'),
      'AWG' => __('AWG - Aruban Florin', 'tc'),
      'AZN' => __('AZN - Azerbaijani Manat', 'tc'),
      'BAM' => __('BAM - Bosnia & Herzegovina Convertible Mark', 'tc'),
      'BBD' => __('BBD - Barbadian Dollar', 'tc'),
      'BDT' => __('BDT - Bangladeshi Taka', 'tc'),
      'BGN' => __('BGN - Bulgarian Lev', 'tc'),
      'BIF' => __('BIF - Burundian Franc', 'tc'),
      'BMD' => __('BMD - Bermudian Dollar', 'tc'),
      'BND' => __('BND - Brunei Dollar', 'tc'),
      'BOB' => __('BOB - Bolivian Boliviano', 'tc'),
      'BRL' => __('BRL - Brazilian Real', 'tc'),
      'BSD' => __('BSD - Bahamian Dollar', 'tc'),
      'BWP' => __('BWP - Botswana Pula', 'tc'),
      'BZD' => __('BZD - Belize Dollar', 'tc'),
      'CAD' => __('CAD - Canadian Dollar', 'tc'),
      'CDF' => __('CDF - Congolese Franc', 'tc'),
      'CHF' => __('CHF - Swiss Franc', 'tc'),
      'CLP' => __('CLP - Chilean Peso', 'tc'),
      'CNY' => __('CNY - Chinese Renminbi Yuan', 'tc'),
      'COP' => __('COP - Colombian Peso', 'tc'),
      'CRC' => __('CRC - Costa Rican Colon', 'tc'),
      'CVE' => __('CVE - Cape Verdean Escudo', 'tc'),
      'CZK' => __('CZK - Czech Koruna', 'tc'),
      'DJF' => __('DJF - Djiboutian Franc', 'tc'),
      'DKK' => __('DKK - Danish Krone', 'tc'),
      'DOP' => __('DOP - Dominican Peso', 'tc'),
      'DZD' => __('DZD - Algerian Dinar', 'tc'),
      'EEK' => __('EEK - Estonian Kroon', 'tc'),
      'EGP' => __('EGP - Egyptian Pound', 'tc'),
      'ETB' => __('ETB - Ethiopian Birr', 'tc'),
      'EUR' => __('EUR - Euro', 'tc'),
      'FJD' => __('FJD - Fijian Dollar', 'tc'),
      'FKP' => __('FKP - Falkland Islands Pound', 'tc'),
      'GBP' => __('GBP - British Pound', 'tc'),
      'GEL' => __('GEL - Georgian Lari', 'tc'),
      'GIP' => __('GIP - Gibraltar Pound', 'tc'),
      'GMD' => __('GMD - Gambian Dalasi', 'tc'),
      'GNF' => __('GNF - Guinean Franc', 'tc'),
      'GTQ' => __('GTQ - Guatemalan Quetzal', 'tc'),
      'GYD' => __('GYD - Guyanese Dollar', 'tc'),
      'HKD' => __('HKD - Hong Kong Dollar', 'tc'),
      'HNL' => __('HNL - Honduran Lempira', 'tc'),
      'HRK' => __('HRK - Croatian Kuna', 'tc'),
      'HTG' => __('HTG - Haitian Gourde', 'tc'),
      'HUF' => __('HUF - Hungarian Forint', 'tc'),
      'IDR' => __('IDR - Indonesian Rupiah', 'tc'),
      'ILS' => __('ILS - Israeli New Sheqel', 'tc'),
      'INR' => __('INR - Indian Rupee', 'tc'),
      'ISK' => __('ISK - Icelandic Krona', 'tc'),
      'JMD' => __('JMD - Jamaican Dollar', 'tc'),
      'JPY' => __('JPY - Japanese Yen', 'tc'),
      'KES' => __('KES - Kenyan Shilling', 'tc'),
      'KGS' => __('KGS - Kyrgyzstani Som', 'tc'),
      'KHR' => __('KHR - Cambodian Riel', 'tc'),
      'KMF' => __('KMF - Comorian Franc', 'tc'),
      'KRW' => __('KRW - South Korean Won', 'tc'),
      'KYD' => __('KYD - Cayman Islands Dollar', 'tc'),
      'KZT' => __('KZT - Kazakhstani Tenge', 'tc'),
      'LAK' => __('LAK - Lao Kip', 'tc'),
      'LBP' => __('LBP - Lebanese Pound', 'tc'),
      'LKR' => __('LKR - Sri Lankan Rupee', 'tc'),
      'LRD' => __('LRD - Liberian Dollar', 'tc'),
      'LSL' => __('LSL - Lesotho Loti', 'tc'),
      'LTL' => __('LTL - Lithuanian Litas', 'tc'),
      'LVL' => __('LVL - Latvian Lats', 'tc'),
      'MAD' => __('MAD - Moroccan Dirham', 'tc'),
      'MDL' => __('MDL - Moldovan Leu', 'tc'),
      'MGA' => __('MGA - Malagasy Ariary', 'tc'),
      'MKD' => __('MKD - Macedonian Denar', 'tc'),
      'MNT' => __('MNT - Mongolian TÃ¶grÃ¶g', 'tc'),
      'MOP' => __('MOP - Macanese Pataca', 'tc'),
      'MRO' => __('MRO - Mauritanian Ouguiya', 'tc'),
      'MUR' => __('MUR - Mauritian Rupee', 'tc'),
      'MVR' => __('MVR - Maldivian Rufiyaa', 'tc'),
      'MWK' => __('MWK - Malawian Kwacha', 'tc'),
      'MXN' => __('MXN - Mexican Peso', 'tc'),
      'MYR' => __('MYR - Malaysian Ringgit', 'tc'),
      'MZN' => __('MZN - Mozambican Metical', 'tc'),
      'NAD' => __('NAD - Namibian Dollar', 'tc'),
      'NGN' => __('NGN - Nigerian Naira', 'tc'),
      'NIO' => __('NIO - Nicaraguan Cordoba', 'tc'),
      'NOK' => __('NOK - Norwegian Krone', 'tc'),
      'NPR' => __('NPR - Nepalese Rupee', 'tc'),
      'NZD' => __('NZD - New Zealand Dollar', 'tc'),
      'PAB' => __('PAB - Panamanian Balboa', 'tc'),
      'PEN' => __('PEN - Peruvian Nuevo Sol', 'tc'),
      'PGK' => __('PGK - Papua New Guinean Kina', 'tc'),
      'PHP' => __('PHP - Philippine Peso', 'tc'),
      'PKR' => __('PKR - Pakistani Rupee', 'tc'),
      'PLN' => __('PLN - Polish Zloty', 'tc'),
      'PYG' => __('PYG - Paraguayan GuaranÃ­', 'tc'),
      'QAR' => __('QAR - Qatari Riyal', 'tc'),
      'RON' => __('RON - Romanian Leu', 'tc'),
      'RSD' => __('RSD - Serbian Dinar', 'tc'),
      'RUB' => __('RUB - Russian Ruble', 'tc'),
      'RWF' => __('RWF - Rwandan Franc', 'tc'),
      'SAR' => __('SAR - Saudi Riyal', 'tc'),
      'SBD' => __('SBD - Solomon Islands Dollar', 'tc'),
      'SCR' => __('SCR - Seychellois Rupee', 'tc'),
      'SEK' => __('SEK - Swedish Krona', 'tc'),
      'SGD' => __('SGD - Singapore Dollar', 'tc'),
      'SHP' => __('SHP - Saint Helenian Pound', 'tc'),
      'SLL' => __('SLL - Sierra Leonean Leone', 'tc'),
      'SOS' => __('SOS - Somali Shilling', 'tc'),
      'SRD' => __('SRD - Surinamese Dollar', 'tc'),
      'STD' => __('STD - SÃ£o TomÃ© and PrÃ­ncipe Dobra', 'tc'),
      'SVC' => __('SVC - Salvadoran Colon', 'tc'),
      'SZL' => __('SZL - Swazi Lilangeni', 'tc'),
      'THB' => __('THB - Thai Baht', 'tc'),
      'TJS' => __('TJS - Tajikistani Somoni', 'tc'),
      'TOP' => __('TOP - Tonga Pa\'anga', 'tc'),
      'TRY' => __('TRY - Turkish Lira', 'tc'),
      'TTD' => __('TTD - Trinidad and Tobago Dollar', 'tc'),
      'TWD' => __('TWD - New Taiwan Dollar', 'tc'),
      'TZS' => __('TZS - Tanzanian Shilling', 'tc'),
      'UAH' => __('UAH - Ukrainian Hryvnia', 'tc'),
      'UGX' => __('UGX - Ugandan Shilling', 'tc'),
      'USD' => __('USD - United States Dollar', 'tc'),
      'UYI' => __('UYI - Uruguayan Peso', 'tc'),
      'UZS' => __('UZS - Uzbekistani Som', 'tc'),
      'VEF' => __('VEF - Venezuelan Bolivar', 'tc'),
      'VND' => __('VND - Vietnamese Dong ', 'tc'),
      'VUV' => __('VUV - Vanuatu Vatu', 'tc'),
      'WST' => __('WST - Samoan Tala', 'tc'),
      'XAF' => __('XAF - Central African Cfa Franc', 'tc'),
      'XCD' => __('XCD - East Caribbean Dollar', 'tc'),
      'XOF' => __('XOF - West African Cfa Franc', 'tc'),
      'XPF' => __('XPF - Cfp Franc', 'tc'),
      'YER' => __('YER - Yemeni Rial', 'tc'),
      'ZAR' => __('ZAR - South African Rand', 'tc'),
      'ZMW' => __('ZMW - Zambian Kwacha', 'tc'),
    );

    $this->currencies = $currencies;

  }

  /**
   * Load CSS and JS Files
   */
  public function enqueue_scripts() {
      if ( $this->is_payment_page() && $this->is_active() ) {
          wp_enqueue_script( 'js-stripe-elements', 'https://js.stripe.com/v3/', array( 'jquery' ) );
          wp_enqueue_style('css-stripe', plugins_url('stripe/assets/css/stripe.css',__FILE__));
          wp_enqueue_script('js-stripe-client', plugins_url('/stripe/assets/js/client.js', __FILE__), array( 'jquery' ) );
          wp_localize_script('js-stripe-client',  'stripe_client', array('publishable_key' => $this->publishable_key) );
      }
  }

  /**
   *  Generate Payment Form
   * @param $cart
   * @return string|void
   */
  public function payment_form($cart){
    global $tc;

    $content = '';

    $content .= '<div id="stripe-inner">';
    $content .= '<div id="card-errors" role="alert"></div>';
    $content .= '<div class="form-row">';
    $content .= '<div id="card-element"></div>';
    $content .= '</div>';
    $content .= '<img id="stripe-loading" title="'. __('Loading...','tc') .'" src="' . plugins_url('/stripe/assets/images/loading.gif', __FILE__) . '"/><input type="button" id="stripe-submit" class="tickera-button" value="' . __('Submit Payment', 'tc') . '"/>';
    $content .= '</div>';

    $this->enqueue_scripts();

    return $content;
  }

  /**
   * Initialize transaction in Stripe Gateway.
   * This will serve as a payment validation.
   */
  public function process_payment_ajax() {
      global $tc;

      tc_final_cart_check($this->cart_contents());

      $this->init_stripe();
      $this->maybe_start_session();

      // Populate payment and cart info
      $payment_info['method'] = __('Credit Card', 'tc');
      $payment_info = $this->save_payment_info($payment_info);
      $this->save_cart_info();

      // Execute if "PaymentIntent" is not yet created. This will initialize a transaction in Stripe Gateway.
      if ( !isset($_SESSION['stripe_payment_intent']) ) {

          // Create payment intent
          $intent = \TCStripe\PaymentIntent::create([
              'amount' => $this->maybe_fix_total($this->total()),
              'currency' => $this->currency,
              'description' => $this->cart_items(),
              'payment_method_types' => ['card']
          ]);

          // Create tickera order
          $order = $tc->generate_order_id();
          $tc->create_order($order, $this->cart_contents(), $this->cart_info(), $payment_info, false);
          $order_id = tc_get_order_id_by_name($order)->ID;

          // Retrieve stripe customer data
          $customer = \TCStripe\Customer::all(['email' => $this->buyer_info( 'email' ), 'limit' => 1]);

          // Create customer if doesn't exists
          if ( !$customer['data'] ) {
              $customer_data = \TCStripe\Customer::create([
                  'name' => $this->buyer_info( 'first_name' ) . ' ' . $this->buyer_info( 'last_name'),
                  'email' =>  $this->buyer_info( 'email' )
              ]);

          } else {
              $customer_data = $customer['data'][0];
          }

          // Update stripe payment intent metadata and customer data
          \TCStripe\PaymentIntent::update(
              $intent['id'],
              ['metadata' => ['order_id' => $order], 'customer' => $customer_data]
          );

          update_post_meta($order_id, 'stripe_payment_intent', $intent['id'] ? $intent['id'] : 'N/A');

          $client_secret = $intent['client_secret'];
          $_SESSION['stripe_payment_intent'] = $intent['id'];

          $stripe_client = array (
              'payment_intent_id' => $intent['id'],
              'client_secret' => $client_secret,
              'customer_name' => $this->buyer_info( 'first_name' ) . ' ' . $this->buyer_info( 'last_name' ),
              'email'		 => $this->buyer_info( 'email' )
          );

      // Execute if "PaymentIntent" has been created. This will validate the payment.
      } else {

          $payment_intent = \TCStripe\PaymentIntent::retrieve( $_SESSION['stripe_payment_intent'] );
          $stripe_client = array(
              'payment_intent_id' => $payment_intent['id'],
              'client_secret' => $payment_intent['client_secret'],
              'customer_name' => $this->buyer_info('first_name') . ' ' . $this->buyer_info('last_name'),
              'email' => $this->buyer_info('email')
          );
      }

      wp_send_json($stripe_client);
  }

  /**
   * Create Tickera order if payment has been validated.
   *
   * @throws \TCStripe\Error\Api
   */
  public function order_confirmation_ajax(){
    global $tc;

    $redirect = false;

    $this->init_stripe();
    $this->maybe_start_session();

    // Retrieve error message
    $stripe_result = $_POST['payment_result'];

    // Retrieve current order id
    $order = $_SESSION['tc_order'];
    $order_id = tc_get_order_id_by_name($order)->ID;

    // Update order status as paid
    if ( 'succeeded' == $stripe_result ) {
        $tc->update_order_payment_status($order_id, true);
        $redirect = $tc->get_confirmation_slug(true, $order);
        unset($_SESSION['stripe_payment_intent']);

    // Error Occur
    } else {

        // Insert Custom Message
        switch ( $stripe_result['type'] ) {
            case 'api_connection_error':
                $note = $stripe_result['message'];
                $note .= '<br><b>' . __('Internal Note: Please double check the transaction in your stripe account.', 'tc') . '</b>';
                break;

            default:
                $note = $stripe_result['message'];
        }

        // Execute only if api connection was interrupted
        if ( 'succeeded' == $stripe_result['payment_intent']['status'] ) {
            $tc->update_order_payment_status($order_id, true);
            $redirect = $tc->get_confirmation_slug(true, $order);
            unset($_SESSION['stripe_payment_intent']);
        }

        // Insert Order Note
        TC_Order::add_order_note( $order_id,  $note );
    }

    wp_send_json($redirect);
  }

  /**
   * Initialize Admin Settings for Stripe Elements
   *
   * @param $settings
   * @param $visible
   */
  public function gateway_admin_settings($settings, $visible){
    global $tc;
    ?>
    <div id="<?php echo $this->plugin_name; ?>"
      class="postbox" <?php echo(!$visible ? 'style="display:none;"' : ''); ?>>
      <h3><span><?php printf(__('%s Settings', 'tc'), $this->admin_name); ?></span>
        <span class="description">
          <?php _e("Accept Visa, MasterCard, American Express, Discover, JCB, and Diners Club cards directly on your site. Credit cards go directly to Stripe's secure environment, and never hit your servers so you can avoid most PCI requirements.", 'tc') ?>
        </span>
      </h3>
      <div class="inside">

        <?php
        $fields = array(
          'is_ssl' => array(
            'title' => __('Mode', 'tc'),
            'type' => 'select',
            'options' => array(
              '0' => __('Sandbox / Test', 'tc'),
              '1' => __('Live', 'tc')
            ),
            'default' => '0',
          ),
          'publishable_key' => array(
            'title' => __('Publishable API Key', 'tc'),
            'type' => 'text',
          ),
          'private_key' => array(
            'title' => __('Secret API Key', 'tc'),
            'type' => 'text',
            'description' => __('You must login to Stripe to <a target="_blank" href="https://manage.stripe.com/#account/apikeys">get your API credentials</a>. You can enter your test credentials, then live ones when ready.', 'tc'),
          ),
          'currency' => array(
            'title' => __('Currency', 'tc'),
            'type' => 'select',
            'options' => $this->currencies,
            'default' => 'AUD',
          ),
        );
        $form = new TC_Form_Fields_API($fields, 'tc', 'gateways', $this->plugin_name);
        ?>
        <table class="form-table">
          <?php $form->admin_options(); ?>
        </table>
      </div>
    </div>
    <?php
  }

  /**
   * Validate Decimal Numbers
   * @param $val
   * @return bool
   */
  public function is_decimal($val){
    return is_numeric($val) && floor($val) != $val;
  }

  /**
   * Calculate Totals
   * @param $total
   * @return float|int
   */
  public function maybe_fix_total($total){
    if (in_array($this->currency, $this->zero_decimal_currencies) && !$this->is_decimal($total)) {
      return $total;
    } else {
      return $total * 100;
    }
  }

}

tc_register_gateway_plugin('TC_Gateway_Stripe_Elements_3DS', 'stripe-elements-3d-secure', __('Stripe Elements 3D Secure', 'tc'));
?>
