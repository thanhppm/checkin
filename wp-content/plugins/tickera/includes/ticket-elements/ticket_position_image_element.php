<?php

class tc_ticket_position_image_element extends TC_Ticket_Template_Elements
{

    var $element_name     = 'tc_ticket_position_image_element';
    var $element_title     = 'Ticket Position Image';
    var $font_awesome_icon = '<i class="fa fa-picture-o"></i>';


    function on_creation()
    {
        $this->element_title = apply_filters('tc_ticket_position_image_element_title', __('Ticket Position Image', 'tc'));
    }

    function ticket_content($ticket_instance_id = false, $ticket_type_id = false)
    {
        if ($ticket_instance_id) {
            $ticket_instance = new TC_Ticket_Instance((int) $ticket_instance_id);
            
            $ticket_position_image = apply_filters( 'tc_ticket_position_image_element', get_post_meta( $ticket_instance->details->ID, 'ticket_position_image', true ) );
            
            if ($ticket_position_image) {
                return '<img src="' . tc_ticket_template_image_url($ticket_position_image) . '" />';
            } else {
                return '';
            }
        } else {
            return apply_filters('tc_ticket_position_image_element_default', '');
        }
    }
}

tc_register_template_element('tc_ticket_position_image_element', __('Ticket Position Image', 'tc'));
