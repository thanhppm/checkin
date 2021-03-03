<?php
/*
  Plugin Name: Checkin apps - redirect to attendee screen
  Plugin URI: https://tickera.com
  Description: When the addon is active, checkin apps will redirect to the attendee details screen upon scanning a ticket.
  Author: Tickera
  Author URI: https://tickera.com
  Version: 1.0
  TextDomain: tc
  Domain Path: /languages/

  Copyright 2019 Tickera (https://tickera.com)
 */
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
if (!class_exists('TC_checkin_apps_redirect_to_attendee_screen')) {
    class TC_checkin_apps_redirect_to_attendee_screen
    {
        public $version		 = '1.0';
        public $title		 = 'Checkin apps - redirect to attendee screen';
        public $name		 = 'checkin_apps_redirect_to_attendee_screen';
        public $dir_name	 = 'checkin_apps_redirect_to_attendee_screen';
        public $location	 = 'plugins';
        public $plugin_dir	 = '';
        public $plugin_url	 = '';
        public function __construct()
        {
            $this->init_vars();

            add_filter('tc_get_event_essentials_data_output', array($this, 'redirect_to_attendee_details_screen_upon_scanning_ticket'));
        }
        public function init_vars()
        {
            //setup proper directories
            if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename(__FILE__))) {
                $this->location		 = 'subfolder-plugins';
                $this->plugin_dir	 = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
                $this->plugin_url	 = plugins_url('/', __FILE__);
            } elseif (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location		 = 'plugins';
                $this->plugin_dir	 = WP_PLUGIN_DIR . '/';
                $this->plugin_url	 = plugins_url('/', __FILE__);
            } elseif (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location		 = 'mu-plugins';
                $this->plugin_dir	 = WPMU_PLUGIN_DIR;
                $this->plugin_url	 = WPMU_PLUGIN_URL;
            } else {
                wp_die(sprintf(__('There was an issue determining where %s is installed. Please reinstall it.', 'tc'), $this->title));
            }
        }

        function redirect_to_attendee_details_screen_upon_scanning_ticket($data){
          $data['show_attendee_screen'] = true;
          return $data;

        }
    }
}

$TC_checkin_apps_redirect_to_attendee_screen = new TC_checkin_apps_redirect_to_attendee_screen();
