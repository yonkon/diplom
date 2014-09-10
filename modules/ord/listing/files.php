<?php

use Components\Entity\OrderFile;

if (!empty($_REQUEST['file']) && $_REQUEST['action'] == 'download') {
  $file = OrderFile::find($_REQUEST['file']);

  header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
  header("Content-Type: application/force-download");
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/download");
  header("Content-Description: File Transfer");

  $extension = pathinfo($file['name']);
  $extension = strtolower($extension['extension']);

  if (file_exists(DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension)) {
    echo file_get_contents(DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension);
    die();
  } else {
    $GUI->ERR("Файл " . DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension . " не найден");
    page_reloadSubSec();
  }
} elseif (!empty($_REQUEST['file']) && $_REQUEST['action'] == 'view') {
  $file = OrderFile::find($_REQUEST['file']);

  $extension = pathinfo($file['name']);
  $extension = strtolower($extension['extension']);
  switch ($extension) {
    case 'jpg':
    case 'jpeg':
    case 'gif':
    case 'png':
      header("Content-Type: image/jpeg");
      break;

    default:
      header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream");
      header("Content-Type: application/download");
      header("Content-Description: File Transfer");
      break;
  }

  if (file_exists(DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension)) {
    echo file_get_contents(DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension);
    die();
  } else {
    $GUI->ERR("Файл " . DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension . " не найден");
    page_reloadSubSec();
  }
} elseif (!empty($_REQUEST['file']) && $_REQUEST['action'] == 'delete') {
  $file = OrderFile::find($_REQUEST['file']);

  $extension = pathinfo($file['name']);
  $extension = strtolower($extension['extension']);

  OrderFile::delete($_REQUEST['file']);

  if (file_exists(DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension)) {
    @unlink(DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension);
    $GUI->OK("Файл " . DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension . " удален");
    page_reloadSubSec();
  } else {
    $GUI->ERR("Файл " . DIR_FS_ORDER_FILES . $_REQUEST['order'] . '/' . $_REQUEST['file'] . '.' . $extension . " не найден");
    page_reloadSubSec();
  }
}
$frm = $GUI->Form("Загрузить файл", "300", "100", CGUI_FORM_FLAG_MODAL);
$ypos = 0;
//$frm->Label("Макс. размер: ", 10, $ypos += 10);
$t = $frm->Hidden($order_id);
$t->linkName = 'order_id';

$frm->Label("Файл: ", 10, $ypos += 10);
$t = $frm->File(10, $ypos += 20, 280);
$t->linkName = 'file';

$frm->Label("Переименовать", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 280);
$t->linkName = 'new_name';

$frm->VLine(10, $ypos += 30, 280);
$frm->Button("Загрузить", 110, $ypos += 10, 80, true);
$frm->height = $ypos + 55;
$frm->OnExecute = "fp_loadfile";

$GUI->Vars["page_top"] = "<a href='#' onclick='cgui_form_modal(\"" . $frm->idname . "\");return false;'>Добавить файл</a>";

$GUI->Vars["page_hdr"] = "Файлы заказа №" . $order_id;
$tbl = $GUI->Table("ord_files");
$tbl->Width = "100%";
$tbl->DataMYSQL("order_files", "id, order_id, creator_id, created, name, size");
$tbl->FilterMYSQL("order_id = " . $order_id);
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10, 20, 50, 100, 0
));

$r = $tbl->NewColumn();
$r->Caption = "Номер";
$r->DoSort = true;
$r->Key = "id";

$r = $tbl->NewColumn();
$r->Caption = "Название";
$r->DoSort = true;
$r->Key = "name";
$r->Align = "left";

$r = $tbl->NewColumn();
$r->Caption = "Размер";
$r->DoSort = true;
$r->Key = "size";
$r->Align = "left";
$r->Format = CGUI_TABLE_FMT_SIZE;

$r = $tbl->NewColumn();
$r->Caption = "Добавил";
$r->DoSort = true;
$r->Key = "creator_id";
$r->Align = "left";
$r->Process = "tp_fls_creator";

$r = $tbl->NewColumn();
$r->Caption = "Дата добавления";
$r->DoSort = true;
$r->Key = "created";
$r->Align = "left";
$r->Format = CGUI_TABLE_FMT_DATETIME;

$r = $tbl->NewColumn();
$r->Caption = "Скачать";
$r->Process = "generate_file_link";

//    $r = $tbl->NewColumn();
//    $r->Caption = "Изменен";
//    $r->DoSort = true;
//    $r->Key = "id";
//    $r->Align = "left";

//