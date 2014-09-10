<?php

$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . $n . ".tmpl.php";

require_once(DIR_FS_DOCUMENT_ROOT . '/ext/PHPExcel/PHPExcel.php');

need_data('data_vuz');
need_data('data_worktypes');
need_data('data_napravl');
need_data('data_discip');
need_data('data_payments');
need_data('data_filials');

switch ($n) {
  default:
  case 1:
    include("list.php");
    break;
}