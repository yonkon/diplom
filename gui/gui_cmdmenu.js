// JavaScript Document

var cgui_cmdmenu_tmr = false;
var cgui_cmdmenu_dir = 0;

function cgui_cmdmenu_move(){
	
	var x = jQuery("#cgui_commandmenu").position().left;

	if (cgui_cmdmenu_dir==1){
		if (x==0) {
			cgui_cmdmenu_dir = 0;
		} else {
			var dx = (0 - x)*0.5;
			if (dx < 1) dx = -x;
		}
		
		jQuery("#cgui_commandmenu").css("left", x+dx+"px");
	}

	if (cgui_cmdmenu_dir==-1){
		if (x==-186) {
			cgui_cmdmenu_dir = 0;
		} else {
			var dx = (-186 - x)*0.5;
			if (dx > -1) dx = -186-x;
		}
		jQuery("#cgui_commandmenu").css("left", x+dx+"px");
	}

	
	if (cgui_cmdmenu_dir!=0) setTimeout("cgui_cmdmenu_move()", 25);
}

function cgui_cmdmenu_show(){
	if (cgui_cmdmenu_tmr){
		clearTimeout(cgui_cmdmenu_tmr);
		cgui_cmdmenu_tmr = false;
	}
	
	cgui_cmdmenu_dir = 1;
	cgui_cmdmenu_move();
	//jQuery("#cgui_commandmenu").css("left", "0");
}

function _cgui_cmdmenu_hide(){
	cgui_cmdmenu_dir = -1;
	cgui_cmdmenu_move();
	//jQuery("#cgui_commandmenu").css("left", "-186px");
}

function cgui_cmdmenu_hide(){
	cgui_cmdmenu_tmr = setTimeout("_cgui_cmdmenu_hide();", 100);
}



jQuery(function(){
	
		var bar = jQuery("#cgui_commandmenu .cgui_commandmenu_bar");
		var cont = jQuery("#cgui_commandmenu .cgui_commandmenu_cont");
		
		cont.mouseout(function(){
			cgui_cmdmenu_hide();
		});
		
		cont.mouseover(function(){
			cgui_cmdmenu_show();
		});
		
		bar.mouseout(function(){
			cgui_cmdmenu_hide();
		});
		
		bar.mouseover(function(){
			cgui_cmdmenu_show();
		});
		
		var itms = cont.find(".item");
		for (var i=0; i<itms.length; i++){
			
			jQuery(itms[i]).mouseover(function(){
				jQuery(this).addClass("ovr");
			});
			
			jQuery(itms[i]).mouseout(function(){
				jQuery(this).removeClass("ovr");
			});

		}



});