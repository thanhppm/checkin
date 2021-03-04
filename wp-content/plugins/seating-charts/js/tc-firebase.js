jQuery( document ).ready( function ( $ ) {

    var config = {
        apiKey: tc_firebase_vars.apiKey,
        authDomain: tc_firebase_vars.authDomain,
        databaseURL: tc_firebase_vars.databaseURL,
    };

    firebase.initializeApp( config );

    window.tc_firebase = {

        /**
         * Initialize Seats
         *
         * @param chart_id
         */
        init: function ( chart_id ) {

            tc_firebase.get_in_cart_seats_results( chart_id );
            tc_firebase.get_reserved_seats_results( chart_id );

            setTimeout(function () {
                tc_firebase.update_firebase_realtime_database( chart_id );
            }, 1000 );

            setTimeout(function () {
                tc_firebase.remove_expired_firebase_seats( chart_id );
            }, 10 * 1000 );

            setInterval(function () {
                tc_firebase.remove_expired_firebase_seats( chart_id );
            }, 2 * 60 * 1000 );
        },

        /**
         * Update Seats in Firebase Realtime Database.
         * Check for expiration.
         *
         * @param chart_id
         */
        remove_expired_firebase_seats: function ( chart_id ) {

            $.post( tc_seat_chart_ajax.ajaxUrl, { action: 'tc_remove_expired_firebase_seats', tc_seating_chart_id: chart_id }, function ( data ) {

                // Make sure to compare with 0. If the return data is 0, it indicates an empty ajax response.
                if ( typeof data !== 'undefined' && data != 0 ) {

                    if ( typeof data.total !== 'undefined' ) {

                        /* Update Woocommerce Subtotal */
                        $( '.tc-seatchart-subtotal' ).html( data.subtotal + '<strong>' + data.total + '</strong>' );

                    } else {

                        /* Update Tickera Subtotal */
                        $( '.tc-seatchart-subtotal strong' ).html( data.subtotal );
                    }
                }
            });
        },

        /**
         * Populate Seats onto Firebase Realtime Database
         * Update Firebase Seats based on seat cookies.
         *
         * @param chart_id
         */
        update_firebase_realtime_database: function ( chart_id ) {

            let tc_seat_cart_items = [];
            $.each( $( '.tc_seat_in_cart:not(.tc-object-selectable)' ), function () {
                var seat_id = $(this).attr( 'id' );
                var seat = chart_id + '-' + seat_id;
                tc_seat_cart_items.push( seat );
            });
            $.post( tc_seat_chart_ajax.ajaxUrl, { action: 'tc_add_seat_to_firebase_cart', tc_seat_cart_items: tc_seat_cart_items }, function ( data ) {} );
        },

        /**
         * Collect all reserved seats from firebase and mark reserved in the frontend
         *
         * @param chart_id
         */
        get_reserved_seats_results: function ( chart_id ) {
            var ref = firebase.database().ref( '/reserved/' + chart_id );
            var two_minutes_ago = get_server_time() - ( 2 * 60 * 1000 );
            ref.orderByChild( 'timestamp' ).startAt(two_minutes_ago).on( 'child_added', function ( data ) {
                $( '.tc_seating_map_' + chart_id + ' #' + data.key + ':not(.tc-object-selectable)' ).css( 'background-color', tc_seat_chart_ajax.tc_reserved_seat_color );
                $( '.tc_seating_map_' + chart_id + ' #' + data.key + ':not(.tc-object-selectable)' ).css( 'color', tc_seat_chart_ajax.tc_reserved_seat_color );
                $( '.tc_seating_map_' + chart_id + ' #' + data.key + ':not(.tc-object-selectable)' ).removeClass( 'ui-selected ui-selectee' );
                $( '.tc_seating_map_' + chart_id + ' #' + data.key + ':not(.tc-object-selectable)' ).addClass( 'tc_seat_reserved' );
            });
        },

        /**
         * Collect all in_cart seats from firebase and mark in_others_cart in the frontend
         *
         * @param chart_id
         */
        get_in_cart_seats_results: function ( chart_id ) {

            var ref = firebase.database().ref( '/in-cart/' + chart_id );
            var two_minutes_ago = get_server_time() - ( 10 * 60 * 1000 );

            ref.orderByChild( 'timestamp' ).startAt( two_minutes_ago ).on( 'child_added', function ( data ) {
                if ( data.val().session_id != tc_firebase_vars.session_id ) {
                    tc_firebase.mark_in_others_cart_seat( chart_id, data.key );
                }
            });

            ref.on( 'child_removed', function ( data ) {
                if ( typeof data.key !== 'undefined' ) {
                    tc_firebase.mark_available_seat( chart_id, data.key );
                }
            });
        },

        /**
         * Mark Seats in Others' Cart
         *
         * @param chart_id
         * @param seat_id
         */
        mark_in_others_cart_seat: function ( chart_id, seat_id ) {
            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).css( 'background-color', tc_firebase_vars.tc_in_others_cart_seat_color );
            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).css( 'color', tc_firebase_vars.tc_in_others_cart_seat_color );
            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).addClass( 'tc_seat_in_others_cart' );
            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).removeClass( 'ui-selected ui-selectee' );
        },

        /**
         * Mark Seats in User's Cart
         *
         * @param chart_id
         * @param seat_id
         */
        mark_available_seat: function ( chart_id, seat_id ) {

            let ticket_type = $( '.tc_seating_map_' + chart_id + ' #' + seat_id).attr( 'data-tt-id' ),
                color = $( 'li.tt_' + ticket_type ).css( 'color' );

            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).css( { 'background-color': color } );
            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).css( { 'color': color } );
            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).removeClass( 'tc_seat_in_cart tc_seat_in_others_cart' );
            $( '.tc_seating_map_' + chart_id + ' #' + seat_id + ':not(.tc-object-selectable)' ).addClass( 'ui-selectee' );
        }
    }

    /**
     * Get the current server time
     *
     * @returns {number}
     */
    function get_server_time() {
        let server_time = Date.now();
        $.ajax( {
            async: false,
            success: function( output, status, xhr ) {
                server_time = Date.parse( xhr.getResponseHeader( 'Date' ) );
            }
        } );
        return server_time;
    }
});