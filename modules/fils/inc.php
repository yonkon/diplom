<?php
$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . "1.tmpl.php";

require_once('functions.php');

switch ($n) {
  case 1:
    require_once('add.php');
    break;

  case 2:
    if (isset($_REQUEST["del"])) {
      require_once('delete.php');
    } elseif (isset($_REQUEST["edit"])) {
      require_once('edit.php');
    } else {
      require_once('list.php');
    }
    break;
}