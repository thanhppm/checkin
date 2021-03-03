<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Custom_Font' ) ) {

	class TC_Custom_Font {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $event	 = array();
		var $details;

		function __construct( $id = '', $output = 'OBJECT' ) {
			$this->id		 = $id;
			$this->output	 = $output;
			$this->details	 = get_post( $this->id, $this->output );

			$custom_fonts	 = new TC_Custom_Fonts();
			$fields		 = $custom_fonts->get_custom_fonts_fields();

			foreach ( $fields as $field ) {
				if ( !isset( $this->details->{$field[ 'field_name' ]} ) ) {
					$this->details->{$field[ 'field_name' ]} = get_post_meta( $this->id, $field[ 'field_name' ], true );
				}
			}
		}

		function TC_Custom_Font( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function get_custom_font() {
			$event = get_post_custom( $this->id, $this->output );
			return $event;
		}

		function delete_custom_font( $force_delete = true ) {
			if ( $force_delete ) {
				wp_delete_post( $this->id );
			} else {
				wp_trash_post( $this->id );
			}
		}

		function get_custom_font_id_by_name( $slug ) {

			$args = array(
				'name'			 => $slug,
				'post_type'		 => 'tc_custom_fonts',
				'post_status'	 => 'any',
				'posts_per_page' => 1
			);

			$post = get_posts( $args );

			if ( $post ) {
				return $post[ 0 ]->ID;
			} else {
				return false;
			}
		}

	}

}
?>