( function ( $ ) {

    /**
     * Remove from cart trigger
     * @param {type} param
     */
    $( document ).ready( function () {

        $('body').on( 'click', '.tc_cart_remove_icon', function ( e ) {

            e.preventDefault();
            $(this).find('i').attr('style', 'opacity:0;');
            $(this).prepend('<div class="tc-delete-loader"></div>');

            $('#tickera_cart, .woocommerce-checkout #customer_details').append('<div class="tc-form-disable"></div>');

            let seat_ticket_type_id = $(this).data('ticket-type-id'),
                seat_sign = $(this).data('seat-sign'),
                chart_id = $(this).data('chart-id'),
                seat_id = $(this).data('seat-id'),
                tcsc_seat = chart_id + '-' + seat_id + '-' + seat_ticket_type_id;

            if( tc_seat_chart_cart_ajax.firebase_integration == 1 ){
                $.post(tc_seat_chart_cart_ajax.ajaxUrl, {
                        action: "tc_remove_seat_from_firebase_cart",
                        seat_ticket_type_id: seat_ticket_type_id,
                        seat_sign: seat_sign,
                        seat_id: seat_id,
                        chart_id: chart_id},
                    function (data) {
                    });
            }

            let data = {
                action: 'tc_remove_seat_from_cart_ajax',
                tcsc_seat: tcsc_seat
            };

            $.post( tc_seat_chart_cart_ajax.ajaxUrl, data, function ( response ) {

                if ( response ) {
                    window.location.href = window.location.href;
                }
            });
        });
    });

})( jQuery );