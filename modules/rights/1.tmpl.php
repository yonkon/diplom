<div style="margin-top:20px; padding: 5px">

<?php

use Components\Classes\db;

global $GUI;

if (isset($GUI->mmenu->selected->section) && $GUI->mmenu->selected->section == 'rights') {
  $result = array();

  $result[] = db::get_select("SELECT * FROM " . TBL_PREF . "roles WHERE id != 0 AND id != 1", "roles", "Выбирете группу");

  echo join($result, "\n");

?>

<form action="/modules/rights/rights.php" method="post">
  <div id="rights_wrap"></div>
  <input type="hidden" name="role_id" value="" id="role_id">
  <input type="submit" value="Сохранить">
</form>
</div>

<?php
}
?>