'use strict';

angular.module('fbImage',['mgcrea.ngStrap.popover', 'Interpolation', 'PathPrefix'])
    .controller('FBAlbumController',['$scope', '$http', 'envPrefix', function($scope, $http, envPrefix){

        $scope.albums = [];
        $scope.images = [];
        $scope.loading = true;
        $scope.selected = [];
        $scope.existImages = [];

        $scope.modalSelectImage = function (image) {
            var index = $scope.selected ? $scope.selected.indexOf(image) : -1;

            if(index === -1){
                $scope.selected.push(image);
            }else{
                $scope.selected.splice(index, 1);
            }
        };

        $scope.isSelected = function (image) {
            var imageName = image.split('/');
            imageName = imageName[imageName.length-1];
            imageName = imageName.split(".");
            imageName = imageName[0];

            return $scope.existImages.indexOf(imageName) !== -1;

        };

        $scope.isAlreadySelected = function (image) {
            var index = $scope.selected ? $scope.selected.indexOf(image) : -1;

            return  index !== -1;
        };

        $scope.submit = function () {

            $scope.loading = true;
            $scope.fbImageModal.selected = angular.copy($scope.selected);
            $scope.fbImageModal.$promise.then($scope.fbImageModal.hide());
        };


        $scope.init = function () {
            $scope.selected = $scope.fbImagesSelected ? angular.copy($scope.fbImagesSelected) : [];

            $http({
                method: 'POST',
                data : {facebookId: $scope.facebookId, accessToken: $scope.accessToken},
                url: envPrefix + 'api/v1.0/files/froms/fbs',
            }).then(function successCallback(response) {

                $scope.albums = response.data.albums;
                $scope.existImages = response.data.selected ? response.data.selected  : [];
                $scope.loading = false;
            }, function errorCallback(response) {
                $scope.loading = false;
            });
        };
        
        
        $scope.changeContent = function (album) {
            $scope.images = $scope.albums[album].images;
        };

        $scope.back = function () {
            $scope.images = [];
        };

    }])