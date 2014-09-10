<div style="margin-top:20px; padding: 5px">
  <center>

    <?php

      foreach ($this->panels as $key => $value) {
        print $this->panels[$key]->GetHTML() . "<br>";
      }

    if (isset($this->Vars['add_expenses'])) {
      print $this->Vars['add_expenses'];
    }

    foreach ($this->tables as $key => $value) {
      print $this->tables[$key]->PrintTable();
    }

      if (isset($this->forms[0])) {
        print $this->forms[0]->GetHTML();
      }
      if (isset($this->forms[1])) {
        print $this->forms[1]->GetHTML();
      }
    ?>

  </center>
</div>