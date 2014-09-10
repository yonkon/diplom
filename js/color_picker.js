(function($) {
  $(function() {
    var i = 1;
    $('#cgui_area').find('table.cgui_table:first').find('tr').each(function() {
      var tr = $(this);
      if (tr.hasClass('header')) {
        return;
      }
      var id = 'color' + i;

      var td = tr.find('td:first-child');
      td.click(function(event) {
        event.stopPropagation();
      });

      td.append('<div class="colorPickerWrap"><input type="text" name="color" id="' + id + '"/></div>');

      td.find('.cgui_table_rowmenu').css({
        float: 'left'
      });

      $('#' + id).colorPicker({
        pickerDefault: $(this).attr('data-color'),
        onColorChange : function(id, newValue) {
          $.ajax({
            method: 'GET',
            url: window.location.href + '&subsection=instant_edit',
            data: {action: 'save', field: 'color', value: newValue, order_id: tr.attr('data-row-id')},
            success: function(result) {
              tr.css({
                backgroundColor: newValue
              }).attr('onmouseout', 'jQuery(this).css("background-color", "' + newValue + '");');
            }
          });
        }
      });
      i++;
    });
  });
})(jQuery);