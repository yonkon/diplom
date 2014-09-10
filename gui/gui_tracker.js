
document.write("<div id='tracker_box' style='position: absolute; display:none; z-index:9999; width: 200px; height: 25px; background-color: #eee; border: 1px solid gray'><div style='position:absolute; margin-top: 12px; margin-left: 5px; height:0; width: 190px; border-bottom: 1px solid white; border-top: 1px solid silver'></div><div id='tracker_box_btn' style='position: absolute; background-color: #eee; width: 10px; height: 16px; margin-top: 3px; margin-left: 5px; border-top: 2px solid white; border-left: 2px solid white; border-right: 2px solid gray; border-bottom: 2px solid gray;'></div></div>");


var tracker_jelem = false;
var tracker_minval;
var tracker_maxval;
var tracker_jbox = false;
var tracker_jbox_btn = false;
var tracker_mouse_downed = 0;
var tracker_mouse_capx = 0;
var tracker_cur_pos = 0;

jQuery(function(){
	
	jQuery(window).resize(function(){
		if(tracker_jbox) tracker_jbox.hide();
	});
	
	jQuery(document).mousedown(function(e){
		if (!tracker_jbox_btn) return;
		
		var mx = e.clientX + jQuery(document).scrollLeft();
		var my = e.clientY + jQuery(document).scrollTop();
		
		var l = tracker_jbox_btn.offset().left;
		var t = tracker_jbox_btn.offset().top;
		
		if ((mx >= l) && (mx <= (l+14)) && (my >= t) && (my <= (t+20))){
			tracker_mouse_downed = 1;
			tracker_mouse_capx = mx;
		}
	});

	jQuery(document).mouseup(function(){
		tracker_mouse_downed = 0;
	});
	
	jQuery(document).mousemove(function(e){
		if (tracker_mouse_downed){
			var x = e.clientX + jQuery(document).scrollLeft();
			var dx = x - tracker_mouse_capx;
			var new_pos = tracker_cur_pos + dx;
			if (new_pos < 0) new_pos = 0;
			if (new_pos > 176) new_pos = 176;
			tracker_jbox_btn.css("margin-left", 5 + new_pos + "px");	
			dx = new_pos - tracker_cur_pos;
			tracker_cur_pos = new_pos;
			tracker_mouse_capx+= dx;
			var nval = Math.floor((tracker_maxval-tracker_minval)*(new_pos / 176)+tracker_minval);
			tracker_jelem.val(nval);
		}
	});	

	jQuery(document).click(function(){
		if (tracker_jbox) tracker_jbox.fadeOut();	
	});

});


function tracker(elem, minval, maxval){
	tracker_minval = minval;
	tracker_maxval = maxval;
	
	tracker_jbox = jQuery('#tracker_box');
	tracker_jbox_btn = jQuery('#tracker_box_btn');
	tracker_jelem = jQuery(elem);
	
	var val = tracker_jelem.val();
	if (val < tracker_minval) val = tracker_minval;
	if (val > tracker_maxval) val = tracker_maxval;
	tracker_jelem.val(val);
	
	tracker_cur_pos = 176 * (val - tracker_minval) / (tracker_maxval-tracker_minval);
	tracker_jbox_btn.css("margin-left", 5 + tracker_cur_pos + "px");
	tracker_jbox.css("left", tracker_jelem.offset().left+"px");
	tracker_jbox.css("top", tracker_jelem.offset().top + tracker_jelem.height() + 2 + "px");
	tracker_jbox.fadeIn();
	
	tracker_jelem.keyup(function(){
		var v = tracker_jelem.val();
		if (v < tracker_minval) v = tracker_minval;
		if (v > tracker_maxval) v = tracker_maxval;
		tracker_jelem.val(v);
		tracker_cur_pos = 176 * (v - tracker_minval) / (tracker_maxval-tracker_minval);
		tracker_jbox_btn.css("margin-left", 5 + tracker_cur_pos + "px");		
	});
	


}
