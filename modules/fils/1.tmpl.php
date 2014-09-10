<div style="margin-top:20px;"><center>

<?php
if (isset($this->tables[0])) $this->tables[0]->PrintTable();
  if (isset($this->forms[0]))print $this->forms[0]->GetHTML();
  if (isset($this->forms[1]))print $this->forms[1]->GetHTML();
?>

</center></div>