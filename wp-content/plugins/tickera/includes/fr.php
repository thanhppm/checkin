<?php

tets_fs()->add_filter( 'show_deactivation_feedback_form', '__return_false');

function tc_get_license_key() {
  @$fr_license_key = tets_fs()->_get_license()->secret_key;

  if (!empty($fr_license_key)) {
    return $fr_license_key;
  } else {
    $tc_general_settings = get_option('tc_general_setting', false);
    $license_key = (defined('TC_LCK') && TC_LCK !== '') ? TC_LCK : (isset($tc_general_settings['license_key']) && $tc_general_settings['license_key'] !== '' ? $tc_general_settings['license_key'] : '');
    return $license_key;
  }
}

tets_fs()->add_action('addons/after_title', 'tc_add_fs_templates_addons_poststuff_before_bundle_message_and_link');

function tc_add_fs_templates_addons_poststuff_before_bundle_message_and_link() {
  if (tc_iw_is_wl() == false) {
    ?>
    <div class="updated"><p><?php printf(__('NOTE: All add-ons are included for FREE with the <a href="%s" target="_blank">Bundle Package</a>', 'tc'), 'https://tickera.com/pricing/?utm_source=plugin&utm_medium=upsell&utm_campaign=addons'); ?></p></div>
      <?php
    }
  }

  function tc_members_account_url($url) {
    return 'https://tickera.com/members';
  }

  tets_fs()->add_filter('pricing_url', 'tc_members_account_url');

  function tc_is_pr_only() {
    if (tets_fs()->is__premium_only()) {
      return true;
    }
    return false;
  }

  if (tets_fs()->is__premium_only()) {
    add_action('admin_init', 'tc_check_fs_license_key');

    function tc_fr_opt_in($license_key) {
      if (!empty($license_key)) {
        $license_key = mb_substr($license_key, 0, 32, 'utf-8'); //get first 32 characters

        //$opt_in_response = tets_fs()->opt_in(false, false, false, $license_key);
        //$opt_in_response = tets_fs()->opt_in( false, false, false, $license_key, false, false, false, null, fs_is_network_admin() ? tets_fs()->get_sites_for_network_level_optin() : array() );
        try {
          $opt_in_response = tets_fs()->activate_migrated_license( $license_key );

          if ( ! tets_fs()->can_use_premium_code() ) {
            //license key is wrong, will consider that migration is done anyway so we don't hit the server multiple times
            update_option('tc_migrated2fs', 3);
            return false;
          } else {
            //license key is valid so we'll redirect user to the event page
            update_option('tc_migrated2fs', 2);
            return true;
          }
        }catch (Exception $e) {
          //in case of Exception, we'll consider that license key isn't valid (it could be added manually in this case)
          update_option('tc_migrated2fs', 4);//we'll save it as 4 to know that there was an error / exception
          return false;
        }
      }
    }

    function tc_check_fs_license_key() {

      if (1 == get_option('tc_migrated2fs', 1)) {
        if (tets_fs()->has_api_connectivity() && !tets_fs()->is_registered()) {
          $license_key = tc_get_license_key();

          if (!empty($license_key)) {
            if (tc_fr_opt_in($license_key)) {
              if ( fs_is_network_admin() ) {
                wp_redirect(network_admin_url('plugins.php'));
              }else{
                wp_redirect(admin_url('edit.php?post_type=tc_events'));
              }
              exit;
            }
          }
        }
      }
    }
  }
  ?>
