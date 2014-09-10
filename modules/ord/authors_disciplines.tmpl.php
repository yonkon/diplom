<div style="margin-top:20px; padding: 5px">
  <form method="post">
    <button type="submit" style="margin: 0 0 10px 100px;">Сохранить</button>
    <?php
    if (isset($this->Vars["table"])) {
      print $this->Vars["table"];
    }
    ?>

    <input type="hidden" name="save" value="1">
    <button type="submit" style="margin: 30px 0 0 100px;">Сохранить</button>
  </form>
</div>

<script>
  (function($) {
    $(function() {
      $('[data-toggle="collapse"]').click(function() {
        var el = $(this);
        var target = $(this).data('target');

        $('.'+target).toggle();

        if ($('.'+target).is(':visible')) {
          el.text('скрыть дисциплины');
        } else {
          el.text('показать дисциплины');
        }
      });

      $('[data-toggle="select"]').click(function() {
        var target = $(this).data('target');
        var allChecked = false;

        $('.'+target).find('input[type=checkbox]').each(function() {
          if ($(this).prop('checked')) {
            allChecked = true;
          } else {
            allChecked = false;
            return false;
          }
        });

        if (allChecked) {
          $('.'+target).find('input[type=checkbox]').attr('checked', false);
        } else {
          $('.'+target).find('input[type=checkbox]').attr('checked', true);
        }
      });
    });
  })(jQuery);
</script>