<div style="margin-top:20px; padding: 5px">
	
	<?php
  		if (isset($this->Vars["tm_started"]))
		{
			print "Крайний запуск отправки: " . date("d.m.Y H:i:s", $this->Vars["tm_started"]) . " (прошло ".$this->Vars["tm_info"].")<br>";
			print "время работы: " . $this->Vars["tm_working"] . " сек.<br>";
			print "удачно / всего отправлено: " . $this->Vars["tm_goodcnt"] . "/" . $this->Vars["tm_allcnt"] . "<br>";
			print "осталось отправить: " . $this->Vars["tm_needcnt"] . "<br><br>";
		}
		
	?>
	
	
  <center>
  	 	  	
  	
    <?php
    	if (isset($this->forms[0])) 
    	{
    		print $this->forms[0]->GetHTML();
  		}
  	?>
    
    <?php
    	if (isset($_GET['mail_type']))
    	{
      		echo ($_GET['mail_type'] == 0 || $_GET['mail_type'] == 1) ? '<h1>Входящие</h1>' : '<h1>Исходящие</h1>';
    	}
    ?>
    
    
    <?php
		if (isset($this->Vars["buttons"]) && is_array($this->Vars["buttons"]))
		{
			foreach ($this->Vars["buttons"] as $button)
			{
  				print '<div style="margin: 10px 20px 10px 0; float: left;">' . $button . '</div>';
			}
			print '<div class="clear"></div>';
		}
  	
  	?>
    
    
    <?php
    	if (isset($this->tables[0]))
    	{
    		if (isset($this->panels[0]))
    		{
        		print $this->panels[0]->GetHTML();
				print "<br>";
      		}
		
    		$this->tables[0]->PrintTable();
  		}
  	?>

    <?php
    	if (isset($this->tables[1]))
    	{
    		print "<h1>Исходящие</h1>";
    		print "<br>";
    		$this->tables[1]->PrintTable();
  		}
  	?>
  	
  </center>
</div>
