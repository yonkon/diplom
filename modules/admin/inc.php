<?php

if ($_SESSION["user"]["data"]["group_id"] == 0) {
  $n = $GUI->mmenu->selected->selected->section;
  $GUI->tmpls[] = $module_root . "1.tmpl.php";

  require_once('functions.php');

  switch ($n) {
    case 1:
      include("modules.php");
      break;
    case 2:
      include("submodules.php");
      break;
    case 3:
      include("commands.php");
      break;
    case 4:
      include("columns.php");
      break;
    default:
      break;
  }
}