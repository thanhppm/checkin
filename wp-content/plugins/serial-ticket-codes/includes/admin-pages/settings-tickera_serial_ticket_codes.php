
<?php $tc_user_rules = apply_filters('tc_serial_apply_rules', true);

if($tc_user_rules == true){?>
<script type="text/javascript">
    jQuery( document ).ready( function ( $ ) {
        jQuery.validator.addMethod( "accept", function ( value, element, param ) {
            var is_valid = value.match( new RegExp( param ) );//"." + param + "$"
            if ( value == '' ) {
                is_valid = true;
            }
            return is_valid;
        }, 'Only numbers 0-9 and letters A-Z are allowed.' );
        char = <?php echo apply_filters('tc_maxlenght_serial', 3); ?>;
        jQuery.validator.addClassRules( "accept_numbers_and_a_z_letters_only", { accept: "^[a-zA-Z0-9]{1,"+char+"}$" } );//accept: "/^[a-zA-Z0-9]$/"

        jQuery( ".tc_form_validation_serial_tickets" ).validate();
    } );
</script>
<?php } ?>

<?php
if ( isset( $_POST[ 'save_tc_serial_tickets_settings' ] ) ) {
	if ( check_admin_referer( 'save_settings' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'save_settings_cap' ) ) {
			update_option( 'tc_serial_tickets_setting', $_POST[ 'tc_serial_tickets_setting' ] );
			$message = __( 'Settings data has been successfully saved.', 'serial' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'serial' );
		}
	}
}
?>
<div class="wrap tc_wrap">
	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

    <div id="poststuff" class="metabox-holder tc-settings">

        <form id="tc-serial-tickets-setting" class="tc_form_validation_serial_tickets" method="post" action="edit.php?post_type=tc_events&page=<?php echo esc_attr( $_GET[ 'page' ] ); ?>&tab=<?php echo esc_attr( $_GET[ 'tab' ] ); ?>">
			<?php
			wp_nonce_field( 'save_settings' );

			$serial_tickets_settings = new TC_Settings_Serial_Tickets();
			$sections				 = $serial_tickets_settings->get_settings_serial_tickets_sections();

			foreach ( $sections as $section ) {
				?>
				<div id="<?php echo esc_attr( $section[ 'name' ] ); ?>" class="postbox">
					<h3><span><?php echo esc_attr( $section[ 'title' ] ); ?></span></h3>
					<div class="inside">
						<span class="description"><?php echo $section[ 'description' ]; ?></span>
						<table class="form-table">
							<?php
							$fields = $serial_tickets_settings->get_settings_serial_tickets_fields();

							foreach ( $fields as $field ) {
								if ( isset( $field[ 'section' ] ) && $field[ 'section' ] == $section[ 'name' ] ) {
									?>
									<tr valign="top" id="<?php echo esc_attr( $field[ 'field_name' ] . '_holder' ); ?>" <?php TC_Fields::conditionals( $field ); ?>>
										<th scope="row"><label for="<?php echo esc_attr( $field[ 'field_name' ] ); ?>"><?php echo $field[ 'field_title' ]; ?><?php (isset( $field[ 'tooltip' ] ) ? tc_tooltip( $field[ 'tooltip' ] ) : ''); ?></label></th>
										<td>
											<?php
											do_action( 'tc_before_serial_ticket_settings_field_type_check' );
											TC_Fields::render_field( $field, 'tc_serial_tickets_setting' );
											do_action( 'tc_after_serial_ticket_settings_field_type_check' );
											?>
										</td>
									</tr>
									<?php
								}
							}
							?>
						</table>
						<div class="description"><?php _e( '<strong>IMPORTANT: </strong> before going to production, if you\'re using bar codes on the tickets, please make sure that your chosen barcode type accepts this kind of formatting.', 'serial' ); ?></div>
					</div>
				</div>
			<?php } ?>

			<?php submit_button( __( 'Save Settings' ), 'primary', 'save_tc_serial_tickets_settings' ); ?>

        </form>
    </div>
</div>