'use strict';

$(document).ready(function(){
  $('input[type=file]').on('change', function (event) {
    var input = event.target;

    if (input.files && input.files[0]) {

      var file = input.files[0];
      var reader = new FileReader();

      reader.onload = function (e){
        if(e && e.target){
          var image = e.target.result;
          if($('#single-image').length){
            $('#single-image').attr('src', image);
          } else {
            $('input[type=file]').parent().append('<img  height="200" id="single-image" src="'+image+'">');
          }
        }
      };

      reader.readAsDataURL(file);
    }
  });
  var type = $('select[id$="_type"]').val();
  if(type != 1){
    $('input[id$="_price"]').parent().parent().hide();
  }

  $('select[id$="_type"]').on('change', function (event) {
    if(event.val == 1){
      $('input[id$="_price"]').parent().parent().show();
    } else {
      $('input[id$="_price"]').parent().parent().hide();
    }
  });
});