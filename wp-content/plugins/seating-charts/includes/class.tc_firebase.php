<?php

/*
TC_Firebase
*/

if (!defined('ABSPATH'))
exit; // Exit if accessed directly

class TC_Firebase {

  var $firebase_path = '';
  var $firebase = '';
  var $firebase_secret = '';

  function __construct() {

    $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

    $this->firebase_secret = isset($tc_seat_charts_settings['secret']) ? $tc_seat_charts_settings['secret'] : '';
    $this->firebase_path = isset($tc_seat_charts_settings['databaseURL']) ? $tc_seat_charts_settings['databaseURL'] : '';

    $this->init_firebase();

    add_action('wp_ajax_nopriv_tc_add_seat_to_firebase_cart', array(&$this, 'tc_add_seat_to_firebase_cart'));
    add_action('wp_ajax_tc_add_seat_to_firebase_cart', array(&$this, 'tc_add_seat_to_firebase_cart'));

    add_action('wp_ajax_nopriv_tc_remove_seat_from_firebase_cart', array(&$this, 'tc_remove_seat_from_firebase_cart'));
    add_action('wp_ajax_tc_remove_seat_from_firebase_cart', array(&$this, 'tc_remove_seat_from_firebase_cart'));
    add_action('tc_seat_chart_woo_cart_item_remove_seat', array(&$this, 'tc_remove_seat_from_firebase_cart'), 10, 2);

    //add_action('tc_check_in_deleted', array(&$this, 'tc_check_in_deleted'), 99, 2);
    add_action('tc_check_in_notification', array(&$this, 'tc_check_in_added'), 99, 1);
    add_action('tc_remove_order_session_data', array($this, 'remove_in_cart_from_firebase'));

    add_action('delete_post', array(&$this, 'tc_delete_chart_from_firebase'), 10, 1);

    add_action('admin_enqueue_scripts', array($this, 'admin_firebase_scripts'));
  }

  function admin_firebase_scripts(){
    global $TC_Seat_Chart;

    $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

    $use_firebase_integration = isset($tc_seat_charts_settings['user_firebase_integration']) ? $tc_seat_charts_settings['user_firebase_integration'] : '0';

    if ($use_firebase_integration == '1') {
      if (!session_id()) {
        @session_start();
      }

        wp_enqueue_script('tc-server-date', plugins_url('../js/ServerDate.js', __FILE__));

        // Include only if base URI and Secret Key are provided
        if ( $this->firebase_path && $this->firebase_secret ) {
            wp_enqueue_script('tc-firebase', 'https://www.gstatic.com/firebasejs/3.2.1/firebase.js');
        }

        wp_enqueue_script('tc-seat-charts-firebase-admin', plugins_url('../js/tc-firebase-admin.js', __FILE__), array('jquery', 'tc-firebase'), $TC_Seat_Chart->version, false);

        wp_localize_script('tc-seat-charts-firebase-admin', 'tc_firebase_vars', array(
            'apiKey' => isset($tc_seat_charts_settings['apiKey']) ? $tc_seat_charts_settings['apiKey'] : '',
            'authDomain' => isset($tc_seat_charts_settings['authDomain']) ? $tc_seat_charts_settings['authDomain'] : '',
            'databaseURL' => isset($tc_seat_charts_settings['databaseURL']) ? $tc_seat_charts_settings['databaseURL'] : '',
            'session_id' => session_id(),
            'tc_chart_id' => isset($_GET['post']) ? $_GET['post'] : false,
            'tc_checkedin_seat_color' => isset($tc_seat_charts_settings['checkedin_seat_color']) ? $tc_seat_charts_settings['checkedin_seat_color'] : '#000',
        ));
    }
  }

  /**
  * Initialize Firebase
  * @global type $TC_Seat_Chart
  */
  function init_firebase() {
    global $TC_Seat_Chart;
    //Firebase Lib
    require_once 'firebase/firebaseInterface.php';
    require_once 'firebase/firebaseStub.php';
    require_once 'firebase/firebaseLib.php';

    // Executes only if base URI and Secret Key are provided
    if ( $this->firebase_path && $this->firebase_secret ) {
        $this->firebase = new \Firebase\FirebaseLib($this->firebase_path, $this->firebase_secret);
    }
  }

  /**
  * Remove in-cart items from Firebase
  * And add it as reserved
  */
  function remove_in_cart_from_firebase() {
    if (!session_id()) {
      @session_start();
    }

    $in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

    $reserved_seats = array();

    $data = array();
    $data['timestamp'] = (time() * 1000);
    $data['session_id'] = session_id();

    foreach ($in_cart_seats as $seat_ticket_type_id => $ticket_type_seats_in_carts) {
      foreach ($ticket_type_seats_in_carts as $ticket_type_seats_in_cart) {
        $seat_id = $ticket_type_seats_in_cart[0];
        $chart_id = (int) $ticket_type_seats_in_cart[2];

        $this->firebase->delete('/in-cart/' . $chart_id . '/' . $seat_id, array('print' => 'silent'));
        $this->firebase->update('/reserved/' . $chart_id . '/' . $seat_id, $data, array('print' => 'silent'));
      }
    }
  }

  /**
  * Delete whole chart from Firebase
  * @param type $post_id
  */
  function tc_delete_chart_from_firebase($post_id) {
    if (get_post_type($post_id) == 'tc_seat_charts') {
      $this->firebase->delete('/in-cart/' . $post_id, array('print' => 'silent'));
      //$this->firebase->delete('/check-ins/' . $post_id, array('print' => 'silent'));
    }
  }

  /**
  * Remove seat from Firebase
  * @param type $seat_row
  * @param type $seat_col
  * @param type $chart_id
  */
  function tc_remove_seat_from_firebase_cart($seat_id = false, $chart_id = false) {
    $chart_id = $chart_id ? $chart_id : $_POST['chart_id'];
    $seat_id = $seat_id ? $seat_id : $_POST['seat_id'];

    $this->firebase->delete('/in-cart/' . $chart_id . '/' . $seat_id, array('print' => 'silent'));
    //exit;
  }

  /**
  * Add in-cart seat to Firebase
  * @global type $tc
  */
  function tc_add_seat_to_firebase_cart() {
    global $tc;

    if (!session_id()) {
      @session_start();
    }

    if (isset($_POST['tc_seat_cart_items'])) {
      $tc_seat_cart_items = $_POST['tc_seat_cart_items'];

      $charts = array();

      foreach ($tc_seat_cart_items as $tc_seat_cart_item) {
        $tc_seat_cart_item = explode('-', $tc_seat_cart_item);

        $chart_id = $tc_seat_cart_item[0];
        $seat_id = $tc_seat_cart_item[1];

        $charts[$chart_id][$seat_id] = array('timestamp' => (time() * 1000), 'session_id' => session_id());

        $data = array();
        $data['timestamp'] = time();
        $data['session_id'] = session_id();
      }

      foreach ($charts as $chart_id => $data) {
        $this->firebase->update('/in-cart/' . $chart_id . '/', $data, array('print' => 'silent'));
      }

      exit;
    }
  }

  /**
  * Delete check-in record from Firebase
  * @param type $ticket_id
  * @param type $checkins
  */
  function tc_check_in_deleted($ticket_id, $checkins) {
    if (is_array($checkins) && count($checkins) > 0) {
      $checkins = $checkins[0];
      $checkins = count($checkins);
    } else {
      $checkins = 0;
    }

    if ($checkins == 0) {
      //delete a record from a firebase
      $chart_id = get_post_meta($ticket_id, 'chart_id', true);
      if (is_numeric($chart_id)) {
        //add a record to the firebase

        //$row = get_post_meta($ticket_id, 'seat_row', true);
        //$col = get_post_meta($ticket_id, 'seat_col', true);

        //$seat = $row . '_' . $col;
        $seat = get_post_meta($ticket_id, 'seat_id', true);
        $this->firebase->delete('/check-ins/' . $chart_id . '/' . $seat . '/', array('print' => 'silent'));
      } else {
        //don't add it since we don't need that info for non-seat tickets
      }
    }
  }

  /**
  * Add check-in record to Firebase
  * @param type $ticket_id
  */
  function tc_check_in_added($ticket_id) {
//echo 'ticket id: '.$ticket_id;
//exit;
    $chart_id = get_post_meta((int)$ticket_id, 'chart_id', true);
    if (is_numeric($chart_id)) {
      //add a record to the firebase

      //$row = get_post_meta($ticket_id, 'seat_row', true);
      //$col = get_post_meta($ticket_id, 'seat_col', true);

      //$seat = $row . '_' . $col;
      $seat = get_post_meta($ticket_id, 'seat_id', true);
      $this->firebase->update('/check-ins/' . $chart_id . '/' . $seat . '/', array('check_in_time' => time()), array('print' => 'silent'));
    } else {
      //don't add it since we don't need that info for non-seat tickets
    }
  }

}

$tc_seat_charts_settings = TC_Seat_Chart::get_settings();

$use_firebase_integration = isset($tc_seat_charts_settings['user_firebase_integration']) ? $tc_seat_charts_settings['user_firebase_integration'] : '0';

/**
* Make sure that admin wants to use Firebase
*/
if ($use_firebase_integration == '1') {
  $tc_firebase = new TC_Firebase();
}
