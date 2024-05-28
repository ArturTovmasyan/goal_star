'use strict';

angular.module('Ads', ['Google'])
    .controller('AdsGeoController', ['$scope', '$timeout', function($scope, $timeout){
        $scope.markersStorage = '';
        $scope.markersClickable = true;

        $scope.$watch('place', function(p){

            if(p && p.address){
              if($scope.isAddManager){
                $scope.markersStorage = p.address;
                return
              }
              if($scope.isEventManager && p.location){
                $scope.markersStorage = [{}];
                $scope.markersStorage[0].location = {
                  latitude: p.location.latitude,
                  longitude: p.location.longitude
                };

                $scope.markersStorage[0].address = p.address;
              }

            }

            if(!$scope.mapScope || !p || !p.location){
                return;
            }

            var m = $scope.mapScope.addMarker(p.location, $scope.mapScope.map);

            m.latitude = m.getPosition().lat();
            m.longitude = m.getPosition().lng();
            $scope.mapScope.map.setCenter(m.getPosition());

            if(!$scope.mapScope.view){
                $scope.mapScope.addListenersOnMarker(m);
            }

          if(!$scope.isEventManager){
            $scope.mapScope.markers.push(m);
          }

        }, false);

        $scope.initEventLocation = function (lat, lng, city) {
          $timeout(function(){
            $scope.markersStorage = [{}];
            $scope.markersStorage[0].location = {
              latitude: lat,
              longitude: lng
            };

            $scope.markersStorage[0].address = city;
          },1000);
        };
      
        $timeout(function(){
            $scope.mapScope = angular.element('.map').isolateScope();
        },500);

    }]);