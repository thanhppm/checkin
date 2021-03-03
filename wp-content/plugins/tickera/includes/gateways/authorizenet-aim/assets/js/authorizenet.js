let authorizenet = JSON.parse(authorizenet_params);
let authorizeBillingFields = [
    'authorize-billing-address',
    'authorize-billing-city',
    'authorize-billing-country',
    'authorize-billing-state',
    'authorize-billing-postal-code',
    'authorize-billing-phone',
    'authorize-card-num',
    'authorize-exp-month',
    'authorize-exp-year',
    'authorize-card-code'
].reduce(function (fields, fieldName) {

    let field = fields[fieldName] = {
        input: document.getElementById(fieldName),
        help: document.getElementById('help-' + fieldName)
    };

    return fields;
}, {});

/**
 *  Initialize Country and Region Fields
 */
jQuery('#authorize-billing-country').select2({
    width: '100%',
    placeholder: "",
    data: authorizenet.country_data,
});
jQuery('#authorize-billing-state').select2({
    width: '100%',
    placeholder: "",
});

/**
 * Update regions based on selected country
 */
jQuery(document).on('change', '#authorize-billing-country', function() {

    let selected_country = jQuery(this).val();

    jQuery.post(tc_ajax.ajaxUrl, { action: "collect_regions_ajax", selected_country: selected_country },
        function (response) {
            if ( response ) {
                // Rebuild Region field
                jQuery('#authorize-billing-state').empty();
                jQuery('#authorize-billing-state').select2({
                    width: '100%',
                    placeholder: "",
                    data: response
                })
            }
        }
    );
});

/**
 * Trigger Validation on Submit Payment
 */
jQuery(document).on('click', '#authorizenet-api .tc_payment_confirm', function(event) {
    let billingIsValid = validateAuthorizeBillingFields();
    if (!billingIsValid) {
        event.preventDefault();
    }
});

/**
 * Validate if Numeric value
 * @param evt
 * @returns {boolean}
 */
function isNumeric(evt) {
    let charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}

/**
 * Validate Billing Fields
 * @returns {boolean}
 */
function validateAuthorizeBillingFields() {
    let isValid = true;

    Object.keys(authorizeBillingFields).forEach(function (fieldName) {
        let fieldEmpty = false;
        let field = authorizeBillingFields[fieldName];

        fieldEmpty = field.input.value.trim() === '';

        if (fieldEmpty) {
            isValid = false;
            field.help.innerText = authorizenet.billing_error;
            field.help.parentNode.classList.add('has-error');
        } else {
            clearAuthorizeFieldsValidations(field);
        }
    });

    return isValid;
}

/**
 * Remove Error messages
 * @param field
 */
function clearAuthorizeFieldsValidations (field) {
    field.help.innerText = '';
    field.help.parentNode.classList.remove('has-error');
}