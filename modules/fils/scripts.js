
function fils_disable_form(frm_id){
	var inps = jQuery("#"+frm_id).find("input,select,textarea,button");
	for (var i=0; i<inps.length; i++)
    jQuery(inps[i]).attr("disabled", "disabled");
}

function fils_add_day_info(frm_id, sel_id, ts_id, te_id, wd_id, id){
	
	fils_disable_form(frm_id);

	var sel = jQuery("#"+sel_id);
	var ts = jQuery("#"+ts_id);
	var te = jQuery("#"+te_id);
	var wd = 0;
	if(jQuery("#"+wd_id).attr("checked") == "checked") wd = "1";

  jQuery.post("/modules/fils/_editday.php", {cmd:"1", fid:id, d: sel.val(), o: ts.val(), c: te.val(), w: wd }, function(data){
		document.location.href = "?section=fils&subsection=2&edit="+id;
	});
}

function fils_reset_day(frm_id, day, id){
	fils_disable_form(frm_id);
  jQuery.post("/modules/fils/_editday.php", {cmd:"2", fid:id, d:day}, function(data){
		document.location.href = "?section=fils&subsection=2&edit="+id;
	});
}