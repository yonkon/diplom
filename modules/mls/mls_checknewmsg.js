var mls_blinkmsgbtn_state=false;
var mls_blinkmsgbtn_obj;

function mls_blinkmessagebtn(){
	if(!mls_blinkmsgbtn_obj.hasClass("punkt_ovr")){
		mls_blinkmsgbtn_state = !mls_blinkmsgbtn_state;
		if (mls_blinkmsgbtn_state) 
			mls_blinkmsgbtn_obj.css("background-color", "yellow");
		else
			mls_blinkmsgbtn_obj.css("background-color", "");
	} else {
		mls_blinkmsgbtn_state=false;
		mls_blinkmsgbtn_obj.css("background-color", "");
	}
	
	setTimeout("mls_blinkmessagebtn()", 500);
}

function mls_checknewmessages(){
	
	jQuery.post("/modules/mls/_checknew.php", {}, function(data){
		if (data=="1"){
			mls_blinkmsgbtn_obj = jQuery("#cgui_mainmenu_section_mls");
			mls_blinkmessagebtn();
		} else
		setTimeout("mls_checknewmessages()", 60000);	
	});
	
}


jQuery(function(){
	setTimeout("mls_checknewmessages()", 1000);
});