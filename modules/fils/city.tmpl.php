<script type="text/javascript">
  function check_cities(hidden_field_id, city_form_id) {
    var form = $('#' + city_form_id);
    var hidden = $('#' + hidden_field_id);
    hidden.val('');

    hidden.val(form.find('select').val().join('_'));

    $.modal.close();
  }

  function open_cities(hidden_field_id, city_form_id) {
    var hidden = $('#' + hidden_field_id);
    var form = $('#' + city_form_id);
    var cities_ids = hidden.val().split('_');
    var length = cities_ids.length;

    form.find('select option').attr('selected', false);

    for (var i = 0; i < length; i++) {
      form.find('select option[value='+cities_ids[i]+']').attr('selected', 'selected');
    }

    form.modal();
  }
</script>

<?php echo $this->Vars["city_modal_form"]->GetHTML()?>