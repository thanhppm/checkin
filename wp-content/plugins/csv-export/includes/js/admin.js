jQuery( document ).ready( function( $ ) {
	
/*
**on load insert ticket id value in all selection option
*/
var ticket_select_all = [];
var i = 0;
jQuery("#tc_export_csv_ticket_type_data option").each(function()
{
var ticket_options = jQuery(this).val();
if( ticket_options != '' ){
	ticket_select_all.push(ticket_options);
}
});
jQuery('#tc_export_csv_ticket_type_data #select_all').val(ticket_select_all);

//alert(tc_csv_vars.ajaxUrl);
//document ready get default ticket type
jQuery.ajax({
	url: tc_csv_vars.ajaxUrl,
	method: 'post',
	data: {
	 id: jQuery('#tc_export_csv_event_data').val(),action:'tc_get_ticket_type',
	},
	dataType:"json",
	success: function(result){
	 if(result){
		 if(result.success == "success") {
			 var cnt = result.data;
			if(cnt == 0){
				jQuery('#tc_export_csv_ticket_type_data').next().remove();
			//	jQuery('#tc_export_csv_ticket_type_data').css('display','block');
				jQuery('#tc_export_csv_ticket_type_data').after("<div id='no_ticket_type'><p>"+tc_csv_vars.ticket_type_message+"</p></div>");
				var str1 = jQuery('#no_ticket_type p').html();
				var str2 = "There are no ticket type for this event";
				if(str1.indexOf(str2) != -1){
						jQuery('#tc_export_csv_ticket_type_data').css('display','none');
				}
			}
		}else{
		 var cnt = result.count;
		 var select = jQuery('#tc_export_csv_ticket_type_data');
		 var i;
		 select.empty();
		 var optionValues = [];//define optionvalue array
		 for(i=0;i<(cnt*2);i += 2){
			 if(cnt>1 && i===0){// check data >1
					//add select id
				 select.append($('<option id="select_all">').text("All"));//add select all id
			 }
			 select.append($('<option>').val(result[i].ticket_id).text(result[i+1].ticket_type));
			 jQuery('#tc_export_csv_ticket_type_data').css('display','block');
			 jQuery('#tc_export_csv_ticket_type_data').next().remove();
			 //convert all value in array
				optionValues.push(result[i].ticket_id);
			} //end foreach
		 jQuery('#tc_export_csv_ticket_type_data #select_all').val(optionValues);//insert array value in select option
		}
	}else{
			jQuery('#tc_export_csv_ticket_type_data').next().remove();
			jQuery('#tc_export_csv_ticket_type_data').css('display','none');
			jQuery('#tc_export_csv_ticket_type_data').after("<div id='no_ticket_type'><p>"+tc_csv_vars.ticket_type_message+"</p></div>");
		}


	}
});

//on change get ticket type
jQuery('#tc_export_csv_event_data').on('change', function(){
	jQuery.ajax({
		url: tc_csv_vars.ajaxUrl,
		method: 'post',
		data: {
		 id: jQuery(this).val(),action:'tc_get_ticket_type_change',
		},
		dataType:"json",
		success: function(result){
			if(result){
					var cnt = result.count;
					var select = jQuery('#tc_export_csv_ticket_type_data');
					var i;
					select.empty();
					var optionValues = [];//define optionvalue array
					for(i=0;i<(cnt*2);i += 2){
							if(cnt>1 && i===0){// check data >1
							 //add select id
							select.append($('<option id="select_all">').text("All"));//add

						}
					 select.append($('<option>').val(result[i].ticket_id).text(result[i+1].ticket_type));
						jQuery('#tc_export_csv_ticket_type_data').css('display','block');
						jQuery('#tc_export_csv_ticket_type_data').next().remove();
						//convert all value in array
						 optionValues.push(result[i].ticket_id);
					}//end for
					$('#tc_export_csv_ticket_type_data #select_all').val(optionValues);//insert array value in select option
					jQuery('#no_ticket_type').css('display','none');
					jQuery('#tc_export_csv_ticket_type_data').css('display','block');
			//	}
			}
			else{

				jQuery('#tc_export_csv_ticket_type_data').next().remove();
				jQuery('#tc_export_csv_ticket_type_data').empty();
				jQuery('#tc_export_csv_ticket_type_data').css('display','none');
				jQuery('#tc_export_csv_ticket_type_data').after("<div id='no_ticket_type'><p>"+tc_csv_vars.ticket_type_message+"</p></div>");
			}

		}
	});
});
	
    jQuery("#tc_select_all_csv").click(function(){
       var tc_select_all = jQuery(this).prop('checked');

      if(tc_select_all == true){
       jQuery("#tc_form_attendees_csv_export .form-table input[type=checkbox]").each(function() {
			jQuery(this).prop("checked", true);
			jQuery('#tc_keep_selection_fields').prop("checked", true);
        });
     }else{
        jQuery("#tc_form_attendees_csv_export .form-table input[type=checkbox]").each(function() {
			jQuery(this).prop("checked", false);
			jQuery('#tc_keep_selection_fields').prop("checked", true);
        });
	 }
    });

    $( '#export_csv_event_data' ).click( function( e ) {
        e.preventDefault( );
        export_csv_attendees_post();
  		remembered_csv_attendees_selection();//remember csv export
        //return false;
    } );

	//remember csv export
	function remembered_csv_attendees_selection(){
		var tc_remember= jQuery('#tc_keep_selection_fields').prop('checked');
		if(tc_remember == true){
			jQuery.ajax({ url: tc_csv_vars.ajaxUrl,method: 'post', data: {	from_data: $( "#tc_form_attendees_csv_export" ).serializeArray(),action:'tc_keep_selection',},
				  dataType:"json",});
		}else{
			jQuery.ajax({	url: tc_csv_vars.ajaxUrl,	method: 'post',data: {	from_data:'uncheck',action:'tc_keep_selection',},	dataType:"json",});
		}
	}

    function export_csv_attendees_post() {
        var progressLabel = $( ".progress-label" );

        $( "#csv_export_progressbar" ).show();

        $.post( tc_csv_vars.ajaxUrl, $( "#tc_form_attendees_csv_export" ).serialize() )
            .done( function( response ) {
                if ( typeof response.data.page !== 'undefined' ) {
                    $( '#page_num' ).val( response.data.page );
                    if ( response.data.done == false ) {
                        $( "#csv_export_progressbar" ).progressbar( {
                            value: response.data.exported,
                            change: function() {
                                progressLabel.text( $( "#csv_export_progressbar" ).progressbar( "value" ) + "%" );
                            },
                        } );
                        export_csv_attendees_post();
                    } else {
                        $( "#csv_export_progressbar" ).progressbar( {
                            value: 100,
                            change: function() {
                                $( "#csv_export_progressbar" ).text( '' );
                            },
                        } );
                        $( "#csv_export_progressbar" ).hide();
                        $( '#page_num' ).val( 1 );
                        if(response.data.found_posts > 0){
							jQuery('.export_error').remove();
							 tc_export_csv();
						}else{
							jQuery('.export_error').remove();
							jQuery('#tc_form_attendees_csv_export .inside').after('<div class="export_error"><p>'+tc_csv_vars.attendee_list_error+'</p></div>');
						}
                    }
                }
            } );
    }

    function tc_export_csv() {
        jQuery.get( tc_csv_vars.ajaxUrl, {
            action: 'tc_export_csv_dummy'
        } )
            .done( function( response ) {
                window.location = tc_csv_vars.ajaxUrl + '?action=tc_export_csv&document_title=' + $( '#document_title' ).val();
            } );
    }

} );
