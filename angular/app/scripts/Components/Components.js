'use strict';

angular.module('Components',['ngAnimate', 'PathPrefix','angular-cache'])
    .constant('UserStatuses',{
        LIKE: 0,
        DENIED: 5,
        BLOCK: 6,
        NATIVE: 7,
        HIDE: 11,
        NEW_FAVORITE: 9,
        FAVORITE: 1,
        SPAM: 10,
        MAN: 4,
        WOMAN: 5,
        BISEXUAL: 6
    })
    .config(function(CacheFactoryProvider){
          angular.extend(CacheFactoryProvider.defaults, {
              maxAge: 24 * 60 * 60 * 1000, // Items added to this cache expire after 15 minutes.
              cacheFlushInterval: 60 * 60 * 1000, // This cache will clear itself every hour.
              deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
              storageMode: 'localStorage' // This cache will use `localStorage`.
          });
    })
    .run(['$http', 'envPrefix', 'template', 'CacheFactory', 'UserContext',
      function($http, envPrefix, template, CacheFactory, UserContext){
          var registerForm1 = envPrefix + "register-step-2",
              registerForm2 = envPrefix + "register-step-3",
              cacheVersion = 1;

          var templateCache = CacheFactory.get('luvbyrd_templates_v' + cacheVersion);

          if(!templateCache){
              templateCache = CacheFactory('luvbyrd_templates_v' + cacheVersion, {
                  maxAge: 3 * 24 * 60 * 60 * 1000 ,// 3 day,
                  deleteOnExpire: 'aggressive'
              });
          }

          if(UserContext.id){
              // var registerFormTemplate1 = templateCache.get('form1-template'),
              //     registerFormTemplate2 = templateCache.get('form2-template');

              // if (!registerFormTemplate1) {
                  $http.get(registerForm1).success(function(data){
                      template.registerFormTemplate1 = data;
                      templateCache.put('form1-template', data);
                  });
              // }else {
              //     template.registerFormTemplate1 = registerFormTemplate1;
              // }
              
              // if (!registerFormTemplate2) {
                  $http.get(registerForm2).success(function(data){
                      template.registerFormTemplate2 = data;
                      templateCache.put('form2-template', data);
                  });
              // }else {
              //     template.registerFormTemplate2 = registerFormTemplate2;
              // }
          }
      }])
    .directive('lsDatepicker',['$filter','$rootScope', function($filter, $rootScope){
        return {
            restrict: 'EA',
            scope: {
                activeDates: '='
            },
            link: function(scope, el){

                var dates = [];
                angular.forEach(scope.activeDates, function(v){
                    dates.push(v.date);
                });

                el.datepicker({
                    format: 'mm/dd/yyyy',
                    todayHighlight: true,
                    beforeShowDay: function(date){

                        var fDate = $filter('date')(date, 'yyyy-MM-dd');
                        var cls = '';

                        if(dates.indexOf(fDate) !== -1){
                            cls = 'active-group-date';
                        }

                        return {
                            enabled: true,
                            classes: cls,
                            tooltip: ''
                        };

                    }
                })
                .on('changeDate', function(d){
                    $rootScope.$broadcast('dateChanged', {
                        date: $filter('date')(d.date, 'yyyy-MM-dd'),
                        event: 'changeDate'
                    });
                })
                .on('changeMonth', function(d){
                    $rootScope.$broadcast('dateChanged', {
                        date: $filter('date')(d.date, 'yyyy-MM-dd'),
                        event: 'changeMonth'
                    });
                })
                .on('changeYear', function(d){
                    $rootScope.$broadcast('dateChanged', {
                        date: $filter('date')(d.date, 'yyyy-MM-dd'),
                        event: 'changeYear'
                    });
                });
            }
        }
    }])
    .directive('lsUiSlider',[function(){
        return {
            restrict: 'EA',
            scope: {
                sliderValue: '=',
                init: '='
            },
            link: function(scope, el){
                scope.slider = el.slider({
                    min: 18,
                    max: 99,
                    range: true,
                    value: scope.init,
                    handle: 'custom'
                });

                scope.sliderValue = angular.copy(scope.init);

                scope.$on('resetLsUiSlider',function(){
                    scope.slider.slider('setValue', [18, 99]);
                    scope.changeValue(18, 99);
                });

                scope.slider.on('change',function(event){

                    scope.changeValue(event.value.newValue[0], event.value.newValue[1]);
                    scope.sliderValue = event.value.newValue;
                    scope.$apply();
                });

                scope.changeValue = function(minVal, maxVal){
                    angular.element('.min-slider-handle.custom').text(minVal);
                    angular.element('.max-slider-handle.custom').text(maxVal);
                    scope.sliderValue = [minVal, maxVal];
                };

                if(scope.init){
                    scope.changeValue(scope.init[0], scope.init[1]);
                }
                else {
                    scope.changeValue(18, 99);
                }
            }
        }
    }])
    .directive('lsSlimScroll',[function(){
        return {
            restrict: 'A',
            scope: true,
            link: function(scope, el, $attr){
                scope.height = $attr.scrollHeight ? $attr.scrollHeight : '500px';

                el.slimScroll({
                    height: scope.height,
                    allowPageScroll: true,
                    scroll: 'bottom',
                    scrollBy: 0
                });

                scope.$on('slimScrollToBottom',function(event, value){
                    el.slimScroll({
                        scrollTo: value
                    })
                });

                el.slimScroll().bind('slimscroll',function(e, pos){
                    if(pos === 'top'){
                        if (scope.$root.$$phase != '$apply' && scope.$root.$$phase != '$digest') {
                            scope.$apply(function() {
                                scope.$eval($attr.scrollBindTopFunction);
                            });
                        }
                    }
                    if(pos === 'bottom'){
                        if (scope.$root.$$phase != '$apply' && scope.$root.$$phase != '$digest') {
                            scope.$apply(function () {
                                scope.$eval($attr.scrollBindBottomFunction);
                            });
                        }
                    }
                })
            }
        }
    }])
    .directive('addTextInTextArea', ['$rootScope', function($rootScope) {
        return {
            require: '^ngModel',
            link: function(scope, el, ngModel) {
                $rootScope.$on('addTextInTextArea', function(e, val) {

                    var element = el[0];

                    if (document.selection) {
                        element.focus();
                        var sel = document.selection.createRange();
                        sel.text = val;
                        element.focus();
                    } else if (element.selectionStart || element.selectionStart === 0) {
                        var startPos = element.selectionStart;
                        var endPos = element.selectionEnd;
                        var scrollTop = element.scrollTop;
                        element.value = element.value.substring(0, startPos) + val + element.value.substring(endPos, element.value.length);
                        element.focus();
                        element.selectionStart = startPos + val.length;
                        element.selectionEnd = startPos + val.length;
                        element.scrollTop = scrollTop;
                    } else {
                        element.value += val;
                        element.focus();
                    }

                    // Angular gave me 'undefined' or empty string, when I was using ngClick in steed of ngKeyUp
                    scope[ngModel.ngModel] = element.value;
                });
            }
        }
    }])
    .filter('replaceUrl', function () {
        var urlPattern = /(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/gi;
        return function (text, target) {
            return text.replace(urlPattern, '<a target="_blank" href="$&">$&</a>');
        };
    })
    .animation('.slide', function() {
        var NG_HIDE_CLASS = 'ng-hide';
        return {
            beforeAddClass: function(element, className, done) {
                if(className === NG_HIDE_CLASS) {
                    element.slideUp(done);
                }
            },
            removeClass: function(element, className, done) {
                if(className === NG_HIDE_CLASS) {
                    element.hide().slideDown(done);
                }
            }
        }
    });