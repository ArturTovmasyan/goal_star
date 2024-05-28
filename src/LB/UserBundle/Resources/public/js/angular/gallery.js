'use strict';

angular.module('gallery',['Confirm', 'socket', 'mgcrea.ngStrap.popover', 'Interpolation', 'PathPrefix', 'fbImage', 'imageEdit', 'user'])
    .controller('GalleryController',['$scope', '$http', 'envPrefix', '$modal', '$rootScope',
        function($scope, $http, envPrefix, $modal, $rootScope){

        $scope.files = [];
        $scope.loading = false;

        $scope.initDropzone = function(url){
            if(!url){
                return;
            }

            Dropzone.options.registerDropzone = false;
            $scope.dropzone = new Dropzone('#registerDropzone', {
                url: url,
                addRemoveLinks: true,
                uploadMultiple: false,
                removedfile: function(d){
                    angular.element(d.previewElement).remove();
                    var id = JSON.parse(d.xhr.responseText);
                    var index = $scope.files.indexOf(id);
                    if(index !== -1){
                        $scope.files.splice(index, 1);
                    }

                    $scope.$apply();
                    $scope.refreshGallery();
                },
                complete: function(res){
                    if(res.xhr.status !== 200){
                        return;
                    }

                    $scope.files.push(JSON.parse(res.xhr.responseText));
                    $scope.$apply();
                }
            });

            $scope.dropzone.on('queuecomplete', function(){
                $scope.refreshGallery();
            });
        };

        $scope.refreshGallery = function(){
            var bool = true;
            angular.forEach($scope.dropzone.files, function(v){
                if(v.status !== "success"){
                    bool = false;
                }
            });

            if(bool){
                window.location.reload();
            }
        };

        $scope.deleteImage = function(url){
            $scope.loading = true;
            return window.location = url;
        };

        $scope.setProfilePicture = function(url){
            return window.location = url;
        };

        $scope.deg = 0;

        $rootScope.$on('showLoading', function () {
            $scope.loading = true;
        });
            
        $rootScope.$on('hideLoading', function () {
            $scope.loading = false;
        });

        $scope.rotate = function(id){
            $scope.isRotating = true;

            $scope.deg =  $scope.deg + 90;
            if($scope.deg == 360){
                $scope.deg = 0;
            }

            $("#"+id).css({
                "-webkit-transform": "rotate("+  $scope.deg  +"deg)",
                "-moz-transform":"rotate("+  $scope.deg  +"deg)",
                "transform": "rotate("+  $scope.deg  +"deg)" /* For modern browsers(CSS3)  */
            });
        };

        $scope.physicallyRotate = function(id)
        {
            if(!$scope.isRotating)return;
            $scope.loading = true;
            var data = {id:id, deg: $scope.deg };
            $http({
                method: 'POST',
                url: envPrefix + 'api/v1.0/files/rotates',
                data: data
            }).then(function successCallback(response) {
                $scope.loading = false;
            }, function errorCallback(response) {
                $scope.loading = false;
            });
            $scope.isRotating = false;
        };
        
        $scope.facebookImage = function () {

            $scope.fbImageModal = $modal({
                scope: $scope,
                controller: 'FBAlbumController',
                templateUrl: '/bundles/lbuser/html/fb_album_action.html'
            });
        };

        $scope.$on('modal.hide',function(e, modal){

            // init fb selected images from modal
            $scope.fbImagesFiles = angular.copy(modal.selected);

            // check selected images
            if($scope.fbImagesFiles && $scope.fbImagesFiles.length > 0){
                $scope.loading = true;

                // check data
                var data = {fbImages : $scope.fbImagesFiles };

                $http({
                    data: data,
                    method: 'POST',
                    url: envPrefix + 'api/v1.0/files/fbs/images',
                }).then(function successCallback(response) {
                    $scope.images = response.data;
                    window.location.reload();
                    // $scope.loading = false;
                }, function errorCallback(response) {
                    $scope.loading = false;
                });
            }


        });

    }])
    .directive('actions',['$popover', function($popover){
        return {
            restrict: 'EA',
            scope: {
                deleteAction: '&',
                setProfileAction: '&',
                setRotateAction: '&',
                imagePath: '@',
                imageId: '@',
                physicallyRotateAction: '&',
                fileLength: '='
            },
            link: function(scope, el){

                $popover(el, {
                    scope: scope,
                    autoClose: true,
                    placement: 'bottom',
                    templateUrl: '/bundles/lbuser/html/gallery_actions.html',
                    trigger: 'click'
                });

                scope.$on('tooltip.hide', function (e) {
                    scope.physicallyRotateAction();
                });
                ///
            }
        }
    }]);