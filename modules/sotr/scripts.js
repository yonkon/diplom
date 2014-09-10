function add_sotr_field(foradd, added){
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

function remove_sotr_field(foradd, added){
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

function save_sotr_fields(added){

	var off = new Array();
	
	for (var i=0; i<added.options.length; i++){
		off.push(added.options[i].value);
	}

  jQuery.post("?section=sotr&subsection=2&sotrfldscfg", { 'flds[]': off }, function(data){
		document.location.reload();
	});
}

function moveup_sotr_field(){
	var sel = document.getElementById('sotrfields_added');
	if (sel && (sel.selectedIndex > 0)){
		var opt1 = sel.options[sel.selectedIndex];
		var opt2 = sel.options[sel.selectedIndex-1];
		sel.insertBefore(opt1, opt2);
	}	
}

function movedown_sotr_field(){
	var sel = document.getElementById('sotrfields_added');
	if (sel && (sel.selectedIndex>-1) && (sel.selectedIndex < (sel.options.length-1))){
		var opt1 = sel.options[sel.selectedIndex];
		var opt2 = sel.options[sel.selectedIndex+1];
		sel.insertBefore(opt2, opt1);
	}	
}

function select_sotr_field(){
  jQuery('#sotr_table_flds_btn_up').removeAttr("disabled");
  jQuery('#sotr_table_flds_btn_down').removeAttr("disabled");
}

function check_is_author(author_group_id, group_field, author_group_field) {
  var group_field = $(group_field);
  var author_group_field = $(author_group_field);
  if (group_field.val() == author_group_id) {
    author_group_field.parent('div').show().prev('div').prev('div').show();
    author_group_field.parent('div').nextAll().css({marginTop: "+=45"});
    author_group_field.closest('form').closest('div').css({height: "+=45"});
  } else {
    if (!author_group_field.parent('div').is(':hidden')) {
      author_group_field.parent('div').nextAll().css({marginTop: "-=45"});
      author_group_field.closest('form').closest('div').css({height: "-=45"});
      author_group_field.parent('div').hide().prev('div').prev('div').hide();
    }
  }
}