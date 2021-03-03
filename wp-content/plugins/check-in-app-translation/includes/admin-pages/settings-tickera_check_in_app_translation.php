<?php
global $tc_checkin_api_translation_settings_data;
if ( isset( $_POST[ 'save_tc_translation_settings' ] ) ) {
	if ( check_admin_referer( 'save_settings' ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			update_option( 'tc_checkin_api_translation_settings', $_POST[ 'tc_checkin_api_translation_settings' ] );
			$message = __( 'Settings data has been successfully saved.', 'tran' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tran' );
		}
	}
}
$tc_checkin_api_translation_settings = get_option( 'tc_checkin_api_translation_settings', false );
?>
<div class="wrap tc_wrap">
    <div id="poststuff" class="metabox-holder tc-settings">
        <form action="" method="post" enctype = "multipart/form-data">
			<?php wp_nonce_field( 'save_settings' ); ?>
            <div id="check_in_app_translation_settings" class="postbox">
                <h3 class="hndle"><span><?php _e( 'Check-in App Translation', 'tran' ); ?></span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody> 
							<?php
							$original_data						 = array(
								'APP_TITLE'					 => 'Ticket Check-in',
								'WORDPRESS_INSTALLATION_URL' => 'WORDPRESS INSTALLATION URL',
								'API_KEY'					 => 'API KEY',
								'AUTO_LOGIN'				 => 'AUTO LOGIN',
								'SIGN_IN'					 => 'SIGN IN',
								'SOLD_TICKETS'				 => 'TICKETS SOLD',
								'CHECKED_IN_TICKETS'		 => 'CHECK-IN TICKETS',
								'HOME_STATS'				 => 'Home - Stats',
								'LIST'						 => 'LIST',
								'SIGN_OUT'					 => 'SIGN OUT',
								'CANCEL'					 => 'CANCEL',
								'SEARCH'					 => 'Search',
								'ID'						 => 'ID',
								'PURCHASED'					 => 'PURCHASED',
								'CHECKINS'					 => 'CHECK-INS',
								'CHECK_IN'					 => 'CHECK IN',
								'SUCCESS'					 => 'SUCCESS',
								'SUCCESS_MESSAGE'			 => 'Ticket has been check-in',
								'OK'						 => 'OK',
								'ERROR'						 => 'ERROR',
								'ERROR_MESSAGE'				 => 'Wrong ticket code',
								'PASS'						 => 'Pass',
								'FAIL'						 => 'Fail',
								'ERROR_LOADING_DATA'		 => 'Error loading data. Please check the URL and API KEY provided',
								'API_KEY_LOGIN_ERROR'		 => 'Error. Please check the URL and API KEY provided',
								'TICKET_TYPE'				 => 'Ticket Type',
								'BUYER_NAME'				 => 'Buyer Name',
								'BUYER_EMAIL'				 => 'Buyer E-mail',
								'PLEASE_WAIT'				 => 'Submitting data, please wait...',
								'EMPTY_LIST'				 => 'The list is empty',
								'BARCODE_SCAN_INFO'			 => 'Select input field and scan a barcode',
								'CHECK_IN_RECORDS_SYNCED'	 => 'check-in records synced with the online database successfully.',
								'ATTENDEES_DOWNLOADED'		 => 'Attendees and tickets data has been downloaded successfully.',
								'INFO'						 => 'Info',
								'ERROR_LICENSE_KEY'			 => 'License key is not valid. Please contact your administrator.',
                                                                'DIALOG_TEXT'			 => 'Are you sure you want to sign out?',
                                                                'DIALOG_HEADER'			 => 'Sign out?'
							);

							$data = array(
								'WORDPRESS_INSTALLATION_URL' => isset( $tc_checkin_api_translation_settings[ 'WORDPRESS_INSTALLATION_URL' ] ) ? $tc_checkin_api_translation_settings[ 'WORDPRESS_INSTALLATION_URL' ] : 'WORDPRESS INSTALLATION URL',
								'API_KEY'					 => isset( $tc_checkin_api_translation_settings[ 'API_KEY' ] ) ? $tc_checkin_api_translation_settings[ 'API_KEY' ] : 'API KEY',
								'AUTO_LOGIN'				 => isset( $tc_checkin_api_translation_settings[ 'AUTO_LOGIN' ] ) ? $tc_checkin_api_translation_settings[ 'AUTO_LOGIN' ] : 'AUTO LOGIN',
								'SIGN_IN'					 => isset( $tc_checkin_api_translation_settings[ 'SIGN_IN' ] ) ? $tc_checkin_api_translation_settings[ 'SIGN_IN' ] : 'SIGN IN',
								'SOLD_TICKETS'				 => isset( $tc_checkin_api_translation_settings[ 'SOLD_TICKETS' ] ) ? $tc_checkin_api_translation_settings[ 'SOLD_TICKETS' ] : 'TICKETS SOLD',
								'CHECKED_IN_TICKETS'		 => isset( $tc_checkin_api_translation_settings[ 'CHECKED_IN_TICKETS' ] ) ? $tc_checkin_api_translation_settings[ 'CHECKED_IN_TICKETS' ] : 'CHECK-IN TICKETS',
								'HOME_STATS'				 => isset( $tc_checkin_api_translation_settings[ 'HOME_STATS' ] ) ? $tc_checkin_api_translation_settings[ 'HOME_STATS' ] : 'Home - Stats',
								'LIST'						 => isset( $tc_checkin_api_translation_settings[ 'LIST' ] ) ? $tc_checkin_api_translation_settings[ 'LIST' ] : 'LIST',
								'SIGN_OUT'					 => isset( $tc_checkin_api_translation_settings[ 'SIGN_OUT' ] ) ? $tc_checkin_api_translation_settings[ 'SIGN_OUT' ] : 'SIGN OUT',
								'CANCEL'					 => isset( $tc_checkin_api_translation_settings[ 'CANCEL' ] ) ? $tc_checkin_api_translation_settings[ 'CANCEL' ] : 'CANCEL',
								'SEARCH'					 => isset( $tc_checkin_api_translation_settings[ 'SEARCH' ] ) ? $tc_checkin_api_translation_settings[ 'SEARCH' ] : 'Search',
								'ID'						 => isset( $tc_checkin_api_translation_settings[ 'ID' ] ) ? $tc_checkin_api_translation_settings[ 'ID' ] : 'ID',
								'PURCHASED'					 => isset( $tc_checkin_api_translation_settings[ 'PURCHASED' ] ) ? $tc_checkin_api_translation_settings[ 'PURCHASED' ] : 'PURCHASED',
								'CHECKINS'					 => isset( $tc_checkin_api_translation_settings[ 'CHECKINS' ] ) ? $tc_checkin_api_translation_settings[ 'CHECKINS' ] : 'CHECK-INS',
								'CHECK_IN'					 => isset( $tc_checkin_api_translation_settings[ 'CHECK_IN' ] ) ? $tc_checkin_api_translation_settings[ 'CHECK_IN' ] : 'CHECK IN',
								'SUCCESS'					 => isset( $tc_checkin_api_translation_settings[ 'SUCCESS' ] ) ? $tc_checkin_api_translation_settings[ 'SUCCESS' ] : 'SUCCESS',
								'SUCCESS_MESSAGE'			 => isset( $tc_checkin_api_translation_settings[ 'SUCCESS_MESSAGE' ] ) ? $tc_checkin_api_translation_settings[ 'SUCCESS_MESSAGE' ] : 'Ticket has been check-in',
								'OK'						 => isset( $tc_checkin_api_translation_settings[ 'OK' ] ) ? $tc_checkin_api_translation_settings[ 'OK' ] : 'OK',
								'ERROR'						 => isset( $tc_checkin_api_translation_settings[ 'ERROR' ] ) ? $tc_checkin_api_translation_settings[ 'ERROR' ] : 'ERROR',
								'ERROR_MESSAGE'				 => isset( $tc_checkin_api_translation_settings[ 'ERROR_MESSAGE' ] ) ? $tc_checkin_api_translation_settings[ 'ERROR_MESSAGE' ] : 'Wrong ticket code',
								'PASS'						 => isset( $tc_checkin_api_translation_settings[ 'PASS' ] ) ? $tc_checkin_api_translation_settings[ 'PASS' ] : 'Pass',
								'FAIL'						 => isset( $tc_checkin_api_translation_settings[ 'FAIL' ] ) ? $tc_checkin_api_translation_settings[ 'FAIL' ] : 'Fail',
								'ERROR_LOADING_DATA'		 => isset( $tc_checkin_api_translation_settings[ 'ERROR_LOADING_DATA' ] ) ? $tc_checkin_api_translation_settings[ 'ERROR_LOADING_DATA' ] : 'Error loading data. Please check the URL and API KEY provided',
								'API_KEY_LOGIN_ERROR'		 => isset( $tc_checkin_api_translation_settings[ 'API_KEY_LOGIN_ERROR' ] ) ? $tc_checkin_api_translation_settings[ 'API_KEY_LOGIN_ERROR' ] : 'Error. Please check the URL and API KEY provided',
								'APP_TITLE'					 => isset( $tc_checkin_api_translation_settings[ 'APP_TITLE' ] ) ? $tc_checkin_api_translation_settings[ 'APP_TITLE' ] : 'Ticket Check-in',
								'TICKET_TYPE'				 => isset( $tc_checkin_api_translation_settings[ 'TICKET_TYPE' ] ) ? $tc_checkin_api_translation_settings[ 'TICKET_TYPE' ] : 'Ticket Type',
								'BUYER_NAME'				 => isset( $tc_checkin_api_translation_settings[ 'BUYER_NAME' ] ) ? $tc_checkin_api_translation_settings[ 'BUYER_NAME' ] : 'Buyer Name',
								'BUYER_EMAIL'				 => isset( $tc_checkin_api_translation_settings[ 'BUYER_EMAIL' ] ) ? $tc_checkin_api_translation_settings[ 'BUYER_EMAIL' ] : 'Buyer E-mail',
								'PLEASE_WAIT'				 => isset( $tc_checkin_api_translation_settings[ 'PLEASE_WAIT' ] ) ? $tc_checkin_api_translation_settings[ 'PLEASE_WAIT' ] : 'Please wait...',
								'EMPTY_LIST'				 => isset( $tc_checkin_api_translation_settings[ 'EMPTY_LIST' ] ) ? $tc_checkin_api_translation_settings[ 'EMPTY_LIST' ] : 'The list is empty',
								'BARCODE_SCAN_INFO'			 => isset( $tc_checkin_api_translation_settings[ 'BARCODE_SCAN_INFO' ] ) ? $tc_checkin_api_translation_settings[ 'BARCODE_SCAN_INFO' ] : 'Select input field and scan a barcode',
								'CHECK_IN_RECORDS_SYNCED'	 => isset( $tc_checkin_api_translation_settings[ 'CHECK_IN_RECORDS_SYNCED' ] ) ? $tc_checkin_api_translation_settings[ 'CHECK_IN_RECORDS_SYNCED' ] : 'check-in records synced with the online database successfully.',
								'ATTENDEES_DOWNLOADED'		 => isset( $tc_checkin_api_translation_settings[ 'ATTENDEES_DOWNLOADED' ] ) ? $tc_checkin_api_translation_settings[ 'ATTENDEES_DOWNLOADED' ] : 'Attendees and tickets data has been downloaded successfully.',
								'INFO'						 => isset( $tc_checkin_api_translation_settings[ 'INFO' ] ) ? $tc_checkin_api_translation_settings[ 'INFO' ] : 'Info',
								'ERROR_LICENSE_KEY'			 => isset( $tc_checkin_api_translation_settings[ 'ERROR_LICENSE_KEY' ] ) ? $tc_checkin_api_translation_settings[ 'ERROR_LICENSE_KEY' ] : 'License key is not valid. Please contact your administrator.',
                                                                'DIALOG_TEXT'			 => isset( $tc_checkin_api_translation_settings[ 'DIALOG_TEXT' ] ) ? $tc_checkin_api_translation_settings[ 'DIALOG_TEXT' ] : 'Are you sure you want to sign out?',
                                                                'DIALOG_HEADER'			 => isset( $tc_checkin_api_translation_settings[ 'DIALOG_HEADER' ] ) ? $tc_checkin_api_translation_settings[ 'DIALOG_HEADER' ] : 'Sign out?'
							);
							?>

							<?php foreach ( $original_data as $key => $value ) { ?>
								<tr valign="top">
									<th scope="row"><label for="tc_checkin_api_translation_settings[<?php echo esc_attr( $key ); ?>]"><?php echo $value; ?></label></th>
									<td>
										<input type="text" name='tc_checkin_api_translation_settings[<?php echo esc_attr( $key ); ?>]' value='<?php echo $data[ $key ]; ?>' />            </td>
								</tr>
							<?php } ?>

                        </tbody>
                    </table>

					<?php submit_button( __( 'Save Settings' ), 'primary', 'save_tc_translation_settings' ); ?>

                </div><!-- .inside -->

            </div><!-- .postbox -->

        </form>
    </div><!-- #poststuff -->
</div><!-- .wrap -->