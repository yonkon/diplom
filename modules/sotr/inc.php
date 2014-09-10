<?php

$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . "1.tmpl.php";

page_ScriptNeed("scripts.js", "modules/sotr");
page_ScriptNeed("gui_table.js", "gui");

require_once('functions.php');

global $data_groups;
$groups = $data_groups;
unset($groups[0]);

need_data('data_filials');

foreach($groups as $key => $group) {
  if ($_SESSION['user']['data']['group_id'] > $group['id']) {
    unset($groups[$key]);
  }
}

switch ($n) {
  case 1:
    require_once('add.php');
    break;

  case 2:
    if (isset($_REQUEST["msgs"])) {
      require_once('listing/messages.php');
    } else if (isset($_REQUEST["del"])) {
      require_once('listing/delete.php');
    } elseif (isset($_REQUEST["edit"])) {
      require_once('listing/edit.php');
    } elseif (isset($_REQUEST["zan"])) {
      require_once('listing/employment.php');
    } else {
      require_once('listing/list.php');
    }
    break;

  case 3:
    if (isset($_REQUEST["msgs"])) {
      require_once('listing/messages.php');
    } else if (isset($_REQUEST["del"])) {
      require_once('listing/delete.php');
    } elseif (isset($_REQUEST["edit"])) {
      require_once('black_list/show.php');
    } else {
      require_once('black_list/list.php');
    }
    break;
}