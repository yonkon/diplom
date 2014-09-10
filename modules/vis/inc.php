<?php

$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . "1.tmpl.php";
page_ScriptNeed("scripts.js", "modules/vis");

require_once('functions.php');

need_data('data_filials');

switch ($n) {
  case 1:
    require_once('add.php');
    break;
  case 2:
    if (isset($_REQUEST['visit'])) {
      require_once('edit.php');
    } else {
      require_once('list.php');
    }
    break;
  case 3:
    break;
  default:
    break;
}