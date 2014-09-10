<div style="margin-top:20px; padding: 5px">
  <center>

    <?php
    if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'show_history') {
      ?>
      <div style="float: left; margin: 0 300px;" id="before_change">
        <?php print $this->forms[0]->GetHTML(); ?>
      </div>
      <div style="float: left;" id="after_change">
        <?php print $this->forms[1]->GetHTML(); ?>
      </div>
      <?php
    } else {
      foreach ($this->panels as $key => $value) {
        print $this->panels[$key]->GetHTML() . "<br>";
      }
//      if (isset($this->panels[0])) {
//        print $this->panels[0]->GetHTML();
//      }
      if (isset($this->tables[0])) {
        $this->tables[0]->PrintTable();
      }
      if (isset($this->forms[0])) {
        print $this->forms[0]->GetHTML();
      }
      if (isset($this->forms[1])) {
        print $this->forms[1]->GetHTML();
      }
    }
    ?>

  </center>
</div>