<?php

/*
 Plugin Name: Tickera - serial ticket codes
 Plugin URI: https://tickera.com/
 Description: Generate serial ticket codes
 Author: Tickera.com
 Author URI: https://tickera.com/
 Version: 1.0.9
 Text Domain: serial
 Domain Path: /languages/

 Copyright 2019 Tickera (https://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !function_exists( 'tcstc_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcstc_fs()
    {
        global  $tcstc_fs ;
        
        if ( !isset( $tcstc_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3178_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3178_MULTISITE', true );
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
            
            $tcstc_fs = fs_dynamic_init( array(
                'id'               => '3178',
                'slug'             => 'serial-ticket-codes',
                'premium_slug'     => 'serial-ticket-codes',
                'type'             => 'plugin',
                'public_key'       => 'pk_c89f1d891d7adc002cdb216c79f73',
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
        
        return $tcstc_fs;
    }

}
function tcstc_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcstc_fs_is_parent_active()
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

function tcstc_fs_init()
{
    
    if ( tcstc_fs_is_parent_active_and_loaded() ) {
        // Parent is active, add your init code here.
        // Init Freemius.
        tcstc_fs();
        if ( !tcstc_fs()->can_use_premium_code() ) {
            return;
        }
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcstc_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcstc_fs_init();
} else {
    
    if ( tcstc_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcstc_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcstc_fs_init();
    }

}

if ( !class_exists( 'TC_Serial_Ticket_Codes' ) ) {
    class TC_Serial_Ticket_Codes
    {
        var  $version = '1.0.8' ;
        var  $title = 'Tickera Serial Ticket Codes' ;
        var  $name = 'serial-ticket-codes' ;
        var  $dir_name = 'serial-ticket-codes' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            $this->init_vars();
            add_action( 'plugins_loaded', array( &$this, 'localization' ), 9 );
            add_action( 'plugins_loaded', array( &$this, 'tc_ticket_code_change' ), 10 );
            add_action( 'tc_settings_menu_tickera_ticket_serial_code', array( &$this, 'tc_settings_menu_tickera_ticket_serial_code_show_page' ) );
            add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_ticket_serial_code' ) );
            add_filter( 'tc_ticket_code', array( &$this, 'tc_get_next_ticket_serial_code' ) );
            add_filter(
                'tc_delete_info_plugins_list',
                array( &$this, 'tc_delete_info_plugins_list_serial_codes' ),
                10,
                1
            );
            add_action(
                'tc_delete_plugins_data',
                array( &$this, 'tc_delete_plugins_data_serial_codes' ),
                10,
                1
            );
        }
        
        function tc_ticket_code_change()
        {
            require_once plugin_dir_path( __FILE__ ) . 'includes/classes/class.settings_serial_tickets.php';
        }
        
        function tc_settings_new_menus_ticket_serial_code( $settings_tabs )
        {
            $settings_tabs['tickera_ticket_serial_code'] = __( 'Serial Tickets', 'serial' );
            return $settings_tabs;
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'serial', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'serial', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'serial', false, 'languages/' );
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
        
        function tc_settings_menu_tickera_ticket_serial_code_show_page()
        {
            require_once plugin_dir_path( __FILE__ ) . 'includes/admin-pages/settings-tickera_serial_ticket_codes.php';
        }
        
        function tc_get_next_ticket_serial_code()
        {
            global  $wpdb ;
            $tc_serial_tickets_setting = $wpdb->get_var( $wpdb->prepare( "SELECT `option_value` FROM {$wpdb->options} WHERE `option_name` = 'tc_serial_tickets_setting'", ARRAY_A ) );
            $tc_serial_tickets_setting = maybe_unserialize( $tc_serial_tickets_setting );
            $tc_custom_ticket_serial_next_number = ( isset( $tc_serial_tickets_setting['tc_custom_ticket_serial_next_number'] ) ? $tc_serial_tickets_setting['tc_custom_ticket_serial_next_number'] : '1' );
            $tc_custom_ticket_serial_next_number = (int) $tc_custom_ticket_serial_next_number;
            $tc_custom_ticket_serial_prefix = ( isset( $tc_serial_tickets_setting['tc_custom_ticket_serial_prefix'] ) ? $tc_serial_tickets_setting['tc_custom_ticket_serial_prefix'] : '' );
            $tc_custom_ticket_serial_sufix = ( isset( $tc_serial_tickets_setting['tc_custom_ticket_serial_sufix'] ) ? $tc_serial_tickets_setting['tc_custom_ticket_serial_sufix'] : '' );
            $tc_custom_ticket_serial_code_length = ( isset( $tc_serial_tickets_setting['tc_custom_ticket_serial_code_length'] ) ? $tc_serial_tickets_setting['tc_custom_ticket_serial_code_length'] : 10 );
            $tc_custom_ticket_serial_pad_string = ( isset( $tc_serial_tickets_setting['tc_custom_ticket_serial_pad_string'] ) ? $tc_serial_tickets_setting['tc_custom_ticket_serial_pad_string'] : '0' );
            
            if ( !empty($tc_custom_ticket_serial_pad_string) || $tc_custom_ticket_serial_pad_string == '0' ) {
                $next_ticket_code = str_pad(
                    (string) $tc_custom_ticket_serial_next_number,
                    (int) $tc_custom_ticket_serial_code_length,
                    $tc_custom_ticket_serial_pad_string,
                    STR_PAD_LEFT
                );
            } else {
                $next_ticket_code = $tc_custom_ticket_serial_next_number;
            }
            
            $next_ticket_code = $tc_custom_ticket_serial_prefix . $next_ticket_code . $tc_custom_ticket_serial_sufix;
            $tc_serial_tickets_setting['tc_custom_ticket_serial_next_number'] = $tc_custom_ticket_serial_next_number + 1;
            update_option( 'tc_serial_tickets_setting', $tc_serial_tickets_setting );
            return $next_ticket_code;
        }
        
        function tc_delete_info_plugins_list_serial_codes( $plugins )
        {
            $plugins['serial-ticket-codes'] = __( 'Serial Tickets', 'serial' );
            return $plugins;
        }
        
        function tc_delete_plugins_data_serial_codes( $submitted_data )
        {
            
            if ( array_key_exists( 'serial-ticket-codes', $submitted_data ) ) {
                global  $wpdb ;
                //Delete options
                $options = array( 'tc_serial_tickets_setting' );
                foreach ( $options as $option ) {
                    delete_option( $option );
                }
            }
        
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_serial_ticket_codes_load()
    {
        global  $tc_serial_ticket_codes ;
        $tc_serial_ticket_codes = new TC_Serial_Ticket_Codes();
    }
    
    add_action( 'tets_fs_loaded', 'tc_serial_ticket_codes_load' );
} else {
    $tc_serial_ticket_codes = new TC_Serial_Ticket_Codes();
}
