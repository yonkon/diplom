(function($) {
  $(function() {
    var td, tr, tr_onclick, instantEditContent, field, value, url;

    if (typeof instant_edit_url == 'undefined' || instant_edit_url == '') {
      url = window.location.href;
    } else {
      url = instant_edit_url;
    }

    $('.instantEdit').click(function(event) {
      $('.instantEditClose').trigger('click');

      event.stopPropagation();
      td = $(this).parent('td');
      tr = $(this).closest('tr');
      tr_onclick = tr.attr('onclick');
      field = $(this).attr('data-field');
      value = $(this).attr('data-value');
      var title = $(this).attr('data-title');

      tr.attr('onclick', '');

      td.prepend('<div class="instantEditPopup">' +
        '<div class="instantEditTitle">' + title + '</div>' +
        '<div class="instantEditContent"></div>' +
        '<div class="instantEditButtons">' +
        '<button class="instantEditSave">Ok</button>' +
        '<button class="instantEditClose">Отмена</button>' +
        '</div>' +

        '</div>');

      instantEditContent = $('.instantEditContent');

      $.ajax({
        method: 'GET',
        url: url + '&subsection=instant_edit',
        data: {action: 'get', field: field, value: value},
        beforeSend: function() {
          instantEditContent.text('Идет загрузка...');
        },
        success: function(result) {
          instantEditContent.html(result);
        }
      });
    });

    $('.instantEditSave').live('click', function() {
      value = $('.instantEditNewValue').val();
      var order_id;

      if (field == 'debt_to_author' || field == 'debt_to_company' || field == 'referrer_payment_status_all') {
        order_id = $('#orders_list').text();
      } else {
        order_id = tr.attr('data-row-id');
      }
      $.ajax({
        method: 'POST',
        url: url + '&subsection=instant_edit',
        data: {action: 'save', field: field, value: value, order_id: order_id},
        beforeSend: function() {
          instantEditContent.text('Идет сохранение...');
        },
        success: function(result) {
          td.find('.instantEditOldValue').html(result);
          td.find('.instantEdit').attr('data-value', result);
        },
        complete: function() {
          $('.instantEditPopup').remove();
        }
      });
    });

    $('.instantEditClose').live('click', function() {
      $('.instantEditPopup').remove();
      if (typeof tr != 'undefined') {
        tr.attr('onclick', tr_onclick);
      }
    });
  });
})(jQuery);