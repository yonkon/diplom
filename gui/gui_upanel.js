
function cgui_upanel_toggle(idname)
{
	var dv = jQuery('#'+idname);
	var lb = jQuery('#'+idname+'_lbl');
	
	if (dv.css("display")=="none"){
		dv.slideDown();
		lb.attr("class", "icon_exp");
	} else {
		dv.slideUp();
		lb.attr("class", "icon");
	}
}

function cgui_bpanel_enable(idname)
{
	var pn = jQuery('#'+idname);
	
	pn.css("visibility", "visible");
	pn.css("display", "block");
}

function cgui_bpanel_disable(idname)
{
	var pn = jQuery('#'+idname);
	
	pn.css("visibility", "hidden");
	//pn.css("disaply", "none");
}

function cgui_bpanel_btn_pressed(idname, func)
{
	var btns = jQuery('#'+idname).find('.cgui_bpanel_button');
	for (var i=0; i<btns.length; i++)
	{
		var b = jQuery(btns[i]);
		b.prop('disabled', true);
		b.attr('class', 'cgui_bpanel_button_dis');
	}
	
	var r = func();
	
	if (r > 0)
	{
		document.location.reload();
	}
	else if (r==0)
	{
		// nothong
	}
	else
	{
		//enable
		
		for (var i=0; i<btns.length; i++)
		{
			var b = jQuery(btns[i]);
			b.prop('disabled', false);
			b.attr('class', 'cgui_bpanel_button');
		}
	}
}
