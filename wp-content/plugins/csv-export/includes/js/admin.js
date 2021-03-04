( function ( $ ) {

    window.tc_csv_export = {

        get_ticket_types_collection: function() {

            $.post( tc_csv_vars.ajaxUrl, { action: 'tc_get_ticket_type', id: $('#tc_export_csv_event_data').val() }, function( response ) {

                if ( typeof response !== 'undefined' && !response.error ) {

                    let ticket_ids = [],
                        i = 0;

                    $('#tc_export_csv_ticket_type_data').next().show();
                    $('#tc_export_csv_ticket_type_data').attr( 'disabled', false );
                    $('#no_ticket_type').hide();

                    // Empty select field on load
                    $('#tc_export_csv_ticket_type_data').empty();

                    // Initialize and option with 'All'
                    $('#tc_export_csv_ticket_type_data').append( '<option selected="selected" id="select_all" value="">' + tc_csv_vars.select_all + '</option>' );

                    // Propagate Ticket Options
                    $.each( response.ticket_types, function ( key, value ) {

                        // Capturing the mark from the server end. This will avoid auto sorting of json response
                        let replaced_key = key.replace( 'TC', '' );

                        ticket_ids[i] = replaced_key; i++;
                        $('#tc_export_csv_ticket_type_data').append( '<option value="'+ replaced_key +'">'+ value +'</option>' );
                    });

                    // Propagate 'All' value
                    $('#tc_export_csv_ticket_type_data option:first-child').attr('value', ticket_ids.join( ',' ) );

                    // Update Chosen Select Field
                    $('#tc_export_csv_ticket_type_data').trigger('chosen:updated');

                } else {
                    $('#tc_export_csv_ticket_type_data').next().hide();
                    $('#tc_export_csv_ticket_type_data').attr( 'disabled', true );

                    if ( !$('#no_ticket_type').length ) {
                        $('#tc_export_csv_ticket_type_data').parent().append("<div id='no_ticket_type'><p>"+tc_csv_vars.ticket_type_message+"</p></div>");

                    } else {
                        $('#no_ticket_type').show();
                    }
                }
            });
        },

        /**
         * Remember csv export
         */
        remember_csv_attendees_selection: function() {

            var tc_remember= $('#tc_keep_selection_fields').prop('checked');

            if ( true == tc_remember ){
                $.ajax( { url: tc_csv_vars.ajaxUrl, method: 'post', data: { from_data: $( "#tc_form_attendees_csv_export" ).serializeArray(), action:'tc_keep_selection' }, dataType:"json" } );

            } else {
                $.ajax( { url: tc_csv_vars.ajaxUrl,	method: 'post', data: { from_data:'uncheck',action:'tc_keep_selection' }, dataType:"json" } );
            }
        },


        export_csv_attendees_post: function () {

            var progressLabel = $( ".progress-label" );

            $( "#csv_export_progressbar" ).show();

            $.post( tc_csv_vars.ajaxUrl, $( "#tc_form_attendees_csv_export" ).serialize() )
                .done( function( response ) {

                    if ( typeof response.page !== 'undefined' ) {

                        $( '#page_num' ).val( response.page );

                        if ( false === response.done ) {

                            $( "#csv_export_progressbar" ).progressbar( {
                                value: response.exported,
                                change: function() {
                                    progressLabel.text( $( "#csv_export_progressbar" ).progressbar( "value" ) + "%" );
                                }
                            } );

                            tc_csv_export.export_csv_attendees_post();

                        } else {

                            $( "#csv_export_progressbar" ).progressbar( {
                                value: 100,
                                change: function() {
                                    $( "#csv_export_progressbar" ).text( '' );
                                }
                            } );

                            $( "#csv_export_progressbar" ).hide();
                            $( '#page_num' ).val( 1 );

                            if( response.found_posts > 0 ){

                                $('.export_error').remove();
                                window.location = tc_csv_vars.ajaxUrl + '?action=tc_export_csv&document_title=' + $( '#document_title' ).val();

                            } else {
                                $('.export_error').remove();
                                $('#tc_form_attendees_csv_export .inside').after('<div class="export_error"><p>'+tc_csv_vars.attendee_list_error+'</p></div>');
                            }
                        }
                    }
                } );
        }
    }

    /**
     * Propagate Ticket Types field on page load
     */
    $( document ).on( 'ready', function() {
        tc_csv_export.get_ticket_types_collection();
    });

    /**
     * Propagate Ticket Types field on event field change
     */
    $( document ).on( 'change', '#tc_export_csv_event_data', function() {
        tc_csv_export.get_ticket_types_collection();
    });

    $( document ).on( 'click', '#tc_select_all_csv', function() {
        var tc_select_all = $(this).prop('checked');

        if( true == tc_select_all ){
            $("#tc_form_attendees_csv_export .form-table input[type=checkbox]").each(function() {
                $(this).prop("checked", true);
                $('#tc_keep_selection_fields').prop("checked", true);
            });

        } else {
            $("#tc_form_attendees_csv_export .form-table input[type=checkbox]").each( function() {
                $(this).prop("checked", false);
                $('#tc_keep_selection_fields').prop("checked", true);
            });
        }
    });

    $( document ).on( 'click', '#export_csv_event_data', function( e ) {
        e.preventDefault( );
        tc_csv_export.export_csv_attendees_post();
        tc_csv_export.remember_csv_attendees_selection();
    });

})( jQuery );