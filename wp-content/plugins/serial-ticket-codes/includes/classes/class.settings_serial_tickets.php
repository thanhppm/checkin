<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Settings_Serial_Tickets' ) ) {

	class TC_Settings_Serial_Tickets {

		function __construct() {
			
		}

		function TC_Settings_Serial_Tickets() {
			$this->__construct();
		}

		function get_settings_serial_tickets_sections() {
			$sections = array(
				array(
					'name'			 => 'serial_tickets_settings',
					'title'			 => __( 'Serial Tickets' ),
					'description'	 => '',
				),
			);

			$sections = apply_filters( 'tc_settings_serial_tickets_sections', $sections );

			return $sections;
		}

		function get_settings_serial_tickets_fields() {

			$fields = array();

			$fields [] = array(
				'field_name'	 => 'tc_custom_ticket_serial_next_number',
				'field_title'	 => __( 'Next number of the serial ticket', 'serial' ),
				'field_type'	 => 'option',
				'default_value'	 => '1',
				'tooltip'		 => 'This is base number which will be incremented each time a ticket is generated.',
				'section'		 => 'serial_tickets_settings',
				'number'		 => true,
				'required'		 => true,
				'maxlength'		 => 8,
			);

			$fields [] = array(
				'field_name'	 => 'tc_custom_ticket_serial_prefix',
				'field_title'	 => __( 'Serial ticket code prefix', 'serial' ),
				'field_type'	 => 'option',
				'default_value'	 => '',
				'tooltip'		 => 'This is the character or set of characters which will be prepended to a ticket code (added BEFORE the base number).',
				'section'		 => 'serial_tickets_settings',
				'maxlength'		 => apply_filters('tc_maxlenght_serial', 3),
				'minlength'		 => 0,
				'field_class'	 => 'accept_numbers_and_a_z_letters_only'
			);

			$fields [] = array(
				'field_name'	 => 'tc_custom_ticket_serial_sufix',
				'field_title'	 => __( 'Serial ticket code suffix', 'serial' ),
				'field_type'	 => 'option',
				'default_value'	 => '',
				'tooltip'		 => 'This is the character or set of characters which will be appended to a ticket code (added AFTER the base number).',
				'section'		 => 'serial_tickets_settings',
				'maxlength'		 => apply_filters('tc_maxlenght_serial',3 ),
				'minlength'		 => 0,
				'field_class'	 => 'accept_numbers_and_a_z_letters_only'
			);

			$fields [] = array(
				'field_name'	 => 'tc_custom_ticket_serial_code_length',
				'field_title'	 => __( 'Serial ticket code minimum length', 'serial' ),
				'field_type'	 => 'select',
				'values'		 => array(
					'5'	 => '5',
					'6'	 => '6',
					'7'	 => '7',
					'8'	 => '8',
					'9'	 => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12',
					'13' => '13',
					'14' => '14',
				),
				'default_value'	 => '10',
				'tooltip'		 => 'Minimum length of the code (without prefix and sufix characters). Empty places will be replaced with Pad characters.',
				'section'		 => 'serial_tickets_settings',
			);

			$fields [] = array(
				'field_name'	 => 'tc_custom_ticket_serial_pad_string',
				'field_title'	 => __( 'Pad character / replace empty character', 'serial' ),
				'field_type'	 => 'option',
				'default_value'	 => '0',
				'tooltip'		 => 'Character which will replace an empty place in the ticket code. For instance, if the "Serial ticket code lenght" is set to 5 for instance and the next serial number of a ticket is 2, ticket code will be 00002',
				'section'		 => 'serial_tickets_settings',
				'maxlength'		 => 1,
				'field_class'	 => 'accept_numbers_and_a_z_letters_only'
			);

			return apply_filters( 'tc_settings_serial_tickets_fields', $fields );
		}

	}

}
?>
