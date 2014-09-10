var adr_book_wnd = false;

function show_adr_book(ret_idname) {
  var ta = jQuery("#" + ret_idname);
//  var h = window.innerHeight - ta.offset().top;
//  if (h > 400) h = 400;
  var t = ta.val().replace(/[^ku0-9]{1,}/gi, ";");
  adr_book_wnd = window.open("/modules/mls/adrbook.php?n=" + ret_idname + "&a=" + t, "win_adrbook", "left=" + ta.offset().left + ",top=" + ta.offset().top + ",width=550,height=400");
}

var show_adr_type = 0;

function onselbooktype(tp) {
  var lst = jQuery("#adr_list_id");
  lst.html("");

  jQuery("#search_field_id").val("Поиск");
  jQuery("#search_field_id").css("color", "silver");

  show_adr_type = tp;

  switch (tp) {
    case "0":
      //users
      var s = "";
      for (var i = 0; i < users_list.length; i++) {
        s += "<div id='divcheckuid" + users_list[i].id + "' style='height: 20px'><input type='checkbox' value='' onchange='checkbox_users(this, " + i + ")'";
        if (selected_users.indexOf(users_list[i].id) != -1) s += " checked";
        s += ">" + users_list[i].name + "</div>";
      }
      lst.html(s);
      break;
    case "1":
      //klients
      var s = "";
      for (var i = 0; i < clients_list.length; i++) {
        s += "<div id='divcheckcid" + clients_list[i].id + "' style='height: 20px'><input type='checkbox' value='' onchange='checkbox_clients(this, " + i + ")'";
        if (selected_clients.indexOf(clients_list[i].id) != -1) s += " checked";
        s += ">" + clients_list[i].name + "</div>";
      }
      lst.html(s);
      break;
  }
}

function checkbox_users(cb, num) {
  var id = users_list[num].id;
  if (jQuery(cb).attr('checked') == 'checked') {
    if (selected_users.indexOf(id) == -1) {
      selected_users.push(id);
    }
  } else {
    var ind = selected_users.indexOf(id);
    if (ind != -1) {
      selected_users.splice(ind, 1);
    }
  }
}

function checkbox_clients(cb, num) {
  var id = clients_list[num].id;
  if (jQuery(cb).attr('checked') == 'checked') {
    if (selected_clients.indexOf(id) == -1) selected_clients.push(id);
  } else {
    var ind = selected_clients.indexOf(id);
    if (ind != -1) selected_clients.splice(ind, 1);
  }
}

function check_adr_field(nm) {
  var t = jQuery('#' + nm).val();
  var expr = /[^0-9uk ;,]{1,}/gi;

  if (expr.test(t)) {
    t = t.replace(expr, "");
    jQuery('#' + nm).val(t);
  }
}

function insert_addresses(ret_name) {
  var fld = jQuery(window.opener.document.getElementById(ret_name));
  var s = "";
  var i;
  for (i = 0; i < selected_users.length; i++)
    s += "u" + selected_users[i] + "; ";
  for (i = 0; i < selected_clients.length; i++)
    s += "k" + selected_clients[i] + "; ";
  fld.val(s);
}

function search_fld_focus(obj) {
  if (jQuery(obj).val() == 'Поиск') {
    jQuery(obj).val('');
    jQuery(obj).css("color", "black");
  }
}

function search_fld_blur(obj) {
  if (jQuery(obj).val() == '') {
    jQuery(obj).val('Поиск');
    jQuery(obj).css("color", "silver");
  }
}

function search_fld_keyup(obj) {
  var t = jQuery(obj).val();
  if ((t == "") || (t == "Поиск")) {

    switch (show_adr_type) {
      case '0':
        for (var i = 0; i < users_list.length; i++)
          jQuery('#divcheckuid' + users_list[i].id).show();
        break;
      case '1':
        for (var i = 0; i < clients_list.length; i++)
          jQuery('#divcheckcid' + clients_list[i].id).show();
        break;
    }

  } else {

    switch (show_adr_type) {
      case '0':
        for (var i = 0; i < users_list.length; i++) {
          if (users_list[i].name.toLowerCase().indexOf(t.toLowerCase()) == -1)
            jQuery('#divcheckuid' + users_list[i].id).hide(); else
            jQuery('#divcheckuid' + users_list[i].id).show();
        }
        break;
      case '1':
        for (var i = 0; i < clients_list.length; i++)
          if (clients_list[i].name.toLowerCase().indexOf(t.toLowerCase()) == -1)
            jQuery('#divcheckcid' + clients_list[i].id).hide(); else
            jQuery('#divcheckcid' + clients_list[i].id).show();
        break;
    }

  }

}

function load_message_text(to_obj, id) {
  var obj = jQuery("#" + to_obj);
  obj.toggle();
  if (obj.html() == "") {
    obj.html("<font color='blue'>загрузка...</font>");
    jQuery.post("/modules/mls/_gettext.php", {num:id}, function (data) {
      obj.html(data);
    });
  }
}

function email_notification_delete(id) {
  window.locationLocked = true;
  if (confirm('Вы уверены, что хотите удалить запись?') ) {
    $.ajax({
      url: '?section=mls&subsection=5&delete='+id,
      beforeSend: function( xhr ) {
        xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
      }
    });
    window.location.reload();
  }
}

function ctrl_inbox_selected()
{
	var cbs = $('.mls_row_selector:checked');
	if (cbs.length)
	{
		// show group menu
		cgui_bpanel_enable( "cgui_bpanel_idu_MlsGrpCmds" );
	}
	else
	{
		// hide group menu
		cgui_bpanel_disable( "cgui_bpanel_idu_MlsGrpCmds" );
	}
}

function sel_all_messages()
{
	var cbs = $('.mls_row_selector');
	
	for (var i=0; i<cbs.length; i++)
	{
		jQuery(cbs[i]).prop("checked", true);
	}
	
	ctrl_inbox_selected();	
}

function sel_readed_messages()
{
	unsel_all_messages();
	
	var cbs = $('.mls_row_readed');
	
	for (var i=0; i<cbs.length; i++)
	{
		jQuery(cbs[i]).prop("checked", true);
	}
	
	ctrl_inbox_selected();	
}

function sel_unreaded_messages()
{
	var cbs = $('.mls_row_readed');
	
	for (var i=0; i<cbs.length; i++)
	{
		jQuery(cbs[i]).prop("checked", false);
	}
	
	cbs = $('.mls_row_unreaded');
	
	for (var i=0; i<cbs.length; i++)
	{
		jQuery(cbs[i]).prop("checked", true);
	}
	
	ctrl_inbox_selected();	
}

function unsel_all_messages()
{
	var cbs = $('.mls_row_selector');
	
	for (var i=0; i<cbs.length; i++)
	{
		jQuery(cbs[i]).prop("checked", false);
	}
	
	ctrl_inbox_selected();	
}

function make_mls_readed()
{
	var cbs = $('.mls_row_selector:checked');
	if (cbs.length)
	{
		var s = "";
		for (var i=0; i<cbs.length; i++)
		{
			var cb = jQuery(cbs[i]);
			var id = cb.parent().find('#message_id');
			if (cb.hasClass('mls_row_unreaded')) s += id.val() + ";";			
		}
						
		jQuery.post("index.php?section=mls&subsection=2", {ids_to_read: s}, function(data){
			document.location.reload();
		});
						
		return 0;	
	}
	return -1; // enable buttons
}

function move_mls_to_trash()
{
	var cbs = $('.mls_row_selector:checked');
	if (cbs.length)
	{
		var s = "";
		for (var i=0; i<cbs.length; i++)
		{
			var cb = jQuery(cbs[i]);
			var id = cb.parent().find('#message_id');
			s += id.val() + ";";			
		}
						
		jQuery.post("index.php?section=mls&subsection=2", {ids_to_trash: s}, function(data){
			document.location.reload();
		});
						
		return 0;	
	}
	return -1; // enable buttons
}

jQuery(function(){
	
	var cbs = $('.mls_row_selector_box');
		
	for (var i=0; i<cbs.length; i++)
	{
		var cb_box = $(cbs[i]);
		var cb = cb_box.find('input:checkbox');
		
		cb.bind("click", function(event)
		{ 
			event.stopPropagation();
			ctrl_inbox_selected();
		});
		
		cb_box.bind("click", function(event)
		{
			event.stopPropagation();
			var cb = $(this).find('input:checkbox');
			cb.prop( "checked", !cb.prop("checked") );
			ctrl_inbox_selected();	
		});
	}
	
	
	
});

