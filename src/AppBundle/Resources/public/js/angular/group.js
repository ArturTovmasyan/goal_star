'use strict';

angular.module('group', ['ngResource',
    'Interpolation',
    'Components',
    'Confirm',
    'Google',
    'socket',
    'search',
    'PathPrefix',
    'ngSanitize',
    'user'])
      .run(function(){

          var desc = $('#app_bundle_lbgroup_type_description')[0];

          if(desc){
              $('#app_bundle_lbgroup_type_description').css({
                  overflow : 'hidden',
                  height : (20 + $('#app_bundle_lbgroup_type_description')[0].scrollHeight)+"px"
              });
          }

          $('#app_bundle_lbgroup_type_description').on('keyup', function (ev) {
              ev.target.style.height = "1px";
              ev.target.style.height = (20 + ev.target.scrollHeight)+"px";
          });
      })
    .service('GroupManager',['$resource', 'envPrefix', function($resource, envPrefix){
        return $resource(envPrefix + 'api/v1.0/groups/:what/:where/:param1', {}, {
            adminInvitesModerators: {method: 'POST', isArray: false, params: {what: 'admins', where: 'moderators'}},
            adminInvitesMembers: {method: 'POST', isArray: false, params: {what: 'admins', where: 'members'}},
            joinAsMember: {method: 'POST', isArray: false, params: {what: 'members'}},
            joinAsModerator: {method: 'POST', isArray: false, params: {what: 'moderators'}},
            getGroups: {method: 'GET', isArray: true, params: {where: 'calendars'}}
        });
    }])
    .controller('GroupSingleController', ['$scope', 'GroupManager', function($scope, GroupManager){

        // list of users
        $scope.moderators = [];
        $scope.members = [];

        // to collect removed users
        $scope.removeModerators = [];
        $scope.removeMembers = [];

        // is group limited
        $scope.isLimited = false;
        $scope.isPrivate = false;
        $scope.memberMemberStatus = false;
        $scope.memberAuthorStatus = false;

        $scope.moderatorModeratorStatus = false;
        $scope.moderatorAuthorStatus = false;

        $scope.groupId = null;

        // add new user
        $scope.addModerator = function(user){

            var moderator = {
                userId: user.id,
                groupId: $scope.groupId,
                status: 1
            };

            return $scope.restModerator(moderator);
        };

        $scope.addMember = function(user){

            var member = {
                userId: user.id,
                groupId: $scope.groupId,
                status: 1
            };

            return $scope.restMember(member);
        };

        // remove item from server and local array
        $scope.removeModerator = function(userId){
            var post = {
                userId: userId,
                groupId: $scope.groupId,
                status: 0
            };

            $scope.restModerator(post);
        };

        $scope.removeMember = function(userId){
            var post = {
                userId: userId,
                groupId: $scope.groupId,
                status: 0
            };

            $scope.restMember(post);
        };

        // send ajax request to the server with given post object
        $scope.restModerator = function(post){
            if(angular.isUndefined(post.status) || !post.userId){
                return;
            }

            GroupManager.adminInvitesModerators({}, post, function(res){
                if(post.status){
                    var mod = res.moderator;
                    mod.author_status = res.author_status;
                    mod.member_status = res.member_status;
                    $scope.moderators.push(mod);
                }
                else {
                    $scope.removeModeratorItem(post.userId)
                }

                $scope.moderatorSelect.val([]).trigger('change');
            });
        };

        $scope.restMember = function(post){
            if(angular.isUndefined(post.status) || !post.userId){
                return;
            }

            GroupManager.adminInvitesMembers({}, post, function(res){

                if(!post.status){
                    $scope.removeMemberItem(post.userId);
                }
                else {
                    var mem = res.member;
                    mem.author_status = res.author_status;
                    mem.member_status = res.member_status;
                    if(!mem.author_status || !mem.member_status){
                        $scope.members.push(mem);
                    }
                }

                $scope.memberSelect.val([]).trigger('change');
            });
        };

        // remove item from local array
        $scope.removeModeratorItem = function(userId){
            if(!userId){
                return;
            }

            for(var i = 0; i < $scope.moderators.length; i++){
                if($scope.moderators[i].id === userId){
                    $scope.moderators.splice(i, 1);
                    break;
                }
            }
        };

        $scope.removeMemberItem = function(userId){
            if(!userId){
                return;
            }

            for(var i = 0; i < $scope.members.length; i++){
                if($scope.members[i].id === userId){
                    $scope.members.splice(i, 1);
                    break;
                }
            }
        };

        // join or leave the group as MEMBER
        $scope.joinLeaveAsMember = function(status){
            if(!$scope.groupId || angular.isUndefined(status)){
                return;
            }

            GroupManager.joinAsMember({}, {group: $scope.groupId, status: status}, function(res){

                if(status && !$scope.isPrivate){
                    $scope.members.push(res.member);
                    $scope.memberAuthorStatus = true;
                }
                else if(!status) {
                    // remove user from list
                    $scope.removeMemberItem(res.id);
                    $scope.memberAuthorStatus = false;
                }

                $scope.memberMemberStatus = status;

            });
        };

        // join or leave the group as MODERATOR
        $scope.joinLeaveAsModerator = function(status){
            if(!$scope.groupId || angular.isUndefined(status)){
                return;
            }

            GroupManager.joinAsModerator({}, {group: $scope.groupId, status: status}, function(res){

                if(status){
                    $scope.moderators.push(res.moderator);
                }
                else if(!status) {
                    // remove user from list
                    $scope.removeModeratorItem(res.id);
                    $scope.moderatorAuthorStatus = false;
                }

                $scope.moderatorModeratorStatus = status;

            });
        };

        $scope.moderatorSelect = angular.element('.moderator-select').select2({
            placeholder: 'Choose Moderator',
            multiple: true,
            ajax: {
                url: angular.element('.moderator-select').data('ajax-url'),
                dataType: 'json',
                delay: 200,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.items
                    };
                }
            },
            templateResult: function (item) {
                return item.label; // format of one dropdown item
            },
            templateSelection: function(item){
                return item.label;
            }
        })
        .on("select2:select", function(ev){
            var user = ev.params.data;
            $scope.addModerator(user);
        });

        $scope.memberSelect = angular.element('.member-select').select2({
            placeholder: 'Choose Member',
            multiple: true,
            ajax: {
                url: angular.element('.moderator-select').data('ajax-url'),
                dataType: 'json',
                delay: 200,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.items
                    };
                }
            },
            templateResult: function (item) {
                return item.label; // format of one dropdown item
            },
            templateSelection: function(item){
                return item.label;
            }
        })
        .on('select2:select', function(ev){
            var user = ev.params.data;
            $scope.addMember(user);
        });

        $scope.memberExists = function(val){
            return $scope.memberExistsVariable = val;
        };

        $scope.requestedMemberExists = function(val){
            return $scope.requestedMemberExistsVariable = val;
        };

        $('[data-toggle="tooltip"]').tooltip();
    }])
    .controller('GroupListController',['$scope',
        'GroupManager',
        '$timeout',
        '$filter',
        '$window',
        '$location',
        function($scope, GroupManager, $timeout, $filter, $window, $location){
            var busy = false;

            $scope.$on('$locationChangeSuccess', function() {
                var page = parseInt($location.hash());

                if($location.hash() === '' && !busy){
                    busy = true;
                    $scope.page = 1;
                    $scope.getGroups($scope.date,  $scope.event);
                    $timeout(function(){
                        busy = false;
                    },2000);
                }
                else if(angular.isNumber(page) &&
                    !isNaN(page) &&
                    page > 0){

                    $scope.page = page;
                    $scope.getGroups($scope.date,  $scope.event);
                }
            });


            $scope.paginationArray = [];
            $scope.start = 0;
            $scope.count = 6;
            $scope.page  = 1;
            $scope.paginationEps = 4;
            $scope.groupsCount = 0;
            $scope.date = $filter('date')(new Date(), 'yyyy-MM-dd');
            $scope.event = null;

            $scope.newPagination = function(count){
                return new Array(Math.ceil(count / $scope.count));
            };

            $scope.paginationSurrounding = function(page){
                return Math.abs(page - $scope.page) < $scope.paginationEps;
            };

            $scope.pagination = function(page){
                if(!page){
                    return;
                }

                $location.hash(page);

                $scope.page = page;
                $window.scrollTo(0, 0);
            };

        $scope.$on('dateChanged', function(event, data){
            if(!data || !data.date){
                return;
            }

            $scope.date = data.date;
            $scope.event = data.event;
            $scope.page  = 1;
            $location.hash(null);

            $scope.getGroups(data.date, data.event);
        });

        $scope.getGroups = function(date, eventName){

            $scope.start = ($scope.page - 1) * $scope.count;

            $scope.membersLoading = true;
            $scope.groups = null;

            var params = {
                what: date,
                param1: $scope.routeNav,
                eventName: eventName,
                count: $scope.count,
                start: $scope.start
            };

            return GroupManager.getGroups(params, function(res){
                $scope.groups = res;


                $scope.membersLoading = false;

                if(res.length){
                    $scope.groupsCount = res[0].group_count;
                    $scope.paginationArray =  $scope.newPagination(res[0].group_count);
                }
                else {
                    $scope.paginationArray = [];
                    $scope.page = 1;
                    $scope.start = 0;
                }

                $scope.pagination_left_dots = ($scope.page > $scope.paginationEps);

                $scope.pagination_right_dots = ($scope.page < ($scope.paginationArray.length - $scope.paginationEps));
            });
        };

        // $timeout(function(){
        //     $scope.getGroups($scope.date);
        // }, 300);

    }]);