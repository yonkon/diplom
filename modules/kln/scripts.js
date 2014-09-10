jQuery.noConflict();
(function($) {
  $(function() {
    if ($('#before_change').html() != null) {
      for (var i = 0; i <= $('#before_change .cgui_form_text').size(); i++) {
        if ($('#before_change #cgui_form_0_field_'+i+'').val() != $('#after_change #cgui_form_1_field_'+i+'').val()) {
          $('#after_change #cgui_form_1_field_'+i+'').css('background-color','red');
        }
      }
    }
  });
})(jQuery);