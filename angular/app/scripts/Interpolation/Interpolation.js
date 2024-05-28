'use strict';

angular.module("Interpolation",[])
    .config(["$interpolateProvider",function($interpolateProvider){
        $interpolateProvider.startSymbol("[[");
        $interpolateProvider.endSymbol("]]");
    }]);