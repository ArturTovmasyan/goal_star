'use strict';

angular.module('member', ['ngResource',
    'Interpolation',
    'socket',
    'Components',
    'ngAnimate',
    'Google',
    'ngRoute',
    'search',
    'angular-cache',
    'mgcrea.ngStrap.tooltip',
    'PathPrefix',
    'mgcrea.ngStrap.modal',
    'user'])
    .config(['$sceDelegateProvider', '$routeProvider', 'CacheFactoryProvider', function($sceDelegateProvider, $routeProvider, CacheFactoryProvider) {
        $routeProvider.when('/', {
            controller: 'ListController'
        });
        angular.extend(CacheFactoryProvider.defaults, {
            // maxAge: 24 * 60 * 60 * 1000, // Items added to this cache expire after 15 minutes.
            // cacheFlushInterval: 60 * 60 * 1000, // This cache will clear itself every hour.
            // deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
            storageMode: 'localStorage' // This cache will use `localStorage`.
        });
    }])
    .service('MemberManager',['$resource', 'envPrefix', function($resource, envPrefix){
        return $resource(envPrefix + 'api/v1.0/:what/:where/:param1/:param2',{},{
            report: {method: 'PUT', isArray: false, params: {what: 'reports'}},
            userStatus: {method: 'GET', isArray: false, params: {what: 'users', param1: 'statuses'}},
            search: {method: "POST", isArray: true, params: {what: 'users', where: 'searches'}},
            favorite: {method: 'PUT', isArray: false, params: {what: 'userrelations', param1: 'favorites'}},
            hide: {method: 'GET', isArray: false, params: {what: 'userrelations', param1: 'hides'}}
        });
    }])
    .controller('SingleController',['$scope',
        'socketValue',
        'MemberManager',
        '$modal',
        '$timeout',
        'UserStatuses',
        'CacheFactory',
        '$http',
        function($scope, socketValue, MemberManager, $modal, $timeout, UserStatuses, CacheFactory, $http){

            var searchDataCache = CacheFactory.get('luvbyrd'),
            path = '/api/v1.0/user/swiper';
            $scope.showUsers = false;
            $scope.isTouchDevice = (window.innerWidth < 992);

            $scope.toggleArrows = function (id, arrowSelector) {
                $(id).on("hide.bs.collapse", function(){
                    $(arrowSelector).removeClass('fa-chevron-up').addClass('fa-chevron-down');
                });
                $(id).on("show.bs.collapse", function(){
                    $(arrowSelector).removeClass('fa-chevron-down').addClass('fa-chevron-up');
                });
            };
            $scope.getSrc = function(path){
                return (path.indexOf('type=large') != -1?(path + '&width=82&height=82'):path);
            };

            if(!searchDataCache){
                searchDataCache = CacheFactory('luvbyrd', {
                    // maxAge: 3 * 24 * 60 * 60 * 1000 ,// 3 day,
                    // deleteOnExpire: 'aggressive'
                });
            }

            var searchUsers = searchDataCache.get('search-users');

            if (!searchUsers) {

                $http.get(path)
                  .success(function(data){
                      $scope.searchUsers = data;
                      searchDataCache.put('search-users', data);
                  });
            }else {
                $scope.searchUsers = searchUsers;
            }

            $timeout(function(){
                $scope.showUsers = true;
                $( "#searching-user" ).fadeIn( "slow" );
                if($scope.searchUsers.length){
                    var search_swiper = new Swiper('#searching-users > .swiper-container', {
                        observer: true,
                        autoHeight: true,
                        slidesPerView: '6',
                        autoplay: 3000,
                        loop: true,
                        nextButton: '.swiper-button-search-next',
                        prevButton: '.swiper-button-search-prev',
                        spaceBetween: 10
                    });
                }
                var galleryTop = new Swiper('.gallery-top', {
                    observer: true,
                    nextButton: '.swiper-button-top-next',
                    prevButton: '.swiper-button-top-prev',
                    autoplay: 7000,
                    slidesPerView: 1,
                    keyboardControl: true,
                    //loop: true,
                    spaceBetween: 10
                });
                var galleryThumbs = new Swiper('.gallery-thumbs', {
                    spaceBetween: 10,
                    centeredSlides: true,
                    slidesPerView: ($scope.isTouchDevice?'auto':6),
                    touchRatio: 0.2,
                    //loop: true,
                    slideToClickedSlide: true
                });
                galleryThumbs.params.control = galleryTop;
                galleryTop.params.control = galleryThumbs;

                $scope.toggleArrows('#collapseInterests', '#Interests i');
                $scope.toggleArrows('#collapseThree', '#headingThree i');

                if(window.innerWidth < 768){
                    $('.gallery-thumbs .swiper-slide').css("margin-right", '15px');
                } else if($scope.isTouchDevice && window.innerWidth >= 768){
                    $('.gallery-thumbs .swiper-slide').css("margin-right", '-25px');
                }
            }, 1000);

            $scope.UserStatuses = UserStatuses;

            $scope.openReport = function(id){
                $scope.reportUserId = id;
                $scope.modal = $modal({
                    scope: $scope,
                    templateUrl: '/bundles/app/htmls/report.html'
                });
            };

            $scope.report = function(id, message){
                MemberManager.report({where: id},{id: id, message: message},function(){
                    $scope.modal.hide();
                });
            };

            $scope.likeUnlike = function(id, url){
                if(socketValue.socket === null){
                    return console.error('There is not the Socket pointer');
                }

                var status = -1;
                if($scope.myStatus === UserStatuses['LIKE']){
                    status = UserStatuses['NATIVE'];
                }
                else {
                    status = UserStatuses['LIKE'];
                }

                if(status !== -1 && id){
                    socketValue.socket.emit('status', {userId: id, status: status});
                    $scope.myStatus = status;
                    if(url){
                        $timeout(function(){
                            window.location = url;
                        }, 500);
                    }
                }
            };

            $scope.hideUnhide = function(id){
                if(id){
                    var status = -1;
                    if($scope.myStatus === UserStatuses['HIDE']){
                        status = UserStatuses['NATIVE'];
                    }
                    else {
                        status = UserStatuses['HIDE'];
                    }

                    MemberManager.userStatus({where: id, param2: status}, function(){
                        $scope.myStatus = status;
                    });
                }
            };

            $scope.block = function(id){
                if(id){
                    var status = -1;
                    if($scope.myStatus === UserStatuses['BLOCK']){
                        status = UserStatuses['NATIVE'];
                    }
                    else {
                        status = UserStatuses['BLOCK'];
                    }

                    MemberManager.userStatus({where: id, param2: status}, function(){
                        $scope.myStatus = status;
                    });
                }
            };

            $scope.favorite = function(id){
                if(id){
                    var status = -1;
                    if($scope.myFavStatus === UserStatuses['NEW_FAVORITE']){
                        status = UserStatuses['NATIVE'];
                    }
                    else {
                        status = UserStatuses['NEW_FAVORITE'];
                    }

                    MemberManager.favorite({where: id, param2: status},{},function(){
                        $scope.myFavStatus = status;
                    });
                }
            };

            $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
        }])
    .controller('ListController',['$scope',
        'MemberManager',
        'UserStatuses',
        '$timeout',
        '$window',
        '$compile',
        '$location',
        'CacheFactory',
        '$http',
        'envPrefix',
        '$modal',
        function($scope, MemberManager, UserStatuses, $timeout, $window, $compile, $location, CacheFactory, $http, envPrefix, $modal){

        var busy = false;
        var searchDataCache = CacheFactory.get('luvbyrd'),
            path = envPrefix + 'api/v1.0/user/swiper';

        if(!searchDataCache){
            searchDataCache = CacheFactory('luvbyrd', {});
        }

        var eventId = searchDataCache.get('event-id');
        if(eventId){
            window.location.href = '/event/'+ eventId;
        }

        $scope.UserStatuses = UserStatuses;
        var tmp = angular.element(".sliding-modal").children();

        $('body').on('change', '.city-form input ', function() {
            $scope.isNewSearch = true;
        });
        $('body').on('click', '.slider-track', function() {
            $scope.isNewSearch = true;
        });
        $('body').on('click', '.gender input[type="checkbox"]', function() {
            $scope.isNewSearch = true;
        });

        $scope.cityChange = function (ev) {
            if(ev.which == 8 || ev.which == 46){
                $scope.isNewSearch = true;
            }
        };

        $scope.$on('$locationChangeSuccess', function() {
            var page = parseInt($location.hash());

            if($location.hash() === '' && !busy){
                busy = true;
                $scope.search.page = 1;
                $scope.doSearch();
                $timeout(function(){
                    busy = false;
                },2000);
            }
            else if(angular.isNumber(page) &&
                !isNaN(page) &&
                page > 0){

                $scope.search.page = page;
                $scope.doSearch();
            }
        });

        $scope.members = [];
        $scope.paginationArray = [];
        $scope.slidingMemberIndex = 0;
        $scope.activeImageSlide = 0;
        $scope.paginationEps = 4;
        $scope.membersCount = 0;
        $scope.isNewSearch = true;
        $scope.search = {
            start: 0,
            count: 18,
            page: 1
        };

        $scope.initSearch = function(){
            $scope.search = {
                gender: {},
                age: [],
                interests: [],
                interestId: null,
                start: 0,
                count: 18,
                page: 1,
                city: null,
                radius: null,
                skiAndRide: null,
                zipCode: null
            };
        };

        $scope.accessDenied = function (data)
        {
            $scope.modalData = data;
            $scope.modal = $modal({
                scope: $scope,
                templateUrl: '/bundles/app/htmls/accessDeniedModal.html'
            });
        };

        $scope.searchMembers = function(){
            // angular.element('button[data-target="#menu-offcanvas"]').click();
            $('#menu-offcanvas').trigger("offcanvas.close");
            $scope.doSearch();
            $location.hash('');
        };

        $scope.doSearch = function(){
            $scope.lastInterest = $scope.search.interests;
            $scope.search.interests = [];
            angular.element(".interest-select").each(function(index, el){
                var val = angular.element(el).val();
                if(val){
                    if(!angular.isUndefined($scope.lastInterest) && !$scope.isNewSearch){
                        for(var i = 0; i < val.length; i++){
                            $scope.isNewSearch = ($scope.lastInterest.indexOf(val[i]) === -1);
                            break;
                        }
                    }
                    $scope.search.interests = $scope.search.interests.concat(val);
                }
            });

            $scope.isNewSearch = ($scope.isNewSearch && $scope.search.page == 1);

            angular.element(".ski-and-riding-responsive").each(function(index, el){
                var val = angular.element(el).val();
                if(val){
                    $scope.search.interests = $scope.search.interests.concat(val);
                }
            });

            $scope.search.start = ($scope.search.page - 1) * $scope.search.count;

            $scope.membersLoading = true;
            $scope.members = [];

            return MemberManager.search({}, $scope.search, function(res){
                $scope.members = res;

                $scope.membersLoading = false;

                if(res.length){
                    if($scope.isNewSearch){
                        $scope.isNewSearch = false;
                        $http.get(path)
                          .success(function(data){
                              searchDataCache.put('search-users', data);
                          });
                    }
                    $scope.membersCount = res[0].users_count;
                    $scope.paginationArray = $scope.newPagination(res[0].users_count);
                }
                else {
                    if($scope.isNewSearch){
                        $scope.isNewSearch = false;
                        searchDataCache.put('search-users', res);
                    }

                    $scope.paginationArray = [];
                    $scope.search.page = 1;
                    $scope.search.start = 0;
                }

                $scope.pagination_left_dots = ($scope.search.page > $scope.paginationEps);

                $scope.pagination_right_dots = ($scope.search.page < ($scope.paginationArray.length - $scope.paginationEps));

                $window.scrollTo(0, 0);
            });
        };

        //$scope.initSearch();

        //$timeout(function(){
        //    $scope.doSearch();
        //},500);

        $scope.reset = function(){

            $scope.initSearch();
            $scope.$broadcast('resetLsUiSlider');

            angular.element(".interest-select").val([]).trigger('change');
            angular.element(".ski-and-riding-responsive").val([]).trigger('change');
            //angular.element(".skyAndRiding").val([]).trigger('change');

            $timeout(function(){
                angular.element('button[data-target="#menu-offcanvas"]').click();
                $scope.doSearch();
            },300);
        };

        /** member sliding **/
        $scope.nextMember = function(likeStatus){

            var status = -1;
            var myStatus = parseInt($scope.slidingMember.status);

            if(myStatus === UserStatuses['HIDE'] && !likeStatus ||
                myStatus === UserStatuses['LIKE'] && likeStatus){
                status = UserStatuses['NATIVE'];
            }
            else if(likeStatus) {
                status = UserStatuses['LIKE'];
            }
            else {
                status = UserStatuses['HIDE'];
            }

            var id = $scope.slidingMember.id;
            $scope.slidingMember = null;
            $scope.animated = false;

            MemberManager.userStatus({where: id, param2: likeStatus != -1 ? status:myStatus}, null);

            $scope.members[$scope.slidingMemberIndex].status = likeStatus != -1 ? status:myStatus;

            if(status == UserStatuses['HIDE']){
                $scope.removeMemberItem(id);

                if(!$scope.members.length){
                    $.modal.close();
                }
            }

            if($scope.slidingMemberIndex >= $scope.members.length - 1){
                if($scope.search.page < Math.ceil($scope.membersCount/$scope.search.count)){

                    $scope.search.page++;
                    var pr = $scope.doSearch();

                    pr.$promise.then(function(){

                        $scope.slidingMemberIndex = 0;
                        $timeout(function(){
                            if(!$scope.members.length){
                                $scope.slidingMember = null;
                                $.modal.close();
                            }
                            else {
                                angular.element(".sliding-modal").children().remove();
                                $scope.slidingMember = angular.copy($scope.members[$scope.slidingMemberIndex]);
                                angular.element(".sliding-modal").append($compile(tmp)($scope));
                                $scope.animated = true;
                            }
                        }, 500);
                    });
                }
                else {
                    $scope.slidingMember = null;
                    $.modal.close();
                }
            }
            else {
                $scope.slidingMemberIndex++;
                $timeout(function(){
                    angular.element(".sliding-modal").children().remove();
                    $scope.slidingMember = angular.copy($scope.members[$scope.slidingMemberIndex]);
                    angular.element(".sliding-modal").append($compile(tmp)($scope));
                    $scope.animated = true;
                }, 100);
            }

            $scope.activeImageSlide = 0;

        };

        $scope.chooseMemberSliding = function(member, k){
            if(!member || !member.id){
                return;
            }

            angular.forEach(member.all_files, function(v){
                v.toRight = true;
                v.toLeft = true;
            });

            $scope.slidingMember = angular.copy(member);
            $scope.activeImageSlide = 0;
            $scope.slidingMemberIndex = k;
            $scope.animated = true;

            angular.element(".sliding-modal").modal({
                fadeDuration: 500
            });
        };

        $scope.nextSlide = function(){
            if($scope.activeImageSlide != $scope.slidingMember.all_files.length - 1){

                $scope.slidingMember.all_files[$scope.activeImageSlide].toLeft = true;
                $scope.slidingMember.all_files[$scope.activeImageSlide].toRight = false;

                $scope.activeImageSlide++;
            }

        };

        $scope.prevSlide = function(){
            if($scope.activeImageSlide != 0){
                $scope.slidingMember.all_files[$scope.activeImageSlide].toLeft = false;
                $scope.slidingMember.all_files[$scope.activeImageSlide].toRight = true;

                $scope.activeImageSlide--;
            }

        };

        $scope.toSelectedSlide = function(index){

            if(index > $scope.activeImageSlide){

                for(var i = $scope.activeImageSlide; i < index; i++){
                    $scope.slidingMember.all_files[i].toLeft = true;
                    $scope.slidingMember.all_files[i].toRight = false;
                }

            }
            else if(index < $scope.activeImageSlide) {
                for(var i = index; i < $scope.activeImageSlide; i++){
                    $scope.slidingMember.all_files[i].toLeft = false;
                    $scope.slidingMember.all_files[i].toRight = true;
                }
            }

            $scope.activeImageSlide = index;

        };

        angular.element($window).on('keydown', function(ev){
            if($scope.slidingMember && (ev.which == 39 || ev.which == 37)){
                ev.stopPropagation();
                ev.preventDefault();

                if(ev.which == 39){
                    $scope.nextSlide();
                }
                else if(ev.which == 37){
                    $scope.prevSlide();
                }

                $scope.$apply();
            }
        });

        angular.element(".sliding-modal").on($.modal.CLOSE, function(){
            $scope.slidingMember = null;
            if(!$scope.$$phase) {
                //$digest or $apply
                $scope.$apply();
            }
        });

        /** end member sliding **/

        $scope.removeMemberItem = function(id){
            if(!id){
                return;
            }

            var index = -1;
            for(var i = 0; i < $scope.members.length; i++){
                if($scope.members[i].id === id){
                    index = i;
                    break;
                }
            }

            if(index >= 0){
                $scope.members.splice(index, 1);
            }
        };

        $scope.newPagination = function(count){
            return new Array(Math.ceil(count / $scope.search.count));
        };

        $scope.paginationSurrounding = function(page){
            return Math.abs(page - $scope.search.page) < $scope.paginationEps;
        };

        $scope.pagination = function(page){
            if(!page){
                return;
            }

            $location.hash(page);

            $scope.search.page = page;
//            $scope.doSearch();
            $window.scrollTo(0, 0);
        };

        angular.element(".interest-select").select2();

        //    var skiAndRiding = angular.element(".skyAndRiding");
        //
        //    skiAndRiding.select2({
        //    ajax: {
        //        url: envPrefix + "app_dev.php/api/v1.0/user/ski/riding",
        //        dataType: 'json',
        //        delay: 250,
        //        data: function (params) {
        //            return {
        //                q: params.term // search term
        //            };
        //        },
        //        results: function (data) {
        //            return data;
        //        },
        //        cache: true
        //    },
        //    minimumInputLength: 3
        //    //initSelection : function (element, callback) {
        //    //    var data = $(element).attr('data-initvalue');
        //    //    data = angular.fromJson(data);
        //    //
        //    //    if(data){
        //    //        callback(data);
        //    //    }
        //    //
        //    //}
        //});

        $(document).on('click', '.cont-click', function(el){
            $('.cont-item').parent().parent().hide();
            $('.cont-click.fa-chevron-up').addClass('fa-chevron-down').removeClass('fa-chevron-up');
            $(el.target).addClass('fa-chevron-up').removeClass('fa-chevron-down');
            var continent = el.target.dataset.continent;
            $('.'+continent).parent().parent().show();
        });

        $(document).on('click', '.cont-click.fa-chevron-up', function(el){
            $('.cont-item').parent().parent().hide();
            $('.cont-click.fa-chevron-up').addClass('fa-chevron-down').removeClass('fa-chevron-up');
        });

        angular.element(".ski-and-riding-responsive").select2({
                templateResult: formatState,
            }
        ).on("select2:open", function (e) {
            $('.cont-item').parent().parent().hide();
        });
    }]);
var counter = 'continent';
function formatState (state) {

    if(state.text[0] == '_'){
        counter = state.text;
    }

    if(state.children){

        var imagePath = $(state.element).data('image');

        return $(
            '<span class="'+ ((state.text[0] == "_")?' continent':(counter + ' cont-item'))+'"><img height="20" src="' + imagePath + '"/> ' + ((state.text[0] == "_")?(state.text.substr(1) +'<i data-continent="'+ counter+'" class="cont-click dynamic-in fa fa-chevron-down pull-right text-gray"></i>'):state.text)+'</span>'
        );
    }
    else{
        return state.text;
    }

};
