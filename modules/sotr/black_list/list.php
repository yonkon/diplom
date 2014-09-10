<?php

use Components\Classes\db;
use Components\Entity\EmployeeBlack;

//list
$Filter = $GUI->FltrCol("black_list", "data_users:conf_blacklistfltr");
$Filter->SrcTable = TABLE_USERS_BLACK;
$Filter->DstTable = "black_list_tmp_" . $_SESSION["user"]["data"]["id"];

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Должность";
$f->keyid = "group_id";
$f->multisel = true;
$d = array();
foreach ($groups as $k => $v) {
  $d[$k] = $v["name"];
}
$f->SetSelectData($d, "");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Филиал";
$f->keyid = "filial_id";
$d = $data_filials;
$d[0] = array('name' => 'не указан');
$f->SetSelectData($d, "name");

$Filter->MakeUserSets(3);

$Filter->Requests();
$Filter->Filtering();

$pan1 = $GUI->UPanel();
$pan1->Caption = "Фильтры";
$pan1->defOpen = $Filter->OpenPanel;
$pan1->AddHTML($Filter->GetHTML());

$tbl = $GUI->Table("sotr" . $n);
$tbl->DataMYSQL($Filter->DstTable);
//$tbl->FilterMYSQL("black_list = 1");
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10, 20, 50, 100, 0
));
$tbl->OnRowStart = "_on_row_start";
$tbl->Width = "100%";

if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=sotr&subsection=3&edit=%var%\"";
}

if (isset($_REQUEST["light"])) {
  $tbl->Highlite = array("id", intval($_REQUEST["light"]));
}

$columns_resource = get_role_columns($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"]);

if (!is_resource($columns_resource)) {
  $GUI->ERR($columns_resource);
  page_reload();
}

$new_columns = array();
$column_group_name = array();
while ($row = db::fetch_array($columns_resource)) {
  if ($row['group_internal_name'] != "") {
    $column_group_name[] = $row['group_internal_name'];
    $new_columns[$row['group_internal_name']]['custom'][] = $row;
  } else {
    $new_columns[] = $row;
  }
}

foreach ($new_columns as $column) {
  if (isset($column['internal_name']) && in_array($column['internal_name'], $column_group_name)) {
    continue;
  }
  if (isset($column['custom']) && count($column['custom'])) {
    $r = $tbl->NewColumn();
    foreach ($column['custom'] as $custom_column) {
      $r1 = new CGUI_TableColumn();
      $r1->Caption = $custom_column['name'];
      $r1->DoSort = $custom_column['do_sort'];
      $r1->Key = $custom_column['internal_name'];
      $r1->Align = $custom_column['align'];
      $r1->Process = $custom_column['on_execute'];
      $r->Custom[] = $r1;
    }
  } else {
    $r = $tbl->NewColumn();
    $r->Caption = $column['name'];
    $r->DoSort = $column['do_sort'];
    $r->Key = $column['internal_name'];
    $r->Align = $column['align'];
    $r->Process = $column['on_execute'];
  }
}