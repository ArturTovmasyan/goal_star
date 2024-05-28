'use strict';

$(document).ready(function(){

    var options = {
        $AutoPlay: true,
        $ArrowKeyNavigation: true,
        $FillMode: 4,
        $ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
            $Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
            $ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
            $AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
            $Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
        }
    };
    var memberSliding = {};
    window.sliding_modal = $('.sliding-modal');
    var sliderTemplate = $("#member-sliding").parent().html();

    $.extend(memberSliding, options);

    memberSliding.$ThumbnailNavigatorOptions = {
        $Class: $JssorThumbnailNavigator$,              //[Required] Class to create thumbnail navigator instance
        $ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
        $ActionMode: 1,                                 //[Optional] 0 None, 1 act by click, 2 act by mouse hover, 3 both, default value is 1
        $SpacingX: 8,                                   //[Optional] Horizontal space between each thumbnail in pixel, default value is 0
        $AutoCenter: 1,
        $DisplayPieces: 7,                             //[Optional] Number of pieces to display, default value is 1
        $ParkingPosition: 0
    };

    var ScaleSlidingMember = function() {
        var parentWidth = $("#member-sliding").parent().width();

        if (parentWidth) {
            window.memberSlider.$ScaleWidth(parentWidth);
        }
        else {
            window.setTimeout(ScaleSlidingMember, 30);  // Need to take attention on this
        }
    };

    $(window).bind("resize", ScaleSlidingMember);
    $(window).bind("orientationchange", ScaleSlidingMember);

    $(document).on('click', '.members .member', function(){
        window.sliding_modal.modal({
            fadeDuration: 500
        });

        if(!window.memberSlider){
            window.memberSlider = new $JssorSlider$('member-sliding', memberSliding);
        }

        //Scale slider after document ready
        ScaleSlidingMember();
    });

    window.sliding_modal.on($.modal.OPEN, function() {
        if(!$("#member-sliding").length){
            sliding_modal.append(sliderTemplate);
        }

    });

    window.sliding_modal.on($.modal.CLOSE, function() {
        if($("#member-sliding").length){
            $("#member-sliding").remove();
            window.memberSlider = null;
        }

    });

});