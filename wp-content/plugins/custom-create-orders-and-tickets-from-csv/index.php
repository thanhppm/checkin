<?php

/**
 * Plugin Name: Create order and tickets from imported CSV
 * Plugin URI: https://tickera.com/
 * Description: Creat order and tickets from imported CSV (custom add-on for Tickera)
 * Version: 1.0
 * Author: Tickera
 * Author URI: https://tickera.com/
 * Developer: Tickera
 * Developer URI: https://tickera.com/
 */
add_filter( 'tc_settings_new_menus', 'tc_settings_new_menus_custom_csv_import' );
add_action( 'tc_settings_menu_tickera_custom_import_csv', 'tc_settings_menu_tickera_custom_import_csv_show_page' );
add_action( 'admin_init', 'tc_custom_import_data', 0 );

 add_action('admin_enqueue_scripts', 'admin_enqueue_scripts_and_styles');
 
 function admin_enqueue_scripts_and_styles() {
     wp_enqueue_script( 'tc-add-ajax-csv', plugins_url('includes/js/common.js', __FILE__), array(), '1.0.0', true );
 }

function tc_settings_new_menus_custom_csv_import( $settings_tabs ) {
	$settings_tabs[ 'tickera_custom_import_csv' ] = __( 'Import CSV', 'tc' );
	return $settings_tabs;
}

function tc_settings_menu_tickera_custom_import_csv_show_page() {
	require_once( 'includes/admin-pages/settings-tickera_custom_import_csv.php' );
}

function tc_custom_import_data() {
	if ( isset( $_POST[ 'tc_custom_import_csv' ] ) ) {

		@error_reporting( E_ERROR );
		@set_time_limit( 0 );
		@ini_set( 'max_input_time', 3600 * 3 );
		@ini_set( 'max_execution_time', 3600 * 3 );

		if ( $_FILES[ 'tc_csv_import_file' ][ 'size' ] > 0 ) {

			//get the csv file 
			$file	 = $_FILES[ 'tc_csv_import_file' ][ 'tmp_name' ];
			$handle	 = fopen( $file, "r" );

			$data	 = fgetcsv( $handle, 100000, ",", "'" ); 
			$line	 = 0;
			do {
				$first_name	 = $data[ 0 ];
				$last_name	 = $data[ 1 ];
				$order_id	 = $data[ 2 ];
				$email		 = $data[ 3 ];
				$ticket_position_image = $data[ 4 ];
				$ticket_seat = $data[ 5 ];
				$ticket_owner_position = $data[ 6 ];
				$ticket_code = $data[ 7 ];
				if ( $line !== 0 ) {
					tc_custom_create_order_from_csv( $_POST[ 'tc_ticket_type_id' ], $first_name, $last_name, $email, $ticket_position_image, $ticket_seat, $ticket_owner_position, $ticket_code, $order_id );
				}
				$line++;
			} while ( $data = fgetcsv( $handle, 100000, ",", "'" ) );

		}
	}
}

function tc_custom_create_order_from_csv( $tc_ticket_type_id, $first_name, $last_name, $email, $ticket_position_image, $ticket_seat, $ticket_owner_position, $ticket_code, $order_id = false ) {
	global $tc;

	if ( !session_id() ) {
		session_start();
	}

	//Order ID
	if ( !$order_id ) {
		echo $order_id;
		$order_id = $tc->generate_order_id();
	}

	$gateway_class		 = 'TC_Gateway_Free_Orders';
	$free_orders_gateway = new TC_Gateway_Free_Orders();

	//Cart Contents
	$cart						 = array();
	$cart[ $tc_ticket_type_id ]	 = 1;
	$cart_contents				 = $cart;

	//Buyer Data
	$buyer_data								 = array();
	$buyer_data[ 'first_name_post_meta' ]	 = $first_name;
	$buyer_data[ 'last_name_post_meta' ]	 = $last_name;
	$buyer_data[ 'email_post_meta' ]		 = $email;

	//Owner Data
	$owner_data									 = array();
	$owner_data[ 'first_name_post_meta' ]		 = $first_name;
	$owner_data[ 'last_name_post_meta' ]		 = $last_name;
	$owner_data[ 'email_post_meta' ]			 = $email;
	$owner_data[ 'ticket_type_id_post_meta' ]	 = $tc_ticket_type_id;
	$owner_data[ 'ticket_position_image_post_meta' ] = $ticket_position_image;
	$owner_data[ 'ticket_seat_post_meta' ] = $ticket_seat;
	$owner_data[ 'ticket_owner_position_post_meta' ] = $ticket_owner_position;
	$owner_data[ 'ticket_code_post_meta' ] = $ticket_code;

	//Cart Info
	$cart_info							 = array();
	$cart_info[ 'coupon_code' ]			 = '';
	$cart_info[ 'total' ]				 = 0;
	$cart_info[ 'currency' ]			 = $tc->get_cart_currency();
	$cart_info[ 'buyer_data' ]			 = $buyer_data;
	$cart_info[ 'owner_data' ]			 = $owner_data;
	$cart_info[ 'gateway_class' ]		 = $gateway_class;
	$cart_info[ 'gateway' ]				 = $free_orders_gateway->plugin_name;
	$cart_info[ 'gateway_admin_name' ]	 = $free_orders_gateway->admin_name;

	//Payment Info
	$payment_info				 = array();
	$payment_info[ 'currency' ]	 = $tc->get_cart_currency();
	$payment_info				 = $free_orders_gateway->save_payment_info( $payment_info );

	tc_custom_csv_create_order_internal( $order_id, $cart_contents, $cart_info, $payment_info, true );
}

function tc_custom_csv_create_order_internal( $order_id, $cart_contents, $cart_info, $payment_info, $paid ) {
	global $wpdb, $tc;

	if ( empty( $order_id ) ) {
		$order_id = $tc->generate_order_id();
	} 
	// else if ( $tc->get_order( $order_id ) ) { //don't continue if the order exists
	// 	return false;
	// }

	$cart_total = $cart_info[ 'total' ];

	$fraud = $tc->check_for_total_paid_fraud( $payment_info[ 'total' ], $cart_total );

	$user_id = get_current_user_id();

//insert post type
	$status = ($paid ? ($fraud ? 'order_fraud' : 'order_paid') : 'order_received');

	$order = $tc->get_order( $order_id );
	
	if (!$order) {
		$order					 = array();
		$order[ 'post_title' ]	 = $order_id;
		$order[ 'post_name' ]	 = $order_id;
		$order[ 'post_content' ] = serialize( $cart_contents );
		$order[ 'post_status' ]	 = $status;
		$order[ 'post_type' ]	 = 'tc_orders';
	
		if ( $user_id != 0 ) {
			$order[ 'post_author' ] = $user_id;
		}
	
		$post_id = wp_insert_post( $order );
	} else {
		$post_id = $order->ID;
	}
	
	/* add post meta */

//Cart Contents
	add_post_meta( $post_id, 'tc_cart_contents', $cart_contents );

//Cart Info
	add_post_meta( $post_id, 'tc_cart_info', $cart_info ); //save row data - buyer and ticket owners data, gateway, total, currency, coupon code, etc.
//Payment Info
	add_post_meta( $post_id, 'tc_payment_info', $payment_info ); //transaction_id, total, currency, method
//Order Date & Time
	add_post_meta( $post_id, 'tc_order_date', time() );

//Order Paid Time
	add_post_meta( $post_id, 'tc_paid_date', ($paid) ? time() : ''  ); //empty means not yet paid
//Event(s) - could be more events at once since customer may have tickets from more than one event in the cart
	add_post_meta( $post_id, 'tc_parent_event', $tc->get_cart_events( $cart_contents ) );

	add_post_meta( $post_id, 'tc_event_creators', $tc->get_events_creators( $cart_contents ) );

//Discount Code
	add_post_meta( $post_id, 'tc_paid_date', ($paid) ? time() : ''  );

//Save Ticket Owner(s) data
	$owner_data		 = $cart_info[ 'owner_data' ];
	$owner_records	 = array();

	$different_ticket_types = array_keys( array( $owner_data[ 'ticket_type_id_post_meta' ] ) );

	$owner_record_num = 1;

	$metas[ 'first_name' ]		 = $owner_data[ 'first_name_post_meta' ];
	$metas[ 'last_name' ]		 = $owner_data[ 'last_name_post_meta' ];
	$metas[ 'email' ]			 = $owner_data[ 'email_post_meta' ];
	$metas[ 'ticket_type_id' ]	 = $owner_data[ 'ticket_type_id_post_meta' ];
	$metas[ 'ticket_position_image' ]	 = $owner_data[ 'ticket_position_image_post_meta' ];
	$metas[ 'ticket_seat' ]	 = $owner_data[ 'ticket_seat_post_meta' ];
	$metas[ 'ticket_owner_position' ]	 = $owner_data[ 'ticket_owner_position_post_meta' ];
	$metas[ 'ticket_code' ]	 = "0000000" . $owner_data[ 'ticket_code_post_meta' ];

	$ticket_instances = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "posts WHERE post_parent = " . $post_id);
	if (!count($ticket_instances)) {
		// if ( apply_filters( 'tc_use_only_digit_order_number', false ) == true ) {
		// 	$metas[ 'ticket_code' ] = apply_filters( 'tc_ticket_code', $order_id);
		// } else {
		// 	$metas[ 'ticket_code' ] = apply_filters( 'tc_ticket_code', $order_id);
		// }

		$arg = array(
			'post_author'	 => isset( $user_id ) ? $user_id : '',
			'post_parent'	 => $post_id,
			'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
			'post_content'	 => (isset( $content ) ? $content : ''),
			'post_status'	 => 'publish',
			'post_title'	 => (isset( $title ) ? $title : ''),
			'post_type'		 => 'tc_tickets_instances',
		);
	
		$owner_record_id = @wp_insert_post( $arg, true );
	} else {
		$owner_record_id = $ticket_instances[0]->ID;
	}
	// print_r($owner_record_id);
	// print_r($ticket_instances);
	// die;


	foreach ( $metas as $meta_name => $mata_value ) {
		update_post_meta( $owner_record_id, $meta_name, $mata_value );
	}

	$ticket_type_id	 = get_post_meta( $owner_record_id, 'ticket_type_id', true );
	$ticket_type	 = new TC_Ticket( $ticket_type_id );
	$event_id		 = $ticket_type->get_ticket_event();

	update_post_meta( $owner_record_id, 'event_id', $event_id );

	//Send order status email to the customer

	$payment_class_name = $cart_info[ 'gateway_class' ];

	$payment_gateway = new TC_Gateway_Free_Orders();

	do_action( 'tc_order_created', $order_id, $status, $cart_contents, $cart_info, $payment_info );
	return $order_id;
}

?>