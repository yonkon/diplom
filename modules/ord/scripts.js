jQuery.noConflict();
(function($) {
  $(function() {
    if ($('#before_change').html() != null) {
      for (var i = 0; i <= $('#before_change .cgui_form_text').size(); i++) {
        if ($('#before_change #cgui_form_0_field_'+i+'').val() != $('#after_change #cgui_form_2_field_'+i+'').val()) {
          $('#after_change #cgui_form_2_field_'+i+'').css('background-color','red');
        }
      }

      for (i = 0; i <= 9; i++) {
        if ($('#cgui_form_oform_editor_before_field_'+i+'').val() != $('#cgui_form_oform_editor_field_'+i+'').val()) {
          $('#cgui_form_oform_editor_field_'+i+'').css('background-color','red');
        }
      }
    }

    function discipline_select2_format(item) {
      if (typeof item.authors_qt != 'undefined') {
        return item.name + '(' + item.authors_qt + ')';
      } else {
        return item.name;
      }
    }

    function sort_by(field, reverse, primer){
      var key = primer ?
        function(x) {return primer(x[field])} :
        function(x) {return x[field]};

      reverse = [-1, 1][+!!reverse];

      return function(a, b) {
        return a = key(a), b = key(b), reverse * ((a > b) - (b > a));
      }
    }

    if (typeof $('.discipline_select2').get(0) != 'undefined') {
      $('.discipline_select2').select2({
        data: {
          results: (typeof disciplines != 'undefined' ? disciplines : {}),
          text: 'name'
        },
        formatSelection: discipline_select2_format,
        formatResult: discipline_select2_format,
        createSearchChoice: function(term) {
          return {
            id: term,
            name: term,
            authors_qt: 'свой вариант'
          };
        },
        containerCss: {
          position: 'absolute',
          marginTop: '190px',
          marginLeft: '190px',
          width: '440px'
        },
        sortResults: function(results, container, query) {
          if (query.term) {
            return results.sort(sort_by('name', true, function(a){return a.length}));
          }
          return results;
        }
      });
    }
  });
})(jQuery);
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

var joform_flds=[];

function show_oform_editor(frmnm, first, cnt, form_id) {
	if (!form_id) {
    form_id = 'cgui_form_oform_editor';
  }
	joform_flds = [];
	for (var i=0; i<cnt; i++){
		joform_flds.push( jQuery('#'+frmnm+'_field_'+(first+i)) );
    jQuery('#cgui_form_oform_editor_field_'+i).val(joform_flds[i].val());
	}
		
	cgui_form_modal(form_id);
}

function closeok_oform_editor(){
	for(var i=0; i<joform_flds.length; i++){
		joform_flds[i].val(jQuery('#cgui_form_oform_editor_field_'+i).val());
	}

  jQuery.modal.close();
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

  jQuery.post("?section=ord&subsection=2&ordfldscfg", { 'flds[]': off }, function(data){
		document.location.reload();
	});
}

function save_order_archive_fields(added){

    var off = new Array();

    for (var i=0; i<added.options.length; i++){
        off.push(added.options[i].value);
    }

    jQuery.post("?section=ord&subsection=3&ordarchfldscfg", { 'flds[]': off }, function(data){
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

function field_autocomplete(field_id, entity) {
  jQuery('#' + field_id).autocomplete({
    minLength: 2,
    source: '/index.php?section=ord&subsection=autocomplete&entity=' + entity
  });
}