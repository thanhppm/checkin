<?php

/*
 Plugin Name: Tickera - Custom Forms
 Plugin URI: http://tickera.com/
 Description: Add custom forms for buyer and attendees
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.2.3
 Text Domain: cf
 Domain Path: /languages/

 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( !function_exists( 'custom_forms_fs' ) ) {
    // Create a helper function for easy SDK access.
    function custom_forms_fs()
    {
        global  $custom_forms_fs ;
        
        if ( !isset( $custom_forms_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3167_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3167_MULTISITE', true );
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
            
            $custom_forms_fs = fs_dynamic_init( array(
                'id'               => '3167',
                'slug'             => 'custom-forms',
                'premium_slug'     => 'custom-forms',
                'type'             => 'plugin',
                'public_key'       => 'pk_32060913427a4d49fcbaad1b976fe',
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
                'override_exact' => true,
                'first-path'     => 'plugins.php',
                'support'        => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $custom_forms_fs;
    }
    
    function custom_forms_fs_settings_url()
    {
        return admin_url( 'edit.php?post_type=tc_events&page=tc_custom_fields' );
    }

}

function custom_forms_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function custom_forms_fs_is_parent_active()
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

function custom_forms_fs_init()
{
    
    if ( custom_forms_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        custom_forms_fs();
        custom_forms_fs()->add_filter( 'connect_url', 'custom_forms_fs_settings_url' );
        custom_forms_fs()->add_filter( 'after_skip_url', 'custom_forms_fs_settings_url' );
        custom_forms_fs()->add_filter( 'after_connect_url', 'custom_forms_fs_settings_url' );
        custom_forms_fs()->add_filter( 'after_pending_connect_url', 'custom_forms_fs_settings_url' );
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( custom_forms_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    custom_forms_fs_init();
} else {
    
    if ( custom_forms_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'custom_forms_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        custom_forms_fs_init();
    }

}

if ( !custom_forms_fs()->can_use_premium_code() ) {
    return;
}
if ( !class_exists( 'TC_Custom_Fields' ) ) {
    class TC_Custom_Fields
    {
        var  $version = '1.2.3' ;
        var  $tc_version_required = '3.3.5' ;
        var  $title = 'Custom Forms' ;
        var  $name = 'tc_custom_fields' ;
        var  $dir_name = 'custom-forms' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            $this->init_vars();
            //if (class_exists('TC')) {//Check if Tickera plugin is active / main Tickera class exists
            global  $tc, $post_type, $post ;
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );
            add_action( 'tc_load_addons', array( $this, 'load_addons' ) );
            add_action( $tc->name . '_add_menu_items_after_ticket_templates', array( $this, 'add_admin_menu_item_to_tc' ) );
            add_action( 'tc_csv_admin_columns', array( $this, 'add_custom_admin_fields_in_csv_addon' ) );
            add_action( 'tc_pdf_admin_columns', array( $this, 'add_custom_admin_fields_in_csv_addon' ) );
            add_filter(
                'tc_pdf_additional_column_titles',
                array( $this, 'add_custom_admin_column_titles_in_pdf' ),
                10,
                2
            );
            add_filter(
                'tc_pdf_additional_column_values',
                array( $this, 'add_custom_admin_column_values_in_pdf' ),
                10,
                4
            );
            add_filter(
                'tc_csv_array',
                array( $this, 'add_custom_fields_to_csv_addon_array' ),
                10,
                4
            );
            add_filter( 'tc_admin_capabilities', array( $this, 'append_capabilities' ) );
            if ( isset( $_GET['page'] ) && ($_GET['page'] == 'tc_custom_fields' || $_GET['page'] == 'tc_orders') || isset( $_REQUEST['post'] ) && get_post_type( $_REQUEST['post'] ) == 'tc_orders' || isset( $_REQUEST['post'] ) && get_post_type( $_REQUEST['post'] ) == 'shop_order' ) {
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_header' ) );
            }
            add_action( 'wp_enqueue_scripts', array( $this, 'front_header' ) );
            add_action( 'init', array( $this, 'register_custom_posts' ), 0 );
            add_filter( 'tc_ticket_fields', array( $this, 'add_additional_ticket_type_fields' ) );
            add_filter( 'tc_form_field_value', array( $this, 'modify_form_field_value' ) );
            add_filter( 'tc_buyer_info_fields', array( $this, 'add_custom_buyer_form_fields' ) );
            add_filter(
                'tc_owner_info_fields',
                array( $this, 'add_custom_owner_form_fields' ),
                10,
                2
            );
            add_filter( 'tc_order_fields', array( &$this, 'add_custom_buyer_fields_to_order_details_page' ) );
            add_filter( 'tc_owner_info_orders_table_fields', array( $this, 'add_custom_owner_fields_to_order_details_page' ) );
            add_filter(
                'tc_checkin_custom_fields',
                array( $this, 'add_checkin_custom_fields' ),
                10,
                5
            );
            add_action( 'plugins_loaded', array( $this, 'load_virtual_tickets_elements' ) );
            add_action( 'tc_order_details_after_table', array( $this, 'tc_after_order_details_fields_add_submit_button' ) );
            if ( isset( $_POST['tc_custom_forms_save_changes'] ) ) {
                add_action( 'tc_order_details_page_start', array( $this, 'tc_custom_forms_maybe_save_data' ) );
            }
            add_action( 'save_post', array( &$this, 'tc_maybe_save_post_data' ) );
            add_action( 'woocommerce_process_shop_order_meta', array( $this, 'tc_custom_forms_maybe_save_data' ) );
            //load templates class
            require_once $this->plugin_dir . 'includes/functions.php';
            //load templates class
            require_once $this->plugin_dir . 'includes/classes/class.forms.php';
            //load templates class
            require_once $this->plugin_dir . 'includes/classes/class.form.php';
            //load templates search class
            require_once $this->plugin_dir . 'includes/classes/class.forms_search.php';
            add_filter(
                'tc_delete_info_plugins_list',
                array( $this, 'tc_delete_info_plugins_list' ),
                10,
                1
            );
            add_action(
                'tc_delete_plugins_data',
                array( $this, 'tc_delete_plugins_data' ),
                10,
                1
            );
            add_action( 'init', array( &$this, 'localization' ), 10 );
            // }
        }
        
        function admin_notices()
        {
            global  $tc ;
            if ( current_user_can( 'manage_options' ) ) {
                
                if ( isset( $tc->version ) && version_compare( $tc->version, $this->tc_version_required, '<' ) ) {
                    ?>
                    <div class="notice notice-error">
                        <p><?php 
                    printf(
                        __( '%s add-on requires at least %s version of %s plugin. Your current version of %s is %s. Please update it.', 'tc' ),
                        $this->title,
                        $this->tc_version_required,
                        $tc->title,
                        $tc->title,
                        $tc->version
                    );
                    ?></p>
                    </div>
                    <?php 
                }
            
            }
        }
        
        function tc_delete_info_plugins_list( $plugins )
        {
            $plugins[$this->name] = $this->title;
            return $plugins;
        }
        
        function tc_delete_plugins_data( $submitted_data )
        {
            
            if ( array_key_exists( $this->name, $submitted_data ) ) {
                global  $wpdb ;
                //Delete posts and post metas
                $wpdb->query( "\n                DELETE\n                p, pm\n                FROM {$wpdb->posts} p\n                JOIN {$wpdb->postmeta} pm on pm.post_id = p.id\n\t\t WHERE p.post_type IN ('tc_forms', 'tc_form_fields')\n\t\t" );
            }
        
        }
        
        /**
         * Check if custom forms data needs to be saved upon order details saving in the admin
         * @global type $post
         * @param type $post_id
         * @return type
         */
        function tc_maybe_save_post_data( $post_id )
        {
            global  $post ;
            if ( get_post_type( $post_id ) != 'tc_orders' ) {
                return;
            }
            $this->tc_custom_forms_maybe_save_data( $post_id );
        }
        
        /**
         * Setup proper directories
         */
        function init_vars()
        {
            
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'cf' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        /**
         * Adds "Save Changes" button on the order details page in the admin
         */
        function tc_after_order_details_fields_add_submit_button()
        {
            submit_button(
                __( 'Save Changes', 'cf' ),
                'primary',
                'tc_custom_forms_save_changes',
                true
            );
        }
        
        /**
         * Save custom forms data on the orders details page in the admin
         * @param type $post_id
         */
        function tc_custom_forms_maybe_save_data( $post_id = false )
        {
            //Save attendee data
            
            if ( isset( $_POST['tc_custom_field_owner_data'] ) ) {
                $owner_data = $_POST['tc_custom_field_owner_data'];
                foreach ( $owner_data as $ticket_instance_id => $meta ) {
                    foreach ( $meta as $meta_key => $meta_value ) {
                        update_post_meta( $ticket_instance_id, $meta_key, $meta_value );
                    }
                }
            }
            
            //Save buyer data
            
            if ( isset( $_POST['tc_custom_field_buyer_data'] ) ) {
                $post_id = key( $_POST['tc_custom_field_buyer_data'] );
                $cart_info = get_post_meta( $post_id, 'tc_cart_info', false );
                $cart_info = $cart_info[0];
                $buyer_data = $_POST['tc_custom_field_buyer_data'][$post_id];
                foreach ( $buyer_data as $key => $value ) {
                    $cart_info['buyer_data'][$key] = $value;
                }
                update_post_meta( $post_id, 'tc_cart_info', $cart_info );
            }
        
        }
        
        /**
         * Add additional field (attendee form dropdown) on the ticket type edit screen
         * @param type $fields
         * @return boolean
         */
        function add_additional_ticket_type_fields( $fields )
        {
            $fields[] = array(
                'field_name'       => 'owner_form_template',
                'field_title'      => __( 'Attendee Form', 'cf' ),
                'field_type'       => 'function',
                'function'         => 'tc_custom_form_fields_owner_form_template_select',
                'tooltip'          => __( 'Custom form shown for attendees on the front-end. You can edit and/or create custom forms <a href="' . admin_url( 'edit.php?post_type=tc_events&page=tc_custom_fields' ) . '" target="_blank">here</a>.', 'cf' ),
                'table_visibility' => false,
                'post_field_type'  => 'post_meta',
                'metabox_context'  => 'side',
            );
            return $fields;
        }
        
        /**
         * Add buyer custom fields to the order details page
         * @param type $fields
         * @return string
         */
        function add_custom_buyer_fields_to_order_details_page( $fields )
        {
            
            if ( isset( $_REQUEST['post'] ) && get_post_type( $_REQUEST['post'] ) == 'tc_orders' || apply_filters( 'tc_custom_forms_show_custom_fields_as_order_columns', false ) == true ) {
                //Show custom fields on the orders details page only
                $forms = new TC_Forms();
                $buyer_form = $forms->get_forms( 'buyer' );
                
                if ( count( $buyer_form ) >= 1 && (isset( $buyer_form[0] ) && !is_null( $buyer_form[0] )) ) {
                    $buyer_form = $buyer_form[0];
                    $args = array(
                        'post_type'              => 'tc_form_fields',
                        'post_status'            => 'publish',
                        'posts_per_page'         => -1,
                        'post_parent'            => $buyer_form->ID,
                        'meta_key'               => 'row',
                        'orderby'                => 'meta_value_num',
                        'order'                  => 'ASC',
                        'no_found_rows'          => true,
                        'update_post_term_cache' => false,
                        'update_post_meta_cache' => false,
                        'cache_results'          => false,
                        'fields'                 => array( 'ID', 'post_parent' ),
                    );
                    $custom_fields = get_posts( $args );
                    if ( count( $custom_fields ) > 0 ) {
                        foreach ( $custom_fields as $custom_field ) {
                            $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                            
                            if ( class_exists( $element_class_name ) ) {
                                $element = new $element_class_name( $custom_field->ID );
                                
                                if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
                                    $fields[] = $element->admin_order_details_page_value();
                                    $fields[] = array(
                                        'id'                => 'separator',
                                        'field_name'        => 'separator',
                                        'field_title'       => '',
                                        'field_type'        => 'separator',
                                        'field_description' => '',
                                        'table_visibility'  => false,
                                        'post_field_type'   => '',
                                    );
                                }
                            
                            }
                        
                        }
                    }
                }
            
            }
            
            return $fields;
        }
        
        /**
         * Add attendee custom fields to the order page
         * @param type $fields
         * @return type
         */
        function add_custom_owner_fields_to_order_details_page( $fields )
        {
            $fields[] = array(
                'id'                => 'custom_fields',
                'field_name'        => 'ticket_type_id',
                'field_title'       => __( 'Custom Fields', 'cf' ),
                'field_type'        => 'function',
                'function'          => 'tc_get_order_details_owner_form_fields_values',
                'field_description' => '',
                'post_field_type'   => 'post_meta',
            );
            return $fields;
        }
        
        /**
         * Add custom fields for buyer on the cart page
         * @global type $wpdb
         * @param type $fields
         * @return type
         */
        function add_custom_buyer_form_fields( $fields )
        {
            $forms = new TC_Forms();
            $buyer_form = $forms->get_forms( 'buyer' );
            
            if ( count( $buyer_form ) >= 1 && (isset( $buyer_form[0] ) && !is_null( $buyer_form[0] )) ) {
                global  $wpdb ;
                $buyer_form = $buyer_form[0];
                for ( $i = 1 ;  $i <= apply_filters( 'tc_form_row_number', 20 ) ;  $i++ ) {
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT *, pm2.meta_value as ord FROM {$wpdb->posts} p, {$wpdb->postmeta} pm, {$wpdb->postmeta} pm2\n\t\t\t\t\t\t\t\t\t\t\tWHERE p.ID = pm.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND p.ID = pm2.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND\tp.post_parent = %d\n\t\t\t\t\t\t\t\t\t\t\tAND (pm.meta_key = 'row' AND pm.meta_value = %d)\n\t\t\t\t\t\t\t\t\t\t\tAND (pm2.meta_key = 'order')\n\t\t\t\t\t\t\t\t\t\t\tORDER BY ord ASC", $buyer_form->ID, $i ), OBJECT );
                    
                    if ( !empty($results) ) {
                        $res = 1;
                        foreach ( $results as $result ) {
                            $post_meta = get_post_meta( $result->ID );
                            $element_class_name = $post_meta['field_type'][0];
                            
                            if ( class_exists( $element_class_name ) ) {
                                $element = new $element_class_name( $result->ID );
                                
                                if ( $res == count( $results ) ) {
                                    $additional_field_class = 'tc_field_col_last_child';
                                } else {
                                    $additional_field_class = '';
                                }
                                
                                $element_content = array(
                                    'field_name'          => $element->standard_field_name( $element->element_name, true ),
                                    'field_title'         => $element->standard_field_label( $element->element_name, true ),
                                    'field_placeholder'   => $element->standard_field_placeholder( $element->element_name, true ),
                                    'field_values'        => $element->standard_field_choice_values( $element->element_name, true ),
                                    'field_default_value' => $element->standard_field_choice_default_values( $element->element_name, true ),
                                    'field_class'         => 'tc_field_col_' . count( $results ) . ' ' . $additional_field_class . ' ' . 'tc_' . $element->element_type . '_field' . (( isset( $element->element_html_class_name ) && !empty($element->element_html_class_name) ? ' ' . $element->element_html_class_name : '' )),
                                    'field_type'          => $element->element_type,
                                    'field_description'   => $element->standard_field_description( $element->element_name, true ),
                                    'post_field_type'     => 'post_meta',
                                    'required'            => $element->standard_field_required( $element->element_name, true ),
                                );
                                $fields[] = $element_content;
                            }
                            
                            $res++;
                        }
                    }
                
                }
            }
            
            return $fields;
        }
        
        /**
         * Add custom fields for attendees on the cart page
         * @global type $wpdb
         * @param type $fields
         * @param type $ticket_type_id
         * @return type
         */
        function add_custom_owner_form_fields( $fields, $ticket_type_id = '' )
        {
            $forms = new TC_Forms();
            $owner_form = $forms->get_forms( 'owner', -1, $ticket_type_id );
            
            if ( count( $owner_form ) >= 1 && (isset( $owner_form[0] ) && !is_null( $owner_form[0] )) ) {
                global  $wpdb ;
                $owner_form = $owner_form[0];
                for ( $i = 1 ;  $i <= apply_filters( 'tc_form_row_number', 20 ) ;  $i++ ) {
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT *, pm2.meta_value as ord FROM {$wpdb->posts} p, {$wpdb->postmeta} pm, {$wpdb->postmeta} pm2\n\t\t\t\t\t\t\t\t\t\t\tWHERE p.ID = pm.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND p.ID = pm2.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND\tp.post_parent = %d\n\t\t\t\t\t\t\t\t\t\t\tAND (pm.meta_key = 'row' AND pm.meta_value = %d)\n\t\t\t\t\t\t\t\t\t\t\tAND (pm2.meta_key = 'order')\n\t\t\t\t\t\t\t\t\t\t\tORDER BY ord ASC", $owner_form->ID, $i ), OBJECT );
                    
                    if ( !empty($results) ) {
                        $res = 1;
                        foreach ( $results as $result ) {
                            $post_meta = get_post_meta( $result->ID );
                            $element_class_name = $post_meta['field_type'][0];
                            
                            if ( class_exists( $element_class_name ) ) {
                                $element = new $element_class_name( $result->ID );
                                
                                if ( $res == count( $results ) ) {
                                    $additional_field_class = 'tc_field_col_last_child';
                                } else {
                                    $additional_field_class = '';
                                }
                                
                                $element_content = array(
                                    'field_name'          => $element->standard_field_name( $element->element_name, true ),
                                    'field_title'         => $element->standard_field_label( $element->element_name, true ),
                                    'field_placeholder'   => $element->standard_field_placeholder( $element->element_name, true ),
                                    'field_values'        => ( isset( $element->field_values ) && !empty($element->field_values) ? $element->field_values : $element->standard_field_choice_values( $element->element_name, true ) ),
                                    'field_default_value' => $element->standard_field_choice_default_values( $element->element_name, true ),
                                    'field_class'         => 'tc_form_id_' . $owner_form->ID . ' tc_ticket_type_id_' . $ticket_type_id . ' tc_field_col_' . count( $results ) . ' ' . $additional_field_class . ' ' . 'tc_' . $element->element_type . '_field' . (( isset( $element->element_html_class_name ) ? ' ' . $element->element_html_class_name : '' )),
                                    'field_type'          => $element->element_type,
                                    'field_description'   => $element->standard_field_description( $element->element_name, true ),
                                    'post_field_type'     => 'post_meta',
                                    'required'            => $element->standard_field_required( $element->element_name, true ),
                                );
                                $fields[] = $element_content;
                            }
                            
                            $res++;
                        }
                    }
                
                }
            }
            
            return $fields;
        }
        
        function add_checkin_custom_fields(
            $custom_fields_vals,
            $ticket_instance_id,
            $event_id,
            $order,
            $ticket_type
        )
        {
            $forms = new TC_Forms();
            $buyer_form = $forms->get_forms( 'buyer' );
            
            if ( count( $buyer_form ) >= 1 && (isset( $buyer_form[0] ) && !is_null( $buyer_form[0] )) ) {
                $buyer_form = $buyer_form[0];
                $args = array(
                    'post_type'      => 'tc_form_fields',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'post_parent'    => $buyer_form->ID,
                    'meta_key'       => 'row',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'ASC',
                    'fields'         => array( 'ID' ),
                );
                $custom_fields = get_posts( $args );
                if ( count( $custom_fields ) > 0 ) {
                    foreach ( $custom_fields as $custom_field ) {
                        $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                        
                        if ( class_exists( $element_class_name ) ) {
                            $element = new $element_class_name( $custom_field->ID );
                            
                            if ( $element->standard_field_show_in_checkin_app( $element->element_name, true ) ) {
                                $custom_field_value = ( isset( $order->details->tc_cart_info['buyer_data'][$element->standard_field_name( $element->element_name, true ) . '_post_meta'] ) ? $order->details->tc_cart_info['buyer_data'][$element->standard_field_name( $element->element_name, true ) . '_post_meta'] : '' );
                                
                                if ( isset( $custom_field_value ) && !empty($custom_field_value) && !is_null( $custom_field_value ) ) {
                                    $custom_fields_vals[] = array( $element->standard_field_label( $element->element_name, true ), $custom_field_value );
                                    //$custom_field_value
                                }
                            
                            }
                        
                        }
                    
                    }
                }
            }
            
            //Owner form
            $owner_form = $forms->get_forms( 'owner', -1, $ticket_type->details->ID );
            
            if ( count( $owner_form ) >= 1 && (isset( $owner_form[0] ) && !is_null( $owner_form[0] )) ) {
                $owner_form = $owner_form[0];
                $args = array(
                    'post_type'      => 'tc_form_fields',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'post_parent'    => $owner_form->ID,
                    'meta_key'       => 'row',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'ASC',
                    'fields'         => array( 'ID' ),
                );
                $custom_fields = get_posts( $args );
                if ( count( $custom_fields ) > 0 ) {
                    foreach ( $custom_fields as $custom_field ) {
                        $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                        
                        if ( class_exists( $element_class_name ) ) {
                            $element = new $element_class_name( $custom_field->ID );
                            
                            if ( $element->standard_field_show_in_checkin_app( $element->element_name, true ) ) {
                                $custom_field_value = get_post_meta( $ticket_instance_id, $element->standard_field_name( $element->element_name, true ), true );
                                if ( isset( $custom_field_value ) && !empty($custom_field_value) && !is_null( $custom_field_value ) ) {
                                    $custom_fields_vals[] = array( $element->standard_field_label( $element->element_name, true ), $custom_field_value );
                                }
                            }
                        
                        }
                    
                    }
                }
            }
            
            return $custom_fields_vals;
        }
        
        /**
         * TO DO
         */
        function add_checkin_custom_fields_old(
            $custom_fields,
            $ticket_instance_id,
            $event_id,
            $order,
            $ticket_type
        )
        {
            $forms = new TC_Forms();
            $buyer_form = $forms->get_forms( 'buyer' );
            
            if ( count( $buyer_form ) >= 1 && (isset( $buyer_form[0] ) && !is_null( $buyer_form[0] )) ) {
                global  $wpdb ;
                $buyer_form = $buyer_form[0];
                for ( $i = 1 ;  $i <= apply_filters( 'tc_form_row_number', 20 ) ;  $i++ ) {
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT *, pm2.meta_value as ord FROM {$wpdb->posts} p, {$wpdb->postmeta} pm, {$wpdb->postmeta} pm2\n\t\t\t\t\t\t\t\t\t\t\tWHERE p.ID = pm.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND p.ID = pm2.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND\tp.post_parent = %d\n\t\t\t\t\t\t\t\t\t\t\tAND (pm.meta_key = 'row' AND pm.meta_value = %d)\n\t\t\t\t\t\t\t\t\t\t\tAND (pm2.meta_key = 'order')\n\t\t\t\t\t\t\t\t\t\t\tORDER BY ord ASC", $buyer_form->ID, $i ), OBJECT );
                    if ( !empty($results) ) {
                        foreach ( $results as $result ) {
                            $post_meta = get_post_meta( $result->ID );
                            $element_class_name = $post_meta['field_type'][0];
                            
                            if ( class_exists( $element_class_name ) ) {
                                $element = new $element_class_name( $result->ID );
                                $custom_field_value = ( isset( $order->details->tc_cart_info['buyer_data'][$element->standard_field_name( $element->element_name, true ) . '_post_meta'] ) ? $order->details->tc_cart_info['buyer_data'][$element->standard_field_name( $element->element_name, true ) . '_post_meta'] : '' );
                                
                                if ( isset( $custom_field_value ) && !empty($custom_field_value) && !is_null( $custom_field_value ) ) {
                                    $custom_fields[] = array( $element->standard_field_label( $element->element_name, true ), $custom_field_value );
                                    //$custom_field_value
                                }
                            
                            }
                        
                        }
                    }
                }
            }
            
            //Owner form
            $owner_form = $forms->get_forms( 'owner', -1, $ticket_type->details->ID );
            
            if ( count( $owner_form ) >= 1 && (isset( $owner_form[0] ) && !is_null( $owner_form[0] )) ) {
                global  $wpdb ;
                $owner_form = $owner_form[0];
                $res = 1;
                for ( $i = 1 ;  $i <= apply_filters( 'tc_form_row_number', 20 ) ;  $i++ ) {
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT *, pm2.meta_value as ord FROM {$wpdb->posts} p, {$wpdb->postmeta} pm, {$wpdb->postmeta} pm2\n\t\t\t\t\t\t\t\t\t\t\tWHERE p.ID = pm.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND p.ID = pm2.post_id\n\t\t\t\t\t\t\t\t\t\t\tAND\tp.post_parent = %d\n\t\t\t\t\t\t\t\t\t\t\tAND (pm.meta_key = 'row' AND pm.meta_value = %d)\n\t\t\t\t\t\t\t\t\t\t\tAND (pm2.meta_key = 'order')\n\t\t\t\t\t\t\t\t\t\t\tORDER BY ord ASC", $owner_form->ID, $i ), OBJECT );
                    if ( !empty($results) ) {
                        foreach ( $results as $result ) {
                            $post_meta = get_post_meta( $result->ID );
                            $element_class_name = $post_meta['field_type'][0];
                            
                            if ( class_exists( $element_class_name ) ) {
                                $element = new $element_class_name( $result->ID );
                                
                                if ( $res == count( $results ) ) {
                                    $additional_field_class = 'tc_field_col_last_child';
                                } else {
                                    $additional_field_class = '';
                                }
                                
                                $custom_field_value = get_post_meta( $ticket_instance_id, $element->standard_field_name( $element->element_name, true ), true );
                                if ( isset( $custom_field_value ) && !empty($custom_field_value) && !is_null( $custom_field_value ) ) {
                                    $custom_fields[] = array( $element->standard_field_label( $element->element_name, true ), $custom_field_value );
                                }
                            }
                        
                        }
                    }
                }
            }
            
            return $custom_fields;
        }
        
        function add_custom_admin_fields_in_csv_addon()
        {
            $args = array(
                'post_type'              => 'tc_form_fields',
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'cache_results'          => false,
                'fields'                 => array( 'ID', 'post_parent' ),
            );
            $custom_fields = get_posts( $args );
            $settings = get_option( 'tc_atteende_keep_selection' );
            if ( count( $custom_fields ) > 0 ) {
                foreach ( $custom_fields as $custom_field ) {
                    $form_status = get_post_status( $custom_field->post_parent );
                    
                    if ( $form_status == 'publish' ) {
                        $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                        $form_type = get_post_meta( $custom_field->post_parent, 'form_type', true );
                        
                        if ( class_exists( $element_class_name ) ) {
                            $element = new $element_class_name( $custom_field->ID );
                            
                            if ( $element->standard_field_export( $element->element_name, true ) ) {
                                $field = $element->admin_order_details_page_value();
                                //check keep selection is on or not
                                if ( isset( $settings ) && !empty($settings) ) {
                                    
                                    if ( strpos( $settings, esc_attr( $field['id'] ) ) !== false ) {
                                        $cf_field = "checked='checked'";
                                    } else {
                                        $cf_field = "";
                                    }
                                
                                }
                                ?>
                                <label class="tc_checkboxes_label">
                                    <input type="checkbox" name="<?php 
                                echo  esc_attr( $field['id'] ) ;
                                ?>" <?php 
                                if ( $cf_field != '' ) {
                                    echo  $cf_field ;
                                }
                                ?>><?php 
                                echo  '<span class="tc_csv_export_field_label">' . esc_attr( $field['field_title'] ) . '</span><span class="tc_csv_export_custom_form_indication">' . get_the_title( $custom_field->post_parent ) . '</span>' ;
                                ?><br />
                                </label>
                                <?php 
                            }
                        
                        }
                    
                    }
                
                }
            }
        }
        
        function add_custom_fields_to_csv_addon_array(
            $tc_csv_array,
            $order,
            $ticket_instance,
            $post
        )
        {
            $args = array(
                'post_type'              => 'tc_form_fields',
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'cache_results'          => false,
                'fields'                 => array( 'ID', 'post_parent' ),
            );
            $custom_fields = get_posts( $args );
            if ( count( $custom_fields ) > 0 ) {
                foreach ( $custom_fields as $custom_field ) {
                    $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                    $form_type = get_post_meta( $custom_field->post_parent, 'form_type', true );
                    
                    if ( class_exists( $element_class_name ) ) {
                        $element = new $element_class_name( $custom_field->ID );
                        
                        if ( $element->standard_field_export( $element->element_name, true ) ) {
                            $field = $element->admin_order_details_page_value();
                            
                            if ( isset( $_POST[$field['id']] ) ) {
                                
                                if ( $form_type == 'owner' ) {
                                    $field_value = array(
                                        tc_make_unique_title( $field['field_title'], $custom_field->ID ) => ( isset( $ticket_instance->details->{$field['id']} ) ? $ticket_instance->details->{$field['id']} : '' ),
                                    );
                                    $tc_csv_array = array_merge( $tc_csv_array, $field_value );
                                }
                                
                                
                                if ( $form_type == 'buyer' ) {
                                    $field_value = array(
                                        tc_make_unique_title( $field['field_title'], $custom_field->ID ) => ( isset( $order->details->tc_cart_info['buyer_data'][$field['id'] . '_post_meta'] ) ? $order->details->tc_cart_info['buyer_data'][$field['id'] . '_post_meta'] : '' ),
                                    );
                                    $tc_csv_array = array_merge( $tc_csv_array, $field_value );
                                }
                            
                            }
                        
                        }
                    
                    }
                
                }
            }
            return $tc_csv_array;
        }
        
        function add_custom_admin_column_titles_in_pdf( $rows, $post )
        {
            $args = array(
                'post_type'              => 'tc_form_fields',
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'cache_results'          => false,
                'fields'                 => array( 'ID', 'post_parent' ),
            );
            $custom_fields = get_posts( $args );
            if ( count( $custom_fields ) > 0 ) {
                foreach ( $custom_fields as $custom_field ) {
                    $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                    $form_type = get_post_meta( $custom_field->post_parent, 'form_type', true );
                    $form_status = get_post_status( $custom_field->post_parent );
                    if ( $form_status == 'publish' ) {
                        
                        if ( class_exists( $element_class_name ) ) {
                            $element = new $element_class_name( $custom_field->ID );
                            
                            if ( $element->standard_field_export( $element->element_name, true ) ) {
                                $field = $element->admin_order_details_page_value();
                                
                                if ( $_POST[$element->form_metas['name']] == 'on' ) {
                                    $rows .= '<th align="center">' . $field['field_title'] . '</th>';
                                    ?>
                                    <label class="tc_checkboxes_label">
                                        <input type="checkbox" name="<?php 
                                    echo  esc_attr( $field['id'] ) ;
                                    ?>" checked="checked"><?php 
                                    echo  $field['field_title'] ;
                                    ?><br />
                                    </label>
                                    <?php 
                                }
                            
                            }
                        
                        }
                    
                    }
                }
            }
            return $rows;
        }
        
        function add_custom_admin_column_values_in_pdf(
            $rows,
            $order,
            $ticket_instance,
            $post
        )
        {
            $args = array(
                'post_type'              => 'tc_form_fields',
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'cache_results'          => false,
                'fields'                 => array( 'ID', 'post_parent' ),
            );
            $custom_fields = get_posts( $args );
            if ( count( $custom_fields ) > 0 ) {
                foreach ( $custom_fields as $custom_field ) {
                    $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                    $form_type = get_post_meta( $custom_field->post_parent, 'form_type', true );
                    
                    if ( class_exists( $element_class_name ) ) {
                        $element = new $element_class_name( $custom_field->ID );
                        
                        if ( $element->standard_field_export( $element->element_name, true ) ) {
                            $field = $element->admin_order_details_page_value();
                            
                            if ( isset( $_POST[$field['id']] ) && $_POST[$element->form_metas['name']] == 'on' ) {
                                if ( $form_type == 'owner' ) {
                                    $rows .= '<td>' . $ticket_instance->details->{$field['id']} . '</td>';
                                }
                                if ( $form_type == 'buyer' ) {
                                    $rows .= '<td>' . $order->details->tc_cart_info['buyer_data'][$field['id'] . '_post_meta'] . '</td>';
                                }
                            }
                        
                        }
                    
                    }
                
                }
            }
            return $rows;
        }
        
        function modify_form_field_value( $value )
        {
            
            if ( $value == 'owner' || $value == 'buyer' ) {
                if ( $value == 'owner' ) {
                    $value = 'Attendee';
                }
                $value = ucfirst( $value );
            }
            
            return $value;
        }
        
        function append_capabilities( $capabilities )
        {
            //Add additional capabilities to staff and admins
            $capabilities['manage_' . $this->name . '_cap'] = 1;
            return $capabilities;
        }
        
        function add_admin_menu_item_to_tc()
        {
            //Add additional menu item under Tickera admin menu
            global  $first_tc_menu_handler ;
            $handler = 'custom_fields';
            add_submenu_page(
                $first_tc_menu_handler,
                __( $this->title, 'cf' ),
                __( $this->title, 'cf' ),
                'manage_' . $this->name . '_cap',
                $this->name,
                $this->name . '_admin'
            );
            eval("function " . $this->name . "_admin() {require_once( '" . $this->plugin_dir . "includes/admin-pages/" . $this->name . ".php');}");
            do_action( $this->name . '_add_menu_items_after_' . $handler );
        }
        
        function load_addons()
        {
            require_once $this->plugin_dir . 'includes/classes/class.form_elements.php';
            $this->load_form_elements();
        }
        
        function load_virtual_tickets_elements()
        {
            global  $post ;
            
            if ( isset( $_GET['page'] ) && $_GET['page'] == 'tc_ticket_templates' || isset( $_GET['download_ticket'] ) || isset( $_GET['order_key'] ) || isset( $_GET['tc_download'] ) || isset( $_GET['tc_preview'] ) ) {
                $args = array(
                    'post_type'              => 'tc_form_fields',
                    'post_status'            => 'publish',
                    'posts_per_page'         => -1,
                    'no_found_rows'          => true,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'cache_results'          => false,
                    'fields'                 => array( 'ID', 'post_parent' ),
                );
                $custom_fields = get_posts( $args );
                if ( count( $custom_fields ) > 0 ) {
                    foreach ( $custom_fields as $custom_field ) {
                        $form_status = get_post_status( $custom_field->post_parent );
                        
                        if ( $form_status == 'publish' ) {
                            $element_class_name = get_post_meta( $custom_field->ID, 'field_type', true );
                            $form_type = get_post_meta( $custom_field->post_parent, 'form_type', true );
                            
                            if ( class_exists( $element_class_name ) ) {
                                $element = new $element_class_name( $custom_field->ID );
                                
                                if ( $element->standard_field_as_ticket_template( $element->element_name, true ) ) {
                                    $field = $element->admin_order_details_page_value();
                                    $class_name = $field['id'];
                                    $element_name = $field['id'];
                                    $element_title = $field['field_title'];
                                    $default_value = $field['field_title'];
                                    include $this->plugin_dir . 'includes/ticket-elements/virtual-ticket-element.php';
                                }
                            
                            }
                        
                        }
                    
                    }
                }
            }
        
        }
        
        function load_form_elements()
        {
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                
                if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'tc_export_attendee_list' ) {
                } else {
                    return;
                }
            
            }
            //get form elements dir
            $dir = $this->plugin_dir . 'includes/form-elements/';
            $form_elements = array();
            if ( !is_dir( $dir ) ) {
                return;
            }
            if ( !($dh = opendir( $dir )) ) {
                return;
            }
            while ( ($plugin = readdir( $dh )) !== false ) {
                if ( substr( $plugin, -4 ) == '.php' ) {
                    $form_elements[] = $dir . '/' . $plugin;
                }
            }
            closedir( $dh );
            sort( $form_elements );
            foreach ( $form_elements as $file ) {
                include $file;
            }
            do_action( 'tc_load_additional_elements' );
        }
        
        function front_header()
        {
            wp_enqueue_style(
                $this->name . '-fields-front',
                $this->plugin_url . 'css/front.css',
                array(),
                $this->version
            );
        }
        
        function admin_header()
        {
            //Add scripts and CSS for the plugin
            wp_enqueue_script(
                $this->name . '-admin',
                $this->plugin_url . 'js/admin.js',
                array(
                'jquery',
                'jquery-ui-core',
                'jquery-ui-sortable',
                'jquery-ui-draggable',
                'jquery-ui-droppable',
                'jquery-ui-accordion',
                'wp-color-picker',
                'thickbox',
                'media-upload'
            ),
                $this->version
            );
            wp_localize_script( $this->name . '-admin', 'tc_custom_fields_vars', array(
                'max_elements_message' => sprintf( __( 'Only %s elements per row are allowed', 'cf' ), apply_filters( 'tc_custom_form_elements_count_per_row', 3 ) ),
                'max_elements'         => apply_filters( 'tc_custom_form_elements_count_per_row', 3 ),
            ) );
            wp_enqueue_style(
                $this->name . '-admin',
                $this->plugin_url . 'css/admin.css',
                array(),
                $this->version
            );
            wp_enqueue_style(
                $this->name . '-fontawesome',
                $this->plugin_url . 'css/font-awesome.min.css',
                array(),
                $this->version
            );
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'cf', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'cf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'cf', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function register_custom_posts()
        {
            $args = array(
                'labels'             => array(
                'name'               => __( 'Forms', 'cf' ),
                'singular_name'      => __( 'Forms', 'cf' ),
                'add_new'            => __( 'Create New', 'cf' ),
                'add_new_item'       => __( 'Create New Form', 'cf' ),
                'edit_item'          => __( 'Edit Form', 'cf' ),
                'edit'               => __( 'Edit', 'cf' ),
                'new_item'           => __( 'New Form', 'cf' ),
                'view_item'          => __( 'View Form', 'cf' ),
                'search_items'       => __( 'Search Forms', 'cf' ),
                'not_found'          => __( 'No Forms Found', 'cf' ),
                'not_found_in_trash' => __( 'No Forms found in Trash', 'cf' ),
                'view'               => __( 'View Form', 'cf' ),
            ),
                'public'             => true,
                'show_ui'            => false,
                'publicly_queryable' => true,
                'capability_type'    => 'post',
                'hierarchical'       => false,
                'query_var'          => true,
            );
            register_post_type( 'tc_forms', $args );
            $args = array(
                'labels'             => array(
                'name'               => __( 'Custom Forms', 'cf' ),
                'singular_name'      => __( 'Custom Forms', 'cf' ),
                'add_new'            => __( 'Create New', 'cf' ),
                'add_new_item'       => __( 'Create New Custom Field', 'cf' ),
                'edit_item'          => __( 'Edit Custom Field', 'cf' ),
                'edit'               => __( 'Edit', 'cf' ),
                'new_item'           => __( 'New Custom Field', 'cf' ),
                'view_item'          => __( 'View Custom Field', 'cf' ),
                'search_items'       => __( 'Search Custom Forms', 'cf' ),
                'not_found'          => __( 'No Custom Forms Found', 'cf' ),
                'not_found_in_trash' => __( 'No Custom Forms found in Trash', 'cf' ),
                'view'               => __( 'View Custom Field', 'cf' ),
            ),
                'public'             => true,
                'show_ui'            => false,
                'publicly_queryable' => true,
                'capability_type'    => 'post',
                'hierarchical'       => false,
                'query_var'          => true,
            );
            register_post_type( 'tc_form_fields', $args );
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_custom_fields_load()
    {
        global  $tc_custom_fields ;
        $tc_custom_fields = new TC_Custom_Fields();
    }
    
    add_action( 'tets_fs_loaded', 'tc_custom_fields_load', 999 );
} else {
    $tc_custom_fields = new TC_Custom_Fields();
}
