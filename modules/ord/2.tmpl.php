<div style="margin-top:20px; padding: 5px">
  <div style="font-weight: bold; border-bottom: 1px dotted gray; margin-bottom: 10px">
  <?php if (isset($this->Vars["page_hdr"])) print $this->Vars["page_hdr"]?></div>
  <?php if (isset($this->Vars["page_top"])) print $this->Vars["page_top"]?>

  <?php
  if (isset($this->Vars["buttons"]) && is_array($this->Vars["buttons"])) {
    foreach ($this->Vars["buttons"] as $button) {
      print '<div style="margin: 10px 20px 10px 0; float: left;">' . $button . '</div>';
    }
    print '<div class="clear"></div>';
  }
  ?>

  <center>
    <?php
    if (isset($_REQUEST['p']) && $_REQUEST['p'] == 5 && isset($_REQUEST['change']) && !empty($_REQUEST['change'])) {
      ?>
      <div style="float: left; margin: 0 100px;" id="before_change">
        <?php print $this->forms[0]->GetHTML(); ?>
      </div>
      <div id="before_change_oform">
        <?php print $this->forms[1]->GetHTML(); ?>
      </div>
      <div style="float: left;" id="after_change">
        <?php print $this->forms[2]->GetHTML(); ?>
      </div>
      <div id="after_change_oform">
        <?php print $this->forms[3]->GetHTML(); ?>
      </div>
      <?php
    } else {

      if (isset($this->forms)) {
        foreach ($this->forms as $key => $value) {
          print $this->forms[$key]->GetHTML();
        }
      }
//      if (isset($this->forms[1])) {
//        print $this->forms[1]->GetHTML();
//      }
//      if (isset($this->forms[2])) {
//        print $this->forms[2]->GetHTML();
//      }
//      if (isset($this->forms[3])) {
//        print $this->forms[3]->GetHTML();
//      }
      if (isset($this->panels[0])) {
        print $this->panels[0]->GetHTML();
      }
      if (isset($this->panels[1])) {
        print "<br>" . $this->panels[1]->GetHTML();
      }
      if (isset($this->panels[2])) {
        print "<br>" . $this->panels[2]->GetHTML();
      }
      if (isset($this->tables[0])) {
        $this->tables[0]->PrintTable();
      }
      if (isset($this->tables[1])) {
        $this->tables[1]->PrintTable();
      }
    }
    ?>

  </center>
</div>