(function($) {
  $(function() {
    $('select[name=roles]').change(function() {
      var role_id = $(this).val();

      if (role_id == 0) {
        $('#rights_wrap').html('');
        return;
      }

      $('#role_id').val(role_id);

      $.ajax({
        type:"POST",
        url:'/modules/rights/ajax.php',
        cache:false,
        data:{action:'get_role_rights', role_id:role_id},
        success:function(result) {
          $('#rights_wrap').html(result);
        }
      });
    });

    $('.submodule_checkbox').live("change", function() {
      if ($(this).attr('checked') == "checked") {
        if ($(this).parent('div').parent('div').find('.module_checkbox').attr('checked') == undefined) {
          $(this).parent('div').parent('div').find('.module_checkbox').attr('checked', "checked");
        }
      }
    });
  });
})(jQuery);