/**
 * TO DO (for future releases)
 */
jQuery(document).ready(function ($) {
    var config = {
        apiKey: tc_firebase_vars.apiKey,
        authDomain: tc_firebase_vars.authDomain,
        databaseURL: tc_firebase_vars.databaseURL,
    };

    firebase.initializeApp(config);

    function tc_get_checkedin_seats_results(chart_id) {

        var ref = firebase.database().ref('/check-ins/' + chart_id);

        ref.on("child_added", function (data) {
          //console.log(data.key);
            tc_mark_checked_in_seat(chart_id, data.key);
        });

        ref.on("child_changed", function (data) {
          //console.log(data);
            tc_mark_checked_in_seat(chart_id, data.key);
        });

        ref.on('child_removed', function (data) {
          //console.log(data);
            tc_mark_checked_in_seat_unchecked(chart_id, data.key);
        });
    }

    tc_get_checkedin_seats_results(tc_firebase_vars.tc_chart_id);

    function tc_mark_checked_in_seat(chart_id, seat_id) {
        $('#' + seat_id + '').css('background-color', tc_firebase_vars.tc_checkedin_seat_color);
        $('#' + seat_id).addClass('tc_seat_checked_in');
    }

    function tc_mark_checked_in_seat_unchecked(chart_id, seat_id) {
        //tc_set_seat_attributes_firebase('.selectable_row #' + seat_id, tc_seats[seat_id]);
        $('.selectable_row #' + seat_id).removeClass('tc_seat_checked_in');

        var ticket_type_id = $('#'+seat_id).data('tt-id');

        var tc_seat_color = tc_seat_default_colors[ticket_type_id];

        if (typeof tc_seat_color == typeof undefined || tc_seat_color == '') {
            tc_seat_color = '#0085BA ';
        }

        $(this).css({'background-color': tc_seat_color});

        tc_controls.tc_mark_reserved_seats($('#post_ID').val());
    }

});
