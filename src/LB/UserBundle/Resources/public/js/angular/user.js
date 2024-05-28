'use strict';

angular.module('user',['Interpolation', 'ngImgCrop', 'ngResource', 'Components', 'Google', 'PathPrefix', 'mgcrea.ngStrap', 'fbImage'])
    .service('UserManager',['$resource', 'envPrefix', function($resource, envPrefix){
        return $resource(envPrefix + 'api/v1.0/:what/:where/:param1/:param2', {}, {
            unBlock: {method: 'GET', isArray: false, params: {what: 'users', param1: 'statuses'}}
        });
    }])
    .directive('lsUserRegisterManage',['$compile',
      '$http',
      '$rootScope',
      'template',
      '$timeout',
      'envPrefix',
      '$window',
      function($compile, $http, $rootScope, template, $timeout, envPrefix, $window){
          return {
              restrict: 'EA',
              scope: {
                  lsType: '@',
                  lsInitialRun: '='
              },
              link: function(scope, el){
                  var path1 = envPrefix + 'register-step-2',
                      path2 = envPrefix + 'register-step-3';

                  if(scope.lsInitialRun){
                      $timeout(function(){
                          scope.run(scope.lsType);
                      }, 1000);
                  }

                  el.bind('click', function(){
                      scope.run(template.currentType?template.currentType:scope.lsType);
                  });

                  scope.run = function(type){
                      $(".modal-loading").show();
                      if( type == 1){
                          if(!template.registerFormTemplate1){
                              $http.get(path1).success(function(data){
                                  template.registerFormTemplate1 = data;
                                  scope.runCallback(template.registerFormTemplate1, type);
                              })
                          } else {
                              scope.runCallback(template.registerFormTemplate1, type);
                          }
                      } else if(type == 2){
                          if(!template.registerFormTemplate2){
                              $http.get(path2).success(function(data){
                                  template.registerFormTemplate2 = data;
                                  scope.runCallback(template.registerFormTemplate2, type);
                              })
                          } else {
                              scope.runCallback(template.registerFormTemplate2, type);
                          }
                      } else {
                          $window.location.reload();
                      }

                  };

                  scope.runCallback = function(myTemplate, type){

                      // var sc = $rootScope.$new();
                      var tmp = $compile(myTemplate)(scope);
                      if(tmp.length > 1) {
                          tmp = tmp.slice(0,1);
                      }
                      scope.openModal(tmp);

                      $timeout(function(){
                          angular.element("#register" + type + "-form").ajaxForm({
                              beforeSubmit: function(){
                                  scope.$apply();
                              },
                              error: function(res){

                              },
                              success: function(res, text, header){
                                  switch (res){
                                      case 'skip':
                                          if(template.currentType == 2){
                                              $window.location.reload();
                                          } else {
                                              template.currentType = 2;
                                              $.modal.close();
                                          }
                                          break;
                                      case 'continue':
                                          template.currentType = 2;
                                          $.modal.close();
                                          scope.runCallback(template.registerFormTemplate2, template.currentType);
                                          break;
                                      case 'ok':
                                          $window.location.reload();
                                          break;
                                      default :
                                          $.modal.close();
                                          scope.runCallback(res, type);

                                  }
                              }
                          });
                      }, 1000);

                      if(!scope.$$phase){
                          scope.$apply()
                      }
                      $(".modal-loading").hide();
                  };

                  scope.openModal = function(tmp){

                      angular.element('body').append(tmp);
                      tmp.modal({
                          fadeDuration: 300,
                          showClose: false
                      });

                      tmp.on($.modal.CLOSE, function(){
                          tmp.remove();
                      })
                  }

              }
          }
      }])
    .controller('RegistrationController',['$scope', '$timeout', '$http', 'envPrefix', '$modal', function($scope, $timeout, $http, envPrefix, $modal){
        $scope.image = '';
        $scope.croppedImage = '';
        $scope.imageCropBlock = false;
        $scope.loading = false;
        $scope.fbImagesSelected = []; // data for images, that already was selected

        $scope.facebookId = null; // data for facebook user id
        $scope.accessToken = null; // data for facebook user token

        $scope.files = [];

        $scope.init = function (images) {

            // in init loading images and selected images is equal
            $scope.fbImagesSelected = $scope.fbImagesFiles = images;
            // show place for selected images
            $scope.fbImagesPlaceShow = Object.keys($scope.fbImagesFiles).length > 0;
        };




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
                },
                complete: function(res){
                    if(res.xhr.status !== 200){
                        return;
                    }

                    $scope.files.push(JSON.parse(res.xhr.responseText));
                    $scope.$apply();
                }
            });
        };

        $scope.initCroppedImage = function(base64){
            $timeout(function(){
                $scope.croppedImage = base64;
            }, 500);
        };

        $scope.deleteUploadFile = function(url, id, ev){
            var el = angular.element(ev.target).parents('.register-images');
            $http['delete'](url).success(function(){
                var ind = -1;
                ind = $scope.files.indexOf(id);

                if(ind !== -1){
                    $scope.files.splice(ind, 1);
                    el.parent().remove();
                }
            })
        };

        var handleFileSelect = function(evt) {
            var file = evt.currentTarget.files[0];
            var reader = new FileReader();

            reader.onload = function (evt) {
                $scope.$apply(function($scope){
                    $scope.imageCropBlock = true;
                    $scope.image = evt.target.result;
                });
            };

            reader.readAsDataURL(file);
        };

        $scope.getFbImages = function (facebookId, accessToken) {
            $scope.loading = true;

            $scope.facebookId = facebookId;
            $scope.accessToken = accessToken;

            $scope.fbImageModal = $modal({
                scope: $scope,
                controller: 'FBAlbumController',
                templateUrl: '/bundles/lbuser/html/fb_album_action.html'
            });

        };

        $scope.$on('modal.hide',function(e, modal){

            // init fb selected images from modal
            $scope.fbImagesFiles = angular.copy(modal.selected);

            // init fb images from modal
            $scope.fbImagesSelected = angular.copy(modal.selected);
            $scope.loading = false;
            $scope.fbImagesPlaceShow = (modal.selected && modal.selected.length > 0);
        });

        $scope.selectImage = function (image) {

            var index = $scope.fbImagesSelected.indexOf(image);

            if(index === -1){
                $scope.fbImagesSelected.push(image);
            }else{
                $scope.fbImagesSelected.splice(index, 1);
                $scope.fbImagesFiles.splice(index, 1);
            }

            if($scope.fbImagesFiles.length == 0){
                $scope.fbImagesPlaceShow = false;
            }
        };

        angular.element(document.querySelector('#fileInput')).on('change', handleFileSelect);


    }])
    .controller('ProfileBlockController',['$scope',
        'UserStatuses',
        'UserManager',
        function($scope, UserStatuses, UserManager){

            $scope.unBlock = function(userId){
                if(!userId){
                    return;
                }

                UserManager.unBlock({where: userId, param2: UserStatuses['NATIVE']}, function(){
                    console.log('unblocked');
                    angular.element('div[data-user='+userId+']').remove();
                });
            }

        }]);