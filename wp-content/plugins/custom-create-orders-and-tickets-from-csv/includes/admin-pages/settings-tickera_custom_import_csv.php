<div class="wrap tc_wrap">
    <div id="poststuff" class="metabox-holder tc-settings">
		<form id="tc_form_import_csv_export" name="tc_form_import_csv_export" enctype="multipart/form-data" method="post">
			<div id="store_settings" class="postbox">
				<h3><span><?php _e( 'Import CSV', 'tc' ); ?></span></h3>
				<div class="inside">
					<table class="form-table">
						<tbody> 
							<tr valign="top">
								<th scope="row"><label for="tc_ticket_type"><?php _e( 'Ticket Type', 'tc' ); ?></label></th>
								<td>
									<select name="tc_ticket_type_id">
										<?php
										$wp_tickets_search = new TC_Tickets_Search( '', '', -1 );
										foreach ( $wp_tickets_search->get_results() as $ticket_type ) {
											$ticket = new TC_Ticket( $ticket_type->ID );
											?>
											<option value="<?php echo esc_attr( $ticket->details->ID ); ?>"><?php echo $ticket->details->post_title . ' (' . get_the_title( $ticket->details->event_name ) . ')'; ?></option>
											<?php
										}
										?>
									</select>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="tc_export_csv_event_data"><?php _e( 'CSV File', 'tc' ); ?></label></th>
								<td>
									<input type="file" name="tc_csv_import_file" />
								</td>
							</tr>

						</tbody>
					</table>

					<p class="submit">
						<input type="submit" name="tc_custom_import_csv" id="tc_custom_import_csv" class="button button-primary" value="Import Data">
					</p>

				</div><!-- .inside -->

			</div><!-- .postbox -->
		</form>
	</div>
</div>