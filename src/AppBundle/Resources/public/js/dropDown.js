$(document).click(function (e) {

    var targetClass = $(e.target).attr("class");
    var classes = targetClass.split(" ");
    var activeDropDown = $(".checkbox-dropdown.is-active");
    if(activeDropDown.length > 0 && activeDropDown[0].innerText != e.target.innerText){
        activeDropDown.removeClass("is-active");
    }
    if(classes.indexOf("checkbox-dropdown") !== -1 ){
        $(e.target).toggleClass("is-active");
    }

});

$(".checkbox-dropdown ul").click(function(e) {
    e.stopPropagation();
});

