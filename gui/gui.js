// JavaScript Document
var cgui_vforms = [];

function cgui_validator_none(obj){
	return true;
}

function cgui_btn_ovr(obj){
	if (jQuery(obj).attr("disabled")=="disabled") return;
	obj.className = "cgui_form_button_ovr";
}

function cgui_btn_def(obj){
	if (jQuery(obj).attr("disabled")=="disabled") return;
	obj.className = "cgui_form_button";
}

function cgui_btn_dis(obj){
	obj.className = "cgui_form_button_dis";
}


function cgui_form_disable_form(frm){
	var elms = frm.find("button");
	for (var i=0; i<elms.length; i++){
		var b = jQuery(elms[i]);
		cgui_btn_dis(elms[i]);
		b.attr("disabled", "disabled");
	}
		
	frm.find("input,textarea,select").attr("readonly", "readonly");
}

function cgui_form_validator(obj){
	
	var i;
	var n = obj.nn.value;
	for (i=0; i<cgui_vforms[n].length; i++){
		
		var fld = jQuery("#"+cgui_vforms[n][i][0]);
		
		
		if (!cgui_vforms[n][i][1](fld.val())){
			cgui_show_err_for_field(cgui_vforms[n][i][0], cgui_vforms[n][i][2]);
			return false;
		}
	}
	
	cgui_form_disable_form(jQuery(obj));
	return true;
}

function cgui_show_err_for_field(idname, text){
	var fld = jQuery("#"+idname);
	var box = jQuery("#"+idname+"_err");
	
	box.css('left', fld.width() + 10 + 'px');		
	box.text(text);
	box.fadeIn('slow');
	fld.focus(function(){ jQuery('#'+idname+'_err').fadeOut('slow'); });
	box.click(function(){ jQuery(this).fadeOut('slow'); });
	
	var sy = box.offset().top - jQuery(document).scrollTop();
	if (sy < 0) jQuery(document).scrollTop(box.offset().top);
}

jQuery(function(){
	var b=[];
	var i;
	
	b = jQuery(".cgui_form_button");
	
	for (i=0; i<b.length; i++){
		jQuery(b[i]).mouseover(function(){ cgui_btn_ovr(this); });
		jQuery(b[i]).mouseout(function(){ cgui_btn_def(this); });
	}
	
});


function cgui_form_modal(idname){
	jQuery('#'+idname).modal();
}


function cgui_form_gridsel_unselect(gridnm, old){
	if (old!=-1){
		jQuery('#'+gridnm+'_'+old).removeClass("sel");
	}	
}

function cgui_form_gridsel_click(gridnm, cur, old){
	cgui_form_gridsel_unselect(gridnm, old);
	jQuery('#'+gridnm+'_'+cur).addClass("sel");
}

