angular.module('Google', [])
    .config(["$interpolateProvider",function($interpolateProvider){
        $interpolateProvider.startSymbol("[[");
        $interpolateProvider.endSymbol("]]");
    }])
    .filter('groups', function(){
        return function(input){
            var arr = [];

            if(typeof input == 'string'){
                return input;
            }

            angular.forEach(input, function(v){
                if(v.location){
                    arr.push({latitude: v.location.latitude, longitude: v.location.longitude, address: v.address});
                } else {
                    arr.push({latitude: v.latitude, longitude: v.longitude, id: v.id});
                }
            });

            return arr;
        }
    })
    .directive('simpleMapMarker',[function(){

        function Initialize(el){
            var m, data = {};
            data.center = new google.maps.LatLng(39.7445004, -104.95715439999998);
            data.zoom = 10;
            data.mapTypeId = google.maps.MapTypeId.ROADMAP;
            m = new google.maps.Map(el,data);

            return m;
        }

        return {
            restrict: 'EA',
            scope: {
                initMarkers: '=',
                view: '=',
                mapClick: '=',
                singleMarker: '=',
                markers: '=storage'
            },
            compile: function compile(){

                return function(scope, el){
                    scope.map = Initialize(el[0]);
                    scope.markers = [];
                    scope.marker = null;

                    scope.addMarker = function(obj, map){
                        var lat = parseFloat(obj.latitude);
                        var lng = parseFloat(obj.longitude);
                        if(!angular.isNumber(lat) || !angular.isNumber(lng)){
                            return;
                        }

                        return new google.maps.Marker({
                            draggable: angular.isDefined(obj.draggable) ? obj.draggable:true,
                            position: new google.maps.LatLng(lat, lng),
                            map: map
                        });
                    };

                    scope.addListenersOnMarker = function(marker){
                        marker.addListener('dragend', function() {

                            marker.latitude = marker.getPosition().lat();
                            marker.longitude = marker.getPosition().lng();

                            if(scope.markers.length && scope.markers[0].address){
                                scope.markers[0].location.latitude = marker.latitude;
                                scope.markers[0].location.longitude = marker.longitude;
                            }

                            if (!scope.$$phase) {
                                scope.$apply();
                            }

                        });

                        marker.addListener('rightclick', function() {
                            if(scope.markers.length && scope.markers[0].address){
                                return;
                            }
                            marker.setMap(null);

                            angular.forEach(scope.markers, function(v, k){
                                if(v.latitude == marker.latitude && v.longitude == marker.longitude){
                                    scope.markers.splice(k, 1);
                                }
                            });

                            if (!scope.$$phase) {
                                scope.$apply();
                            }
                        });
                    };

                    // init markers
                    angular.forEach(scope.initMarkers, function(v, k){
                        var m = scope.addMarker(v, scope.map);

                        m.latitude = m.getPosition().lat();
                        m.longitude = m.getPosition().lng();
                        m.id = v.id;

                        if(k === scope.initMarkers.length - 1){
                            scope.map.setCenter(m.getPosition())
                        }

                        if(!scope.view){
                            scope.addListenersOnMarker(m);
                        }

                        scope.markers.push(m);
                    });

                    scope.$watch('singleMarker', function(d){
                        if(!d || !d.latitude || !d.longitude){
                            return;
                        }

                        if(scope.marker){
                            scope.marker.setPosition(new google.maps.LatLng(parseFloat(d.latitude), parseFloat(d.longitude)));
                        }
                        else {
                            d.draggable = false;
                            scope.marker = scope.addMarker(d, scope.map);
                        }

                        scope.map.setCenter(scope.marker.getPosition());

                    }, true);

                    scope.map.addListener('click', function(e){
                        if(scope.mapClick){
                            var newMarker = {latitude: e.latLng.lat(), longitude: e.latLng.lng()};
                            var m = scope.addMarker(newMarker, scope.map);
                            scope.addListenersOnMarker(m);

                            m.latitude = m.getPosition().lat();
                            m.longitude = m.getPosition().lng();

                            scope.markers.push(m);
                            scope.$apply();
                        }
                    });
                };
            }
        };
    }])
    .directive('googlePlacesAutocomplete',['$timeout',function($timeout){
        return {
            restrict: 'EA',
            scope: {
                place: '=',
                types: '=',
                ngModel: '=',
                hiddenStorage: '@'
            },
            compile: function(){
                return function(scope, el){

                    $timeout(function(){
                        if(!scope.place){
                            scope.place = {};
                        }
                    }, 100);

                    el.on('keydown', function(ev){
                        if(ev.which == 13){
                            ev.stopPropagation();
                            ev.preventDefault();
                            return false;
                        }
                    });

                    el.on('keyup',function(ev){
                        if(ev.which == 13){
                            return;
                        }

                        scope.place = null;
                        angular.element(scope.hiddenStorage).val(null);
                        angular.element(scope.hiddenStorage).attr('value',null);
                        scope.$apply();
                    });

                    var autocomplete = new google.maps.places.Autocomplete(el[0],{types: scope.types ? scope.types:['establishment']});
                    google.maps.event.addListener(autocomplete, 'place_changed', function(){
                        var result = autocomplete.getPlace();

                        if(!result.geometry ||
                            !result.geometry.location ||
                            !result.formatted_address){
                            return;
                        }

                        scope.place = {};
                        scope.place.location = {
                            latitude: result.geometry.location.lat(),
                            longitude: result.geometry.location.lng()
                        };

                        scope.place.address = result.formatted_address;

                        angular.element(scope.hiddenStorage).val(JSON.stringify(scope.place));
                        angular.element(scope.hiddenStorage).attr('value',JSON.stringify(scope.place));

                        scope.$apply();
                    })
                }
            }
        }
    }]);