jQuery(document).ready(function ($) {


    $('body').on('keyup change keydown', '#tc_options_search_val', function (e) {
        var searched_option = $(this).val();
        //console.log(searched_option);
        if (searched_option == '') {
            $(".form-table tr").show();
        } else {
            try {
                var search_key_match = new RegExp(searched_option, 'i');
            } catch (e) {
                var search_key_match = '';
            }
            
            $(".form-table label").each(function () {
                if (($(this).html().match(search_key_match))) {
                    $(this).parent().parent().show();
                }else{
                    $(this).parent().parent().hide();
                }
            });
           /* if ((items[i].id.match(search_key_match))) {

            }*/
            //show only those which match with the keyword
            //hide others
        }
        // $(this).parent().find('.quantity').val(parseInt(quantity) + 1);
    });

    //$form.find( '#publish, #save-post,.save-bulk-form, [type="submit"]' ).click( function( e ) {
    jQuery(".post-type-tc_tickets #post, .post-type-tc_events #post").validate({ignore: '[id^="acf"], #us_portfolio_settings input'});
    jQuery("#tc-general-settings, #tc_ticket_type_form, #tc_discount_code_form, .tc_form_validation_required").validate({
        rules: {
            /* field: {
             required: true,
             number: true
             },*/
            field: {
                number: true
            },
            field: {
                required: true
            }
        }
    });



    if ($('#discount_type').length && $('#discount_value').length) {
        tc_check_discount_code_type();

        $('#discount_type').change(function () {
            tc_check_discount_code_type();
        });

    }

    function tc_check_discount_code_type() {
        if ($('#discount_type').length && $('#discount_value').length) {
            if ($("#discount_type option:selected").val() == '3') {
                $('tr.discount_availability').hide();
            } else {
                $('tr.discount_availability').show();
            }
        }
    }

    //jQuery( '#tc-general-settings' ).validate();

    $(document).on('change','.has_conditional', function ( ) {
        tc_conditionals_init();
    });

    function tc_conditionals_init( ) {
        $('.tc_conditional').each(function (i, obj) {
            tc_conditional($(this));
        });
    }

    function tc_conditional(obj) {

        var field_name = $(obj).attr('data-condition-field_name');
        if (!$('.' + field_name).hasClass('has_conditional')) {
            $('.' + field_name).addClass('has_conditional');
        }

        var field_type = $(obj).attr('data-condition-field_type');
        var value = $(obj).attr('data-condition-value');
        var action = $(obj).attr('data-condition-action');
        if (field_type == 'radio') {
            var selected_value = $('.' + field_name + ':checked').val( );
            //alert(selected_value);
        }

        if (field_type == 'text' || field_type == 'textarea' || field_type == 'select') {
            var selected_value = $('.' + field_name).val( );
        }

        if (value == selected_value) {
            if (action == 'hide') {
                $(obj).hide();
            }
            if (action == 'show') {
                $(obj).show(200);
            }
        } else {
            if (action == 'hide') {
                $(obj).show(200);
            }
            if (action == 'show') {
                $(obj).hide();
            }
        }

        fix_chosen();
    }

    tc_conditionals_init( );

    $('.tc_tooltip').tooltip({
        content: function () {
            return $(this).prop('title');
        },
        show: null,
        close: function (event, ui) {
            ui.tooltip.hover(
                    function () {
                        $(this).stop(true).fadeTo(100, 1);
                    },
                    function () {
                        $(this).fadeOut("100", function () {
                            $(this).remove();
                        })
                    });
        }
    });

    /* Toggle Controls */
    //tickera_page_tc_ticket_types
    var tc_event_id = 0;
    var tc_ticket_id = 0;
    var tc_event_status = 'publish';
    var tc_ticket_status = 'publish';

    var tc_toggle = {
        init: function () {
            $('body').addClass('tctgl');
            this.attachHandlers('.tctgl .tc-control');
        },
        tc_controls: {
            $tc_toggle_init: function (selector)
            {
                $(selector).click(function ()
                {
                    tc_event_id = $(this).attr('event_id');
                    tc_ticket_id = $(this).attr('ticket_id');

                    if ($(this).hasClass('tc-on')) {
                        $(this).removeClass('tc-on');
                        tc_event_status = 'private';
                        tc_ticket_status = 'private';
                    } else {
                        $(this).addClass('tc-on');
                        tc_event_status = 'publish';
                        tc_ticket_status = 'publish';
                    }

                    var attr = $(this).attr('event_id');
                    if (typeof attr !== typeof undefined && attr !== false) {//Event toggle
                        $.post(
                                tc_vars.ajaxUrl, {
                                    action: 'change_event_status',
                                    event_status: tc_event_status,
                                    event_id: tc_event_id,
                                }
                        );
                    } else {
                        $.post(
                                tc_vars.ajaxUrl, {
                                    action: 'change_ticket_status',
                                    ticket_status: tc_ticket_status,
                                    ticket_id: tc_ticket_id,
                                }
                        );
                    }


                });

            }
        },
        attachHandlers: function (selector) {
            this.tc_controls.$tc_toggle_init(selector);
        }
    };

    tc_toggle.init();


    $("input.tc_active_gateways").change(function () {
        //alert($(this).val());
        var currently_selected_gateway_name = $(this).val();
        var checked = $(this).attr('checked');
        if (checked == 'checked') {
            $('#' + currently_selected_gateway_name).show(200);
        } else {
            $('#' + currently_selected_gateway_name).hide(200);
        }
    });


    if (tc_vars.animated_transitions) {
        $(".tc_wrap").fadeTo(250, 1);
        $(".tc_wrap #message").delay(2000).slideUp(250);
    } else {
        $(".tc_wrap").fadeTo(0, 1);
    }


    $('.tc_delete_link').click(function (event)
    {
        tc_delete(event);
    });

    function tc_delete_confirmed() {
        return confirm(tc_vars.delete_confirmation_message);
    }

    function tc_delete(event) {
        if (tc_delete_confirmed()) {
            return true;
        } else {
            event.preventDefault()
            return false;
        }
    }


    $('.file_url_button').click(function ()
    {
        var target_url_field = $(this).prevAll(".file_url:first");
        wp.media.editor.send.attachment = function (props, attachment)
        {
            $(target_url_field).val(attachment.url);
        };
        wp.media.editor.open(this);
        return false;
    });


    /* Ticket Tempaltes */

    var ticket_classes = new Array();
    var parent_id = 0;

    $('.tc-color-picker').wpColorPicker();
    $("ul.sortables").sortable({
        connectWith: 'ul',
        forcePlaceholderSize: true,
        //placeholder: "ui-state-highlight",
        receive: function (template, ui) {
            update_li();
            $(".rows ul li").last().addClass("last_child");
        },
        stop: function (template, ui) {
            update_li();
        }
    })/*.disableSelection()*/;

    //$( ".sortables" ).disableSelection();

    function update_li() {

        var children_num = 0;
        var current_child_num = 0;

        $(".rows ul").each(function () {

            ticket_classes.length = 0; //empty the array

            children_num = $(this).children('li').length;
            $(this).children('li').removeClass();
            $(this).children('li').addClass("ui-state-default");
            $(this).children('li').addClass("cols cols_" + children_num);
            $(this).children('li').last().addClass("last_child");
            $(this).find('li').each(function (index, element) {
                if ($.inArray($(this).attr('data-class'), ticket_classes) == -1) {
                    ticket_classes.push($(this).attr('data-class'));
                }
            });
            $(this).find('.rows_classes').val(ticket_classes.join());
        });
        tc_fix_template_elements_sizes();

        $(".rows ul li").last().addClass("last_child");
        $(".tc_wrap select").css('width', '25em');
        $(".tc_wrap select").css('display', 'block');

        $(".tc_wrap select").chosen({disable_search_threshold: 5});
        $(".tc_wrap select").css('display', 'none');
        $(".tc_wrap .chosen-container").css('width', '100%');
        $(".tc_wrap .chosen-container").css('max-width', '25em');
        $(".tc_wrap .chosen-container").css('min-width', '1em');
    }

    function tc_fix_template_elements_sizes() {
        $(".rows ul").each(function () {
            var maxHeight = -1;

            $(this).find('li').each(function () {
                $(this).removeAttr("style");
                maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
            });

            $(this).find('li').each(function () {
                $(this).height(maxHeight);
            });
        });
    }


    if ($('#ticket_elements').length) {
        update_li();
        tc_fix_template_elements_sizes();
    }


    jQuery('.close-this').click(function (event) {
        event.preventDefault();
        jQuery(this).closest('.ui-state-default').appendTo('#ticket_elements');
        update_li();
        tc_fix_template_elements_sizes();
    });



    function fix_chosen() {
        $(".tc_wrap select").css('width', '25em');
        $(".tc_wrap select").css('display', 'block');
        $(".tc_wrap select").chosen({disable_search_threshold: 5, allow_single_deselect: false});
        $(".tc_wrap select").css('display', 'none');
        $(".tc_wrap .chosen-container").css('width', '100%');
        $(".tc_wrap .chosen-container").css('max-width', '25em');
        $(".tc_wrap .chosen-container").css('min-width', '1em');
    }

    /*$( '.order_status_change' ).on( 'change', function () {
     var new_status = $( this ).val();
     var order_id = $( '#order_id' ).val();
     
     $.post( tc_vars.ajaxUrl, { action: "change_order_status", order_id: order_id, new_status: new_status }, function ( data ) {
     if ( data != 'error' ) {
     $( '.tc_wrap .message_placeholder' ).html( '' );
     $( '.tc_wrap .message_placeholder' ).append( '<div id="message" class="updated fade"><p>' + tc_vars.order_status_changed_message + '</p></div>' );
     $( ".tc_wrap .message_placeholder" ).show( 250 );
     $( ".tc_wrap .message_placeholder" ).delay( 2000 ).slideUp( 250 );
     } else {
     //current_form.html(data);//Show error message
     }
     $( this ).fadeTo( "fast", 1 );
     } );
     } );*/

    $('#tc_order_resend_condirmation_email').on('click', function (event) {
        event.preventDefault();
        var new_status = $('.order_status_change').val();
        var order_id = $('#order_id').val();

        $(this).hide();
        $(this).after('<span id="tc_resending">' + tc_vars.order_confirmation_email_resending_message + '</a>');

        $.post(tc_vars.ajaxUrl, {action: "change_order_status", order_id: order_id, new_status: new_status}, function (data) {
            if (data != 'error') {

                $('.tc_wrap .message_placeholder').html('');
                $('.tc_wrap .message_placeholder').append('<div id="message" class="updated fade"><p>' + tc_vars.order_confirmation_email_resent_message + '</p></div>');
                $(".tc_wrap .message_placeholder").show(250);
                $(".tc_wrap .message_placeholder").delay(2000).slideUp(250);
                $('#tc_order_resend_condirmation_email').show();
                $('#tc_resending').remove();
            } else {
                //current_form.html(data);//Show error message
            }
            $(this).fadeTo("fast", 1);
        });

    });



    /* PAYMENT GATEWAY IMAGE SWITCH */

    jQuery(".tc_active_gateways").each(function () {

        if (this.checked) {
            jQuery(this).closest('.image-check-wrap').toggleClass('active-gateway');
        }

        jQuery(this).change(function () {
            fix_chosen();

            if (this.checked) {
                jQuery(this).closest('.image-check-wrap').toggleClass('active-gateway');
            } else {
                jQuery(this).closest('.image-check-wrap').toggleClass('active-gateway');
            }
        });
    })

    if (jQuery('#tickets_limit_type').val() == 'event_level') {
        jQuery('#event_ticket_limit').parent().parent().show();
    } else {
        jQuery('#event_ticket_limit').parent().parent().hide();
    }

    jQuery('#tickets_limit_type').on('change', function () {
        if (jQuery('#tickets_limit_type').val() == 'event_level') {
            jQuery('#event_ticket_limit').parent().parent().show();
        } else {
            jQuery('#event_ticket_limit').parent().parent().hide();
        }
    });

    $(".tc_wrap select").chosen({disable_search_threshold: 5, allow_single_deselect: false});

    /**
     * INLINE EDIT
     * @param replaceWith
     * @param connectWith
     */
    $.fn.inlineEdit = function (replaceWith, connectWith) {

        let clicked;

        $(this).hover( function ( ) {
            $(this).addClass('inline_hover');

        }, function () {
            $(this).removeClass('inline_hover');
        });

        $(this).click( function() {

            if ( ! $(this).hasClass( 'inline_clicked' ) ) {

                clicked = $(this);

                // Leave a mark on input
                $(this).addClass('inline_clicked');

                let orig_val = $(this).html();
                $(replaceWith).val( $.trim( orig_val) );

                $(this).hide();
                $(this).after( replaceWith );
                replaceWith.focus();


                /* Update Attendee Information */
                $( replaceWith ).blur( function () {

                    // Remove a mark of an input
                    clicked.removeClass('inline_clicked');

                    if ( clicked.val() != "" ) {
                        connectWith.val( $(this).val()).change();
                        clicked.text($(this).val());
                    }

                    clicked.text( $(this).val() );

                    var ticket_id = $(this).parent('tr').find('.ID');
                    ticket_id = ticket_id.attr('data-id');

                    save_attendee_info(ticket_id, $(this).prev().attr('class'), $(this).val());

                    $(this).remove();
                    clicked.show();

                });
            }
        });
    };

    /**
     * INLINE SELECT2 EDIT
     * @param replaceWith
     * @param connectWith
     */
    $.fn.inlineSelect2Edit = function (replaceWith, connectWith) {

        let clicked;

        $(this).hover(function ( ) {
                $(this).addClass('inline_hover');
            }, function () {
                $(this).removeClass('inline_hover');
            }
        );

        $(this).click(function () {

            if ( ! $(this).hasClass( 'inline_clicked' ) ) {

                clicked = $(this);

                // Leave a mark on input
                $(this).addClass('inline_clicked');

                // Collect Related Ticket Types
                let ticket_instance_id = $(this).siblings('.ID').attr('data-id');
                $.post( tc_vars.ajaxUrl, {action: 'get_ticket_type_instances', tc_ticket_instance_id: ticket_instance_id }, function(response){
                    if ( !response.error ) {
                        clicked.hide().after(replaceWith);
                        initialize_select2(replaceWith, response);
                    } else {
                        replaceWith = '<td>' + response.error + '</td>';
                        clicked.hide().after(replaceWith);
                    }
                });

                /* Update Attendee Information */
                $( replaceWith ).on('select2:close', function() {

                    // Remove a mark of an input
                    clicked.removeClass('inline_clicked');

                    let select2 = $(this).find(':selected');

                    if (select2.val() != "") {
                        connectWith.val(select2.val()).change();
                        clicked.text(select2.text());
                    }

                    clicked.text(select2.text());

                    var ticket_id = $(this).parent('tr').find('.ID');
                    ticket_id = ticket_id.attr('data-id');

                    save_attendee_info(ticket_id, $(this).prev().attr('class'), select2.val());

                    $(this).select2('destroy').empty().remove();
                    clicked.show();
                });
            }
        });
    };

    /**
     * Better Order: Update Ticket Instances Metabox
     * @type {jQuery|HTMLElement}
     */
    let replaceWithInput = $('<input name="temp" class="tc_temp_value" type="text" />'),
        replaceWithOption = $('<select class="ticket_type_id_select2"></select>'),
        connectWith = $('input[name="hiddenField"]');

    $('#order-details-tc-metabox-wrapper .order-details tr').find('td.ticket_type_id:first').inlineSelect2Edit(replaceWithOption, connectWith);
    $('td.first_name, td.last_name, td.owner_email').inlineEdit(replaceWithInput, connectWith);

    /**
     * Better Order: Update Temporary fields on keyup
     */
    $(".tc_temp_value").live('keyup', function (e) {
        if (e.keyCode == 13) {
            $(this).blur( );
        }
        e.preventDefault( );
    });

    /**
     * Initialize Select2
     * @param elem
     * @param dataSource
     */
    function initialize_select2(elem, dataSource) {
        $(elem).select2({
            data: dataSource
        });
    }

    function save_attendee_info(ticket_id, meta_name, meta_value) {
        var data = {
            action: 'save_attendee_info',
            post_id: ticket_id,
            meta_name: meta_name,
            meta_value: meta_value
        }
        $.post(tc_vars.ajaxUrl, data);
    }

    jQuery(document).ready(function () {

        if (tc_vars.tc_check_page == 'tc_settings') {
            jQuery(".nav-tab-wrapper").sticky({
                topSpacing: 30,
                bottomSpacing: 50
            });
        }

    });

    jQuery(window).resize(function () {
        tc_page_names_width();
    });
    // js for trash confirmation
    jQuery(document).ready(function($){
        $('.post-type-tc_tickets a.submitdelete').click(function(e){
            e.preventDefault();
            var href = $(this).attr('href');
                splt_hrf = href.split('=');
                splt_hrf = splt_hrf[1].split('&');
                id = splt_hrf[0];
            $.post(tc_vars.ajaxUrl, {action: "trash_post_before", trash_id: id, btn_action: 'trash'}, function (data) {
                var sold = data;
                if(sold >0){

                   if(sold == 1){
                        var r = confirm(tc_vars.single_sold_ticket_trash_message.replace("%s",sold));
                       }else{
                        var r = confirm(tc_vars.multi_sold_tickets_trash_message.replace("%s",sold));
                       }

                    if(r){
                        window.location = href;
                    }
                   }
                else{
                     window.location = href;
                }
            });
        });
        $('#doaction').click(function(e){
            
            if($('#bulk-action-selector-top').val() == 'trash'){
                e.preventDefault();
                if($('input[name="post[]"]:checked').length > 0){
                    var tids = [];
                    $.each($('input[name="post[]"]:checked'), function(){
                        tids.push($(this).val());
                    });
                    $.post(tc_vars.ajaxUrl, {action: "trash_post_before", multi_trash_id: tids, btn_action: 'multi_trash'}, function (data) {
                        var sold = data;
                        
                        if(sold > 0){
                           var r = confirm(tc_vars.multi_check_tickets_trash_message);
                            if(!r){
                                e.preventDefault();
                            }
                            else{
                                $('#doaction').unbind(e);
                                $('#doaction').click();
                            }
                        }
                        else{
                            $('#doaction').unbind(e);
                            $('#doaction').click();
                        }
                    });
                }
            }
        });
    });
    function tc_page_names_width() {
        jQuery('.tc_wrap .nav-tab-wrapper ul').width(jQuery('.tc_wrap .nav-tab-wrapper').width());
    }

    tc_page_names_width();
});

