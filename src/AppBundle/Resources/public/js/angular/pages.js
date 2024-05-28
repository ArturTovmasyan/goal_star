'use strict';

angular.module('pages', ['Interpolation',
  'socket',
  'Components',
  'ngAnimate',
  'PathPrefix',
  'user'])
  .constant('relationsConstants',{
    LIKE         : 0,
    FAVORITE     : 1,
    NEW_VISITOR  : 2,
    MESSAGE      : 3,
    FRIEND       : 4,
    DENIED       : 5,
    BLOCK        : 6,
    NATIVE       : 7,
    VISITOR      : 8,
    NEW_FAVORITE : 9,
    SPAM         : 10,
    HIDE         : 11,
    LIKED_BY_ME  :12,
    FAVORITE_BY_ME :13
  })
  .controller('userPagesController',['$scope',
    '$timeout',
    '$window',
    '$location',
    '$http',
    'envPrefix',
    'relationsConstants',
    function($scope, $timeout, $window, $location, $http, envPrefix, relationsConstants){
      $scope.usersLoading = true;
      $scope.users = [];
      $scope.paginationArray = [];
      $scope.paginationEps = 4;
      $scope.start = 0;
      $scope.pagePath = '';
      $scope.usersCount = 0;
      $scope.page = 1;
      $scope.count = 12;
      $scope.relationsConstants = relationsConstants;
      var path = envPrefix + 'api/v1.0/users/{path}/user/by',
          countPath = envPrefix + 'api/v1.0/userrelations/{status}/count',
          busy = false;

      $timeout(function () {
        countPath = countPath.replace('{status}', $scope.pagePath);
        $http.get(countPath)
          .success(function(data){
              $scope.usersCount = data;
              $scope.paginationArray = $scope.newPagination(data);
          });
      }, 1000);

      $scope.openPage = function () {
        $scope.start = ($scope.page -1)* $scope.count;
        
        var url = path + '?start='+$scope.start + "&count=" + $scope.count;

        $http.get(url)
          .success(function(data){
            $scope.users = data;
            $scope.usersLoading = false;
            if(!data.length){
              $scope.paginationArray = [];
              $scope.page = 1;
              $scope.start = 0;
            }

            $scope.pagination_left_dots = ($scope.page > $scope.paginationEps);

            $scope.pagination_right_dots = ($scope.page < ($scope.paginationArray.length - $scope.paginationEps));

            $window.scrollTo(0, 0);
          });
      };

      $scope.$on('$locationChangeSuccess', function() {
        var page = parseInt($location.hash());

        if($scope.pagePath.indexOf('users_') == 0){
          $scope.pagePath = $scope.pagePath.slice(6);
        }
        path = path.replace('{path}', $scope.pagePath);

        if($location.hash() === '' && !busy){
          busy = true;
          $scope.page = 1;
          $scope.openPage();
          $timeout(function(){
            busy = false;
          },2000);
        }
        else if(angular.isNumber(page) &&
          !isNaN(page) &&
          page > 0){

          $scope.page = page;
          $scope.openPage();
        }
      });

      $scope.getFullName = function (user) {
          return (user.first_name.length > 15)?(user.first_name.substr(0,13) + '...'):(user.first_name.length + user.last_name.length > 16)?(user.first_name + ' ' + user.last_name.substr(0,13 - user.first_name.length) + '...'): user.first_name + ' ' + user.last_name;
      };

      $scope.newPagination = function(count){
        return new Array(Math.ceil(count / $scope.count));
      };

      $scope.paginationSurrounding = function(page){
        return (Math.abs(page - $scope.page) < $scope.paginationEps && page < $scope.paginationArray.length);
      };

      $scope.pagination = function(page){
        if(!page){
          return;
        }
        $scope.users = [];
        $scope.usersLoading = true;
        $location.hash(page);

        $scope.page = page;
        $window.scrollTo(0, 0);
      };

    }]);
