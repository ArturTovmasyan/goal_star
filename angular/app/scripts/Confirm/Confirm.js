'use strict';

angular.module('Confirm',['mgcrea.ngStrap.modal', 'ngAnimate'])
    .directive('lsConfirm',['$modal', function($modal){
        return {
            restrict: 'EA',
            scope: {
                modalTitle: '@',
                confirm: '&'
            },
            link: function(scope, el){

                el.bind('click',function(){
                    $modal({
                        scope: scope,
                        title: scope.modalTitle ? scope.modalTitle : 'Confirm',
                        templateUrl: '/app/scripts/Confirm/confirm.html'
                    });
                })
            }
        }
    }]);