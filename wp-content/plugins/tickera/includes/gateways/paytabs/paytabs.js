let paytabs = JSON.parse(paytabs_params);
let paytabs_overlay = document.getElementById('paytabs_overlay');
let paytabsBillingFields = [
    'paytabs-billing-email',
    'paytabs-billing-phone',
    'paytabs-billing-first-name',
    'paytabs-billing-last-name',
    'paytabs-billing-street-address',
    'paytabs-billing-extended-address',
    'paytabs-billing-city',
    'paytabs-billing-region',
    'paytabs-billing-postal-code',
    'paytabs-billing-country-code'
].reduce(function (fields, fieldName) {
    let field = fields[fieldName] = {
        input: document.getElementById(fieldName),
        help: document.getElementById('help-' + fieldName)
    };

    field.input.addEventListener('focus', function() {
        clearPaytabsFieldValidations(field);
    });

    return fields;
}, {});
paytabsBillingFields['paytabs-billing-extended-address'].optional = true;


/**
 * Remove Error messages
 * @param field
 */
function clearPaytabsFieldValidations (field) {
    field.help.innerText = '';
    field.help.parentNode.classList.remove('has-error');
}


/**
 *  Initialize Country and Region Fields
 */
for ( let i = 0;  i < paytabs.country_data.length; i++ ) {
    jQuery('#tbl_paytabs #paytabs-billing-country-code').append( '<option value="' + paytabs.country_data[i].id + '">' + paytabs.country_data[i].text + '</option>' );
}


/**
 * Validate Billing Fields
 * @returns {boolean}
 */
function validatePaytabsBillingFields() {
    var isValid = true;

    Object.keys(paytabsBillingFields).forEach(function (fieldName) {
        var fieldEmpty = false;
        var field = paytabsBillingFields[fieldName];

        if (field.optional) {
            return;
        }

        fieldEmpty = field.input.value.trim() === '';

        if (fieldEmpty) {
            isValid = false;
            field.help.innerText = paytabs.billing_error;
            field.help.parentNode.classList.add('has-error');
        } else {
            clearPaytabsFieldValidations(field);
        }
    });

    return isValid;
}


/**
 * Update regions based on selected country
 */
jQuery(document).on('change', '#tbl_paytabs #paytabs-billing-country-code', function() {

    let selected_country = jQuery(this).val();

    // Make sure to empty field before process
    jQuery('#tbl_paytabs #paytabs-billing-region').empty();
    jQuery('#tbl_paytabs #paytabs-billing-region').attr('disabled', true);

    jQuery( paytabs.region_data ).each( function( index, elem ) {
        if ( elem.countryShortCode == selected_country ) {
            for ( let i = 0;  i < elem.regions.length; i++ ) {
                jQuery('#tbl_paytabs #paytabs-billing-region').append( '<option value="' + elem.regions[i].name + '">' + elem.regions[i].name + ' | ' + elem.regions[i].shortCode + '</option>' );
            }
            jQuery('#tbl_paytabs #paytabs-billing-region').attr('disabled', false);
        }
    });
});


jQuery(document).on('click', '#paytabs .tc_payment_confirm', function( event ){
    event.preventDefault();

    let billingIsValid = validatePaytabsBillingFields();
    if (!billingIsValid) {
        return;
    }

    paytabs_overlay.style.display = 'block';

    // Retrieve form values
    let paytabs_arguments = {
        billing_phone: paytabsBillingFields['paytabs-billing-phone'].input.value.trim(),
        billing_street_address: paytabsBillingFields['paytabs-billing-street-address'].input.value.trim(),
        billing_extended_address: paytabsBillingFields['paytabs-billing-extended-address'].input.value.trim(),
        billing_city: paytabsBillingFields['paytabs-billing-city'].input.value.trim(),
        billing_region: paytabsBillingFields['paytabs-billing-region'].input.value.trim(),
        billing_postal_code: paytabsBillingFields['paytabs-billing-postal-code'].input.value.trim(),
        billing_country_code: paytabsBillingFields['paytabs-billing-country-code'].input.value.trim(),
    };

    // Request Paytabs Payment Page
    jQuery.post(tc_ajax.ajaxUrl, { action: "request_paytabs_paypage_ajax", paytabs_arguments: paytabs_arguments },

        function( response ) {

            if ( '4012' == response.response_code ) {

                /*
                 * Redirect to Pay Page
                 */

                window.location.href = response.payment_url;

            } else {

                /*
                 * Handle Pay Page Error
                 * Display error on failed page creation
                 */

                let errorContainer = document.getElementById('paytabs_errors');
                if ( typeof response.result !== "undefined" ) {
                    errorContainer.innerText = "Error: " + response.result;

                } else {
                    errorContainer.innerText = "Error: " + response.details;
                }

                errorContainer.className += ' _active';
                paytabs_overlay.style.display = 'none';
            }
        }
    );
});