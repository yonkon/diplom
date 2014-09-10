function cgui_filters_change_group(obj, uri, idname){
	document.location.href=uri+'&'+idname+'='+obj.value;
}

function cgui_filters_rename_grp(obj, uri, idname, nm){
	var newnm = prompt('Название набора', nm);
	if (newnm.length){
		
		document.location.href=uri+'&'+idname+'='+encodeURIComponent(newnm);
	}
}

function cgui_filters_addfilter(id, uri, idname){
	if (id==-1)return;
	document.location.href = uri+'&'+idname+'='+id;
}

function cgui_filters_remove_fltr(id, uri, idname){
	if (confirm('Удалить фильтр?'))
		document.location.href = uri+'&'+idname+'='+id;	
}

function cgui_filters_change_state(id, uri, idname){
	document.location.href = uri+'&'+idname+'='+id;	
}

function cgui_filters_submit_form(idname){
	var f = document.getElementById(idname+"_form");
	if (f){
		f.submit();	
	}
}
