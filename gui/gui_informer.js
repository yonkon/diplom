var cgui_informer_tm = false;

function cgui_informer_tmev(){
	jQuery("#cgui_informer").fadeOut("slow");
}

jQuery(function(){
	cgui_informer_tm = setTimeout("cgui_informer_tmev()", 10000);
	jQuery("#cgui_informer").click(function(){
		if (cgui_informer_tm){
			clearTimeout(cgui_informer_tm);
			jQuery("#cgui_informer").fadeOut("slow");
		}
	});
});