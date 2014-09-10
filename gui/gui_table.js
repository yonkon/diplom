// JavaScript Document
var __cgui_tablecellinfo_last = false;
var __cgui_tablecellinfo_timer = false;

function cgui_tablecellinfo_show(objname){
	var obj = jQuery('#'+objname);
	var lnk = jQuery('#'+objname+"_link");
	
	obj.css("left", lnk.position().left);
	
	if (obj == __cgui_tablecellinfo_last){
		if (__cgui_tablecellinfo_timer){
			clearTimeout(__cgui_tablecellinfo_timer);
			__cgui_tablecellinfo_timer = false;
		}
		return;
	}
	
	if (__cgui_tablecellinfo_last){
		__cgui_tablecellinfo_last.hide();
		clearTimeout(__cgui_tablecellinfo_timer);
		__cgui_tablecellinfo_timer = false;
		obj.show();
	} else {
		obj.fadeIn("fast");
	}
	__cgui_tablecellinfo_last = obj;
}

function __cgui_tablecellinfo_hide(objname){
	var obj = jQuery('#'+objname);
	obj.fadeOut("fast");
	__cgui_tablecellinfo_last = false;
}

function cgui_tablecellinfo_hide(objname){
	__cgui_tablecellinfo_timer = setTimeout("__cgui_tablecellinfo_hide('"+objname+"')", 250);
}

var _cgui_tablerowmenu_tmr = false;
var _cgui_tablerowmenu_obj = false;

function _cgui_tablerowmenu_close(){
	if (_cgui_tablerowmenu_obj) _cgui_tablerowmenu_obj.hide();
	if (_cgui_tablerowmenu_tmr) clearTimeout(_cgui_tablerowmenu_tmr);
	_cgui_tablerowmenu_tmr = false;
}

function cgui_tablerowmenu_show(menu){
	if (menu) {
		if (menu != _cgui_tablerowmenu_obj) _cgui_tablerowmenu_close();			
		_cgui_tablerowmenu_obj = menu;
	}
	
	if (_cgui_tablerowmenu_obj){
		_cgui_tablerowmenu_obj.show();
		if (_cgui_tablerowmenu_tmr){
			clearTimeout(_cgui_tablerowmenu_tmr);
			_cgui_tablerowmenu_tmr = false;
		}
	}
}

function cgui_tablerowmenu_close(){
	if (_cgui_tablerowmenu_tmr){
		clearTimeout(_cgui_tablerowmenu_tmr);
	}
	_cgui_tablerowmenu_tmr = setTimeout("_cgui_tablerowmenu_close()", 250);
}

jQuery(function(){
	var d = jQuery("div.cgui_table_rowmenu");
	
	for (var i=0; i<d.length; i++){
		
		var dd = jQuery(d[i]);
		
		jQuery(dd.find("div:first")).bind("mouseout", function(){ 
			cgui_tablerowmenu_close();
		});
		
		jQuery(dd.find("div:first")).bind("mouseover", function(){ 
			cgui_tablerowmenu_show(0);
		});
		
		jQuery(dd.find("div.cgui_table_rowmenu_menuitem")).bind("click", function(){
			_cgui_tablerowmenu_close();
		});
		
		jQuery(dd.find("div.cgui_table_rowmenu_menuitem")).bind("mouseover", function(){
			var t = jQuery(this);
			t.addClass("over");
			cgui_tablerowmenu_show(0);
		});
		
		jQuery(dd.find("div.cgui_table_rowmenu_menuitem")).bind("mouseout", function(){
			var t = jQuery(this); 
			t.removeClass("over");
			cgui_tablerowmenu_close();
		});
		
		jQuery(dd.find("div.cgui_table_rowmenu_menusplit")).bind("mouseover", function(){
			cgui_tablerowmenu_show(0);
		});
		
		jQuery(dd.find("div.cgui_table_rowmenu_menusplit")).bind("mouseout", function(){
			cgui_tablerowmenu_close();
		});
		
		dd.click(function(event){
			event.stopPropagation();
			//show menu
			var m = jQuery(this).find("div:first");
			cgui_tablerowmenu_show(m);
		});
	}
	
});
