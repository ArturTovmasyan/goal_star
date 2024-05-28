'use strict';

angular.module('event', ['ngResource',
    'Interpolation',
    'mgcrea.ngStrap.modal',
    'ngAnimate',
    'Components',
    'Google',
    'PathPrefix',
    'angular-cache'])
    .config(['CacheFactoryProvider', function(CacheFactoryProvider) {
      angular.extend(CacheFactoryProvider.defaults, {
          // maxAge: 24 * 60 * 60 * 1000, // Items added to this cache expire after 15 minutes.
          // cacheFlushInterval: 60 * 60 * 1000, // This cache will clear itself every hour.
          // deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
          storageMode: 'localStorage' // This cache will use `localStorage`.
      });
    }])
    .service('EventManager',['$resource', 'envPrefix', function($resource, envPrefix){
      return $resource(envPrefix + 'api/v1.0/:what/:where/:param1/:param2/:param3',{},{
          getEvents: {method: 'GET', isArray: true, params: {what: 'events', param1: 'freshes'}},
          getEventUsers: {method: 'GET', isArray: true, params: {what: 'events', param1: 'users'}},
          connectEvents: {method: 'PUT', isArray: false, params: {what: 'connects', param1: 'event'}}
      });
    }])
    .directive('lsUsers',['$modal', 'EventManager', function($modal, EventManager){
      return {
        restrict: 'EA',
        scope: {
          eventId: '@'
        },
        link: function(scope, el){
          
          scope.nextUsers = function () {console.log('next User');
            if(scope.busy || scope.reserve.length == 0)return;
            scope.users = scope.users.concat(scope.reserve);
            scope.setReserve();
          };
          
          scope.setReserve = function () {
            if(scope.noItem)return;
            scope.busy = true;
            EventManager.getEventUsers({where: scope.eventId, param2: scope.start, param3: scope.count}, function (data) {
              scope.busy = false;
              scope.start += scope.count;
              scope.noItem = data.length == 0;
              scope.reserve = data;
            });
          };
          
          
          el.bind('click',function(){
            scope.busy = true;
            scope.noItem = false;
            scope.reserve = [];
            scope.start = 0;
            scope.count = 10;

            $(".modal-loading").show();
            EventManager.getEventUsers({where: scope.eventId, param2: scope.start, param3: scope.count}, function (data) {
              $(".modal-loading").hide();
              scope.start += scope.count;
              scope.users = data;
              scope.setReserve();
              $modal({
                scope: scope,
                templateUrl: '/bundles/app/htmls/usersModal.html'
              });

              scope.$on('modal.hide',function(){
                scope.noItem = true;
              });
            }, function () {
              // alert('error');
              $(".modal-loading").hide();
            });
          })
        }
      }
    }])
    .controller('EventsController', ['$scope', 'EventManager', 'CacheFactory', function($scope, EventManager, CacheFactory){
        $scope.loading = true;
        $scope.isEnd = false;
        $scope.busy = true;
        $scope.reserve = [];
        $scope.events = [];
        $scope.start = 0;
        $scope.count = 8;
        $scope.singleLength = 0;

        EventManager.getEvents({where: $scope.start, param2: $scope.count}, function (data) {
          $scope.loading = false;
          $scope.busy = false;
          $scope.isEnd = (data.length == 0);
          $scope.singleLength = data.length;
          $scope.events = $scope.gerMatrix(data);
          $scope.start = $scope.start + $scope.count;
          $scope.setReserve()
        });

        $scope.setReserve = function () {
          $scope.busy = true;
          EventManager.getEvents({where: $scope.start, param2: $scope.count}, function (data) {
            $scope.busy = false;
            $scope.isEnd = (data.length == 0);
            $scope.singleLength = data.length;
            $scope.reserve = $scope.gerMatrix(data);
            $scope.start = $scope.start + $scope.count;
          });
        };

        $scope.gerMatrix = function (data) {
          var matrix = [[],[],[],[],[],[]];
          // var arr = [];
          if(!$scope.isEnd){
            switch ($scope.singleLength){
              case 1:
                data[0].row = 1;
                matrix[0].push(data[0]);
                break;
              case 2:
                data[0].row = 1;
                data[1].row = 1;
                matrix[0].push(data[0]);
                // matrix.push(arr);
                matrix[1].push(data[1]);
                break;
              case 3:
                data[0].row = 1;
                data[1].row = 1;
                data[2].row = 1;
                matrix[0].push(data[0]);
                // matrix.push(arr);
                matrix[1].push(data[1]);
                // matrix.push(arr);
                matrix[2].push(data[2]);
                break;
              case 4:
                data[0].row = 1;
                data[1].row = 2;
                data[2].row = 1;
                data[3].row = 1;
                matrix[0].push(data[0]);
                matrix[0].push(data[3]);
                // matrix.push(arr);
                matrix[1].push(data[1]);
                // matrix.push(arr);
                matrix[2].push(data[2]);
                break;
              case 5:
                matrix = standatrMatrix(data, matrix);
                break;
              case 6:
                data[5].row = 1;
                matrix = standatrMatrix(data, matrix);
                matrix[3].push(data[5]);
                break;
              case 7:
                data[5].row = 1;
                data[6].row = 1;
                matrix = standatrMatrix(data, matrix);
                matrix[3].push(data[5]);
                matrix[4].push(data[6]);
                break;
              case 8:
                data[5].row = 1;
                data[6].row = 1;
                data[7].row = 1;
                matrix = standatrMatrix(data, matrix);
                matrix[3].push(data[5]);
                matrix[4].push(data[6]);
                matrix[5].push(data[7]);
                break;
            }
          }
          return matrix;
        };

      function standatrMatrix(data, matrix) {
        data[0].row = 1;
        data[1].row = 2;
        data[2].row = 1;
        data[3].row = 1;
        data[4].row = 1;
        matrix[0].push(data[0]);
        matrix[0].push(data[3]);
        // matrix.push(arr);
        matrix[1].push(data[1]);
        // matrix.push(arr);
        matrix[2].push(data[2]);
        matrix[2].push(data[4]);

        return matrix;
      }
        $scope.getReserve = function(){
          $scope.events = $scope.events.concat($scope.reserve);
          $scope.setReserve();
        }
    }])
    .controller('EventController', ['$scope', 'EventManager', 'CacheFactory', function($scope, EventManager, CacheFactory){

      var loginCache = CacheFactory.get('luvbyrd');
      $scope.isDonate = false;
      $scope.start = 0;
      $scope.count = 30;
      $scope.busy = true;
      $scope.isEnd = false;
      $scope.isMobile = window.innerWidth < 767;
      $scope.isTouchDevice = window.innerWidth < 992;
      $scope.slideCount = $scope.isTouchDevice?($scope.isMobile?1:2):3;
      $scope.events = [];

      if(!loginCache){
        loginCache = CacheFactory('luvbyrd', {});
      }
      
      $scope.connect = function (id, e) {
        if(!$scope.logged){
          loginCache.put('event-id', id);
          loginCache.put('event-id-free', {id: id});
          window.location.href = '/login';
        } else {
          EventManager.connectEvents({where: id}, {}, function () {
            $scope.connected = true;
          });
        }
      };

      $scope.donate = function (id, e) {
        var donate = $("#donate").val();
        if(!donate || !(donate > 0))return;
        var name = 'Donate';
        var amount = donate * 100;
        var currency = 'usd';
        var stripeId = id;
        var email = $(e.target).data('email');
        if(!$scope.logged){
          loginCache.put('event-id', id);
          loginCache.put('event-id-donate', {
            name: name,
            amount:amount,
            currency:currency,
            stripeId:stripeId,
            email:email});
          window.location.href = '/login';
        } else {
          generateStripe(null , name, amount, currency, stripeId, email)
        }
      };
      
      $scope.buy = function (id,e) {
        var name = $(e.target).data('name');
        var amount = $(e.target).data('amount');
        var currency = $(e.target).data('currency');
        var stripeId = $(e.target).data('stripe-id');
        var email = $(e.target).data('email');
        
        if(!$scope.logged){
          loginCache.put('event-id', id);
          loginCache.put('event-id-buy', {
            name: name,
            amount:amount,
            currency:currency,
            stripeId:stripeId,
            email:email});
          window.location.href = '/login';
        } else {
          generateStripe(null , name, amount, currency, stripeId, email)
        }
      };

      setTimeout(function () {

        var eventId = loginCache.get('event-id');
        var cacheFreeData = loginCache.get('event-id-free');
        var cacheDonateData = loginCache.get('event-id-donate');
        var cacheBuyData = loginCache.get('event-id-buy');
        if(cacheFreeData && eventId == $scope.eventId){
          loginCache.remove('event-id-free');
          loginCache.remove('event-id');
          $(".modal-loading").show();
          EventManager.connectEvents({where: cacheFreeData.id}, {}, function () {
            $scope.connected = true;
            $(".modal-loading").hide();
          });
        } else if(cacheDonateData && eventId == $scope.eventId){
          loginCache.remove('event-id-donate');
          loginCache.remove('event-id');
          $("#donate").val(cacheDonateData.amount/100);
          $scope.isDonate = true;
          generateStripe(null , cacheDonateData.name, cacheDonateData.amount, cacheDonateData.currency, cacheDonateData.stripeId, cacheDonateData.email);
        } else if(cacheBuyData && eventId == $scope.eventId){
          loginCache.remove('event-id-buy');
          loginCache.remove('event-id');
          generateStripe(null , cacheBuyData.name, cacheBuyData.amount, cacheBuyData.currency, cacheBuyData.stripeId, cacheBuyData.email);
        }

        EventManager.getEvents({where: $scope.start, param2: $scope.count, id: $scope.eventId}, function (data) {
          $scope.busy = false;
          $scope.isEnd = (data.length == 0);
          $scope.events = data;
          $scope.initSlide();
        });
      },100);


      $scope.initSlide = function () {
        setTimeout(function () {
          var see_other_swiper = new Swiper('#seeOther', {
            observer: true,
            autoHeight: true,
            slidesPerView: $scope.slideCount,
            autoplay: 3000,
            loop: false,
            nextButton: '.swiper-button-search-next',
            prevButton: '.swiper-button-search-prev',
            spaceBetween: 20
          });
        }, 200)
      };

      $scope.scrollTo = function (elemId){
        $("html, body").animate({ scrollTop: $(elemId).offset().top - 200}, "slow");
      }
    }]);