'use strict';

function navigateMenu(target, li){
    if($(target).length){
        $('.vertical-menu a').removeClass('selected-menu');
        $(li).addClass('selected-menu');
        $(document).scrollTo($(target),500);
    }
}

$(document).ready(function(){
    $('.slick-slider').slick({
        slidesToShow: 3,
        slidesToScroll: 3,
        autoplay: true,
        speed: 1500
    });

    var aArray = [];
    aArray.push('#content-top');
    aArray.push('#signup');
    aArray.push('#available');
    aArray.push('#slider');
    aArray.push('#featured');

    $(window).scroll(function(){
        var windowPos = $(window).scrollTop(); // get the offset of the window from the top of page
        var windowHeight = $(window).height(); // get the height of the window
        var docHeight = $(document).height();

        for (var i = 0; i < aArray.length; i++) {
            var theID = aArray[i];
            if($(theID) && $(theID).offset()){
                var divPos = $(theID).offset().top; // get the offset of the div from the top of page
                var divHeight = $(theID).height(); // get the height of the div in question
                if (windowPos >= (divPos - 70) && windowPos < (divPos + divHeight - 70)) {
                    $(".vertical-menu a[data-target-id='" + theID + "']").addClass("selected-menu");
                } else {
                    $(".vertical-menu a[data-target-id='" + theID + "']").removeClass("selected-menu");
                }
            }
        }

        if(windowPos + windowHeight == docHeight) {
            if (!$(".vertical-menu li:last-child a").hasClass("selected-menu")) {
                var navActiveCurrent = $(".nav-active").data("target-id");
                $(".vertical-menu a[data-target-id='" + navActiveCurrent + "']").removeClass("selected-menu");
                $(".vertical-menu li:last-child a").addClass("selected-menu");
            }
        }
    })
})