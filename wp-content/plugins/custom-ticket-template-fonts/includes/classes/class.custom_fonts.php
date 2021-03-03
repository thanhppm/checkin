<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Custom_Fonts' ) ) {

	class TC_Custom_Fonts {

		var $form_title				 = '';
		var $valid_admin_fields_type	 = array( 'text', 'textarea', 'image', 'function' );

		function __construct() {
			$this->form_title				 = __( 'Custom Fonts', 'cttf' );
			$this->valid_admin_fields_type	 = apply_filters( 'tc_valid_admin_fields_type', $this->valid_admin_fields_type );
		}

		function TC_Custom_Fonts() {
			$this->__construct();
		}

		function get_custom_fonts_fields() {
			global $tc;
			$default_fields = array(
				array(
					'field_name'		 => 'custom_font_name',
					'field_title'		 => __( 'Font Name', 'cttf' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Set the font name. ', 'cttf' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
				),
				array(
					'field_name'		 => 'custom_font',
					'field_title'		 => __( 'File', 'cttf' ),
					'field_type'		 => 'image',
					'field_description'	 => __( 'Browse for a font file.', 'cttf' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta',
					'default_value'		 => '',
				),
			);

			return apply_filters( 'tc_custom_fonts_fields', $default_fields );
		}

		function get_columns() {
			$fields	 = $this->get_custom_fonts_fields();
			$results = search_array( $fields, 'table_visibility', true );

			$columns = array();

			$columns[ 'ID' ] = __( 'ID', 'cttf' );

			foreach ( $results as $result ) {
				$columns[ $result[ 'field_name' ] ] = $result[ 'field_title' ];
			}

			$columns[ 'edit' ]	 = __( 'Edit', 'cttf' );
			$columns[ 'delete' ] = __( 'Delete', 'cttf' );

			return $columns;
		}

		function check_field_property( $field_name, $property ) {
			$fields	 = $this->get_custom_fonts_fields();
			$result	 = search_array( $fields, 'field_name', $field_name );
			return isset( $result[ 0 ][ 'post_field_type' ] ) ? $result[ 0 ][ 'post_field_type' ] : '';
		}

		function is_valid_custom_font_field_type( $field_type ) {
			if ( in_array( $field_type, $this->valid_admin_fields_type ) ) {
				return true;
			} else {
				return false;
			}
		}

		function get_custom_fonts() {
			
		}

		function add_new_custom_font() {
			global $user_id, $post;

			if ( isset( $_POST[ 'add_new_custom_font' ] ) ) {

				$metas				 = array();
				$post_field_types	 = tc_post_fields();

				foreach ( $_POST as $field_name => $field_value ) {

					if ( preg_match( '/_post_title/', $field_name ) ) {
						$title = $field_value;
					}

					if ( preg_match( '/_post_excerpt/', $field_name ) ) {
						$excerpt = $field_value;
					}

					if ( preg_match( '/_post_content/', $field_name ) ) {
						$content = $field_value;
					}

					if ( preg_match( '/_post_meta/', $field_name ) ) {
						$metas[ str_replace( '_post_meta', '', $field_name ) ] = $field_value;
					}

					do_action( 'tc_after_custom_font_post_field_type_check' );
				}

				$metas = apply_filters( 'tc_custom_fonts_metas', $metas );

				$arg = array(
					'post_author'	 => $user_id,
					'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
					'post_content'	 => (isset( $content ) ? $content : ''),
					'post_status'	 => 'publish',
					'post_title'	 => (isset( $title ) ? $title : ''),
					'post_type'		 => 'tc_custom_fonts',
				);

				if ( isset( $_POST[ 'post_id' ] ) ) {
					$arg[ 'ID' ] = $_POST[ 'post_id' ]; //for edit 
				}

				$post_id = @wp_insert_post( $arg, true );

				//Update post meta
				if ( $post_id !== 0 ) {
					if ( isset( $metas ) ) {
						foreach ( $metas as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}

				return $post_id;
			}
		}

	}

}
?>
