'use strict';

angular.module('Facebook',[])
    .run([function(){
        window.fbAsyncInit = function() {
            FB.init({
//                appId      : '989308927799994', //.loc
                appId      : '1705691336309955',
                xfbml      : true,
                version    : 'v2.5'
            });
        };
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }])
    .directive('fbShare',[function(){
        return {
            restrict: 'A',
            scope: {
                name: '@fbName',
                link: '@fbLink',
                caption: '@fbCaption',
                picture: '@fbPicture',
                description: '@fbDescription',
                message: '@fbMessage'
            },
            compile: function(){
                return function(scope,el){

                    el.click(function(){
                        FB.ui({
                            method: 'feed',
                            name: scope.name,
                            link: scope.link,
                            picture: scope.picture,
                            caption: scope.caption,
                            description: scope.description,
                            message: scope.message
                        })
                    })
                }
            }
        }
    }])