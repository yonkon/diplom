
document.write("<style> .timepicker_box { position:absolute; display:none; margin-top: 20px; margin-left:0px; border: 1px solid gray; background-color: white; width:200px; height:70px; z-index: 2; overflow:hidden; font-size: 8pt; } </style>");
document.write("<style> .timepicker_line { margin-top:5px; } </style>");
document.write("<style> .timepicker_cell { cursor:pointer; background: #eee; float:left; border: 1px solid silver; width:20px; margin-right:1px; margin-left:1px; text-align:center; } </style>");
document.write("<style> .timepicker_ovr_cell { background-color: #ccf;} </style>");
document.write("<style> .timepicker_sel_cell { background-color: #00f; color: white; } </style>");
document.write("<style> .timepicker_header { margin-top: 4px; margin-left: 2px; color: gray; } </style>");


var timepicker_active_div = false;
var timepicker_active_fld = false;
var timerpicker_sel_hr = 0;
var timerpicker_sel_mn = 0;

jQuery(document).bind("click", function(e){
  if (timepicker_active_fld === false) {
    return true;
  }

	var t = jQuery(e.target);
	//alert(t.attr('id') + ' ' + timepicker_active_fld.attr('id'));
	if (t.attr('id')==timepicker_active_fld.attr('id')) return false;
	if (t.closest("#"+timepicker_active_div.attr('id')).length) return false;
	
	if (timepicker_active_div){
		timepicker_active_fld.removeAttr("readonly");
		timepicker_active_div.fadeOut(200);
		timepicker_active_div = false;
		timepicker_active_fld = false;
	}
	
});

function timepicker_returntime(cell){
	if (timepicker_active_fld){
		
		var divs = jQuery(cell).parent().find("div");
		for(var i=0; i<divs.length; i++)
      jQuery(divs[i]).removeClass("timepicker_sel_cell");
		
		var h = timerpicker_sel_hr.toString();
		if (h.length==1) h = '0'+h;
		var m = timerpicker_sel_mn.toString();
		if (m.length==1) m = '0'+m;
		var tm = h+":"+m;
		timepicker_active_fld.val(tm);
    jQuery(cell).addClass("timepicker_sel_cell");
		var fnm = "timepicker_se_"+timepicker_active_fld.attr("id")+"('"+tm+"');";
		eval(fnm);
	}
}


var timepicker_moveobj = false;
var timepicker_movespd = 0;
var timepicker_movelimit = 0;
var timepicker_moveleft = 0;
var timepicker_fld = false;

function timepicker_mout(){
	timepicker_moveobj = false;
	timepicker_movespd = 0;
}

function _timepicker_move(){
	if (timepicker_moveobj){
		
		timepicker_moveleft-= timepicker_movespd/100;
		
		if (timepicker_moveleft >= 0){
			timepicker_moveleft = 0;
			timepicker_moveobj.style.marginLeft = "0";
			return;
		}

		if (timepicker_moveleft <= timepicker_movelimit){
			timepicker_moveleft = timepicker_movelimit;
			timepicker_moveobj.style.marginLeft = timepicker_moveleft+"px";
			return;
		}
		
		timepicker_moveobj.style.marginLeft = timepicker_moveleft+"px";
		
		setTimeout("_timepicker_move()", 50);	
	}
}

function timepicker_start_move(obj, spd){
	timepicker_movespd = spd;
	timepicker_moveobj = obj;
	timepicker_movelimit = 200 - jQuery(obj).width();
	timepicker_moveleft = timepicker_moveobj.style.marginLeft;
	if (timepicker_moveleft=="") timepicker_moveleft = 0;
	timepicker_moveleft = parseInt(timepicker_moveleft,10);
	
	setTimeout("_timepicker_move()", 100);
}

function timepicker_mmove(event, obj, prnt){

	var x = event.clientX - jQuery("#"+prnt).offset().left - 100;
	if (x > 50){
		timepicker_start_move(obj, x-50);
	} else if (x < -50){
		timepicker_start_move(obj, x+50);
	} else {
		timepicker_mout();	
	}
	
}

function timepicker_makelike(first, last, step, name, varname){
	var s = "<div id='"+varname+"_div' class='timepicker_line' style='width:"+ 24*(last-first+1)/step +"px' onmousemove='timepicker_mmove(event, this, \""+name+"\")' onmouseleave='timepicker_mout()'>";
	
	var x = first;
	while ( x <= last ){
		s+= "<div class='timepicker_cell' onclick='"+varname+"="+x+"; timepicker_returntime(this);' onmouseover='jQuery(this).addClass(\"timepicker_ovr_cell\")' onmouseout='jQuery(this).removeClass(\"timepicker_ovr_cell\")'>"+x+"</div>";
		x+= step;
	}
	s+= "<div style='clear:both'></div></div>";	
	return s;
}

function timepicker_select_cell(parent, name, val){
	var l = jQuery("#"+parent+"_timepicker").find("#"+name+":first");
	var d = l.find("div");
	
	for (var i=0; i<d.length; i++){
		var c = jQuery(d[i]);
		if (i==val)
			c.addClass("timepicker_sel_cell");
		else
			c.removeClass("timepicker_sel_cell");
	}
	
	var x = -24*val+90;
	if (x>0) x=0;
	var lim =  200 - l.width();
	if ((lim<0) && (x<lim)) x = lim;	
	l.css("margin-left", x+"px");
	
	
}

function timepicker_init(name, tmin, tmax, step){

	var fld = jQuery("#"+name);
	var div = jQuery("#"+name+"_timepicker");
	
	var lims = "";
	if ((tmin>0) || (tmax<1380)){
		lims = "от ";
		var h = Math.floor(tmin/60);
		var m = tmin - 60*h;
		if (h.toString().length==1) h = "0"+h;
		if (m.toString().length==1) m = "0"+m;
		lims+= h+":"+m+" до ";
		h = Math.floor(tmax/60);
		m = tmax - 60*h;
		if (h.toString().length==1) h = "0"+h;
		if (m.toString().length==1) m = "0"+m;
		lims+= h+":"+m;
	}
	
	
	div.html( "<div class='timepicker_header'>Выберите время: "+lims+"</div>" + timepicker_makelike(0,23,1,name,"timerpicker_sel_hr") + timepicker_makelike(0,59,step,name,"timerpicker_sel_mn") );
				
	fld.bind("focus", function(){
	
		if (timepicker_active_fld && (name == timepicker_active_fld.attr("id"))) return false;
		
		if (timepicker_active_div){
			timepicker_active_fld.removeAttr("readonly");
			timepicker_active_div.fadeOut(400);
			timepicker_active_div = false;
			timepicker_active_fld = false;
		}
		
		var t = fld.val();
		var tt = t.split(":");
		if (tt.length==2){
			timerpicker_sel_hr = parseInt(tt[0],10);
			timerpicker_sel_mn = parseInt(tt[1],10);
		} else {
			timerpicker_sel_hr = 0;
			timerpicker_sel_mn = 0;
		}
		
		timepicker_select_cell(name, "timerpicker_sel_hr_div", timerpicker_sel_hr);
		timepicker_select_cell(name, "timerpicker_sel_mn_div", timerpicker_sel_mn / step);
		
				
		fld.attr("readonly", "readonly");
		timepicker_active_fld = fld;
		div.fadeIn(400);
		timepicker_active_div = div;
		
		
	});
	
	
		
	
}
