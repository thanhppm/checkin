<?php
/*
  Plugin Name: Tickera - Delete all Check-ins
  Plugin URI: http://tickera.com/
  Description: Adds a button named DELETE CHECKINS to Tickera Settings -> System tab which deletes all the check-ins for all the attendees
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0
  TextDomain: tc
  Domain Path: /languages/
  Copyright 2018 Tickera (http://tickera.com/)
 */

function tc_delete_all_checkins() {
    ?> <div class="postbox">
            <h3 class="hndle"> Delete all check-ins </h3>
            <div class="inside">
                <span class="description"> <font color="red"><strong>Please note that this is not reversible!</strong></font></br>Once deleted, check-ins cannot be reverted back!</span>
            <br />
            <form method="post"><input type="submit" name="submit" class="button button-primary" value="DELETE CHECKINS" /></form>
            <br />
            </div>
        </div>
    <?php
    if (isset($_POST['submit'])) {
        global $wpdb;
        $wpdb->query("UPDATE " . $wpdb->postmeta . " SET meta_value = '' WHERE meta_key = 'tc_checkins' ");
    }
}

add_action('tc_after_system', 'tc_delete_all_checkins');


