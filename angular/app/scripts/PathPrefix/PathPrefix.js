'use strict';

angular.module("PathPrefix", [])
    .constant('envPrefix', (window.location.pathname.indexOf('app_dev.php') === -1) ? "/" : "/app_dev.php/")
    .value('template', {
      registerFormTemplate1: '',
      registerFormTemplate2: '',
      currentType: null
    });