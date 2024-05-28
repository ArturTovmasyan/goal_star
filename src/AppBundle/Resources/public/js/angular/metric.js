'use strict';

angular.module('MetricAdmin', ['Interpolation'])
    .controller('RelationController', ['$scope', function($scope){


        angular.element('.group-by-select').on('change', function(){
            $scope.changeGroupBy();
        });

        $scope.changeGroupBy = function () {
            var value = angular.element('.group-by-select:not(.select2-container)').val();
            var groupBy = parseInt(value);

            if(groupBy == 3){
                $scope.showFromTo = false;
                $scope.disableFilterButton = false;
                $scope.required = false;
                $scope.showYearForMonthly = false;
            }
            else if(groupBy == 2){
                $scope.showFromTo = false;
                $scope.disableFilterButton = false;
                $scope.required = false;
                $scope.showYearForMonthly = true;
            }
            else {
                $scope.showFromTo = true;
                $scope.disableFilterButton = false;
                $scope.required = true;
                $scope.showYearForMonthly = false;
            }

            if ($scope.$root.$$phase != '$apply' && $scope.$root.$$phase != '$digest') {
                $scope.$apply();
            }

        };

        $scope.changeGroupBy();
        
    }])
  .controller('topUsersController', ['$scope', function($scope){


    angular.element('.top-by-select').on('change', function(){
      $scope.changeTable();
    });

    angular.element('.gender-by-select').on('change', function(){
      $scope.changeChart();
    });

    $scope.changeTable = function () {
      $scope.type = angular.element('.top-by-select:not(.select2-container)').val();
      if ($scope.$root.$$phase != '$apply' && $scope.$root.$$phase != '$digest') {
        $scope.$apply();
      }
    };

    $scope.changeChart = function () {
      $scope.gender = angular.element('.gender-by-select:not(.select2-container)').val();
      if ($scope.$root.$$phase != '$apply' && $scope.$root.$$phase != '$digest') {
        $scope.$apply();
      }
    };

    $scope.changeTable();
    $scope.changeChart();

  }]);