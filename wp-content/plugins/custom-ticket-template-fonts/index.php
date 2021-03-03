<?php

/*
 Plugin Name: Tickera - Custom Ticket Template Fonts
 Plugin URI: http://tickera.com/
 Description: Add custom ticket template fonts
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.0.7
 Text Domain: cttf
 Domain Path: /languages/

 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !function_exists( 'cttf_fs' ) ) {
    // Create a helper function for easy SDK access.
    function cttf_fs()
    {
        global  $cttf_fs ;
        
        if ( !isset( $cttf_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3180_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3180_MULTISITE', true );
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
            
            $cttf_fs = fs_dynamic_init( array(
                'id'               => '3180',
                'slug'             => 'custom-ticket-template-fonts',
                'premium_slug'     => 'custom-ticket-template-fonts',
                'type'             => 'plugin',
                'public_key'       => 'pk_55b857a77ae4f07237e79c149bdeb',
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
        
        return $cttf_fs;
    }

}
function cttf_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function cttf_fs_is_parent_active()
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

function cttf_fs_init()
{
    
    if ( cttf_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        cttf_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( cttf_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    cttf_fs_init();
} else {
    
    if ( cttf_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'cttf_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        cttf_fs_init();
    }

}

if ( !cttf_fs()->can_use_premium_code() ) {
    return;
}

if ( !class_exists( 'TC_Custom_Ticket_Template_Fonts' ) ) {
    class TC_Custom_Ticket_Template_Fonts
    {
        var  $version = '1.0.7' ;
        var  $title = 'Custom Ticket Template Fonts' ;
        var  $name = 'tc_custom_fields' ;
        var  $dir_name = 'custom-ticket-template-fonts' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            $this->init_vars();
            $this->init();
            require_once $this->plugin_dir . 'includes/classes/class.custom_font.php';
            require_once $this->plugin_dir . 'includes/classes/class.custom_fonts.php';
            require_once $this->plugin_dir . 'includes/classes/class.custom_fonts_search.php';
            add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
            add_action( 'tc_settings_menu_tickera_custom_fonts', array( &$this, 'tc_settings_menu_tickera_custom_fonts_show_page' ) );
            add_action(
                'tc_ticket_font',
                array( &$this, 'tc_load_custom_fonts' ),
                10,
                2
            );
            add_action(
                'upgrader_process_complete',
                array( &$this, 'tc_regenerate_fonts' ),
                10,
                2
            );
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'cttf' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        function init()
        {
            add_action( 'init', array( &$this, 'localization' ), 10 );
            add_action( 'init', array( &$this, 'register_custom_posts' ), 0 );
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'cttf', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    //load_plugin_textdomain( 'tc-mijireh', false, $this->plugin_dir . '/languages/' );
                    load_plugin_textdomain( 'cttf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'cttf', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function tc_regenerate_fonts( $upgrader_object, $options )
        {
            $current_plugin_path_name = 'tickera/tickera.php';
            if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
                foreach ( $options['plugins'] as $each_plugin ) {
                    
                    if ( $each_plugin == $current_plugin_path_name ) {
                        global  $pdf, $tc ;
                        require_once $tc->plugin_dir . 'includes/tcpdf/examples/tcpdf_include.php';
                        $custom_fonts_search = new TC_Custom_Fonts_Search();
                        foreach ( $custom_fonts_search->get_results() as $custom_font ) {
                            $custom_font_obj = new TC_Custom_Font( $custom_font->ID );
                            $attachment_id = $this->get_attachment_id( $custom_font_obj->details->custom_font_file_url );
                            $font = TCPDF_FONTS::addTTFfont(
                                get_attached_file( $attachment_id ),
                                'TrueType',
                                'ansi',
                                32
                            );
                        }
                    }
                
                }
            }
        }
        
        function tc_load_custom_fonts( $selected_font, $default_font )
        {
            global  $pdf, $tc ;
            require_once $tc->plugin_dir . 'includes/tcpdf/examples/tcpdf_include.php';
            $custom_fonts_search = new TC_Custom_Fonts_Search();
            foreach ( $custom_fonts_search->get_results() as $custom_font ) {
                $custom_font_obj = new TC_Custom_Font( $custom_font->ID );
                $current_font_title = $custom_font_obj->details->custom_font_name;
                $attachment_id = $this->get_attachment_id( $custom_font_obj->details->custom_font_file_url );
                $font = TCPDF_FONTS::addTTFfont(
                    get_attached_file( $attachment_id ),
                    'TrueTypeUnicode',
                    '',
                    96
                );
                $current_font_name = $font;
                //$font;
                ?>
                <option value='<?php 
                echo  esc_attr( $current_font_name ) ;
                ?>' <?php 
                selected( ( !empty($selected_font) ? $selected_font : $default_font ), $current_font_name, true );
                ?>><?php 
                echo  $current_font_title ;
                ?></option>
                <?php 
            }
        }
        
        /*
         * Get attachment ID by its URL
         */
        function get_attachment_id( $url )
        {
            global  $wpdb ;
            $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid='%s';", $url ) );
            return $attachment[0];
        }
        
        function register_custom_posts()
        {
            $args = array(
                'labels'             => array(
                'name'               => __( 'Custom Fonts', 'cttf' ),
                'singular_name'      => __( 'Custom Fonts', 'cttf' ),
                'add_new'            => __( 'Create New', 'cttf' ),
                'add_new_item'       => __( 'Create New Font', 'cttf' ),
                'edit_item'          => __( 'Edit Font', 'cttf' ),
                'edit'               => __( 'Edit', 'cttf' ),
                'new_item'           => __( 'New Font', 'cttf' ),
                'view_item'          => __( 'View Font', 'cttf' ),
                'search_items'       => __( 'Search Fonts', 'cttf' ),
                'not_found'          => __( 'No Fonts Found', 'cttf' ),
                'not_found_in_trash' => __( 'No Fonts found in Trash', 'cttf' ),
                'view'               => __( 'View Font', 'cttf' ),
            ),
                'public'             => false,
                'show_ui'            => false,
                'publicly_queryable' => false,
                'capability_type'    => 'post',
                'hierarchical'       => false,
                'query_var'          => true,
            );
            register_post_type( 'tc_custom_fonts', $args );
        }
        
        function tc_settings_new_menus_additional( $settings_tabs )
        {
            $settings_tabs['tickera_custom_fonts'] = __( 'Custom Ticket Fonts', 'cttf' );
            return $settings_tabs;
        }
        
        function tc_settings_menu_tickera_custom_fonts_show_page()
        {
            require_once $this->plugin_dir . 'includes/admin-pages/settings-tickera_custom_fonts.php';
        }
    
    }
    if ( !function_exists( 'is_plugin_active_for_network' ) ) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
    }
    
    if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
        function tc_custom_fonts_load()
        {
            global  $tc_custom_fonts ;
            $tc_custom_fonts = new TC_Custom_Ticket_Template_Fonts();
        }
        
        add_action( 'tets_fs_loaded', 'tc_custom_fonts_load' );
    } else {
        $tc_custom_fonts = new TC_Custom_Ticket_Template_Fonts();
    }

}

//Allow .ttf file format upload
add_filter( 'upload_mimes', 'tc_add_custom_upload_mimes' );
function tc_add_custom_upload_mimes( $existing_mimes )
{
    $existing_mimes['ttf'] = 'application/x-font-ttf';
    return $existing_mimes;
}
