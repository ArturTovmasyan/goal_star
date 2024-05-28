'use strict';

angular.module('main', ['ng.deviceDetector', 'mgcrea.ngStrap.tooltip', 'mgcrea.ngStrap.modal', 'ngAnimate'])
    .controller('MainController', ['$scope', 'deviceDetector', '$modal', '$timeout', function($scope, deviceDetector, $modal, $timeout ) {

        $scope.disableSignIn = false;

        $scope.keyUp = function(){
            $scope.email = event.target.value;
            $scope.disableButton();
        };


        $scope.signUpChange = function(){
            $scope.disableButton();
        };

        $scope.disableButton = function(){
            $scope.disableSignIn =

                ($scope.username && $scope.username.length > 0) ||
                ($scope.password && $scope.password.length > 0) ||
                ($scope.confirmPassword && $scope.confirmPassword.length > 0) ||
                ($scope.email && $scope.email.length > 0) ||
                $scope.agree;
        };

        $scope.deviceDetector = deviceDetector;

        if (deviceDetector.raw.os.android || deviceDetector.raw.os.ios) {
//            if (!$.cookie('mobile_modal')) {
                $scope.modal = $modal({
                    scope: $scope,
                    templateUrl: '/bundles/app/htmls/mobileModal.html'
                });

//                $.cookie('mobile_modal', true, {expires: 1}); // set this for creating in cookie
//            }
        }

    }]);