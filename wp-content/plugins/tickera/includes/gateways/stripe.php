<?php
/*
Stripe - Payment Gateway
*/
global $tc;

class TC_Gateway_Stripe extends TC_Gateway_API {

  var $plugin_name = 'stripe';
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

  //Support for older payment gateway API
  public function on_creation() {
    $this->init();
  }

  public function init_stripe(){
    global $tc;


    if (!class_exists('TCStripe\Stripe') && !class_exists('Stripe') && !class_exists('TCStripe\\Stripe')) {//Load Stripe classes only if it doesn't exist already
      require_once $tc->plugin_dir . '/includes/gateways/stripe/init.php';
    }
    \TCStripe\Stripe::setApiKey($this->private_key);
  }

  public function init(){
    global $tc;

    get_option('tc_settings');

    $this->admin_name = __('Stripe', 'tc');
    $this->public_name = __('Stripe', 'tc');

    $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/stripe.png', $this->plugin_name);
    $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-stripe.png', $this->plugin_name);

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

  public function get_stripe_session($order_id)  {
    global $tc;

    $line_items = [];
    //$ticket = new TC_Ticket($ticket_type);
    $line_items[] = [
      'currency' => $this->currency,
      'quantity' => 1,
      'amount' => $this->maybe_fix_total($this->total()),
      'name' => $this->cart_items()
    ];

    $this->init_stripe();

    $stripe_session = \TCStripe\Checkout\Session::create([
      'client_reference_id' => $order_id,
      'customer_email' => $this->buyer_info('email'),
      'success_url' => add_query_arg( 'stripe_session_id', '{CHECKOUT_SESSION_ID}', $tc->get_confirmation_slug(true, $order_id) ),//
      'cancel_url' => $tc->get_cancel_url($order_id),
      'payment_method_types' => ['card'],
      'payment_intent_data' => [
        'description' => $this->cart_items(),
      ],
      'line_items' => $line_items
    ]);

    $_SESSION['stripe_session_id'] = $stripe_session['id'];
    $_SESSION['stripe_payment_intent'] = $stripe_session['payment_intent'];
  }

  public function payment_form($cart){
    global $tc;
  }


  public function process_payment($cart){
    global $tc;

    tc_final_cart_check($cart);

    $this->maybe_start_session();
    $this->save_cart_info();

    $order_id = $tc->generate_order_id();

    $this->get_stripe_session($order_id);

    $paid = false;

    $payment_info = $this->save_payment_info();

    $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

    if (is_numeric($order_id)) {
      //DO nothing
    } else {
      $order = tc_get_order_id_by_name($order_id); //get order post from order's slug id
      $order_id	 = $order->ID;
    }

    update_post_meta($order_id, 'stripe_session_id', isset($_SESSION['stripe_session_id']) ? $_SESSION['stripe_session_id'] : 'N/A');
    update_post_meta($order_id, 'stripe_payment_intent', isset($_SESSION['stripe_payment_intent']) ? $_SESSION['stripe_payment_intent'] : 'N/A');

    if(!isset($_SESSION['stripe_session_id'])){
      TC_Order::add_order_note($order_id, __('Error on server: stripe_session_id and stripe_payment_intent cannot be set because SESSION is empty.', 'tc'));
    }

    header('Content-Type: text/html');
    ?>
    <div class="frame" style="white-space: nowrap;text-align: center;">
      <span class="helper" style="display: inline-block; height: 100%;vertical-align: middle;"></span>
    </div>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
    const stripe = Stripe('<?php echo $this->publishable_key; ?>');
    stripe.redirectToCheckout({
      sessionId: '<?php echo $_SESSION['stripe_session_id']; ?>'
    }).then(function (result) {
    });
    </script>
    <?php
    exit(0);
  }

  public function order_confirmation($order, $payment_info = '', $cart_info = ''){
    global $tc;

    $order = tc_get_order_id_by_name($order);

    $this->init_stripe();
    $stripe_session_id = get_post_meta($order->ID, 'stripe_session_id', true);//$_SESSION['stripe_session_id']
    $stripe_payment_intent = get_post_meta($order->ID, 'stripe_payment_intent', true);//$_SESSION['stripe_payment_intent']

    if(isset($_GET['stripe_session_id']) && !empty($_GET['stripe_session_id'])){
      $stripe_session_id = $_GET['stripe_session_id'];
    }

    $stripe_session = \TCStripe\Checkout\Session::retrieve($stripe_session_id);

    if ($stripe_session['payment_intent'] === $stripe_payment_intent) {
      $payment_intent = \TCStripe\PaymentIntent::retrieve($stripe_payment_intent);
      if ($payment_intent['status'] === 'succeeded') {
        $tc->update_order_payment_status($order->ID, true);
      }else{
        TC_Order::add_order_note($order->ID, __('Payment status: ', 'tc'). $payment_intent['status']);
      }

    }else{
      $payment_intent = \TCStripe\PaymentIntent::retrieve($stripe_session['payment_intent']);
      if ($payment_intent['status'] === 'succeeded') {
        $tc->update_order_payment_status($order->ID, true);
      }else{
        TC_Order::add_order_note($order->ID, __('Payment status: ', 'tc'). $payment_intent['status']);
      }
    }
  }

  /**
   * Generate Order Confirmation Page upon success checkout
   * @param $order
   * @param string $cart_info
   * @return string
   */
  public function order_confirmation_message($order, $cart_info = ''){
      global $tc;

      $order = tc_get_order_id_by_name($order);
      $order = new TC_Order($order->ID);

      $content = '';

      switch ( $order->details->post_status ) {

          case 'order_received':
              $content .= '<p>' . sprintf(__('Your payment via Stripe for this order totaling <strong>%s</strong> is not yet complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
              $content .= '<p>' . __('Current order status:', 'tc') . ' <strong>' . __('Pending Payment') . '</strong></p>';
              break;

          case 'order_fraud':
              $content .= '<p>' . __('Your payment is under review. We will back to you soon.', 'tc') . '</p>';
              break;

          case 'order_paid':
              $content .= '<p>' . sprintf(__('Your payment via Stripe for this order totaling <strong>%s</strong> is complete.', 'tc'), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
              break;

          case 'order_cancelled':
              $content .= '<p>' . sprintf(__('Your payment via Stripe for this order totaling <strong>%s</strong> is cancelled.', 'tc'), $this->public_name, apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
              break;

          case 'order_refunded':
              $content .= '<p>' . sprintf(__('Your payment via Stripe for this order totaling <strong>%s</strong> is refunded.', 'tc'), $this->public_name, apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info['total'])) . '</p>';
              break;

      }

      $content = apply_filters('tc_order_confirmation_message_content_' . $this->plugin_name, $content);
      $content = apply_filters('tc_order_confirmation_message_content', $content, $order);

      $tc->remove_order_session_data();
      unset($_SESSION['stripe_session_id'], $_SESSION['stripe_payment_intent']);
      $tc->maybe_skip_confirmation_screen($this, $order);
      return $content;
  }

  public function gateway_admin_settings($settings, $visible){
    global $tc;

    // Notify admin if there's a similar/updated version of Stripe Gateway installed
    $tc->tc_payment_gateway_alternative($this->plugin_name);

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

  public function is_decimal($val){
    return is_numeric($val) && floor($val) != $val;
  }

  public function maybe_fix_total($total){
    if (in_array($this->currency, $this->zero_decimal_currencies) && !$this->is_decimal($total)) {
      return $total;
    } else {
      return $total * 100;
    }
  }

}

tc_register_gateway_plugin('TC_Gateway_Stripe', 'stripe', __('Stripe', 'tc'));
?>
