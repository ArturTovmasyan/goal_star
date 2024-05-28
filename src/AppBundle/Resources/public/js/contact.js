/**
 * Created by aram on 3/11/16.
 */

$button = $(".contact-button");
$loading = $(".form-loading");

$(document).on({
    ajaxStart: function() {

        $button.addClass("sr-only");
        $loading.removeClass("sr-only");
    },
    ajaxStop: function() {
        $button.removeClass("sr-only");
        $loading.addClass("sr-only");
    }
});

var options = {
    success:       showResponse,  // post-submit callback
    error:       showErrors,  // post-submit callback
    beforeSubmit: showRequest,
    clearForm: true  ,      // clear all form fields after successful submit
    resetForm: true        // reset the form after successful submit

};

// bind form using 'ajaxForm'
$("#contact-form").ajaxForm(options);

function showRequest()
{
    $("#error-text").html('');
}

// pre-submit callback
function showErrors(xhr, status, error) {

    var results = xhr.responseText;
    var obj = jQuery.parseJSON(results);

    $(obj).each(function( index, element ) {
        $("#error-text").append('<p class="text-red">' + element + '</p>');
    });

    console.log('test');

    return false;
}

// post-submit callback
function showResponse(responseText, statusText, xhr, $form)  {
    return true;
}