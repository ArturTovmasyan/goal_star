'use strict';

angular.module('userAdmin',['Google', 'mgcrea.ngStrap', 'PathPrefix'])
    .controller('UserAdminController',['$scope', '$modal', function($scope, $modal){
        
        $scope.sendMessage = function () {

            var userIds = [];
            var checked = angular.element('input:checked');

            $('input:checked').each(function() {
                var id = $(this).val();

                if(id > 0){
                    userIds.push($(this).val());
                }
            });


            $scope.myModal = $modal({
                scope: $scope,
                controller: 'UserMessageController',
                templateUrl: '/bundles/lbuser/html/message.html',
                resolve: {
                    ids: function () {
                        return userIds;
                    }
                }
            });

        }
    }])
    .controller('UserMessageController',['$scope', '$http', 'envPrefix', 'ids', function($scope, $http, envPrefix, ids){

        $scope.loading = false;
        $scope.message = null;
        $scope.ids = ids.length > 0 ? ids : null;
        $scope.errorText = $scope.ids ? null : 'No items were selected';

        $scope.submit = function () {
            $scope.loading = true;
            var data = {userIds : ids , msg: $scope.message};

            $http({
                data: data,
                method: 'PUT',
                url: envPrefix + 'api/v1.0/user/message/from/admin'
            }).then(function successCallback(response) {
                $scope.images = response.data;
                $scope.loading = false;

                $scope.myModal.$promise.then($scope.myModal.hide());
                window.location.reload();


            }, function errorCallback() {
                $scope.loading = false;
                $scope.errorText = 'Oops! Something went wrong.';
            });

        }

    }])
;