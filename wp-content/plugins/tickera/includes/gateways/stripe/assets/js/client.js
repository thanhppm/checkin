let display_error = document.getElementById('card-errors'),
    form_completed = false,
    error_message;


let style = {
    base: {
        color: '#32325d',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
            color: '#aab7c4'
        }
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
    }
};

/**
 * Initialize Stripe Elements and Error handling
 */
try {
    var stripe = Stripe(stripe_client.publishable_key);
    var elements = stripe.elements();

    var card = elements.create("card", { hidePostalCode: true, style: style }  );
    card.mount("#card-element");
} catch (err) {
    display_error.textContent = err;
}


/**
 * Handle real-time validation errors from the card Element.
 */
card.addEventListener('change', function(event) {
    if (event.error) {
        error_message = event.error.message;
        display_error.textContent = error_message;
        form_completed = false;
    } else {
        display_error.textContent = '';
        form_completed = true;
    }
});


/**
 * Process Payment when "Submit Payment" Button is clicked
 */
jQuery( document ).on( 'click', '#stripe-submit', function( ev ) {
    ev.preventDefault();

    if ( form_completed ) {

        form_loading(true);
        jQuery.post( tc_ajax.ajaxUrl, { action: "process_payment" }, function ( response ) {

            stripe.confirmCardPayment( response.client_secret, {
                receipt_email: response.email,
                payment_method: {
                    card: card,
                    billing_details: {
                        name: response.customer_name,
                        email: response.email
                    }
                }
            }).then( function ( result ) {

                if ( typeof result.error !== 'undefined' ) {

                    /*
                     * Payment intent request has failed
                     * Allow the user to select another payment method
                     */
                    display_error.textContent = result.error.message;
                    order_confirmation( result );

                } else {

                    /*
                     * The payment has been processed!
                     */

                    switch( result.paymentIntent.status ) {
                        case 'succeeded':
                        case 'requires_capture':
                            display_error.textContent = '';
                            order_confirmation( result.paymentIntent );
                            break;
                    }
                }
            });
        });

    } else {
        display_error.textContent = error_message;
    }
});


/**
 * Validate Payment
 */
function order_confirmation( result ) {
    jQuery.post(tc_ajax.ajaxUrl, { action: "order_confirmation", payment_result: result },
        function (response) {
            if ( response != false ) {
                window.location.assign(response);
            } else {
                form_loading(false);
            }
        }
    );
}


/**
 * Show/Hide elements
 *
 * @param attr_val
 */
function form_loading(attr_val) {

    switch(attr_val) {
        case true:
            jQuery('.StripeElement').css('pointer-events', 'none');
            jQuery('#stripe-submit').attr('disabled', true).hide();
            jQuery('#stripe-loading').show();
            break;

        case false:
            jQuery('.StripeElement').css('pointer-events', 'initial');
            jQuery('#stripe-submit').attr('disabled', false).show();
            jQuery('#stripe-loading').hide();
            break;

        default:
            jQuery('.StripeElement').css('pointer-events', 'none');
            jQuery('#stripe-submit').attr('disabled', true);
            jQuery('#stripe-loading').show();
    }
}