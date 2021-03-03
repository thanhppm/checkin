<?php

$settings = get_option( 'tc_atteende_keep_selection' );
if( isset( $settings ) && !empty( $settings ) ) {

    $all_setting = unserialize($settings);

    foreach ($all_setting['remember_setting'] as $keys => $values) {

        switch( $keys ) {

            case 'tc_list_from':
                $from_date = $values;
                break;

            case 'tc_list_to':
                $to_date = $values;
                break;

            case 'tc_limit_order_type':
                $order_status = $values;
                break;

            case 'document_title':
                $document_title = $values;
                break;

            case 'tc_export_csv_event_data':
                $tc_export_csv_event_data = $values;
                break;

            case 'tc_export_csv_ticket_type_data':
                $tc_export_csv_ticket_type_data = $values;
                break;

            case 'tc_keep_selection_fields':
                $tc_keep_selection_fields = ( 'on' == $values || 1 == $values ) ? "checked='checked'" : '';
                break;

            case 'tc_select_all_csv':
                $tc_select_all_csv = ( 'on' == $values || 1 == $values ) ? "checked='checked'" : '';
                break;
        }
    }

} else {

    $from_date = "";
    $to_date = "";
    $order_status="";
    $document_title = "";
    $tc_export_csv_event_data = "";
    $tc_export_csv_ticket_type_data = "";
    $tc_keep_selection_fields = "";
    $tc_select_all_csv = "";
}

?>
   <div class="wrap tc_wrap tc_csv_export">
    <div id="poststuff" class="metabox-holder tc-settings">
        <form id="tc_form_attendees_csv_export" method="post">
            <input type="hidden" name="action" value="tc_export_attendee_list" />
            <input type="hidden" name="page_num" id="page_num" value="1" />
            <div id="store_settings" class="postbox">
                <h3><span><?php _e('Attendee List (CSV Export)', 'tccsv'); ?></span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><label for="tc_export_csv_event_data"><?php _e('Event', 'tccsv'); ?></label></th>
                                <td>
                                    <?php
                                    $wp_events_search = new TC_Events_Search('', '', -1);
                                    ?>
                                    <select name="tc_export_csv_event_data" id="tc_export_csv_event_data">
                                        <?php
                                        foreach ($wp_events_search->get_results() as $event) {

                                            $event_obj = new TC_Event($event->ID);
                                            $event_object = $event_obj->details;
                                            $event_date = $event_obj->get_event_date();
                                            ?>
                                            <option <?php if($tc_export_csv_event_data == $event_object->ID){ echo "selected='selected'"; }?>value="<?php echo $event_object->ID; ?>"><?php echo apply_filters('tc_csv_event_name', $event_object->post_title . ' (' . $event_date . ')', $event_object); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <?php ?>
                                </td>
                            </tr>
                            <!-- added ticket type -->
                            <tr valign="top">
                                <th scope="row"><label for="tc_export_csv_ticket_type_data"><?php _e('Ticket Type', 'tccsv'); ?></label></th>
                                <td>
                                    <?php
                                    global $wpdb;

                                    if( isset( $tc_export_csv_ticket_type_data ) && $tc_export_csv_ticket_type_data != '' ) :

                                        $post_type = $wpdb->get_row("SELECT post_type FROM $wpdb->posts WHERE ID IN ($tc_export_csv_ticket_type_data)");

                                        if ( 'product' == $post_type->post_type ) {

                                            $ticket_type_args = array(
                                                'post_type' => 'product',
                                                'post_status' => 'publish',
                                                'meta_key' => '_event_name',
                                                'meta_value' => $tc_export_csv_event_data,
                                                'posts_per_page' => -1,
                                                'fields' => 'ids',
                                                'orderby' => 'ID'
                                            );
                                            $ticket_type = get_posts( $ticket_type_args );

                                        } else {

                                            $ticket_type_args = array(
                                                'post_type' => 'product',
                                                'post_status' => 'publish',
                                                'meta_key' => 'event_name',
                                                'meta_value' => $tc_export_csv_event_data,
                                                'posts_per_page' => -1,
                                                'fields' => 'ids',
                                                'orderby' => 'ID'
                                            );
                                            $ticket_type = get_posts( $ticket_type_args );
                                        }

                                        ?>
                                        <select name="tc_export_csv_ticket_type_data" id="tc_export_csv_ticket_type_data">

                                            <?php if( $ticket_type && $tc_export_csv_ticket_type_data ) : ?>
                                                <option <?php if($tc_export_csv_ticket_type_data != ''){echo "selected='selected'";}?> id="select_all" value=""><?php _e('ALL', 'tccsv'); ?></option>

                                            <?php else : ?>
                                                <option id="select_all" value=""><?php _e('ALL', 'tccsv'); ?></option>

                                            <?php endif; ?>

                                            <?php foreach ( $ticket_type as $ticket_type_id ) :

                                                $ticket_id = $ticket_type_id;
                                                $ticket_title =  get_the_title( $ticket_type_id );

                                                if ( $tc_export_csv_ticket_type_data ) : ?>
                                                    <option value="<?php echo $ticket_id;?>"><?php echo $ticket_title;?></option>

                                                <?php else : ?>
                                                    <option <?php if($tc_export_csv_ticket_type_data == $ticket_id){echo "selected='selected'";}?>value="<?php echo $ticket_id;?>"><?php echo $ticket_title;?></option>

                                                <?php endif; ?>

                                            <?php endforeach; ?>
                                        </select>
                                    <?php

                                    else : ?>
                                        <select name="tc_export_csv_ticket_type_data" id="tc_export_csv_ticket_type_data"></select>

                                    <?php endif; ?>
                                </td>
                            </tr>
                           <!-- added ticket type -->
                           <!--export from-to date-->
                            <tr valign="top">
                                <th scope="row"><label for="tc_list_from"><?php _e('Export From', 'tccsv'); ?></label></th>
                                <td width="30%">
                                    <input type="text" name='tc_list_from' class="tc_date_field" value='<?php if($from_date != ''){echo $from_date;}else{echo  _e('From Date', 'tccsv');} ?>' />
                                </td>
                                <th scope="row"><label for="tc_list_to"><?php _e('To', 'tccsv'); ?></label></th>
                                <td width="30%">
                                    <input type="text" name='tc_list_to' class="tc_date_field" value='<?php if($to_date != ''){echo $to_date;}else{echo _e('To Date', 'tccsv');} ?>' />
                                </td>
                            </tr>
		                    <!--export from-to date end-->

                            <tr valign="top">
                                <th scope="row"><label for="attendee_export_field"><?php _e('Show Columns', 'tccsv'); ?></label></th>
                                <td><fieldset>
                                        <?php
                                        $csv_fields = apply_filters('tc_csv_admin_fields', array(
                                            'col_owner_first_name' => __('Attendee First Name', 'tccsv'),
                                            'col_owner_last_name' => __('Attendee Last Name', 'tccsv'),
                                            'col_owner_name' => __('Attendee Full Name', 'tccsv'),
                                            'col_owner_email' => __('Attendee E-mail', 'tccsv'),
                                            'col_payment_date' => __('Payment Date', 'tccsv'),
                                            'col_order_number' => __('Order Number', 'tccsv'),
                                            'col_order_total' => __('Order Total', 'tccsv'),
                                            'col_order_total_once' => __('Order Total (Shown Once)', 'tccsv'),
                                            'col_payment_gateway' => __('Payment Gateway', 'tccsv'),
                                            'col_order_status' => __('Order Status', 'tccsv'),
                                            'col_discount_code' => __('Discount Code', 'tccsv'),
                                            'col_ticket_id' => __('Ticket Code', 'tccsv'),
                                            'col_ticket_instance_id' => __('Ticket ID', 'tccsv'),
                                            'col_ticket_type' => __('Ticket Type', 'tccsv'),
                                            'col_buyer_first_name' => __('Buyer First Name', 'tccsv'),
                                            'col_buyer_last_name' => __('Buyer Last Name', 'tccsv'),
                                            'col_buyer_name' => __('Buyer Full Name', 'tccsv'),
                                            'col_buyer_email' => __('Buyer Email', 'tccsv'),
                                            'col_checked_in' => __('Checked-in', 'tccsv'),
                                            'col_checkins' => __('Check-ins', 'tccsv'),
											'col_owner_api_key' => __('Api Key', 'tccsv'),
											'col_order_price' => __('Price', 'tccsv'),//Price checkbox
                                        ));

                                        $settings = get_option( 'tc_atteende_keep_selection' );
                                        if(isset($settings) && !empty($settings)) {
                                          $all_setting = unserialize($settings);
                                            foreach ($all_setting['remember_setting'] as $checkbox_keys => $checkbox_values) {
                                               if($checkbox_values == 'on' || $checkbox_values == 1){
                                                 $checkbox_key[] = $checkbox_keys;
                                               }
                                            }
                                          $csv_checked_by_default =  apply_filters('tc_csv_checked_fields', $checkbox_key);
                                        }else{
                                          $csv_checked_by_default =  apply_filters('tc_csv_checked_fields', array(
                                              'col_owner_name',
                                              'col_owner_email',
                                              'col_order_number',
                                              'col_ticket_id',
                                              'col_ticket_instance_id',
                                              'col_ticket_type',
                                              'col_buyer_name',
                                              'col_buyer_email',
                                              'col_owner_phone_number',
                                              'col_buyer_phone_number',
                                          ));
                                        }

                                        foreach ($csv_fields as $key => $val) {
                                            if (in_array($key, $csv_checked_by_default)) {
                                                $checked = 'checked="checked"';
                                            } else {
                                                $checked = '';
                                            }
                                            ?>
                                            <label for="<?php echo esc_attr($key); ?>" class="tc_checkboxes_label">
                                                <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" <?php echo esc_attr($checked); ?>>
                                                <?php echo esc_attr($val); ?>
                                            </label>
                                            <?php
                                        }
                                        ?>

                                        <?php do_action('tc_csv_admin_columns'); ?>
                                    </fieldset>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="tc_limit_order_type"><?php _e('Order Status', 'tccsv'); ?></label></th>
                                <td>
                                    <select name="tc_limit_order_type" id="tc_limit_order_type">
                                        <?php
                                        $payment_statuses = apply_filters('tc_csv_payment_statuses', array(
                                            'any' => __( 'Any', 'tccsv' ),
                                            'order_paid' => __( 'Paid', 'tccsv' ),
                                            'order_received' => __( 'Pending / Received', 'tccsv' ),
                                            'order_fraud'       =>  'Fraud Detected',
                                            'order_cancelled' => __( 'Cancelled', 'tccsv' ),
                                            'order_refunded' => __( 'Refunded', 'tccsv' )
                                        ));
                                        foreach ($payment_statuses as $payment_status_key => $payment_status_value) {
                                            ?>
                                            <option <?php if( isset( $order_status ) && $order_status == $payment_status_key ){ echo "selected='selected'";}?> value="<?php echo esc_attr($payment_status_key); ?>"><?php echo esc_attr($payment_status_value); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="document_title"><?php _e('Document Title', 'tccsv'); ?></label></th>
                                <td>
                                    <input type="text" name='document_title' id="document_title" value='<?php if($document_title != ''){echo $document_title;}else{ echo _e('Attendee List', 'tccsv');} ?>' />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="document_title"><?php _e('Select / Deselect All', 'tccsv'); ?></label></th>
                                <td>
                                  <input type="checkbox" name='tc_select_all_csv' id="tc_select_all_csv" <?php echo ( isset( $tc_select_all_csv ) && $tc_select_all_csv ) ? $tc_select_all_csv : ''; ?>/>
                                </td>
                            </tr>
                            <!-- Keep selection fields-->
                            <tr valign="top">
                                <th scope="row"><label for="document_title"><?php _e('Remember Export Fields', 'tccsv'); ?></label></th>
                                <td>
                                    <input type="checkbox" name='tc_keep_selection_fields' id="tc_keep_selection_fields" <?php if($tc_keep_selection_fields != ''){echo $tc_keep_selection_fields;}?>/>
                                </td>
                            </tr>
                            <!-- Keep selection fields-->
                        </tbody>
                    </table>

                    <div id="csv_export_progressbar"><div class="progress-label"></div></div>
                </div><!-- .inside -->

            </div><!-- .postbox -->

            <p class="submit">
                <input type="submit" name="export_csv_event_data" id="export_csv_event_data" class="button button-primary" value="Export Data">
            </p>

        </form>
    </div><!-- #poststuff -->
</div><!-- .wrap -->
