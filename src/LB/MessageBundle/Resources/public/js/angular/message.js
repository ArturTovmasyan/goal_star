'use strict';

angular.module('message',['Interpolation',
    'socket',
    'ngResource',
    'ngAnimate',
    'Components',
    'emoji',
    'Smiley',
    'ngSanitize',
    'monospaced.elastic',
    'mgcrea.ngStrap.tooltip',
    'mgcrea.ngStrap.popover',
    'PathPrefix',
    'infinite-scroll',
    'mgcrea.ngStrap.modal',
    'user'])
    .service('MessagesManager',['$resource', 'envPrefix', function($resource, envPrefix){
        return $resource(envPrefix + 'api/v1.0/:dir/:what/:where/:param1/:param2/:param3', {}, {
            getUserMessages: {method: 'GET', isArray: true, params: {where: 'limiteds', param2: 'messages', dir: 'messages'}},
            report: {method: 'PUT', isArray: false, params: {what: 'reports'}},
            friends: {method: 'GET', isArray: true, params: {dir: 'messages'}},
            conversationStatus: {method: 'GET', isArray: false, params: {dir: 'userrelations', where: 'conversations'}}
        });
    }])
    .factory('lsInfiniteUsers', ['MessagesManager', function(MessagesManager) {
      var lsInfiniteUsers = function(loadCount) {
          this.users = [];
          this.noReserve = false;
          this.noItem = false;
          this.busy = false;
          this.request = 0;
          this.start = 0;
          this.reserve = [];
          this.count = loadCount ? loadCount : 10;
      };

      lsInfiniteUsers.prototype.reset = function(){
          this.users = [];
          this.busy = false;
          this.reserve = [];
          this.request = 0;
          this.start = 0;
          this.noReserve = false;
      };

      lsInfiniteUsers.prototype.imageLoad = function() {
          var img;
          this.busy = false;
          angular.forEach(this.reserve, function(item) {
              if(item.profile_Image ){
                  img = new Image();
                  img.src = item.profile_Image;
              }
          });
      };

      lsInfiniteUsers.prototype.getReserve = function() {
          if(!this.noReserve) {
              this.busy = this.noItem;
              this.users = this.users.concat(this.reserve);
              this.nextReserve();
          }
      };

      lsInfiniteUsers.prototype.nextReserve = function() {

          if (this.busy) return;
          this.busy = true;

          MessagesManager.friends({start: this.start, count: this.count, id: this.id}, function (newData) {
              if(!newData.length){
                  this.noReserve = true;
              } else {
                  this.reserve = newData;
                  this.imageLoad();
                  this.start += this.count;
                  this.request++;
                  this.busy = false;
              }
          }.bind(this));
      };

      lsInfiniteUsers.prototype.nextFriends = function(id) {
          if (this.busy) return;
          this.busy = true;
          this.noItem = false;

          if(this.request){
              this.getReserve();
          } else {
              this.id = id?id:null;
              MessagesManager.friends({start: this.start, count: this.count, id: this.id }, function (newData) {
                  // if get empty
                  if(!newData.length){
                      this.noItem = true;
                  } else {
                      this.busy = false;
                      this.users = this.users.concat(newData);
                      this.start += this.count;
                      this.request++;
                      this.nextReserve();
                  }
              }.bind(this));
          }
      };

      return lsInfiniteUsers;
    }])
    .controller('ChatController',['$scope',
        'MessagesManager',
        'socketValue',
        '$timeout',
        '$modal',
        '$window',
        '$http',
        '$compile',
        'SmileyItems',
        'SmileyResourcePath',
        'UserStatuses',
        'lsInfiniteUsers',
        '$rootScope',
        function($scope,
             MessagesManager,
             socketValue,
             $timeout,
             $modal,
             $window,
             $http,
             $compile,
             SmileyItems,
             SmileyResourcePath,
             UserStatuses,
             lsInfiniteUsers,
             $rootScope){

            $scope.userId = null;
            $scope.userUId = null;
            $scope.loading = false;
            $scope.start = 0;
            $scope.count = 10;
            $scope.messages = [];
            $scope.unreadMessagesCountByUser = {};
            $scope.conversationStatusByUser = {};

            $scope.UserStatuses = UserStatuses;
            $scope.SmileyItems = SmileyItems;
            $scope.SmileyResourcePath = SmileyResourcePath;
            $scope.Friends = new lsInfiniteUsers(15);
            $timeout(function () {
                $scope.Friends.nextFriends($scope.visitUser?$scope.visitUser:0);
            },100);

            $scope.nextFriends = function () {
              if(!($scope.Friends.noItem || $scope.Friends.busy)){
                  $rootScope.$broadcast('slimScrollToBottom', 3000);
                  $scope.Friends.nextFriends($scope.visitUser?$scope.visitUser:0);
              }  
            };

            if(socketValue && socketValue.socket) {
                socketValue.socket.on('message', function (data) {
                    if (angular.isDefined($scope.unreadMessagesCountByUser[data.from_user_id])) {
                        $scope.unreadMessagesCountByUser[data.from_user_id]++;
                    }

                    // when scope.userId is chosen,it is one of them, from or to

                    var d = {};

                    if ($scope.me !== data.from_user_id) {

                        d = {
                            user_id: data.from_user_id,
                            user: {
                                first_name: data.from_user.first_name,
                                message_image: data.from_user.message_image
                            }
                        };

                        var msgFromUser = angular.element('.messages-user-ul > li[data-user-id=' + data.from_user.id + ']');

                        if (msgFromUser.length) {
                            msgFromUser.remove();
                        }

                        d.new_message = true;
                        d.user_is_online = true;
                    }
                    else {
                        var msgToUser = angular.element('.messages-user-ul > li[data-user-id=' + data.to_user_id + ']');

                        d = {
                            user_id: data.to_user_id,
                            user: {
                                first_name: msgToUser.data('user-name'),
                                message_image: msgToUser.data('message-image')
                            }
                        };

                        d.user_is_online = msgToUser.find('.is-online').hasClass('green');

                        if (msgToUser.length) {
                            msgToUser.remove();
                        }

                    }

                    $scope.addMessageUser(d);

                    if (parseInt(data.from_user_id) == $scope.userId
                      || parseInt(data.to_user_id) == $scope.userId) {
                        $scope.addMessage(data);
                    }

                    $scope.$apply();
                });
            }

            $scope.addMessageUser = function(messageUser){
                $http.get('/bundles/lbmessage/htmls/messageUser.html')
                    .success(function(res){
                        var tmp = res.replace(/__message_user_id__/g, messageUser.user_id)
                            .replace(/__profile_image__/g, messageUser.user.message_image)
                            .replace(/__name__/g, messageUser.user.first_name);

                        if(messageUser.user_is_online){
                            tmp = $(tmp);
                            //console.log(tmp.find('.is-online'));
                            tmp.find('.is-online').addClass('fa fa-circle green');
                        }

                        var template = $compile(tmp)($scope);
                        if(messageUser.new_message){
                            // $scope.unreadMessagesCountByUser[messageUser.user_id] = 1;
                        }

                        if($scope.conversationStatusByUser[messageUser.user_id] != $scope.UserStatuses['FAVORITE']){
                            var unreadIndex = angular.element('.messages-user-ul > li[data-favorite=false][data-unread=true]').last();
                            var indexUser = angular.element('.messages-user-ul > li[data-favorite=true]').last();
                            if(unreadIndex.length){
                                unreadIndex.after(template);
                            } else if(indexUser.length){
                                indexUser.after(template);
                            }
                            else {
                                angular.element(".message-users .messages-user-ul").prepend(template);
                            }
                        }
                        else {
                            var unreadIndex = angular.element('.messages-user-ul > li[data-favorite=true][data-unread=true]').last();
                            if(unreadIndex.length){
                                unreadIndex.after(template);
                            } else {
                                angular.element(".message-users .messages-user-ul").prepend(template);
                            }
                        }
                    });
            };

            // get user messages
            $scope.getUserMessages = function(userId, firstName, lastName, imagePath){
                if(!userId){
                    return;
                }

                $scope.loading = true;
                $scope.messages = [];
                $scope.messageUserFullName = '' + firstName + ' ' + lastName;
                $scope.userId = userId;
                $scope.imegePath = imagePath;
                $scope.start = 0;
                $scope.userUId = null;

                var nav = {what: userId, param1: $scope.start, param3: $scope.count};
                MessagesManager.getUserMessages(nav, function(data){
                    $scope.messages = data.reverse();
                    $scope.loading = false;

                    if($scope.messages.length){
                        $scope.userUId =  $scope.messages[0].from_user.id !== $scope.me ? $scope.messages[0].from_user.u_id : $scope.messages[0].to_user.u_id;
                    }

                    console.log(data);

                    // walk on count messages
                    $scope.start += $scope.count;

                    $scope.scrollToBottom();
                    $scope.readUserMessages();
                });
            };

            // send new message
            $scope.sendMessage = function($event){
                if(!$scope.userId || !$scope.message){
                    return;
                }

                if(!$event || ($event && $event.which == 13 && !$event.shiftKey)){
                    var message = {
                        content: $scope.message,
                        subject: 'message',
                        userId: $scope.userId
                    };

                    if(socketValue && socketValue.socket) {
                        socketValue.socket.emit('message', message);
                    }
                    $scope.message = '';
                }
            };

            // add new message after socket answer
            $scope.addMessage = function(data){
                $scope.messages.push(data);
                $scope.start++;

                $scope.scrollToBottom();

                if(parseInt(data.from_user_id) == $scope.userId){
                    $scope.readUserMessages();
                }
            };

            $scope.loadMore = function(){

                window.scrollTo(0,0);

                if($scope.loading){
                    return;
                }

                $scope.loading = true;
                var nav = {what: $scope.userId, param1: $scope.start, param3: $scope.count};

                MessagesManager.getUserMessages(nav, function(data){
                    if(data && data.length){
                        $scope.messages = data.reverse().concat($scope.messages);

                        // walk on count messages
                        $scope.start += $scope.count;
                    }

                    $scope.loading = false;
                });

            };
            
            $scope.getActivity = function (lastActivity) {
                var result = {'minute' : -1, 'title' : null};
                
                // now
                var now = new Date();

                if (!lastActivity) {
                    return result;
                }

                var ms = moment(now).diff(moment(new Date(lastActivity)));
                var d = moment.duration(ms),
                    y = Math.floor(d.asYears()),
                    m = Math.floor(d.asMonths()),
                    dd = Math.floor(d.asDays()),
                    h = Math.floor(d.asHours()),
                    mm = Math.floor(d.asMinutes());

                // activity result
                if (!angular.isUndefined(d)) {
                    if(y > 0) {
                        result = {'minute': dd * 365 * 1440 + mm, 'title': 'active within 1 year'};
                    } else if (m >= 6) {
                        result = {'minute': dd * 30 * 1440 + mm, 'title': 'active within 6 months'};
                    } else if(m > 0) {
                        result = {'minute': dd * 30 * 1440 + mm, 'title': 'active within one month'};
                    } else if(dd >= 7) {
                        result = {'minute': dd * 1440 + mm, 'title': 'active within one week'};
                    } else if(dd >= 3) {
                        result = {'minute': dd * 1440 + mm, 'title': 'active within 72 hrs'};
                    } else if(dd > 0) {
                        result = {'minute': dd * 1440 + mm, 'title': 'active within 24 hrs'};
                    } else  if(h > 0) {
                        result = {'minute': h * 60 + mm, 'title': 'active within 1 hr'};
                    } else {
                        result = {'minute': mm, 'title': 'active less than 1 hr'};
                    }
                }

                return result;
            };

            $scope.scrollBindBottomFunction = function(){
                // window.scrollTo(0,document.body.scrollHeight);
            };

            $scope.scrollToBottom = function(){
                $timeout(function(){
                    var lastEl = angular.element('.message-row:last-child');
                    angular.element('.slimScrollDiv>div').scrollTo(lastEl);
                },100);
            };

            $scope.readUserMessages = function(){
                $timeout(function(){
                    if(socketValue && socketValue.socket) {
                        socketValue.socket.emit('readMessage', {userId: $scope.userId});
                    }
                    $scope.unreadMessagesCountByUser[$scope.userId] = 0;
                },1000);
            };

            // Message Actions
            $scope.openReport = function(){
                if(!$scope.userId){
                    return;
                }

                $scope.reportUserId = angular.copy($scope.userId);
                $scope.modal = $modal({
                    scope: $scope,
                    templateUrl: '/bundles/app/htmls/report.html'
                });
            };

            $scope.report = function(id, message){
                MessagesManager.report({where: id},{id: id, message: message},function(){
                    $scope.modal.hide();
                });
            };

            $scope.favorite = function(id){

                var Id = id ? id : $scope.userId;

                if(!Id){
                    return;
                }

                var status = -1;
                if($scope.conversationStatusByUser[Id] === $scope.UserStatuses['FAVORITE']){
                    status = $scope.UserStatuses['NATIVE'];
                }
                else {
                    status = $scope.UserStatuses['FAVORITE'];
                }

                var param = {what: Id, param1: status };
                MessagesManager.conversationStatus(param, function(){
                    $scope.conversationStatusByUser[Id] = status;
                    var user = angular.element('.messages-user-ul > li[data-user-id=' + Id + ']');
                    if(user.length){
                        var data = {
                            user_id: Id,
                            user: {
                                first_name: user.data('user-name'),
                                message_image: user.data('message-image')
                            }
                        };

                        user.remove();
                        $scope.addMessageUser(data);
                    }
                });
            };

            $scope.spam = function(){
                if(!$scope.userId){
                    return;
                }

                var status = -1;
                if($scope.conversationStatusByUser[$scope.userId] === $scope.UserStatuses['SPAM']){
                    status = $scope.UserStatuses['NATIVE'];
                }
                else {
                    status = $scope.UserStatuses['SPAM'];
                }

                var param = {what: $scope.userId, param1: status };

                MessagesManager.conversationStatus(param, function(){
                    $scope.conversationStatusByUser[$scope.userId] = status;
                });
            };

            $scope.selectSmiley = function(code){
                $scope.$emit('addTextInTextArea', code);
            };

            // End Messages Actions

            // angular.element($window).on('keydown', function(ev){
            //
            //     if($scope.userId && (ev.which == 40 || ev.which == 38)){
            //         ev.stopPropagation();
            //         ev.preventDefault();
            //
            //         var cur = angular.element(".message-users li[data-user-id="+$scope.userId+"]");
            //         var item = null;
            //         if(ev.which == 40){
            //             item = cur.next();
            //         }
            //         else if(ev.which == 38){
            //             item = cur.prev();
            //         }
            //
            //         if(item && item.data('user-id') && item.data('user-name')){
            //             $scope.getUserMessages(item.data('user-id'), item.data('user-name'), '')
            //         }
            //
            //         $scope.$apply();
            //     }
            // });

            $('[data-toggle="tooltip"]').tooltip();

        }])
    .filter('responsiveText',['$filter', function($filter){
        return function(string){
            var str = '';

            if(screen.width >= 320 && screen.width < 360){
                str = $filter('limitTo')(string, 7) + '...';
            }
            else if (screen.width >= 360 && screen.width < 480){
                str = $filter('limitTo')(string, 9) + '...';
            }
            else if (screen.width >= 480 && screen.width < 560){
                str = $filter('limitTo')(string, 15) + '...';
            }
            else if (screen.width >= 560 && screen.width <= 768){
                str = $filter('limitTo')(string, 16) + '...';
            }
            else if (screen.width > 768){
                str = $filter('limitTo')(string, 18) + '...';
            }

            return str;
        }
    }]);
