/**
 * Created by vaz on 2/5/14.
 */
angular.module('color.components', [])
// this functionory is used to convert rgb numbers to hex
  .factory('RGB2Hex', [function () {
    return function (r, g, b) {

      var hexRed = r.toString(16);
      var hexGreen = g.toString(16);
      var hexBlue = b.toString(16);

      if (hexRed.length < 2) {
        hexRed = '0' + hexRed;
      }
      if (hexGreen.length < 2) {
        hexGreen = '0' + hexGreen;
      }
      if (hexBlue.length < 2) {
        hexBlue = '0' + hexBlue;
      }

      var color = '#' + hexRed + hexGreen + hexBlue;

      return color.toUpperCase();
    }
  }])
  .directive('lbColorPicker',['RGB2Hex',function (RGB2Hex) {
    return{
      restrict: 'A',
      template: '<canvas></canvas>',
      scope: {
        setDefault: '&lbDefaultColor',
        setTmp: '&lbTmpColor'
      },
      compile: function (element, attrs) {

        // get canvas
        var canvas = element.find('canvas');
        var ctx = canvas[0].getContext('2d');


        // load an image
        var colorImage = new Image;
        colorImage.onload = function () {

          //
          canvas.attr('width', colorImage.width + 'px');
          canvas.attr('height', colorImage.height + 'px');

          ctx.drawImage(colorImage, 0, 0, colorImage.width, colorImage.height);
        };

        colorImage.src = '/bundles/lbuser/images/color/colors.png';
        // selector image
        var selectorImage = new Image;
        selectorImage.src = '/bundles/lbuser/images/color/select.png';

        // this is needed in order to not call update function
        var oldColor = '';
//                var cellSize = {with: 9, height: 15};

        return function link(scope, element, attrs) {

          // click
          element.bind('click', function (event) {

            var posX = ( event.offsetX ) ? event.offsetX : event.layerX;
            var posY = ( event.offsetY ) ? event.offsetY : event.layerY;

            var pixelData = ctx.getImageData(posX, posY, 1, 1).data;

            var tColor = RGB2Hex(pixelData[0], pixelData[1], pixelData[2]);

            if (tColor != '#000000') {
              ctx.clearRect(0, 0, canvas[0].width, canvas[0].height);

              // calculating which is the nearest color
              var n = Math.floor(posX / 9 );
              var m = Math.floor((posY + 6) / 15);

              var xCenter, yCenter, minLength = 1000;

              for (var i = n - 1; i <= n + 1; i++) {
                for (var j = m - 1; j <= m + 1; j++) {
                  if(i >= 0 && j >= 0 && ((i+j) % 2 == 0)){

                    // center of cubs
                    var cX = 9 * i + 9;
                    var cY = 15 * j + 9;

                    // distance
                    var distance = Math.sqrt(Math.pow(posX - cX, 2) + Math.pow(posY - cY, 2));

                    if(minLength > distance){
                      minLength = distance;
                      xCenter = cX;
                      yCenter = cY;
                    }
                  }
                }
              }

              ctx.drawImage(colorImage, 0, 0, colorImage.width, colorImage.height);
              ctx.drawImage(selectorImage, xCenter - 10, yCenter - 11, selectorImage.width, selectorImage.height);

              scope.$apply(function () {
                scope.setDefault({'color': tColor});
              });
            }
          });

          element.bind('mousemove', function (event) {

            var posX = ( event.offsetX ) ? event.offsetX : event.layerX;
            var posY = ( event.offsetY ) ? event.offsetY : event.layerY;

            var pixelData = ctx.getImageData(posX, posY, 1, 1).data;

            scope.$apply(function () {
              if (pixelData[0] == 0 && pixelData[1] == 0 && pixelData[2] == 0) {
                scope.setTmp({'color': ''});
              } else {
                var tColor = RGB2Hex(pixelData[0], pixelData[1], pixelData[2]);
                if (tColor != oldColor) {
                  scope.setTmp({'color': tColor});
                  oldColor = tColor;
                }
              }
            });
          });
        }
      }
    };
  }]);
