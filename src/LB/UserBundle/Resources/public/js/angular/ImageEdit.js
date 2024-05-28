angular.module('imageEdit',['Confirm',
  'socket',
  'mgcrea.ngStrap.popover',
  'Interpolation',
  'angular-cache',
  'color.components',
  'PathPrefix'])
  .config(['CacheFactoryProvider',function(CacheFactoryProvider){
    angular.extend(CacheFactoryProvider.defaults, {
      maxAge: 24 * 60 * 60 * 1000, // Items added to this cache expire after 15 minutes.
      cacheFlushInterval: 60 * 60 * 1000, // This cache will clear itself every hour.
      deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
      storageMode: 'localStorage' // This cache will use `localStorage`.
    });
  }])
  .value('template', {
    editImageTemplate: '',
    imagePath: '',
    imageId: ''
  })
  .run(['$http', 'envPrefix', 'template', 'CacheFactory',
    function($http, envPrefix, template, CacheFactory){
      var editImageUrl = envPrefix + "image/edit-modal";

      var templateCache = CacheFactory.get('luvbyrd_templates_v1');

      if(!templateCache){
        templateCache = CacheFactory('luvbyrd_templates_v1', {
          maxAge: 3 * 24 * 60 * 60 * 1000 ,// 3 day,
          deleteOnExpire: 'aggressive'
        });
      }

        var editImageTemplate = templateCache.get('edit-image-template');

        // if (!editImageTemplate) {
          $http.get(editImageUrl).success(function(data){
            template.editImageTemplate = data;
            templateCache.put('edit-image-template', data);
          });
        // }else {
        //   template.editImageTemplate = editImageTemplate;
        // }
    }])
  .directive('editPhoto',['$compile',
    '$http',
    '$rootScope',
    '$timeout',
    'template',
    function($compile, $http, $rootScope, $timeout, template){
      return {
        restrict: 'EA',
        scope: {
          imagePath: '@',
          imageId: '@'
        },
        link: function(scope, el){

          el.bind('click', function(){
            scope.run();
          });

          scope.run = function(){
            $(".modal-loading").show();

            template.imagePath = scope.imagePath;
            template.imageId = scope.imageId;
            var sc = $rootScope.$new();
            // var tmp = $compile('<div style="height: 500px;width: 500px;top: 50%"><img src="'+scope.imagePath+'" alt="image" /></div>')(sc);
            var tmp = $compile(template.editImageTemplate)(sc);
            $timeout(function(){
              $(".modal-loading").hide();
              scope.openModal(tmp);
            }, 200);
          };

          scope.openModal = function(tmp){

            angular.element('body').append(tmp);
            tmp.modal({
              fadeDuration: 300,
              showClose: false
            });

            tmp.on($.modal.CLOSE, function(){
              tmp.remove();
            })
          }
        }
      }
    }
  ])
  .controller('ImageEditController',['$scope', '$http', 'envPrefix', 'template', '$timeout', 'RGB2Hex', '$interval',
    function($scope, $http, envPrefix, template, $timeout, RGB2Hex, $interval){
    $scope.imagePath = template.imagePath;
    $scope.currentFont = 'px Arial';
    $scope.isTouchDevice = (window.innerWidth <= 769);
    $scope.placeholder = $scope.isTouchDevice?'Tap to add a caption': 'Add a caption';
    var filName = $scope.imagePath.split("/"),
        editImageUrl = envPrefix + "api/v1.0/files/images/edits",
        canvasOffsetX = (window.innerWidth < 768)?7:10,
        canvasOffsetY = (window.innerWidth == 768)?20:22,
        mobileOffsetX = $scope.isTouchDevice?(window.innerWidth * 7/ 100 + ((window.innerWidth == 768)?8:0)):67,
        mobileOffsetY = $scope.isTouchDevice?(window.innerWidth * 12/ 100 - ((window.innerWidth == 768)?20:0)):72,
        img = new Image(),
        cachedimg = new Image(),
        isLoadingImage = false,
        k = {
          width: 577,
          height: 577
        },
        textField,
        sizeField,
        ctx,
        c;

    // this is main color
    $scope.defaultColor = "#000000";
    // this is used in order to change temp default color
    $scope.tmpColor = "#000000";
    /// this is used in order to check validation and update defaultColor
    $scope.color = "#000000";

    //
    $scope.detalization = 10;

    $scope.palitra = [];

    $scope.updatePalitra = function () {
      // if patter is right than change default color
      var colorPattern = /^#[0-9a-f]{6}$/i;
      var value = $scope.color;

      if (value.match(colorPattern)) {

        $scope.defaultColor = value;

        // recalculate pallitra
        $scope.palitra = [];

        var count = $scope.detalization;

        // get each color
        var red = parseInt(value.substr(1, 2), 16);
        var green = parseInt(value.substr(3, 2), 16);
        var blue = parseInt(value.substr(5, 2), 16);

        // get from balck to current color
        for (var i = 0; i < count; i++) {
          $scope.palitra.push(RGB2Hex(Math.round(red / count * i), Math.round(green / count * i), Math.round(blue / count * i)));
        }

        // add current one
        $scope.palitra.push(value);

        // get from current color to white
        for (var i = count - 1; i >= 0; i--) {
          $scope.palitra.push(RGB2Hex(255 - Math.round((255 - red) * i / count), 255 - Math.round((255 - green) * i / count), 255 - Math.round((255 - blue) * i / count)));
        }

      }
    };

    // this is used from directivve
    $scope.setDefaultColor = function (color){
      $scope.color = color;
      $scope.defaultColor = color;
      $scope.tmpColor = '';
      $('#image-text').css('color', color);
      ctx.fillStyle = color;
      $scope.updatePalitra();
      $scope.showColors = false;
    };

    $scope.setTmpColor = function (color){
      $scope.tmpColor = color;
    };

    //
    $scope.getCurrentColor = function(){
      return ($scope.tmpColor) ? $scope.tmpColor : $scope.defaultColor;
    };

    $scope.updatePalitra();

    $scope.goTo = function (type) {
      var left = document.getElementById('input-box').style.left,
          top = document.getElementById('input-box').style.top;
      switch(type) {
        case 1:
          document.getElementById('input-box').style.top = (top?top.slice(0, -2):mobileOffsetY) - 20 + 'px';
          break;
        case 2:
          document.getElementById('input-box').style.top = +(top?top.slice(0, -2):mobileOffsetY) + 20 + 'px';
          break;
        case 3:
          document.getElementById('input-box').style.left = +(left?left.slice(0, -2):mobileOffsetX) + 20 + 'px';
          break;
        case 4:
          document.getElementById('input-box').style.left = (left?left.slice(0, -2):mobileOffsetX) - 20 + 'px';
          break;
      }

    };

    $scope.saveImage = function () {
      var text = document.getElementById('image-text');
      var layerX = document.getElementById('input-box').style.left;
      var layerY = document.getElementById('input-box').style.top;
     
      layerX = layerX?layerX.slice(0, -2):mobileOffsetX;
      layerY = layerY?layerY.slice(0, -2):mobileOffsetY;
      
      ctx.fillText(text.value,(layerX > 0)?(+layerX + canvasOffsetX):0,(layerY > 30)?(+layerY + canvasOffsetY):20);
      var dataURL = c.toDataURL();
      $http.post(editImageUrl, {
        filename: filName[filName.length -1],
        imagePath: dataURL
      }).success(function (res) {
        $(template.imageId).attr('src', res.name + '&'+(new Date().getTime()));
        $scope.$emit('hideLoading');
        $(".modal-loading").hide();
      });

      $.modal.close();
      $(".modal-loading").show();
      $scope.$emit('showLoading');
    };

    img.onload = function(){
      isLoadingImage = true;
      if(window.innerWidth < 768){
        k.width = (img.width < window.innerWidth - 50)?img.width: window.innerWidth - 50;
        k.height = k.width * img.height / img.width;
      } else {
        k.height = img.height < 577?img.height: 577;
        k.width = k.height * img.width / img.height;
      }
    };

    $scope.drawInCanvas = function () {
      c.width = k.width;
      c.height = k.height;

      $('#input-box').css('width', c.width*80/100 + 'px');

      ctx = c.getContext("2d");
      ctx.font = sizeField[0].value + $scope.currentFont;
      // ctx.fillText("", 50, 200);
      ctx.clearRect(0, 0, c.width, c.height);
      ctx.drawImage(img, 0, 0,c.width,c.height);
    };

    function drawingInterval() {
      if(isLoadingImage){
        $interval.cancel($scope.interval);
        $scope.drawInCanvas();
      }
    }

    img.src = $scope.imagePath + '?'+(new Date().getTime());

    $timeout(function () {
      textField = $('#image-text');
      sizeField = $('#fontSize');

      textField.css('font', sizeField[0].value + $scope.currentFont);
      c = $("#myCanvas")[0];

     if(!isLoadingImage){
       $scope.interval = $interval(drawingInterval,100);
     } else {
       $scope.drawInCanvas()
     }

      sizeField.on('change', function (res) {
        textField.css('font', sizeField[0].value + $scope.currentFont);
        ctx.font = res.target.value + $scope.currentFont;
      })
    }, 500);

  }]);
