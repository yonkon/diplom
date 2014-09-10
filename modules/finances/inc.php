<?php

$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . "1.tmpl.php";

need_data('data_filials');

require_once('functions.php');

switch ($n) {
  case 1:
    require_once('income/list.php');
    break;

  case 2:
    switch(@$_REQUEST['action']) {
      case 'add':
        require_once('expenses/add.php');
        break;

      case 'edit':
        require_once('expenses/edit.php');
        break;

      case 'autocomplete':
        require_once('expenses/autocomplete.php');
        break;

      default:
        require_once('expenses/list.php');
        break;
    }
    break;

  case 3:
    require_once('referrer_system/list.php');
    break;
}