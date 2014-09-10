<script>

function update_klient_search_filter(gridnm, old, m1, m2, m3, m4, m5){
	var old_id = -1;
	if (old!=-1) {
		cgui_form_gridsel_unselect(gridnm, old);
		old_id = jQuery('#'+gridnm+'_'+old+'_0').text();
	}

	
	var m1_text = jQuery('#'+m1).val();
	var m2_text = jQuery('#'+m2).val();
	var m3_text = jQuery('#'+m3).val();
	var m4_text = jQuery('#'+m4).val();
	var m5_text = jQuery('#'+m5).val();

	var elms = jQuery('#'+gridnm+'_table tr');
	var cnt = elms.length;
	
	for (var i=0; i<cnt; i++){
		var id = jQuery('#'+gridnm+'_'+i+'_0').text();
		var name = jQuery('#'+gridnm+'_'+i+'_1').text();
		var mail = jQuery('#'+gridnm+'_'+i+'_2').text();
		var tel = jQuery('#'+gridnm+'_'+i+'_3').text();
		var ref_code = jQuery('#'+gridnm+'_'+i+'_4').text();
		var tr = jQuery('#'+gridnm+'_'+i);
		
		var vis = true;
		
		if (m1_text!=""){
			if (id.indexOf(m1_text)==-1) vis = false;
		}

		if (m2_text!=""){
			if (name.indexOf(m2_text)==-1) vis = false;
		}

		if (m3_text!=""){
			if (mail.indexOf(m3_text)==-1) vis = false;
		}

		if (m4_text!=""){
			if (tel.indexOf(m4_text)==-1) vis = false;
		}

    if (m5_text!=""){
      if (ref_code.indexOf(m5_text)==-1) vis = false;
    }
		
		if (vis)
			tr.show();
		else
			tr.hide();
				
	}
	
	return -1;
}

</script>

<?php echo $this->Vars["kln_search_modal_form"]->GetHTML()?>