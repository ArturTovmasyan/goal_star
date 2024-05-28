/**
 * Created by aram on 5/23/16.
 */

'use strict';

var subscriberId = null;
var stripePublishKey = $("#stripe_publish_key").val();
var ios = $("#is_ios").val();
var donate = $("#donate").val();
var coupon = null;
var prefix = (window.location.pathname.indexOf('app_dev.php') === -1) ? "/" : "/app_dev.php/";

var handler = StripeCheckout.configure({
    key: stripePublishKey,
    image: '/luvbyrd57.png',
    locale: 'auto',
    token: function(response) {
        $('#shadow').show();
        $('.modal-loading').show();
        donate = $("#donate").val();
        $.ajax({
            type: "PUT",
            url: prefix + 'api/v1.0/payment',
            headers: { 'token': response.id, 'coupon' : coupon },
            data: {
                email : response.email,
                subscriberId: subscriberId,
                donate:donate
            },
            success:function(data) {
                $('#shadow').hide();
                $('.modal-loading').hide();

                if(ios === 'true'){
                    window.location = prefix + 'payment/payed';
                }
                else{
                    location.reload();
                }

            },
            error:function(data) {
                $('#shadow').hide();
                $('.modal-loading').hide();

                if(ios === 'true'){
                    window.location = prefix + 'payment/not-payed' + '?error=' + data.responseText;
                }
                else{
                    toastr.error(data.responseText);
                }
            }
        });
    }
});

var changeCardHandler = StripeCheckout.configure({
    key: stripePublishKey,
    image: '/luvbyrd57.png',
    locale: 'auto',
    token: function(response) {
        $('#shadow').show();
        $('.modal-loading').show();
        $.ajax({
            type: "POST",
            url: prefix + 'api/v1.0/payments/cards',
            headers: { 'token': response.id },
            data: {
                email : response.email
            },
            success:function(data) {
                $('#shadow').hide();
                $('.modal-loading').hide();
                toastr.success("Your card successfully changed");
            },
            error:function(data) {
                $('#shadow').hide();
                $('.modal-loading').hide();

                if(ios === 'true'){
                    window.location = prefix + 'payment/not-payed' + '?error=' + data.responseText;
                }
                else{
                    toastr.error(data.responseText);
                }
            }
        });
    }
});

$(".generateStripe").bind('click', function(e) {

    var name = $(this).data('name');
    var amount = $(this).data('amount');
    var currency = $(this).data('currency');
    var id = $(this).data('stripe-id');
    var email = $(this).data('email');

    generateStripe(e, name, amount, currency, id, email);
});

/**
 *
 * @param email
 */
function changeCard(email)
{
    changeCardHandler.open({
        name: 'luvbyrd.com',
        description: 'change card',
        amount: 0,
        email: email,
        billingAddress: true,
        zipCode: true
    });
}

function generateStripe(e, name, amount, currency, id, email)
{
    if(e && $(e.target).is('input')){
        return e.preventDefault();
    }

    subscriberId = id;
    coupon = $("#"+id+"_coupon").val();
    var success = true;

    if(coupon){
        success = false;
        $.ajax({
            type: "GET",
            url: prefix + 'api/v1.0/payment/discount/price',
            headers: { 'coupon': coupon, 'price' : amount },
            success:function(data) {
                success = true;
                amount = data;
                openHandler(name, amount, currency, email);
            },
            error:function(data) {
                success = false;
                toastr.error(data.responseText);
            }
        });
    }

    if(success){
        openHandler(name, amount, currency, email);
    }

    if(e){
        e.preventDefault();
    }
}

function openHandler(name, amount, currency, email) {
    handler.open({
        name: 'luvbyrd.com',
        description: name,
        amount: amount,
        currency: currency,
        email: email,
        billingAddress: true,
        zipCode: true
    });
}

function pressEnter(ev, name, amount, currency, id, email){
    if(ev.which == 13){
        generateStripe(null , name, amount, currency, id, email)
    }
}

// Close Checkout on page navigation:
$(window).on('popstate', function() {
    handler.close();
    changeCardHandler.close();
});