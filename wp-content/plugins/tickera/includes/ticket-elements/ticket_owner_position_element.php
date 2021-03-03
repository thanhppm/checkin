<?php

class tc_ticket_owner_position_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_owner_position_element';
	var $element_title	 = 'Ticket Owner Posititon';
        var $font_awesome_icon = '<i class="fa fa-users"></i>';


	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_owner_position_element_title', __( 'Ticket Owner Posititon', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket_Instance( (int) $ticket_instance_id );
			$ticket_owner_position		 = apply_filters( 'tc_ticket_owner_position_element', get_post_meta( $ticket_instance->details->ID, 'ticket_owner_position', true ));
			return apply_filters( 'tc_ticket_owner_position_element', $ticket_owner_position );
		} else {
			return apply_filters( 'tc_ticket_owner_position_element_default', '' );
		}
	}

}

tc_register_template_element( 'tc_ticket_owner_position_element', __( 'Ticket Owner Posititon', 'tc' ) );
