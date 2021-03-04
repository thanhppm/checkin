<?php

/*
 * TC_Firebase
 */

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class TC_Firebase {

    var $firebase_path = '';
    var $firebase = '';
    var $firebase_secret = '';
    var $firebase_enabled = false;

    function __construct() {

        $tc_seat_charts_settings = TC_Seat_Chart::get_settings();
        $this->firebase_enabled = isset( $tc_seat_charts_settings['user_firebase_integration'] ) ? $tc_seat_charts_settings['user_firebase_integration'] : false;
        $this->firebase_secret = isset( $tc_seat_charts_settings['secret'] ) ? $tc_seat_charts_settings['secret'] : '';
        $this->firebase_path = isset( $tc_seat_charts_settings['databaseURL'] ) ? $tc_seat_charts_settings['databaseURL'] : '';

        $this->init_firebase();

        add_action( 'wp_ajax_nopriv_tc_add_seat_to_firebase_cart', array( &$this, 'tc_add_seat_to_firebase_cart' ) );
        add_action( 'wp_ajax_tc_add_seat_to_firebase_cart', array( &$this, 'tc_add_seat_to_firebase_cart' ) );
        add_action( 'wp_ajax_nopriv_tc_remove_expired_firebase_seats', array( &$this, 'tc_remove_expired_firebase_seats' ) );
        add_action( 'wp_ajax_tc_remove_expired_firebase_seats', array( &$this, 'tc_remove_expired_firebase_seats' ) );
        add_action( 'wp_ajax_nopriv_tc_remove_seat_from_firebase_cart', array( &$this, 'tc_remove_seat_from_firebase_cart' ) );
        add_action( 'wp_ajax_tc_remove_seat_from_firebase_cart', array( &$this, 'tc_remove_seat_from_firebase_cart' ) );
        add_action( 'tc_seat_chart_woo_cart_item_remove_seat', array( &$this, 'tc_remove_seat_from_firebase_cart' ), 10, 2 );
        add_action( 'tc_check_in_notification', array( &$this, 'tc_check_in_added' ), 99, 1 );
        add_action( 'tc_remove_order_session_data', array( $this, 'remove_in_cart_from_firebase' ) );
        add_action( 'delete_post', array( &$this, 'tc_delete_chart_from_firebase' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_firebase_scripts' ) );
    }

    function admin_firebase_scripts() {

        global $TC_Seat_Chart;

        if ( $this->firebase_enabled ) {

            $tc_seat_charts_settings = TC_Seat_Chart::get_settings();

            if (!session_id()) {
                @session_start();
            }

            // Include only if base uri and Secret Key exists
            if ( $this->firebase_path && $this->firebase_secret ) {
                wp_enqueue_script( 'tc-firebase', 'https://www.gstatic.com/firebasejs/3.2.1/firebase.js' );
            }

            wp_enqueue_script( 'tc-seat-charts-firebase-admin', plugins_url( '../js/tc-firebase-admin.js', __FILE__ ), array( 'jquery', 'tc-firebase' ), $TC_Seat_Chart->version, false );

            wp_localize_script( 'tc-seat-charts-firebase-admin', 'tc_firebase_vars', array (
                    'apiKey' => isset( $tc_seat_charts_settings['apiKey'] ) ? $tc_seat_charts_settings['apiKey'] : '',
                    'authDomain' => isset( $tc_seat_charts_settings['authDomain'] ) ? $tc_seat_charts_settings['authDomain'] : '',
                    'databaseURL' => isset( $tc_seat_charts_settings['databaseURL'] ) ? $tc_seat_charts_settings['databaseURL'] : '',
                    'session_id' => session_id(),
                    'tc_chart_id' => isset( $_GET['post'] ) ? $_GET['post'] : false,
                    'tc_checkedin_seat_color' => isset( $tc_seat_charts_settings['checkedin_seat_color'] ) ? $tc_seat_charts_settings['checkedin_seat_color'] : '#000',
                )
            );
        }
    }

    /**
     * Initialize Firebase
     *
     * @global type $TC_Seat_Chart
     */
    function init_firebase() {

        // Firebase Lib
        require_once 'firebase/firebaseInterface.php';
        require_once 'firebase/firebaseStub.php';
        require_once 'firebase/firebaseLib.php';

        // Executes only if base URI and Secret Key are provided
        if ( $this->firebase_path && $this->firebase_secret ) {
            $this->firebase = new \Firebase\FirebaseLib( $this->firebase_path, $this->firebase_secret );
        }
    }

    /**
     * Remove in-cart items from Firebase
     * And add it as reserved
     */
    function remove_in_cart_from_firebase() {

        if ( !session_id() ) {
            @session_start();
        }

        $in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

        $data = [];
        $data['timestamp'] = ( time() * 1000 );
        $data['session_id'] = session_id();

        foreach ( $in_cart_seats as $seat_ticket_type_id => $ticket_type_seats_in_carts ) {

            foreach ( $ticket_type_seats_in_carts as $ticket_type_seats_in_cart ) {
                $seat_id = $ticket_type_seats_in_cart[0];
                $chart_id = (int) $ticket_type_seats_in_cart[2];

                $this->firebase->delete( '/in-cart/' . $chart_id . '/' . $seat_id, array( 'print' => 'silent') );
                $this->firebase->update( '/reserved/' . $chart_id . '/' . $seat_id, $data, array( 'print' => 'silent' ) );
            }
        }
    }

    /**
     * Delete whole chart from Firebase
     *
     * @param type $post_id
     */
    function tc_delete_chart_from_firebase( $post_id ) {
        if ( 'tc_seat_charts' == get_post_type( $post_id ) ) {
            $this->firebase->delete( '/in-cart/' . $post_id, array( 'print' => 'silent' ) );
        }
    }

    /**
     * Remove seat from Firebase
     *
     * @param bool $seat_id
     * @param bool $chart_id
     */
    function tc_remove_seat_from_firebase_cart( $seat_id = false, $chart_id = false ) {
        $chart_id = $chart_id ? $chart_id : $_POST['chart_id'];
        $seat_id = $seat_id ? $seat_id : $_POST['seat_id'];
        $test = $this->firebase->delete( '/in-cart/' . $chart_id . '/' . $seat_id, array( 'print' => 'pretty' ) );
    }

    /**
     * Add in-cart seat to Firebase
     *
     * @global type $tc
     */
    function tc_add_seat_to_firebase_cart() {

        if ( !session_id() ) {
            @session_start();
        }

        if ( isset( $_POST['tc_seat_cart_items'] ) && $_POST['tc_seat_cart_items'] ) {

            $charts = [];
            $tc_seat_cart_items = $_POST['tc_seat_cart_items'];

            foreach ( $tc_seat_cart_items as $tc_seat_cart_item ) {

                $tc_seat_cart_item = explode( '-', $tc_seat_cart_item );
                $chart_id = $tc_seat_cart_item[0];
                $seat_id = $tc_seat_cart_item[1];

                $seat_data =  $this->firebase->get( '/in-cart/' . $chart_id . '/' . $seat_id );
                $seat_data = json_decode( $seat_data, true );

                $charts[$chart_id][$seat_id] = $seat_data
                    ? [ 'timestamp' => $seat_data['timestamp'], 'session_id' => $seat_data['session_id'], 'expires' => $seat_data['expires'] ]
                    : [ 'timestamp' => self::tc_get_current_time(), 'expires' => self::tc_get_expiration(), 'session_id' => session_id() ];
            }

            foreach ( $charts as $chart_id => $data ) {
                $this->firebase->update( '/in-cart/' . $chart_id . '/', $data, array( 'print' => 'silent' ) );
            }

            exit;
        }
    }

    /**
     * Update Seats Firebase Realtime Database.
     */
    function tc_remove_expired_firebase_seats() {

        if ( isset( $_POST['tc_seating_chart_id'] ) && $_POST['tc_seating_chart_id'] ) {

            $chart_id = (int) $_POST['tc_seating_chart_id'];
            $fb_seats_data = json_decode( $this->firebase->get( '/in-cart/' . $chart_id ), true );

            foreach ( (array) $fb_seats_data as $fb_seat_id => $fb_seat_value ) {

                /*
                 * Compare current date vs expiration date time.
                 * Remove seat that doesn't has expiration.
                 * Remove expired seat from firebase.
                 *
                 * Issue: Simultaneously setting and retrieval of cookie will potentially cause a discrepancy in the returned values.
                 * Solution: Remove expired seat one at a time.
                 */
                if ( !isset( $fb_seat_value['expires'] ) || ( ( self::tc_get_current_time()/1000 ) >= ( $fb_seat_value['expires']/1000 ) ) ) {

                    $current_in_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();
                    $response = TC_Seat_Chart::tc_remove_seat_from_cart( $chart_id, $fb_seat_id );
                    $new_inc_cart_seats = TC_Seat_Chart::get_cart_seats_cookie();

                    if ( $current_in_cart_seats != $new_inc_cart_seats ) {
                        self::tc_remove_seat_from_firebase_cart( $fb_seat_id, $chart_id );
                        wp_send_json( $response );
                    }
                    break;
                }
            }
        }
    }

    /**
     * Delete check-in record from Firebase.
     *
     * @param type $ticket_id
     * @param type $checkins
     */
    function tc_check_in_deleted( $ticket_id, $checkins ) {

        $checkins = ( is_array( $checkins ) && count( $checkins ) > 0 ) ? count( $checkins[0] ) : 0;

        if ( ! $checkins ) {

            // Delete a record from a firebase
            $chart_id = get_post_meta( $ticket_id, 'chart_id', true );

            if ( is_numeric( $chart_id ) ) {

                // Add a record to the firebase
                $seat = get_post_meta( $ticket_id, 'seat_id', true );
                $this->firebase->delete( '/check-ins/' . $chart_id . '/' . $seat . '/', array( 'print' => 'silent' ) );
            }
        }
    }

    /**
     * Add check-in record to Firebase.
     *
     * @param type $ticket_id
     */
    function tc_check_in_added( $ticket_id )
    {

        $chart_id = get_post_meta((int)$ticket_id, 'chart_id', true);

        if (is_numeric($chart_id)) {

            // Add a record to the firebase
            $seat = get_post_meta($ticket_id, 'seat_id', true);
            $this->firebase->update('/check-ins/' . $chart_id . '/' . $seat . '/', array('check_in_time' => time()), array('print' => 'silent'));
        }
    }

    /**
     * Generate Expiration Time.
     * Using server time as the basis of seat expiration. This value will be used to compare current time via javascript
     * @var duration +1 = 1 min; default: 5 minutes
     *
     * @return float|int
     */
    function tc_get_expiration() {
        $duration = 5;
        $offset = 1000;
        return ( time() + ( $duration * 60 ) ) * $offset;
    }

    /**
     * Generate Server Time.
     * Instead of using Wordpress current_time, server time will be used.
     * This is to compare current and expiry time via javascript.
     *
     * @return float|int
     */
    function tc_get_current_time() {
        $offset = 1000;
        return ( time() * $offset );
    }
}

/**
 * Make sure that admin wants to use Firebase
 */
$tc_seat_charts_settings = TC_Seat_Chart::get_settings();
$use_firebase_integration = isset( $tc_seat_charts_settings['user_firebase_integration'] ) ? $tc_seat_charts_settings['user_firebase_integration'] : false;

if ( $use_firebase_integration ) {
    $tc_firebase = new TC_Firebase();
}