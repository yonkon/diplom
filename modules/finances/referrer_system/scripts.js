var instant_edit_url = '/index.php?section=ord';

function order_filter_list(nmsel, nmtext){
	var sel = document.getElementById(nmsel);
	var txt = jQuery('#'+nmtext).val();
	txt = txt.toLowerCase();
	
	if (txt=="") return;
	
	for (var i=1; i<sel.options.length; i++){
		var t = sel.options[i].innerText;
		t = t.toLowerCase();
		if ( t.indexOf(txt) != -1){
			sel.selectedIndex = i;
			return;
		}
	}
	
	sel.selectedIndex = 0;
	
}

function order_filter_reset(nmsel, nmtext){
	var sel = document.getElementById(nmsel);
	if (sel.selectedIndex!=0) jQuery('#'+nmtext).val('');
}
/////////////////

function add_order_field(foradd, added){
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

function remove_order_field(foradd, added){
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

function save_order_fields(added){

	var off = new Array();
	
	for (var i=0; i<added.options.length; i++){
		off.push(added.options[i].value);
	}

  jQuery.post("?section=finances&subsection=3&ref_system_flds_cfg", { 'flds[]': off }, function(data){
		document.location.reload();
	});
}

function moveup_order_field(){
	var sel = document.getElementById('orderfields_added');
	if (sel && (sel.selectedIndex > 0)){
		var opt1 = sel.options[sel.selectedIndex];
		var opt2 = sel.options[sel.selectedIndex-1];
		sel.insertBefore(opt1, opt2);
	}	
}

function movedown_order_field(){
	var sel = document.getElementById('orderfields_added');
	if (sel && (sel.selectedIndex>-1) && (sel.selectedIndex < (sel.options.length-1))){
		var opt1 = sel.options[sel.selectedIndex];
		var opt2 = sel.options[sel.selectedIndex+1];
		sel.insertBefore(opt2, opt1);
	}	
}

function select_order_field(){
  jQuery('#ord_table_flds_btn_up').removeAttr("disabled");
  jQuery('#ord_table_flds_btn_down').removeAttr("disabled");
}