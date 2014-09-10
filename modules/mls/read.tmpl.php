<div style="margin-top:20px; padding: 5px; margin-left: 30px; margin-right:30px;"><center>
<?php
	
	if ($this->Vars["tmpl_mls_prew_lim"]){
		print '<div style="background-color: white; margin-bottom: 4px; border: 1px solid silver; padding: 5px; text-align:left; font-size:8pt;"><i>еще '.$this->Vars["tmpl_mls_prew_lim"].'</i></div>';
	}
	
	$__cnt = count($this->Vars["tmpl_mls_prew"]);
	if ($__cnt){
		for ($__i=$__cnt-1; $__i >=0; $__i--){
 			$v = $this->Vars["tmpl_mls_prew"][$__i];
			print '<div style="background-color: white; margin-bottom: 4px; border: 1px solid silver; padding: 5px; text-align:left; font-size:8pt; font-weight:bold; color:#999">'.
    		'<a href="#" style="color:#999" onclick="load_message_text(\'msg_prw_'.$__i.'\', '.$v["id"].');">Сообщение №'.$v["id"].' от '.date("d.m.y",$v["created"]).'. Отправитель: '.$v["sender"].' Тема: '.$v["subject"].'</a><div id="msg_prw_'.$__i.'" style="color:black; font-weight:normal; display:none"></div></div>';
		}
	}
?>
<div style="padding: 5px; text-align:left; background-color: #eee; border: 1px solid silver">
		
	<?php if ($this->Vars["type"] == "i"): ?><input style="margin-right: 15px" type="button" value="К списку" onclick="document.location.href='?section=mls&subsection=2'"><?php endif ?>
    <?php if ($this->Vars["type"] == "o"): ?><input style="margin-right: 15px" type="button" value="К списку" onclick="document.location.href='?section=mls&subsection=3'"><?php endif ?>
    <?php if ($this->Vars["type"] == "b"): ?><input style="margin-right: 15px" type="button" value="К списку" onclick="document.location.href='?section=mls&subsection=4'"><?php endif ?>
    
   
    <?php if ($this->Vars["type"]=="i"):?>
    <input style="margin-left: 5px" type="button" value="Ответить" onclick="document.location.href='?section=mls&subsection=1&_to=<?php echo $this->Vars["tmpl_ml"]["creator_id"]?>&_ans=<?php echo $this->Vars["tmpl_ml"]["id"]?>'">
    <?php endif ?>
    
    <input style="margin-left: 5px" type="button" value="Переслать" onclick="document.location.href='?section=mls&subsection=1&_rep=<?php echo $this->Vars["tmpl_ml"]["id"]?>'">
    
    <?php if ($this->Vars["type"]=="i"):?>
    <input style="margin-left: 5px" type="button" value="В корзину" onclick="if (confirm('Переместить в корзину?')) document.location.href='?section=mls&subsection=4&_add=<?php echo $this->Vars["tmpl_ml"]["id"]?>'">
    <?php endif ?>
</div>

<div style="background-color: white; border: 1px solid silver; padding: 10px">
<div style="text-align: left; font-size:8pt; margin-bottom: 10px; color:#999; font-weight: bold"><?php echo $this->Vars["tmpl_info"]?></div>
<div style="text-align: left; font-size:11pt; margin-bottom: 10px; margin-top:10px; color:#333; font-weight: bold"><?php echo $this->Vars["tmpl_subj"]?></div>
<div style="border: 1px solid silver; padding: 5px"><?php echo $this->Vars["tmpl_text"]?></div>
</div>

<?php
			
	$__cnt = count($this->Vars["tmpl_mls_after"]);
	if ($__cnt){
		for ($__i=0; $__i<$__cnt; $__i++){
 			$v = $this->Vars["tmpl_mls_after"][$__i];
			print '<div style="background-color: white; margin-top: 4px; border: 1px solid silver; padding: 5px; text-align:left; font-size:8pt; font-weight:bold;">'.
    		'<a href="#" style="color:#999" onclick="load_message_text(\'msg_aft_'.$__i.'\', '.$v["id"].');">Сообщение №'.$v["id"].' от '.date("d.m.y",$v["created"]).'. Отправитель: '.$v["sender"].' Тема: '.$v["subject"].'</a><div id="msg_aft_'.$__i.'" style="color:black; font-weight:normal; display:none"></div></div>';
		}
	}
	
	if ($this->Vars["tmpl_mls_after_lim"]){
		print '<div style="background-color: white; margin-top: 4px; border: 1px solid silver; padding: 5px; text-align:left; font-size:8pt;"><i>еще '.$this->Vars["tmpl_mls_after_lim"].'</i></div>';
	}
?>

</center></div>