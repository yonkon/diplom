jQuery.noConflict();
function add_visit_field(foradd, added){
  var ind = foradd.selectedIndex;
  if(ind > -1){
    while (foradd.options[ind].selected && (ind<foradd.options.length)){
      var opt = foradd.options[ind];
      var newopt = document.createElement("option");
      newopt.appendChild(document.createTextNode(opt.text));
      newopt.setAttribute("value", opt.value);
      added.appendChild(newopt);
      foradd.remove(ind);
    }
  }
}

function remove_visit_field(foradd, added){
  var ind = added.selectedIndex;
  if(ind > -1){
    while (added.options[ind].selected && (ind<added.options.length)){
      var opt = added.options[ind];
      var newopt = document.createElement("option");
      newopt.appendChild(document.createTextNode(opt.text));
      newopt.setAttribute("value", opt.value);
      foradd.appendChild(newopt);
      added.remove(ind);
    }
  }
}

function save_visit_fields(added){

  var off = new Array();

  for (var i=0; i<added.options.length; i++){
    off.push(added.options[i].value);
  }

  jQuery.post("?section=vis&subsection=2&visitfldscfg", { 'flds[]': off }, function(data){
    document.location.reload();
  });
}

function moveup_visit_field(){
  var sel = document.getElementById('visitfields_added');
  if (sel && (sel.selectedIndex > 0)){
    var opt1 = sel.options[sel.selectedIndex];
    var opt2 = sel.options[sel.selectedIndex-1];
    sel.insertBefore(opt1, opt2);
  }
}

function movedown_visit_field(){
  var sel = document.getElementById('visitfields_added');
  if (sel && (sel.selectedIndex>-1) && (sel.selectedIndex < (sel.options.length-1))){
    var opt1 = sel.options[sel.selectedIndex];
    var opt2 = sel.options[sel.selectedIndex+1];
    sel.insertBefore(opt2, opt1);
  }
}

function select_visit_field(){
  jQuery('#visit_table_flds_btn_up').removeAttr("disabled");
  jQuery('#visit_table_flds_btn_down').removeAttr("disabled");
}



function vis_get_user_visits(id,dt,mlist){
	var box = jQuery("#vis_user_busy_box");
	
	box.html("<font color=gray><i>запрос данных о занятости ...</i></font>");
  jQuery.post("/modules/vis/_checkvis.php", {uid:id,date:dt,ml:mlist}, function(data){
		box.html(data);	
	});
}


function vis_get_user_order_dolg(id,inp_id,dir_id){
  jQuery('#'+inp_id).attr('disabled', 'disabed');
  jQuery('#'+dir_id).attr('disabled', 'disabed');

  jQuery.post("/modules/vis/_getordmon.php", {oid:id}, function(data){
    jQuery('#'+inp_id).val(data);
    jQuery('#'+dir_id).val(0);
    jQuery('#'+inp_id).removeAttr('disabled');
    jQuery('#'+dir_id).removeAttr('disabled');
	});
}