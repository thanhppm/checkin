<?php
global $tc;

$custom_fonts = new TC_Custom_Fonts();

$page	 = $_GET[ 'page' ];
$tab	 = $_GET[ 'tab' ];

if ( isset( $_POST[ 'add_new_custom_font' ] ) ) {
	if ( check_admin_referer( 'save_custom_font' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'add_custom_font_cap' ) ) {
			$custom_fonts->add_new_custom_font();
			$message = __( 'Custom Font data has been successfully saved.', 'cttf' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'cttf' );
		}
	}
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) {
	$custom_font = new TC_Custom_Font( (int) $_GET[ 'ID' ] );
	$post_id	 = (int) $_GET[ 'ID' ];
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' ) {
	if ( !isset( $_POST[ '_wpnonce' ] ) ) {
		check_admin_referer( 'delete_' . $_GET[ 'ID' ] );
		if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_custom_font_cap' ) ) {
			$custom_font = new TC_Custom_Font( (int) $_GET[ 'ID' ] );
			$custom_font->delete_custom_font();
			$message	 = __( 'Custom Font has been successfully deleted.', 'cttf' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'cttf' );
		}
	}
}

if ( isset( $_GET[ 'page_num' ] ) ) {
	$page_num = (int) $_GET[ 'page_num' ];
} else {
	$page_num = 1;
}

if ( isset( $_GET[ 's' ] ) ) {
	$custom_fonts_search = $_GET[ 's' ];
} else {
	$custom_fonts_search = '';
}

$wp_custom_fonts_search	 = new TC_Custom_Fonts_Search( $custom_fonts_search, $page_num );
$fields					 = $custom_fonts->get_custom_fonts_fields();
$columns				 = $custom_fonts->get_columns();
?>
<div class="wrap tc_wrap">
    
        <div id="poststuff" class="metabox-holder tc-settings">
            <div id="store_settings" class="postbox">
                <h3 class="hndle"><span><?php _e( 'Add Custom Fonts for Tickets', 'cttf' ); ?></span></h3>
                <div class="inside">
    
    <h2><?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) { ?><a href="edit.php?post_type=tc_events&page=<?php echo $_GET[ 'page' ] . '&tab=' . $_GET[ 'tab' ]; ?>" class="add-new-h2"><?php _e( 'Add New', 'cttf' ); ?></a><?php } ?></h2>

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

    <form action="" method="post" enctype = "multipart/form-data">
		<?php wp_nonce_field( 'save_custom_font' ); ?>
		<?php
		if ( isset( $post_id ) ) {
			?>
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
			<?php
		}
		?>
        <table class="event-table">
            <tbody>
				<?php foreach ( $fields as $field ) { ?>
					<?php if ( $custom_fonts->is_valid_custom_font_field_type( $field[ 'field_type' ] ) ) { ?>    
						<tr valign="top">

							<th scope="row"><label for="<?php echo $field[ 'field_name' ]; ?>"><?php echo $field[ 'field_title' ]; ?></label></th>

							<td>
								<?php do_action( 'tc_before_custom_fonts_field_type_check' ); ?>
								<?php
								if ( $field[ 'field_type' ] == 'function' ) {
									eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '"' . (isset( $post_id ) ? ',' . $post_id : '') . ');' );
									?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'text' ) { ?>
									<input type="text" class="regular-<?php echo $field[ 'field_type' ]; ?>" value="<?php
									if ( isset( $custom_font ) ) {
										if ( $field[ 'post_field_type' ] == 'post_meta' ) {
											echo stripslashes( esc_attr( isset( $custom_font->details->{$field[ 'field_name' ]} ) ? $custom_font->details->{$field[ 'field_name' ]} : ''  ) );
										} else {
											echo stripslashes( esc_attr( $custom_font->details->{$field[ 'post_field_type' ]} ) );
										}
									} else {
										echo stripslashes( esc_attr( isset( $field[ 'default_value' ] ) ? $field[ 'default_value' ] : ''  ) );
									}
									?>" id="<?php echo esc_attr( $field[ 'field_name' ] ); ?>" name="<?php echo esc_attr( $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ); ?>">
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?>
									<textarea class="regular-<?php echo $field[ 'field_type' ]; ?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"><?php
										if ( isset( $custom_font ) ) {
											if ( $field[ 'post_field_type' ] == 'post_meta' ) {
												echo esc_textarea( isset( $custom_font->details->{$field[ 'field_name' ]} ) ? $custom_font->details->{$field[ 'field_name' ]} : ''  );
											} else {
												echo esc_textarea( $custom_font->details->{$field[ 'post_field_type' ]} );
											}
										}
										?></textarea>
									<br /><?php echo $field[ 'field_description' ]; ?>
								<?php } ?>
								<?php
								//Image
								if ( $field[ 'field_type' ] == 'image' ) {
									?>
									<div class="file_url_holder">
										<label>
											<input class="file_url" type="text" size="36" name="<?php echo $field[ 'field_name' ] . '_file_url_' . $field[ 'post_field_type' ]; ?>" value="<?php
											if ( isset( $custom_font ) ) {
												echo esc_attr( isset( $custom_font->details->{$field[ 'field_name' ] . '_file_url'} ) ? $custom_font->details->{$field[ 'field_name' ] . '_file_url'} : ''  );
											}
											?>" />
											<input class="file_url_button button-secondary" type="button" value="<?php _e( 'Browse', 'cttf' ); ?>" />
											<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
										</label>
									</div>
								<?php }
								?>
								<?php do_action( 'tc_after_custom_fonts_field_type_check' ); ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
            </tbody>
        </table>

		<?php submit_button( (isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'edit' ? __( 'Update', 'cttf' ) : __( 'Add New', 'cttf' ) ), 'primary', 'add_new_custom_font', true ); ?>

    </form>



    <div class="tablenav">
        <div class="alignright actions new-actions">
            <form method="get" action="?page=<?php echo esc_attr( $page ); ?>" class="search-form">
                <p class="search-box">
                    <input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>' />
                    <input type='hidden' name='tab' value='<?php echo esc_attr( $tab ); ?>' />
                    <label class="screen-reader-text"><?php _e( 'Search Custom Fonts', 'cttf' ); ?>:</label>
                    <input type="text" value="<?php echo esc_attr( $custom_fonts_search ); ?>" name="s">
                    <input type="submit" class="button" value="<?php _e( 'Search Custom Fonts', 'cttf' ); ?>">
                </p>
            </form>
        </div><!--/alignright-->

    </div><!--/tablenav-->

    <table cellspacing="0" class="widefat shadow-table">
        <thead>
            <tr>
				<?php
				$n = 1;
				foreach ( $columns as $key => $col ) {
					?>
					<th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo (isset( $col_sizes[ $n ] ) ? $col_sizes[ $n ] . '%' : ''); ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
					<?php
					$n++;
				}
				?>
            </tr>
        </thead>

        <tbody>
			<?php
			$style = '';

			foreach ( $wp_custom_fonts_search->get_results() as $custom_font ) {

				$custom_font_obj	 = new TC_Custom_Font( $custom_font->ID );
				$custom_font_object	 = apply_filters( 'tc_custom_font_object_details', $custom_font_obj->details );

				$style	 = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
				?>
				<tr id='user-<?php echo $custom_font_object->ID; ?>' <?php echo $style; ?>>
					<?php
					$n		 = 1;
					foreach ( $columns as $key => $col ) {
						if ( $key == 'edit' ) {
							?>
							<td>                    
								<a class="custom_fonts_edit_link" href="<?php echo admin_url( 'edit.php?post_type=tc_events&page=' . $tc->name . '_settings&tab=' . $_GET[ 'tab' ] . '&action=' . $key . '&ID=' . $custom_font_object->ID ); ?>"><?php _e( 'Edit', 'cttf' ); ?></a>
							</td>
						<?php } elseif ( $key == 'delete' ) {
							?>
							<td>
								<a class="custom_fonts_edit_link tc_delete_link" href="<?php echo wp_nonce_url( 'edit.php?post_type=tc_events&page=' . $tc->name . '_settings&tab=' . $_GET[ 'tab' ] . '&action=' . $key . '&ID=' . $custom_font_object->ID, 'delete_' . $custom_font_object->ID ); ?>"><?php _e( 'Delete', 'cttf' ); ?></a>
							</td>
							<?php
						} else {
							?>
							<td>
								<?php
								$post_field_type = $custom_fonts->check_field_property( $key, 'post_field_type' );

								if ( isset( $post_field_type ) && $post_field_type == 'post_meta' ) {
									echo apply_filters( 'tc_custom_font_field_value', $custom_font_object->$key, $post_field_type, $key );
								} else {
									echo apply_filters( 'tc_custom_font_field_value', (isset( $custom_font_object->$post_field_type ) ? $custom_font_object->$post_field_type : $custom_font_object->$key ), $post_field_type, $key );
								}
								?>
							</td>
							<?php
						}
					}
					?>
				</tr>
				<?php
			}
			?>

			<?php
			if ( count( $wp_custom_fonts_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="6"><div class="zero-records"><?php _e( 'No Custom Fonts found.', 'cttf' ) ?></div></td>
				</tr>
				<?php
			}
			?>
        </tbody>
    </table><!--/widefat shadow-table-->

    <div class="tablenav">
        <div class="tablenav-pages"><?php $wp_custom_fonts_search->page_links(); ?></div>
    </div><!--/tablenav-->

</div>
                
            </div>
        </div><!-- #poststuff -->
</div><!-- .wrap -->