var braintree3ds2 = JSON.parse(braintree_params);
var dropin, amount, clientToken, ajaxUrl;
var payBtn = document.getElementById('pay-btn-3ds2');
var methodBraintree = document.getElementById('braintree_3ds2');
var tableBraintree = document.getElementById('tbl_braintree');
var overlay = document.getElementById('braintree_overlay');
var preload = document.getElementById('braintree_preload');
var paymentForm = document.getElementById('tc_payment_form');
var paymentFormChildChount = paymentForm.childElementCount;
var nonceGroup = document.querySelector('.nonce-group');
var nonceInput = document.querySelector('.nonce-group input');
var nonceSpan = document.querySelector('.nonce-group span');
var payGroup = document.querySelector('.pay-group');
var billingFields = [
    'billing-email',
    'billing-phone',
    'billing-first-name',
    'billing-last-name',
    'billing-street-address',
    'billing-extended-address',
    'billing-city',
    'billing-region',
    'billing-postal-code',
    'billing-country-code'
].reduce(function (fields, fieldName) {
    var field = fields[fieldName] = {
        input: document.getElementById(fieldName),
        help: document.getElementById('help-' + fieldName)
    };

    field.input.addEventListener('focus', function() {
        clearFieldValidations(field);
    });

    return fields;
}, {});
billingFields['billing-extended-address'].optional = true;


/**
 * Remove Error messages
 * @param field
 */
function clearFieldValidations (field) {
    field.help.innerText = '';
    field.help.parentNode.classList.remove('has-error');
}

/**
 * Validate Billing Fields
 * @returns {boolean}
 */
function validateBillingFields() {
    var isValid = true;

    Object.keys(billingFields).forEach(function (fieldName) {
        var fieldEmpty = false;
        var field = billingFields[fieldName];

        if (field.optional) {
            return;
        }

        fieldEmpty = field.input.value.trim() === '';

        if (fieldEmpty) {
            isValid = false;
            field.help.innerText = braintree3ds2.billing_error;
            field.help.parentNode.classList.add('has-error');
        } else {
            clearFieldValidations(field);
        }
    });

    return isValid;
}

/**
 * Initialize Braintree Dropin connection
 */
function start() {
    // Assign global amount value for threeDSecure
    amount = braintree3ds2.amount;

    // Assign global ajaxUrl value for payment_method_nonce
    ajaxUrl = braintree3ds2.callback;

    // Pass Client Token for Verification
    clientToken = braintree3ds2.token;

    onFetchClientToken(clientToken);
}

/**
 * Initialize Braintree Dropin form
 * @param clientToken
 * @returns {*}
 */
function onFetchClientToken(clientToken) {
    return setupDropin(clientToken).then(function (instance) {
        dropin = instance;
        payBtn.style.display = 'block';
        setupForm();

    }).catch(function (err) {
        onFetchResponseError(err);
        preload.style.display = 'none';
    });
}

/**
 * Display error messages
 * @param err
 */
function onFetchResponseError(err) {
    let errorContainer = document.getElementById('payment_method_errors');

    console.log(braintree3ds2.component_error, err);
    errorContainer.innerText = "Error: " + err.message;
}

/**
 * Create Braintree Dropin form
 * @param clientToken
 * @returns {*}
 */
function setupDropin (clientToken) {
    return braintree.dropin.create({
        authorization: clientToken,
        container: '#drop-in',
        threeDSecure: true,
    })
}

/**
 * Enable Pay button on Braintree Initialization
 */
function setupForm() {
    enablePayNow();
}

/**
 * Enable/Display Pay button
 */
function enablePayNow() {
    payBtn.value = braintree3ds2.pay_now;
    payBtn.removeAttribute('disabled');
}

/**
 * Display payload nonce
 * @param payload
 * @param liabilityShift
 */
function showNonce(payload, liabilityShift) {
    nonceSpan.textContent = braintree3ds2.liability_shifted + liabilityShift;
    nonceInput.value = payload.nonce;
    payGroup.classList.add('hidden');
    payGroup.style.display = 'none';
    nonceGroup.classList.remove('hidden');
}

/**
 * Process payment and create Tickera order
 * @param nonce
 */
function nonceCallback(nonce) {

    var xhttp = new XMLHttpRequest();
    var params = 'nonce=' + nonce;
    xhttp.open('POST', ajaxUrl, true);

    //Send the proper header information along with the request
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhttp.onreadystatechange = function() {//Call a function when the state changes.
        if(xhttp.readyState == 4 && xhttp.status == 200) {
            let response = JSON.parse(xhttp.responseText);
            if (response) {
                window.location.assign(response);
            } else {
                window.location.reload();
            }
        }
    }
    xhttp.send(params);
}

/**
 * Validate if Numeric value
 * @param evt
 * @returns {boolean}
 */
function isNumeric(evt)
{
    let charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}

/**
 * Prepare error messages
 * @param err
 * @param ref
 */
function errorMessage(err, ref = null) {
    let container = document.createElement("div");
    container.classList.add('tc_transaction_errors');
    container.innerText = err;
    paymentForm.prepend(container);

    if (ref != null) {
        let anchor = document.createElement('a');
        anchor.setAttribute('href',ref);
        anchor.setAttribute('target','blank');
        anchor.innerText = 'Three D Secure Validation Errors';
        container.append(anchor);
    }

    document.getElementById('tc_payment_form').scrollIntoView({block:'center',behavior: 'smooth'});
}

/**
 * Request payment method to Braintree Gateway and validation
 */
payBtn.addEventListener('click', function(event) {

    event.preventDefault();

    paymentForm.firstChild.remove();

    payBtn.setAttribute('disabled', 'disabled');
    payBtn.value = braintree3ds2.processing;

    var billingIsValid = validateBillingFields();

    if (!billingIsValid) {
        enablePayNow();
        return;
    }

    dropin.requestPaymentMethod({
        threeDSecure: {
            amount: amount,
            email: billingFields['billing-email'].input.value,
            billingAddress: {
                givenName: billingFields['billing-first-name'].input.value,
                surname: billingFields['billing-last-name'].input.value,
                phoneNumber: billingFields['billing-phone'].input.value.replace(/[\(\)\s\-]/g, ''), // remove (), spaces, and - from phone number
                streetAddress: billingFields['billing-street-address'].input.value,
                extendedAddress: billingFields['billing-extended-address'].input.value,
                locality: billingFields['billing-city'].input.value,
                postalCode: billingFields['billing-postal-code'].input.value,
                countryCodeAlpha3: billingFields['billing-country-code'].input.value,
                // region: billingFields['billing-region'].input.value, // TODO: Braintee unable to detect country code when region is enabled
            }
        }
    }, function(err, payload) {
        if (err) {
            dropin.clearSelectedPaymentMethod();

            let message = braintree3ds2.tokenization_error;
            let refLink = 'https://developers.braintreepayments.com/reference/general/validation-errors/all/php';
            let errOriginal = err.details.originalError;

            if (typeof errOriginal.details != 'undefined') {
                message = message +
                    err.message + '\n\r' +
                    errOriginal.details.originalError.error.message + '\n\r';

                errorMessage(message, refLink);
            } else {
                errorMessage(message + err.message + '\n\r', refLink);
            }

            enablePayNow();
            return;
        }

        if (!payload.liabilityShifted) {
            dropin.clearSelectedPaymentMethod();
            errorMessage(braintree3ds2.process_error);
            enablePayNow();
            return;
        }

        console.log(braintree3ds2.verification_success, payload);
        overlay.style.display = 'block';
        nonceCallback(payload.nonce);
    });
});

if(paymentFormChildChount > 1) {
    methodBraintree.addEventListener('click', function(event) {
        let dropIn = document.getElementById('drop-in');
        if (!dropIn.children.length) {
            start();
        }
    });
} else {
    let dropIn = document.getElementById('drop-in');
    if (!dropIn.children.length) {
        start();
    }
}

// Select the node that will be observed for mutations
const dropInNode = document.getElementById('drop-in');

// Options for the observer (which mutations to observe)
const dropInConfig = { childList: true };

// Callback function to execute when mutations are observed
const dropInCallback = function(mutationsList, dropInObserver) {
    // Use traditional 'for loops' for IE 11
    for(let mutation of mutationsList) {
        if (mutation.type === 'childList') {
            tableBraintree.style.display = 'table';
            preload.style.display = 'none';
        }
    }

    // Stop observing
    dropInObserver.disconnect();
};

// Create an observer instance linked to the callback function
const dropInObserver = new MutationObserver(dropInCallback);

// Start observing the target node for configured mutations
dropInObserver.observe(dropInNode, dropInConfig);

/**
 *  Initialize Country and Region Fields
 */
for ( let i = 0;  i < braintree3ds2.country_data.length; i++ ) {
    jQuery('#tbl_braintree #billing-country-code').append( '<option value="' + braintree3ds2.country_data[i].id + '">' + braintree3ds2.country_data[i].text + '</option>' );
}

/**
 * Update regions based on selected country
 */
jQuery(document).on('change', '#tbl_braintree #billing-country-code', function() {

    let selected_country = jQuery(this).val();

    // Make sure to empty field before process
    jQuery('#tbl_braintree #billing-region').empty();
    jQuery('#tbl_braintree #billing-region').attr('disabled', true);

    jQuery( braintree3ds2.region_data ).each( function( index, elem ) {
        if (elem.countryShortCode == selected_country) {
            for (let i = 0; i < elem.regions.length; i++) {
                jQuery('#tbl_braintree #billing-region').append('<option value="' + elem.regions[i].name + '">' + elem.regions[i].name + ' | ' + elem.regions[i].shortCode + '</option>');
            }
            jQuery('#tbl_braintree #billing-region').attr('disabled', false);
        }
    });
});