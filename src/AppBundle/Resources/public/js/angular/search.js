'use strict';

angular.module('search', [])
    .run(function(){
        angular.element(document).ready(function(){
            var offCanvas = angular.element(".navbar-offcanvas");
            var toggle = angular.element("button.navbar-toggle");

            offCanvas.on('shown.bs.offcanvas', function(){
                toggle.addClass('sidebar-menu');

                // setTimeout(function() {
                    // toggle.children('i:first-child').hide();
                    // toggle.children('i:last-child').show();
                // }, 200);

                //button.addClass('toggle-right');
                //button.removeClass('toggle-left');
            });

            offCanvas.on('hidden.bs.offcanvas', function(){
                toggle.removeClass('sidebar-menu');

                // setTimeout(function(){
                    // toggle.children('i:last-child').hide();
                    // toggle.children('i:first-child').show();
                // }, 200);

                //button.removeClass('toggle-right');
                //button.addClass('toggle-left');
            });
        })
    });