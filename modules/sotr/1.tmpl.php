<div style="margin-top:20px; padding: 5px">
  <center>

    <?php
    if (isset($this->panels[0])) {
      print $this->panels[0]->GetHTML();
    }
    if (isset($this->panels[1])) {
      print "<br>" . $this->panels[1]->GetHTML();
    }
    if (isset($this->tables[0])) {
      $this->tables[0]->PrintTable();
    }
    if (isset($this->tables[1])) {
      print "<br>";
      $this->tables[1]->PrintTable();
    }
    if (isset($this->forms[0])) {
      print $this->forms[0]->GetHTML();
    }
    ?>

  </center>
</div>