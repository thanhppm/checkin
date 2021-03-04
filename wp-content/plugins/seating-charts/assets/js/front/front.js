jQuery(document).ready(function ($) {

    window.tc_front = {

        /**
         * Get inset property
         *
         * @param allStyle
         * @returns {undefined|*}
         */
        getInsetStyle: function( allStyle ) {
            let styles = allStyle.split( '; ' );
            for ( let i = 0; i < styles.length; i++ ) {
                let astyle = styles[i].split( ': ' );
                if ( 'inset' == astyle[0] ){
                    return astyle[1];
                }
            }
            return undefined;
        }
    }

    // Set legend invisible when on phone
    function tc_legend_set() {
        if ($('.tc-legend-arrow').hasClass('tc-legend-open')) {
            var tc_seating_legend = $('.tc-seating-legend-wrap').outerWidth();
            $(".tc-seating-legend-wrap").css('left', -Math.abs(tc_seating_legend));
        }

        var get_window_width = $(this).width();

        if (get_window_width < 780) {
            var tc_seating_legend = $('.tc-seating-legend-wrap').outerWidth();
            $(".tc-seating-legend-wrap").animate({
                left: -Math.abs(tc_seating_legend),
            }, 0, function () {
                // Animation complete.
            });

            $('.tc-legend-arrow').removeClass('tc-legend-close');
            $('.tc-legend-arrow').addClass('tc-legend-open');

        }
    }

    $('body').on('click', '.tc_seating_map_button', function (e) {

        $('.tc_seating_map').html('');
        $('.tc-modal-wrap').remove();

        var seating_map_id = $(this).data('seating-map-id');
        var show_legend = $(this).data('show_legend');
        var button_title = $(this).data('button_title');
        var subtotal_title = $(this).data('subtotal_title');
        var cart_title = $(this).data('cart_title');

        var seating_map_html = $('.tc_seating_map_' + seating_map_id).html();

        $(window).on('resize', function (e) {
            tc_controls.centerPoint(seating_map_id);
            tc_controls.reposition();
        });

        $('.tc_seating_map_' + seating_map_id).css({
            position: 'fixed',
            top: 0,
            right: 0,
            bottom: 0,
            left: 0,
            zIndex: 999999,
            display: 'block'
        });

        $('body').prepend('<div class="tc-chart-preloader"><div class="tc-loader"></div></div>');

        $.post(tc_common_vars.ajaxUrl, {
            action: "tc_load_seating_map",
            chart_id: seating_map_id,
            show_legend: show_legend,
            button_title: button_title,
            subtotal_title: subtotal_title,
            cart_title: cart_title

        }, function (data) {

            $('.tc_seating_map_' + seating_map_id).html(data);
            $('html').css('overflow', 'hidden');

            function tc_cart_hover() {
                var tc_ticket_cart_height = $('.tc-tickets-cart').height();
                $('.tc-tickets-cart').css('bottom', tc_ticket_cart_height * -1);
            }

            // Mark Seats
            tc_mark_in_cart_seats(seating_map_id);
            tc_mark_reserved_seats(seating_map_id);
            tc_mark_reserved_standings(seating_map_id);
            tc_mark_unavailable_seats(seating_map_id);

            // Remove Unneeded Classes
            $('.tc-group-wrap').removeClass('ui-draggable');
            $(".tc-group-wrap *").removeClass('ui-draggable-handle');
            $(".tc-group-wrap").find('.tc-group-controls').remove();
            $(".tc-group-wrap").find('.ui-resizable-handle').remove();
            $(".tc-group-wrap").find('.ui-resizable-autohide').removeClass('ui-resizable-autohide');
            $(".tc-group-wrap").find('.ui-resizable').removeClass('ui-resizable');

            // Selectables
            tc_front_selectables();

            // Set Wrapper Height
            tc_controls.set_wrapper_height();

            // Initialize Zoom Slider
            $(".tc-zoom-slider").slider({
                value: tc_common_vars.front_zoom_level,
                orientation: "horizontal",
                min: 0.30,
                max: 1,
                step: 0.10,
                slide: function (event, ui) {
                    var init_zoom = window.tc_seat_zoom_level;

                    $(this).parent().find('.tc-slider-value').val(ui.value);

                    window.tc_seat_zoom_level = ui.value;

                    tc_controls.zoom();
                },
                create: function (event, ui) {
                    var bar = $(this).slider('value');
                    $(this).parent().find('.tc-slider-value').val(bar);
                }
            });

            // Make sure to always replace inset property with the general top, right, bottom, left properties
            $.each( $( '.tc-group-wrap' ), function () {

                let getStyle = $(this).attr( 'style' ),
                    element = tc_front.getInsetStyle( getStyle );

                if ( element !== undefined ) {

                    let value = element.split(' '),
                        topPosition = value[0] ? value[0] : 'auto',
                        rightPosition = value[1] ? value[1] : 'auto',
                        bottomPosition = value[2] ? value[2] : 'auto',
                        leftPosition = value[3] ? value[3] : 'auto',
                        removeTrail = leftPosition.replace( ';', '' );

                    $(this).css( { 'inset': '', 'top': topPosition, 'left': removeTrail } );
                }
            });

            // Init Controls
            tc_controls.init();
            window.dispatchEvent(new Event('resize'));
            tc_controls.centerPoint();
            tc_controls.reposition();
            tc_controls.centerPoint();

            $('.tc-chart-preloader').remove();
            tc_controls.tc_legend_set();
        });

        /**
         * Validates in cart data when proceeding to cart page
         */
        $('body').on('click', '.tc-checkout-button', function( event ) {
            tc_check_minimum_tickets( event );
        });
    });

    $('body').on('click', '#tc-regular-modal .tc_cart_button', function (e) {
        tc_seat_chart_add_to_cart($(this));
    });

    $('body').on('click', '#tc-modal-added-to-cart .tc_remove_from_cart_button', function (e) {
        tc_seat_chart_remove_from_cart($(this));
    });

    /**
     * Remove seat from cart
     *
     * @param button
     */
    function tc_seat_chart_remove_from_cart( button ) {

        button.prop( 'disabled', true );
        $( '#tc-modal-added-to-cart button.tc_remove_from_cart_button' ).html( tc_seat_chart_ajax.tc_removing_from_cart_title );

        let selected_seat = $( '.ui-selected' ),
            ticket_type = selected_seat.attr( 'data-tt-id' ),
            color = $( 'li.tt_' + ticket_type ).css( 'color' ),
            chart_id = button.parent().find( '.tc_regular_modal_seating_chart_id' ).val(),
            ticket_type_id = button.parent().find( '.tc_regular_modal_ticket_type_id' ).val(),
            seat_id = button.parent().find( '.tc_regular_modal_seat_id' ).val(),
            seat_label = $( '.tc_regular_modal_seat_label' ).html(),
            is_standee = selected_seat.hasClass( 'tc-object-selectable' ),
            tcsc_seat = chart_id + '-' + seat_id + '-' + ticket_type_id;

        selected_seat.removeClass( 'tc_seat_in_cart' );
        selected_seat.css( { 'background-color': color } );

        if ( 1 == tc_seat_chart_ajax.tc_check_firebase && !is_standee ) {
            $.post(tc_seat_chart_ajax.ajaxUrl, { action: "tc_remove_seat_from_firebase_cart", seat_id: seat_id, chart_id: chart_id }, function ( data ) {} );
        }

        $.post( tc_seat_chart_ajax.ajaxUrl, { action: 'tc_remove_seat_from_cart_ajax', tcsc_seat: tcsc_seat }, function ( response ) {

            if ( response ) {
                $( '#tc-modal-added-to-cart .tc_remove_from_cart_button' ).prop( 'disabled', false );
                $( '.ui-dialog-content' ).dialog('close' );
                $( '.tc-seatchart-subtotal strong' ).html( response.total );
                $( '.tc-seatchart-in-cart-count' ).val( response.in_cart_count );
                $( '#tc-modal-added-to-cart button.tc_remove_from_cart_button' ).html( tc_seat_chart_ajax.tc_remove_from_cart_button_title );
                tc_mark_in_cart_seat();
            }
        });
    }

    /**
     * Function checks if requirement of minimum tickets is met
     * @param event
     */
    function tc_check_minimum_tickets(event) {
        jQuery('.tc-seating-legend ul li.tc-ticket-listing').each(function () {

            var tc_min_tickets_per_order = jQuery(this).attr('data-min-tickets-per-order');
            var tc_max_tickets_per_order = jQuery(this).attr('data-max-tickets-per-order');

            var tc_ticket_type_id = jQuery(this).attr('data-ticket-type-id');

            if (tc_min_tickets_per_order != 0 || tc_max_tickets_per_order != 0 || tc_min_tickets_per_order != '' || tc_max_tickets_per_order != '') {

                i = 0;

                jQuery('.tc_seat_unit.tc_seat_in_cart').each(function () {
                    var tc_this_ticket_id = jQuery(this).attr('data-tt-id');

                    if (tc_ticket_type_id == tc_this_ticket_id) {
                        i++;
                    }

                });

                if (tc_min_tickets_per_order > i && i !== 0 && tc_min_tickets_per_order != '') {
                    $("#tc-ticket-requirements").dialog({
                        bgiframe: true,
                        closeOnEscape: false,
                        draggable: false,
                        resizable: false,
                        dialogClass: "no-close",
                        modal: true,
                        title: false,
                        closeText: "<i class='fa fa-times'></i>",
                        buttons: [
                            {
                                text: 'OK',
                                click: function () {
                                    $(this).dialog("close");

                                }
                            }
                        ]
                    });

                    event.preventDefault();

                    var tc_ticket_title = jQuery('.tt_' + tc_ticket_type_id).attr('data-tt-title');

                    jQuery('#tc-ticket-requirements').html(tc_seat_chart_ajax.tc_minimum_tickets_message + tc_ticket_title + tc_seat_chart_ajax.tc_minimum_tickets_message_is + tc_min_tickets_per_order + '!');

                }

                if (tc_max_tickets_per_order < i && i !== 0 && tc_max_tickets_per_order != '') {

                    $("#tc-ticket-requirements").dialog({
                        bgiframe: true,
                        closeOnEscape: false,
                        draggable: false,
                        resizable: false,
                        dialogClass: "no-close",
                        modal: true,
                        title: false,
                        closeText: "<i class='fa fa-times'></i>",
                        buttons: [
                            {
                                text: 'OK',
                                click: function () {
                                    $(this).dialog("close");

                                }
                            }
                        ]
                    });

                    event.preventDefault();

                    var tc_ticket_title = jQuery('.tt_' + tc_ticket_type_id).attr('data-tt-title');

                    jQuery('#tc-ticket-requirements').html(tc_seat_chart_ajax.tc_maximum_tickets_message + tc_ticket_title + tc_seat_chart_ajax.tc_minimum_tickets_message_is + tc_max_tickets_per_order + '!');

                }
            }
        });
    }

    /**
     * Call this method to display error dialog box
     * @param error_message
     */
    function display_error_dialog_box( error_message ) {

        // Display a dialog box with error message
        $("#tc-ticket-requirements").dialog({
            bgiframe: true,
            closeOnEscape: false,
            draggable: false,
            resizable: false,
            dialogClass: "no-close",
            modal: true,
            title: false,
            closeText: "<i class='fa fa-times'></i>",
            buttons: [ { text: 'OK', click: function () { $(this).dialog("destroy");  $(".tc-group-wrap *").removeClass('ui-selected'); } } ]
        });

        jQuery('#tc-ticket-requirements').html( error_message );
    }

    /**
     * Add seat to cart
     *
     * @param button
     */
    function tc_seat_chart_add_to_cart( button ) {

        $( '#tc-regular-modal button.tc_cart_button' ).html( tc_seat_chart_ajax.tc_adding_to_cart_title );

        let standing_qty = $( '.model_extras .tc_quantity_selector' ).val(),
            get_href_value = $( '.tc-checkout-button' ).attr( 'href' ),
            tc_seat_cart_items = [],
            tc_seat_cart_items_firebase = []

        $( '.tc-checkout-button' ).removeAttr( 'href' );
        $( '.tc-checkout-button' ).attr( 'style', 'opacity: 0.4;' );
        button.prop( 'disabled', true );

        if ( typeof standing_qty === 'undefined' ) {
            standing_qty = 0;
        }

        let selected_seat = $( '.ui-selected' ),
            chart_id = button.parent().find( '.tc_regular_modal_seating_chart_id' ).val(),
            ticket_type_id = button.parent().find( '.tc_regular_modal_ticket_type_id' ).val(),
            seat_id = button.parent().find( '.tc_regular_modal_seat_id' ).val(),
            seat_label = $( '#tc-regular-modal .tc_regular_modal_seat_label' ).html(),
            seat = ticket_type_id + '-' + seat_id + '-' + seat_label + '-' + chart_id,
            seat_firebase = chart_id + '-' + seat_id + '-' + ticket_type_id,
            is_standee = selected_seat.hasClass( 'tc-object-selectable' );

        tc_seat_cart_items.push( seat );
        tc_seat_cart_items_firebase.push( seat_firebase );
        selected_seat.addClass( 'tc_seat_in_cart' );

        if ( 1 == tc_seat_chart_ajax.tc_check_firebase && !is_standee ) {
            $.post(tc_seat_chart_ajax.ajaxUrl, { action: "tc_add_seat_to_firebase_cart", tc_seat_cart_items: tc_seat_cart_items_firebase, tc_standing_qty: standing_qty }, function ( data ) {});
        }

        $.post( tc_seat_chart_ajax.ajaxUrl, { action: "tc_add_seat_to_cart", tc_seat_cart_items: tc_seat_cart_items, standing_qty: standing_qty }, function ( data ) {

            // Remove BOM from string and parse
            var response = jQuery.parseJSON( data.replace( /\0/g, '' ) );

            if ( response ) {

                $( '#tc-regular-modal .tc_cart_button' ).prop( 'disabled', false );
                button.prop( 'disabled', false );
                $( '.ui-dialog-content' ).dialog( 'close' );
                $( '.tc-seatchart-subtotal strong' ).html( response.total );
                $( '.tc-seatchart-in-cart-count' ).val( response.in_cart_count );
                $( '.tc-checkout-button' ).attr( 'href', get_href_value );
                $( '.tc-checkout-button' ).attr( 'style', 'opacity: 1; cursor: pointer;' );
            }

            $('#tc-regular-modal .tc_cart_button').prop( 'disabled', false );
        });

        tc_mark_in_cart_seat();
    }

    function tc_mark_in_cart_seat() {
        $.each($(".tc_seat_in_cart"), function () {
            $(this).css('background-color', tc_seat_chart_ajax.tc_in_cart_seat_color);
            $(this).css('color', tc_seat_chart_ajax.tc_in_cart_seat_color);
            $(this).addClass('tc_seat_in_cart');
            $(this).removeClass('ui-selected');
        });
    }

    /**
     * Mark in-cart seats on the specified chart
     * @param {type} seat_chart_id
     * @returns {undefined}
     */
    function tc_mark_in_cart_seats( seat_chart_id ) {
        for (var k in tc_in_cart_seats[seat_chart_id]) {
            if (tc_in_cart_seats[seat_chart_id].hasOwnProperty(k)) {

                $('.tc_seating_map_' + seat_chart_id + ' #' + k).css('background-color', tc_seat_chart_ajax.tc_in_cart_seat_color);
                $('.tc_seating_map_' + seat_chart_id + ' #' + k).css('color', tc_seat_chart_ajax.tc_in_cart_seat_color);
                $('.tc_seating_map_' + seat_chart_id + ' #' + k).addClass('tc_seat_in_cart');
                $('.tc_seating_map_' + seat_chart_id + ' #' + k).removeClass('ui-selected ui-selectee');
            }
        }
    }

    /**
     * Mark reserved seats on the chart
     *
     * @param {type} seat_chart_id
     * @returns {undefined}
     */
    function tc_mark_reserved_seats( seat_chart_id ) {
        for ( var k in tc_reserved_seats[seat_chart_id] ) {
            if ( tc_reserved_seats[seat_chart_id].hasOwnProperty(k) ) {
                $( '.tc_seating_map_' + seat_chart_id + ' #' + k + ':not(.tc-object-selectable)' ).css( 'background-color', tc_seat_chart_ajax.tc_reserved_seat_color );
                $( '.tc_seating_map_' + seat_chart_id + ' #' + k + ':not(.tc-object-selectable)' ).css( 'color', tc_seat_chart_ajax.tc_reserved_seat_color );
                $( '.tc_seating_map_' + seat_chart_id + ' #' + k + ':not(.tc-object-selectable)' ).addClass( 'tc_seat_reserved' );
                $( '.tc_seating_map_' + seat_chart_id + ' #' + k + ':not(.tc-object-selectable)' ).removeClass( 'ui-selected ui-selectee tc_seat_in_cart' );
            }
        }
    }

    function tc_mark_unavailable_seats(seat_chart_id) {
        $('.tc-seating-legend ul li.tc-ticket-listing').each(function () {

            var is_sales_available = $(this).attr('data-is-sales-available');
            var ticket_type_id = $(this).data('ticket-type-id');

            if (is_sales_available !== '1') {
                $('.tc_seat_unit, .tc-object-selectable, .tc-table-chair').each(function () {
                    var tc_this_ticket_id = $(this).attr('data-tt-id');
                    if (ticket_type_id == tc_this_ticket_id) {
                        $(this).css('background-color', tc_seat_chart_ajax.tc_unavailable_seat_color);
                        $(this).css('color', tc_seat_chart_ajax.tc_unavailable_seat_color);
                        $(this).addClass('tc_seat_unavailable');
                        $(this).removeClass('ui-selected ui-selectee');
                    }
                });
            }
        });
    }

    /**
     * Mark standee Seats as reserved
     *
     * @param seat_chart_id
     */
    function tc_mark_reserved_standings( seat_chart_id ) {

        $.each( $( '.tc-object-selectable' ), function () {
            let ticket_type_id = $( this ).data( 'tt-id' ),
                qty_left = $( '.tc-seating-legend .tt_' + ticket_type_id ).data( 'qty-left' );

            if ( 0 == qty_left ) {
                $( this ).css( 'background-color', tc_seat_chart_ajax.tc_reserved_seat_color );
                $( this ).addClass( 'tc_seat_reserved' );
                $( this ).removeClass( 'ui-selected ui-selectee tc_seat_in_cart' );
            }
        });
    }

    function tc_front_selectables() {
        $(".ui-selectable").selectable({
            filter: '.tc_set_seat',
            cancel: '.tc_seat_reserved, .tc_seat_in_others_cart, .tc_seat_unavailable',
            selected: function (e, ui) {
                var check_class = jQuery(ui.selected).attr('class');

                if (e.srcElement !== undefined) {
                    var checkElement = e.srcElement;
                } else {
                    var checkElement = e.target;
                    checkElement.id = 'mozila';
                }


                if (check_class.indexOf('tc_seat_unavailable ') == -1 && checkElement.id !== '' && checkElement.type !== 'mousemove') {

                    if ($(ui.selected).hasClass('tc_seat_in_others_cart')) {
                        $(ui.selected).removeClass('ui-selected');
                    }

                    var selected = $('.ui-selected').last();

                    if (!selected.hasClass('tc_seat_reserved')) {

                        var ticket_type = selected.attr('data-tt-id');
                        var ticket_type_title = $('li.tt_' + ticket_type).attr('data-tt-title');
                        var ticket_type_price = $('li.tt_' + ticket_type).attr('data-tt-price');
                        var ticket_seat_number = selected.find('span p').html();

                        if (typeof ticket_seat_number === 'undefined' || !ticket_seat_number) {
                            ticket_seat_number = '';
                        }

                        if (!selected.hasClass('tc_seat_in_cart') || (selected.hasClass('tc_seat_in_cart') && selected.hasClass('tc-object-selectable'))) {
                            $('#tc-regular-modal .model_extras .tc_quantity_selector').remove();
                            if ((selected.hasClass('tc-object-selectable'))) {

                                $("#tc-regular-modal .model_extras").html(tc_seat_chart_ajax.tc_loading_options_message);
                                $('#tc-regular-modal button.tc_cart_button').html(tc_seat_chart_ajax.tc_add_to_cart_button_title);

                                $.post(tc_seat_chart_ajax.ajaxUrl, {action: "tc_seat_chart_get_standing_area_options", seat_ticket_type_id: ticket_type}, function (data) {
                                    $("#tc-regular-modal .model_extras").html(data);
                                });

                                $("#tc-regular-modal .tc_regular_modal_ticket_type").html(ticket_type_title);
                                $("#tc-regular-modal .tc_regular_modal_seat_label").html(ticket_seat_number);
                                $("#tc-regular-modal .tc_regular_modal_price").html(ticket_type_price);

                                var seating_chart_id = $(this).parents('.tc_seating_map').first().attr('data-seating-chart-id');

                                $('#tc-regular-modal .tc_regular_modal_seating_chart_id').val(seating_chart_id);

                                $('#tc-regular-modal .tc_regular_modal_ticket_type_id').val(ticket_type);

                                $('#tc-regular-modal .tc_regular_modal_seat_id').val(selected.attr('id'));

                                $("#tc-regular-modal").dialog({
                                    resizable: false,
                                    draggable: false,
                                    height: "auto",
                                    width: 490,
                                    modal: true,
                                    close: function (event, ui) {
                                        $(".tc-group-wrap *").removeClass('ui-selected');
                                    }
                                });
                            } else {
                                $('#tc-regular-modal button.tc_cart_button').html(tc_seat_chart_ajax.tc_add_to_cart_button_title);
                                $("#tc-regular-modal .tc_regular_modal_ticket_type").html(ticket_type_title);
                                $("#tc-regular-modal .tc_regular_modal_seat_label").html(ticket_seat_number);
                                $("#tc-regular-modal .tc_regular_modal_price").html(ticket_type_price);

                                var seating_chart_id = $(this).parents('.tc_seating_map').first().attr('data-seating-chart-id');

                                $('#tc-regular-modal .tc_regular_modal_seating_chart_id').val(seating_chart_id);

                                $('#tc-regular-modal .tc_regular_modal_ticket_type_id').val(ticket_type);

                                $('#tc-regular-modal .tc_regular_modal_seat_id').val(selected.attr('id'));

                                $("#tc-regular-modal").dialog({
                                    dialogClass: 'tc-tickera-seating-modal',
                                    resizable: false,
                                    draggable: false,
                                    height: "auto",
                                    closeOnEscape: true,
                                    width: 490,
                                    modal: true,
                                    close: function (event, ui) {
                                        $(".tc-group-wrap *").removeClass('ui-selected');
                                    }
                                });
                            }
                        } else {
                            $('#tc-modal-added-to-cart button.tc_cart_button').html(tc_seat_chart_ajax.tc_remove_from_cart_button_title);
                            $("#tc-modal-added-to-cart .tc_regular_modal_ticket_type").html(ticket_type_title);
                            $("#tc-modal-added-to-cart .tc_regular_modal_seat_label").html(ticket_seat_number);
                            $("#tc-modal-added-to-cart .tc_regular_modal_price").html(ticket_type_price);

                            var seating_chart_id = $(this).parents('.tc_seating_map').first().attr('data-seating-chart-id');
                            $('#tc-modal-added-to-cart .tc_regular_modal_seating_chart_id').val(seating_chart_id);

                            $('#tc-modal-added-to-cart .tc_regular_modal_ticket_type_id').val(ticket_type);

                            $('#tc-modal-added-to-cart .tc_regular_modal_seat_id').val(selected.attr('id'));

                            $("#tc-modal-added-to-cart").dialog({
                                dialogClass: 'tc-tickera-seating-modal',
                                resizable: false,
                                draggable: false,
                                height: "auto",
                                width: 490,
                                closeOnEscape: true,
                                modal: true,
                                close: function (event, ui) {
                                    $(".tc-group-wrap *").removeClass('ui-selected');
                                }
                            });
                        }
                    }
                }
            },
            selecting: function (e, ui) {

                if ($(".ui-selected, .ui-selecting").length > 1) {
                    $('.ui-selecting').removeClass("ui-selecting");
                }

            },
        });
    }
});