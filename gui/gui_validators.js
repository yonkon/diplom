
function cgui_validator_noempty(value){
	return (value != "");
}

function cgui_validator_AZaz09(value){
	var re = new RegExp("[^a-z0-9]", "i");
	return (re.exec(value)==null);	
}

function cgui_validator_09(value){
	var re = new RegExp("[^0-9]", "i");
	return (re.exec(value)==null);	
}


function cgui_validator_email(value){
	
	function isValidEmail (email, strict){
		if (!strict) {
      email = email.replace(/^\s+|\s+$/g, '');
    }
		return (/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/i).test(email);
	}

	return isValidEmail(value, false);
}

function cgui_validator_nozero(value){
	return (value != 0);
}

function cgui_validator_telnum(value){
	return true;
}