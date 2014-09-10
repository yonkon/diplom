<center>
<?php if(isset($this->Vars["login_message"])):?>
	<div style='width: 300px; padding: 20px; margin-top: 20px; border: solid 1px gray'><?php echo $this->Vars["login_message"]?></div>
<?php else: ?>
	<div style='height: 78px'></div>
<?php endif;?>

<div style="margin-top: 20px;">
	<?php echo $this->forms[0]->GetHTML();?>
</div>
</center>
