<?php

/*
 Plugin Name: Tickera CSV Export
 Plugin URI: http://tickera.com/
 Description: Export attendees data in CSV file format
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.2.5.6
 Text Domain: tccsv
 Domain Path: /languages/
 Copyright 2017 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !function_exists( 'csv_export_fs' ) ) {
    // Create a helper function for easy SDK access.
    function csv_export_fs()
    {
        global  $csv_export_fs ;
        
        if ( !isset( $csv_export_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3168_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3168_MULTISITE', true );
            }
            // Include Freemius SDK.
            
            if ( file_exists( dirname( dirname( __FILE__ ) ) . '/tickera-event-ticketing-system/freemius/start.php' ) ) {
                // Try to load SDK from parent plugin folder.
                require_once dirname( dirname( __FILE__ ) ) . '/tickera-event-ticketing-system/freemius/start.php';
            } else {
                
                if ( file_exists( dirname( dirname( __FILE__ ) ) . '/tickera/freemius/start.php' ) ) {
                    // Try to load SDK from premium parent plugin folder.
                    require_once dirname( dirname( __FILE__ ) ) . '/tickera/freemius/start.php';
                } else {
                    require_once dirname( __FILE__ ) . '/freemius/start.php';
                }
            
            }
            
            $csv_export_fs = fs_dynamic_init( array(
                'id'               => '3168',
                'slug'             => 'csv-export',
                'premium_slug'     => 'csv-export',
                'type'             => 'plugin',
                'public_key'       => 'pk_13a7fbe9592e5e651369b0ef62d7e',
                'is_premium'       => true,
                'is_premium_only'  => true,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'parent'           => array(
                'id'         => '3102',
                'slug'       => 'tickera-event-ticketing-system',
                'public_key' => 'pk_7a38a2a075ec34d6221fe925bdc65',
                'name'       => 'Tickera',
            ),
                'menu'             => array(
                'first-path' => 'plugins.php',
                'support'    => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $csv_export_fs;
    }

}
function csv_export_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function csv_export_fs_is_parent_active()
{
    $active_plugins = get_option( 'active_plugins', array() );
    
    if ( is_multisite() ) {
        $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
        $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
    }
    
    foreach ( $active_plugins as $basename ) {
        if ( 0 === strpos( $basename, 'tickera-event-ticketing-system/' ) || 0 === strpos( $basename, 'tickera/' ) ) {
            return true;
        }
    }
    return false;
}

function csv_export_fs_init()
{
    
    if ( csv_export_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        csv_export_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( csv_export_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    csv_export_fs_init();
} else {
    
    if ( csv_export_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'csv_export_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        csv_export_fs_init();
    }

}

if ( !csv_export_fs()->can_use_premium_code() ) {
    return;
}
if ( !class_exists( 'TC_Export_Csv_Mix' ) ) {
    class TC_Export_Csv_Mix
    {
        var  $version = '1.2.5.6' ;
        var  $title = 'CSV Export' ;
        var  $name = 'tc_export_csv_mix' ;
        var  $dir_name = 'csv-export' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            $this->init_vars();
            global  $tc, $post_type, $post ;
            add_action( $tc->name . '_add_menu_items_after_ticket_templates', array( $this, 'add_admin_menu_csv_export_to_tc' ) );
            add_filter( 'tc_admin_capabilities', array( $this, 'append_capabilities' ) );
            add_action( 'plugins_loaded', array( &$this, 'localization' ), 9 );
            if ( isset( $_GET['page'] ) && $_GET['page'] == 'tc_export_csv_mix' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'tc_events' ) {
                add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
            }
            add_action( 'wp_ajax_tc_export_attendee_list', array( &$this, 'tc_export_attendee_list' ) );
            add_action( 'wp_ajax_tc_export_csv', array( &$this, 'tc_export' ) );
            add_action( 'wp_ajax_tc_export_csv_dummy', array( &$this, 'tc_export_csv_dummy' ) );
            
            if ( apply_filters( 'tc_bridge_for_woocommerce_is_active', false ) == false ) {
                //check bridge is active or not
                add_action( 'wp_ajax_tc_get_ticket_type', array( &$this, 'tc_get_ticket_type' ) );
                //using ajax get ticket type
                add_action( 'wp_ajax_tc_get_ticket_type_change', array( &$this, 'tc_get_ticket_type_change' ) );
                //using ajax get ticket type
            } else {
                add_action( 'wp_ajax_tc_get_ticket_type', array( &$this, 'woo_tc_get_ticket_type' ) );
                //using ajax get ticket type
                add_action( 'wp_ajax_tc_get_ticket_type_change', array( &$this, 'woo_get_ticket_type_change' ) );
                //using ajax get ticket type
            }
            
            add_action( 'wp_ajax_tc_keep_selection', array( &$this, 'tc_keep_selection' ) );
            //keep selection using ajax
        }
        
        /*
         **Keep selection attendee csv export
         */
        function tc_keep_selection()
        {
            $formdata = $_POST['from_data'];
            
            if ( $formdata == 'uncheck' ) {
                delete_option( 'tc_atteende_keep_selection' );
            } else {
                foreach ( $formdata as $key => $value ) {
                    $attendee_field['remember_setting'][$value['name']] = $value['value'];
                }
                update_option( 'tc_atteende_keep_selection', serialize( $attendee_field ), $autoload = null );
            }
        
        }
        
        /*
         **on page load get ticket type
         */
        function tc_get_ticket_type()
        {
            
            if ( isset( $_POST ) ) {
                $event_id = (int) $_POST['id'];
                $ticket_type = new WP_Query( array(
                    'post_type'              => 'tc_tickets',
                    'post_status'            => 'publish',
                    'meta_key'               => 'event_name',
                    'meta_value'             => $event_id,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'cache_results'          => false,
                    'fields'                 => array( 'ID' ),
                    'orderby'                => 'ID',
                ) );
                $i = 0;
                while ( $ticket_type->have_posts() ) {
                    $ticket_type->the_post();
                    $response[]['ticket_id'] = get_the_ID();
                    $response[]['ticket_type'] = get_the_title();
                    $i++;
                    $response['count'] = $i;
                }
                $settings = get_option( 'tc_atteende_keep_selection' );
                
                if ( $settings != '' ) {
                    $resp['success'] = 'success';
                    $resp['data'] = $i;
                    echo  json_encode( $resp ) ;
                } else {
                    echo  json_encode( $response ) ;
                }
                
                wp_reset_postdata();
                clearstatcache();
                die;
            }
        
        }
        
        /*
         **on page load woo get ticket type
         */
        function woo_tc_get_ticket_type()
        {
            
            if ( isset( $_POST ) ) {
                $event_id = (int) $_POST['id'];
                $ticket_type = new WP_Query( array(
                    'post_type'              => 'product',
                    'post_status'            => 'publish',
                    'meta_key'               => '_event_name',
                    'meta_value'             => $event_id,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'cache_results'          => false,
                    'fields'                 => array( 'ID' ),
                    'orderby'                => 'ID',
                ) );
                $i = 0;
                while ( $ticket_type->have_posts() ) {
                    $ticket_type->the_post();
                    $response[]['ticket_id'] = get_the_ID();
                    $response[]['ticket_type'] = get_the_title();
                    $i++;
                    $response['count'] = $i;
                }
                $settings = get_option( 'tc_atteende_keep_selection' );
                
                if ( $settings != '' ) {
                    $resp['success'] = 'success';
                    $resp['data'] = $i;
                    echo  json_encode( $resp ) ;
                } else {
                    echo  json_encode( $response ) ;
                }
                
                wp_reset_postdata();
                clearstatcache();
                die;
            }
        
        }
        
        /**
         * on click get ticket type
         */
        function tc_get_ticket_type_change()
        {
            
            if ( isset( $_POST ) ) {
                $event_id = (int) $_POST['id'];
                $ticket_type = new WP_Query( array(
                    'post_type'              => 'tc_tickets',
                    'post_status'            => 'publish',
                    'meta_key'               => 'event_name',
                    'meta_value'             => $event_id,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'cache_results'          => false,
                    'fields'                 => array( 'ID' ),
                    'orderby'                => 'ID',
                ) );
                $i = 0;
                while ( $ticket_type->have_posts() ) {
                    $ticket_type->the_post();
                    $response[]['ticket_id'] = get_the_ID();
                    $response[]['ticket_type'] = get_the_title();
                    $i++;
                    $response['count'] = $i;
                }
                wp_send_json( $response );
                wp_reset_postdata();
                clearstatcache();
                die;
            }
        
        }
        
        /**
         * on click get woo ticket type
         */
        function woo_get_ticket_type_change()
        {
            
            if ( isset( $_POST ) ) {
                $event_id = (int) $_POST['id'];
                $ticket_type = new WP_Query( array(
                    'post_type'              => 'product',
                    'post_status'            => 'publish',
                    'meta_key'               => '_event_name',
                    'meta_value'             => $event_id,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'cache_results'          => false,
                    'fields'                 => array( 'ID' ),
                    'orderby'                => 'ID',
                ) );
                $i = 0;
                while ( $ticket_type->have_posts() ) {
                    $ticket_type->the_post();
                    $response[]['ticket_id'] = get_the_ID();
                    $response[]['ticket_type'] = get_the_title();
                    $i++;
                    $response['count'] = $i;
                }
                echo  json_encode( $response ) ;
                wp_reset_postdata();
                clearstatcache();
                die;
            }
        
        }
        
        function tc_export_csv_dummy()
        {
        }
        
        /*
         ** append menu capabilities
         */
        function append_capabilities( $capabilities )
        {
            //Add additional capabilities to staff and admins
            $capabilities['manage_' . $this->name . '_cap'] = 1;
            return $capabilities;
        }
        
        /*
         ** add admin csv menu
         */
        function add_admin_menu_csv_export_to_tc()
        {
            //Add additional menu item under Tickera admin menu
            global  $first_tc_menu_handler ;
            $handler = 'csv_export';
            add_submenu_page(
                $first_tc_menu_handler,
                __( $this->title, 'tccsv' ),
                __( $this->title, 'tccsv' ),
                'manage_' . $this->name . '_cap',
                $this->name,
                $this->name . '_admin'
            );
            eval("function " . $this->name . "_admin() {require_once( '" . $this->plugin_dir . "includes/admin-pages/settings-tickera_export_csv_mixed_data.php');}");
            do_action( $this->name . '_add_menu_items_after_' . $handler );
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tccsv-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'tccsv', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    //load_plugin_textdomain( 'tccsv', false, $this->plugin_dir . '/languages/' );
                    load_plugin_textdomain( 'tccsv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'tccsv', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function init_vars()
        {
            //setup proper directories
            
            if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename( __FILE__ ) ) ) {
                $this->location = 'subfolder-plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
                $this->plugin_url = plugins_url( '/', __FILE__ );
            } else {
                
                if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
                    $this->location = 'plugins';
                    $this->plugin_dir = WP_PLUGIN_DIR . '/';
                    $this->plugin_url = plugins_url( '/', __FILE__ );
                } else {
                    
                    if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
                        $this->location = 'mu-plugins';
                        $this->plugin_dir = WPMU_PLUGIN_DIR;
                        $this->plugin_url = WPMU_PLUGIN_URL;
                    } else {
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tccsv' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        function enqueue_scripts()
        {
            wp_enqueue_style( $this->name . '-jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
            wp_enqueue_style( $this->name . '-admin', $this->plugin_url . 'includes/css/admin.css' );
            wp_enqueue_script( 'jquery-ui-progressbar' );
            wp_enqueue_script(
                $this->name . '-admin',
                $this->plugin_url . 'includes/js/admin.js',
                array( 'jquery' ),
                false,
                false
            );
            $admin_url = strtok( admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ), '?' );
            wp_localize_script( $this->name . '-admin', 'tc_csv_vars', array(
                'ajaxUrl'             => $admin_url,
                'ticket_type_message' => __( 'There are no ticket type for this event', 'tccsv' ),
                'attendee_list_error' => __( 'There are no exporting data', 'tccsv' ),
            ) );
        }
        
        function array2csv( array $array )
        {
            if ( count( $array ) == 0 ) {
                return null;
            }
            ob_start();
            $df = fopen( "php://output", 'w' );
            fputcsv( $df, array_keys( reset( $array ) ) );
            foreach ( $array as $row ) {
                fputcsv( $df, $row );
            }
            fclose( $df );
            return ob_get_clean();
        }
        
        function set_max( $value )
        {
            if ( $value > 100 ) {
                $value = 100;
            }
            return round( $value, 0 );
        }
        
        /**
         * Process export attendee list
         */
        function tc_export_attendee_list()
        {
            global  $wpdb ;
            error_reporting( E_ERROR );
            if ( !session_id() ) {
                session_start();
            }
            $time_start = microtime( true );
            $order_status = $_POST['tc_limit_order_type'];
            ini_set( 'max_input_time', 3600 * 3 );
            ini_set( 'max_execution_time', 3600 * 3 );
            set_time_limit( 0 );
            $per_page = apply_filters( 'tc_csv_export_per_page_limit', 66 );
            $page = max( 1, $_POST['page_num'] );
            $date_from = date( 'Y-m-d H:i:s', strtotime( $_POST['tc_list_from'] ) );
            // From list
            $date_to = date( 'Y-m-d H:i:s', strtotime( $_POST['tc_list_to'] ) );
            // To list
            // Condition for all ticket_type or specific 1
            $meta_key = 'ticket_type_id';
            $meta_value = $_POST['tc_export_csv_ticket_type_data'];
            $meta_val = explode( ",", $meta_value );
            // Collect all product variation IDs
            $get_ticket_type_id = $wpdb->get_results( "SELECT DISTINCT {$wpdb->posts}.ID as ticket_id FROM {$wpdb->posts} LEFT JOIN {$wpdb->postmeta} AS {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.meta_value WHERE {$wpdb->posts}.post_parent IN ({$meta_value}) AND {$wpdb->posts}.post_type LIKE 'product_variation' ORDER BY {$wpdb->posts}.ID ASC" );
            $all_product_id = [];
            foreach ( $get_ticket_type_id as $ticket_key => $ticket_value ) {
                $all_product_id[] = $ticket_value->ticket_id;
            }
            $meta_values = array_merge( $meta_val, $all_product_id );
            $wc_order_status_values = array( 'any', 'wc-cancelled' );
            $post_status = ( in_array( $order_status, $wc_order_status_values ) ? array( 'trash', 'publish' ) : 'publish' );
            $query = new WP_Query( array(
                'date_query'             => array(
                '0' => array(
                'after'     => $date_from,
                'before'    => $date_to,
                'compare'   => '>=',
                'column'    => 'post_date',
                'inclusive' => true,
            ),
            ),
                'post_type'              => 'tc_tickets_instances',
                'post_status'            => $post_status,
                'posts_per_page'         => $per_page,
                'paged'                  => $page,
                'meta_key'               => $meta_key,
                'meta_value'             => $meta_values,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'cache_results'          => false,
                'fields'                 => array( 'ID' ),
                'orderby'                => 'ID',
            ) );
            
            if ( $page == 1 ) {
                unset( $_SESSION['tc_csv_array'] );
                $tc_csv_array = array();
                $_SESSION['tc_csv_array'] = $tc_csv_array;
            } else {
                $tc_csv_array = $_SESSION['tc_csv_array'];
            }
            
            // Selected Event get API Key ID
            
            if ( isset( $_POST['tc_export_csv_event_data'] ) ) {
                $tc_event_prepare = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} posts JOIN {$wpdb->postmeta} postmeta ON postmeta.post_id = posts.ID WHERE posts.post_type = 'tc_api_keys' AND postmeta.meta_key = 'event_name' AND postmeta.meta_value = %d", $_POST['tc_export_csv_event_data'] );
                $api_ids = $wpdb->get_results( $tc_event_prepare );
            }
            
            $i = 0;
            $previous_order_id = 0;
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                // Search all the tickets from the event that are confirmed
                $instance = new TC_Ticket_Instance( $post_id );
                $order = new TC_Order( $instance->details->post_parent );
                // Check if Order has multiple instances
                $multiple_instances = ( $previous_order_id != $order->id ? false : true );
                $previous_order_id = $order->id;
                // Validate Order Post Type
                $passed_validation = ( in_array( $order->details->post_type, array( 'tc_orders', 'shop_order' ) ) ? true : false );
                
                if ( $order->id && $passed_validation && ('any' == $order_status || $order->details->post_status == $order_status) ) {
                    // Check to see if owner first name is checked
                    $tc_first_name_array = ( isset( $_POST['col_owner_first_name'] ) ? array(
                        __( 'First Name', 'tccsv' ) => $instance->details->first_name,
                    ) : array() );
                    do_action( 'tc_export_csv_after_owner_first_name', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if owner last name is checked
                    $tc_last_name_array = ( isset( $_POST['col_owner_last_name'] ) ? array(
                        __( 'Last Name', 'tccsv' ) => $instance->details->last_name,
                    ) : array() );
                    do_action( 'tc_export_csv_after_owner_last_name', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if owner name is checked
                    $tc_name_array = ( isset( $_POST['col_owner_name'] ) ? array(
                        __( 'Name', 'tccsv' ) => $instance->details->first_name . ' ' . $instance->details->last_name,
                    ) : array() );
                    do_action( 'tc_export_csv_after_owner_name', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if owner email is checked
                    $tc_owner_email_array = ( isset( $_POST['col_owner_email'] ) ? array(
                        __( 'Attendee E-mail', 'tccsv' ) => $instance->details->owner_email,
                    ) : array() );
                    do_action( 'tc_export_csv_after_owner_email', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if payment date is checked
                    $tc_payment_array = ( isset( $_POST['col_payment_date'] ) ? array(
                        __( 'Payment Date', 'tccsv' ) => tc_format_date( strtotime( $order->details->post_date_gmt ) ),
                    ) : array() );
                    $tc_order_number_array = ( isset( $_POST['col_order_number'] ) ? array(
                        __( 'Order Number', 'tccsv' ) => apply_filters( 'tc_export_order_number_column_value', $order->details->post_title, $order->details->ID ),
                    ) : array() );
                    do_action( 'tc_export_csv_after_order_number', ( isset( $_POST ) ? $_POST : '' ) );
                    $tc_payment_gateway_array = ( isset( $_POST['col_payment_gateway'] ) ? array(
                        __( 'Payment Gateway', 'tccsv' ) => apply_filters( 'tc_order_payment_gateway_name', ( isset( $order->details->tc_cart_info['gateway_admin_name'] ) ? $order->details->tc_cart_info['gateway_admin_name'] : '' ), $order->details->ID ),
                    ) : array() );
                    do_action( 'tc_export_csv_after_payment_gateway', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if discount code is checked
                    $tc_discount_array = ( isset( $_POST['col_discount_code'] ) ? array(
                        __( 'Discount Code', 'tccsv' ) => $order->details->tc_discount_code,
                    ) : array() );
                    do_action( 'tc_export_csv_after_discount_value', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if order status is checked
                    $tc_order_status_values = array(
                        'order_paid'      => 'Paid',
                        'order_received'  => 'Received / Pending',
                        'order_fraud'     => 'Fraud Detected',
                        'order_cancelled' => 'Cancelled',
                        'order_refunded'  => 'Refunded',
                    );
                    $order_st = ( isset( $order_status_values[$order->details->post_status] ) ? __( $tc_order_status_values[$order->details->post_status], 'tccsv' ) : '' );
                    $order_st = apply_filters(
                        'tc_order_status_title',
                        $order_st,
                        $order->details->ID,
                        $order->details->post_status
                    );
                    $tc_order_status_array = ( isset( $_POST['col_order_status'] ) ? array(
                        __( 'Order Status', 'tccsv' ) => $order_st,
                    ) : array() );
                    do_action( 'tc_export_csv_after_order_status', ( isset( $_POST ) ? $_POST : '' ) );
                    $tc_order_total_array = ( isset( $_POST['col_order_total'] ) ? array(
                        __( 'Order Total', 'tccsv' ) => round( round( $order->details->tc_payment_info['total'], 2 ), 2 ),
                    ) : array() );
                    do_action( 'tc_export_csv_after_order_total', ( isset( $_POST ) ? $_POST : '' ) );
                    
                    if ( isset( $_POST['col_order_total_once'] ) ) {
                        
                        if ( !$multiple_instances ) {
                            $tc_post_type = get_post_type( $order->id );
                            $order_total_once = ( 'shop_order' == $tc_post_type ? wc_get_order( $order->id )->get_total() : round( $order->details->tc_payment_info['total'], 2 ) );
                            $tc_order_total_once_array = array(
                                __( 'Order Total (Shown Once)', 'tccsv' ) => round( $order_total_once, 2 ),
                            );
                        } else {
                            $tc_order_total_once_array = array(
                                __( 'Order Total (Shown Once)', 'tccsv' ) => '',
                            );
                        }
                    
                    } else {
                        $tc_order_total_once_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_order_total_once', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if ticket id is checked
                    $tc_ticket_id_array = ( isset( $_POST['col_ticket_id'] ) ? array(
                        __( 'Ticket Code', 'tccsv' ) => $instance->details->ticket_code,
                    ) : array() );
                    do_action( 'tc_export_csv_after_ticket_id', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if ticket id is checked
                    $tc_ticket_type_instance_id = ( isset( $_POST['col_ticket_instance_id'] ) ? array(
                        __( 'Ticket ID', 'tccsv' ) => $instance->details->ID,
                    ) : array() );
                    do_action( 'tc_export_csv_after_ticket_instance_id', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if ticket type is checked
                    
                    if ( isset( $_POST['col_ticket_type'] ) ) {
                        $tc_ticket_type_array = array(
                            __( 'Ticket Type', 'tccsv' ) => apply_filters(
                            'tc_checkout_owner_info_ticket_title',
                            get_the_title( $instance->details->ticket_type_id ),
                            ( isset( $instance->details->ticket_type_id ) ? $instance->details->ticket_type_id : $instance->details->ticket_type_id ),
                            array(),
                            $instance->details->ID
                        ),
                        );
                    } else {
                        $tc_ticket_type_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_ticket_type', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if buyer first name is checked
                    
                    if ( isset( $_POST['col_buyer_first_name'] ) ) {
                        $buyer_first_name = ( isset( $order->details->tc_cart_info['buyer_data']['first_name_post_meta'] ) ? $order->details->tc_cart_info['buyer_data']['first_name_post_meta'] : '' );
                        $tc_buyer_first_name_info_array = array(
                            __( 'Buyer First Name', 'tccsv' ) => apply_filters( 'tc_ticket_checkin_buyer_first_name', $buyer_first_name, $order->details->ID ),
                        );
                    } else {
                        $tc_buyer_first_name_info_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_buyer_first_name', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if buyer name is checked
                    
                    if ( isset( $_POST['col_buyer_last_name'] ) ) {
                        $buyer_last_name = ( isset( $order->details->tc_cart_info['buyer_data']['last_name_post_meta'] ) ? $order->details->tc_cart_info['buyer_data']['last_name_post_meta'] : '' );
                        $tc_buyer_last_name_info_array = array(
                            __( 'Buyer Last Name', 'tccsv' ) => apply_filters( 'tc_ticket_checkin_buyer_last_name', $buyer_last_name, $order->details->ID ),
                        );
                    } else {
                        $tc_buyer_last_name_info_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_buyer_last_name', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if buyer name is checked
                    
                    if ( isset( $_POST['col_buyer_name'] ) ) {
                        $buyer_full_name = ( isset( $order->details->tc_cart_info['buyer_data']['first_name_post_meta'] ) ? $order->details->tc_cart_info['buyer_data']['first_name_post_meta'] . ' ' . $order->details->tc_cart_info['buyer_data']['last_name_post_meta'] : '' );
                        $tc_buyer_info_array = array(
                            __( 'Buyer Name', 'tccsv' ) => apply_filters( 'tc_ticket_checkin_buyer_full_name', $buyer_full_name, $order->details->ID ),
                        );
                    } else {
                        $tc_buyer_info_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_buyer_name', ( isset( $_POST ) ? $_POST : '' ) );
                    //CHECK TO SEE IF BUYER E-MAIL IS CHECKED
                    
                    if ( isset( $_POST['col_buyer_email'] ) ) {
                        $buyer_email = ( isset( $order->details->tc_cart_info['buyer_data']['email_post_meta'] ) ? $order->details->tc_cart_info['buyer_data']['email_post_meta'] : '' );
                        $tc_buyer_email_array = array(
                            __( 'Buyer E-Mail', 'tccsv' ) => apply_filters( 'tc_ticket_checkin_buyer_email', $buyer_email, $order->details->ID ),
                        );
                    } else {
                        $tc_buyer_email_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_email', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check to see if attendee is checked-in
                    
                    if ( isset( $_POST['col_checked_in'] ) ) {
                        $checkins = get_post_meta( $instance->details->ID, 'tc_checkins', true );
                        $checked_in = ( count( $checkins ) > 0 && is_array( $checkins ) ? __( 'Yes', 'tccsv' ) : __( 'No', 'tccsv' ) );
                        $tc_checked_in_array = array(
                            __( 'Checked-in', 'tccsv' ) => $checked_in,
                        );
                    } else {
                        $tc_checked_in_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_checked_in', ( isset( $_POST ) ? $_POST : '' ) );
                    // Check-ins list for an attendee
                    
                    if ( isset( $_POST['col_checkins'] ) ) {
                        $checkins_list = [];
                        $checkins = get_post_meta( $instance->details->ID, 'tc_checkins', true );
                        
                        if ( count( $checkins ) > 0 && is_array( $checkins ) ) {
                            foreach ( $checkins as $checkin ) {
                                $api_key = $checkin['api_key_id'];
                                $api_key_obj = new TC_API_Key( (int) $api_key );
                                $api_key_name = $api_key_obj->details->api_key_name;
                                
                                if ( apply_filters( 'tc_show_checkins_api_key_names', true ) == true ) {
                                    $api_key_name = ( !empty($api_key_name) ? $api_key_name : $api_key );
                                    $api_key_title = ' (' . $api_key_name . ')';
                                } else {
                                    $api_key_title = '';
                                }
                                
                                $checkins_list[] = tc_format_date( $checkin['date_checked'] ) . $api_key_title;
                            }
                            $checkins = implode( "\r\n", $checkins_list );
                        } else {
                            $checkins = '';
                        }
                        
                        $tc_checkins_array = array(
                            __( 'Check-ins', 'tccsv' ) => $checkins,
                        );
                    } else {
                        $tc_checkins_array = array();
                    }
                    
                    do_action( 'tc_export_csv_after_checkins', ( isset( $_POST ) ? $_POST : '' ) );
                    // API Keys list for an attendee
                    
                    if ( isset( $_POST['col_owner_api_key'] ) ) {
                        $api_key_value = [];
                        foreach ( $api_ids as $api_key => $api_id ) {
                            $api_key_value[] = get_post_meta( $api_id->ID, 'api_key', true );
                        }
                        $tc_api_key = ( count( $api_key_value ) > 0 ? array(
                            __( 'API Key', 'tccsv' ) => implode( ",", $api_key_value ),
                        ) : '' );
                    } else {
                        $tc_api_key = array();
                    }
                    
                    do_action( 'tc_export_csv_after_api_key', ( isset( $_POST ) ? $_POST : '' ) );
                    // Price list for an attendee
                    
                    if ( isset( $_POST['col_order_price'] ) ) {
                        
                        if ( apply_filters( 'tc_bridge_for_woocommerce_is_active', false ) == false ) {
                            //check bridge is active or not
                            $tc_order_price = get_post_meta( $instance->details->ID, 'ticket_subtotal', true );
                            $tc_order_price_array = array(
                                __( 'Price', 'tccsv' ) => $tc_order_price,
                            );
                        } else {
                            $tc_order_price = get_post_meta( $instance->details->ticket_type_id, '_price', true );
                            $tc_order_price_array = array(
                                __( 'Price', 'tccsv' ) => $tc_order_price,
                            );
                        }
                    
                    } else {
                        $tc_order_price_array = array();
                    }
                    
                    $tc_csv_array[] = apply_filters(
                        'tc_csv_array',
                        array_merge(
                        $tc_first_name_array,
                        $tc_last_name_array,
                        $tc_name_array,
                        $tc_owner_email_array,
                        $tc_payment_array,
                        $tc_order_number_array,
                        $tc_payment_gateway_array,
                        $tc_order_status_array,
                        $tc_order_total_array,
                        $tc_order_total_once_array,
                        $tc_discount_array,
                        $tc_ticket_id_array,
                        $tc_ticket_type_instance_id,
                        $tc_ticket_type_array,
                        $tc_buyer_first_name_info_array,
                        $tc_buyer_last_name_info_array,
                        $tc_buyer_info_array,
                        $tc_buyer_email_array,
                        $tc_checked_in_array,
                        $tc_checkins_array,
                        $tc_api_key,
                        $tc_order_price_array
                    ),
                        $order,
                        $instance,
                        $_POST,
                        $i
                    );
                    $_SESSION['tc_csv_array'] = $tc_csv_array;
                }
                
                $i++;
            }
            $exported = $page * $per_page;
            $time_end = microtime( true );
            $execution_time = $time_end - $time_start;
            $response = array(
                'exported'       => $this->set_max( ceil( $exported / ($query->found_posts / 100) ) ),
                'page'           => $page + 1,
                'done'           => false,
                'execution_time' => $execution_time,
                'found_posts'    => $query->found_posts,
            );
            if ( $exported >= $query->found_posts ) {
                $response['done'] = true;
            }
            wp_send_json_success( $response );
        }
        
        function tc_export()
        {
            if ( !session_id() ) {
                session_start();
            }
            
            if ( defined( 'TC_DEBUG' ) ) {
                error_reporting( E_ALL );
                ini_set( 'display_errors', 1 );
            } else {
                error_reporting( 0 );
            }
            
            $this->download_send_headers( $_GET['document_title'] . ".csv" );
            echo  $this->array2csv( $_SESSION['tc_csv_array'] ) ;
            exit;
        }
        
        function download_send_headers( $filename )
        {
            // disable caching
            
            if ( !empty($_GET['document_title']) ) {
                $now = gmdate( "D, d M Y H:i:s" );
                header( "Expires: Tue, 03 Jul 2001 06:00:00 GMT" );
                header( "Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate" );
                header( "Last-Modified: {$now} GMT" );
                // force download
                header( "Content-Type: application/force-download" );
                header( "Content-Type: application/octet-stream" );
                header( "Content-Type: application/download" );
                // disposition / encoding on response body
                header( "Content-Disposition: attachment;filename={$filename}" );
                header( "Content-Transfer-Encoding: binary" );
                //unset( $_POST );
            }
        
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_export_csv_mix_load()
    {
        global  $tc_export_csv_mix ;
        $tc_export_csv_mix = new TC_Export_Csv_Mix();
    }
    
    add_action( 'tets_fs_loaded', 'tc_export_csv_mix_load' );
} else {
    $tc_export_csv_mix = new TC_Export_Csv_Mix();
}
