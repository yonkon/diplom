<?php

use Components\Classes\db;
use Components\Entity\Employee;

//list
if (isset($_REQUEST["sotrfldscfg"])) {
  if (isset($_POST["flds"])) {
    $_SESSION["user"]["data"]["conf_sotrfld"] = serialize($_POST["flds"]);
  } else {
    $_SESSION["user"]["data"]["conf_sotrfld"] = serialize(array());
  }

  Employee::update($_SESSION["user"]["data"]["id"], array(
    'conf_sotrfld' => $_SESSION["user"]["data"]["conf_sotrfld"],
  ));
  $_SESSION["sotrfldscfg"] = true;

  $GUI->OK("Выполнено");
  die("");
}

$Filter = $GUI->FltrCol("sotr", "data_users:conf_sotrfltr");
$Filter->SrcTable = TABLE_USERS;
$Filter->DstTable = "data_users_tmp_" . $_SESSION["user"]["data"]["id"];

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

$ryk_group_id = get_role_id_by_name('Руководитель');
$mysql_filter = '';
if ($_SESSION["user"]["data"]["group_id"] > $ryk_group_id) {
  $mysql_filter .= ' AND (filial_id = ' . $_SESSION["user"]["data"]["filial_id"] . ' OR filial_id = 0) AND group_id > 0';
}

$tbl->FilterMYSQL("black_list <> 1" . $mysql_filter);
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10,
  20,
  50,
  100,
  0
));
$tbl->OnRowStart = "_on_row_start";
$tbl->Width = "100%";
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=sotr&subsection=2&edit=%var%\"";
}

if (isset($_REQUEST["light"])) {
  $tbl->Highlite = array("id", intval($_REQUEST["light"]));
}

$rm = $tbl->CreateRowMenu();

if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
  $rm->AddCommand("Удалить", "?section=sotr&subsection=2&del=%1%");
}
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Написать")) {
  $rm->AddCommand("Написать", "?section=mls&subsection=1&_to=u%1%");
}
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "История переписки")) {
  $rm->AddCommand("История переписки", "?section=sotr&subsection=2&msgs=%1%");
}
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Занятость")) {
  $rm->AddCommand("Занятость", "?section=sotr&subsection=2&zan=%1%");
}
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $rm->AddCommand("Редактировать", "?section=sotr&subsection=2&edit=%1%");
}

$columns_resource = get_role_columns($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"]);

if (!is_resource($columns_resource)) {
  $GUI->ERR($columns_resource);
  page_reload();
}

$new_columns = $allcols = $column_group_name = array();
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
    $r = new CGUI_TableColumn();
    $allcols[$column['order']] = $r;
    $r->Caption = $column['name'];
    $r->DoSort = $column['do_sort'];
    $r->Key = $column['internal_name'];
    $r->Align = $column['align'];
    $r->Process = $column['on_execute'];
  }
}

$pan2 = $GUI->UPanel();
if (isset($_SESSION["sotrfldscfg"])) {
  unset($_SESSION["sotrfldscfg"]);
  $pan2->defOpen = true;
}

$pan2->Caption = "Поля таблицы";
$pan2->Html = "";

$flds_added = array();
if (!empty($_SESSION["user"]["data"]["conf_sotrfld"])) {
  // Используемые колонки
  $tmp_flds_added = unserialize($_SESSION["user"]["data"]["conf_sotrfld"]);
  foreach ($tmp_flds_added as $v) {
    if (array_key_exists($v, $allcols)) {
      $flds_added[] = $v;
    }
  }
} else {
  // По умолчанию
  $flds_added = array();
}

$flds_to_add = array();
foreach ($allcols as $k => $v) {
  if (!in_array($k, $flds_added)) {
    $flds_to_add[] = $k;
  }
}

$pan2->Html .= "<table style='text-align:left'><tr><td>Доступные поля</td><td></td><td>Выбранные поля</td><td></td></tr><tr>";
$pan2->Html .= "<td><select multiple id='sotrfields_foradd' style='width:200px' size='8'>";
foreach ($flds_to_add as $v) {
  $pan2->Html .= "<option value='" . $v . "'>" . $allcols[$v]->Caption . "</option>";
}
$pan2->Html .= "</select></td><td style='text-align:center; padding:4px'>" . "<input type='button' value='>' onclick='add_sotr_field(sotrfields_foradd, sotrfields_added)'><br>" . "<input type='button' value='<' onclick='remove_sotr_field(sotrfields_foradd, sotrfields_added)'></td>";

$pan2->Html .= "<td><select multiple id='sotrfields_added' style='width:200px' size='8' onchange='select_sotr_field()'>";
foreach ($flds_added as $v) {
  $pan2->Html .= "<option value='" . $v . "'>" . $allcols[$v]->Caption . "</option>";
  $tbl->Columns[] = $allcols[$v];
}
$pan2->Html .= "</select></td><td align='center'>" . "<input type='button' value='&uarr;' id='sotr_table_flds_btn_up' onclick='moveup_sotr_field()' disabled><br>" . "<input type='button' value='&darr;' id='sotr_table_flds_btn_down' onclick='movedown_sotr_field()' disabled>" . "</td></tr></table>";

$pan2->Html .= "<div style='border-bottom: 1px solid white; border-top: 1px solid silver; height: 0px; margin-top:10px'></div>" . "<div style='text-align:left; margin-left: 20px; margin-bottom: 10px; margin-top: 10px'><input type='button' value='Применить' onclick='save_sotr_fields(sotrfields_added)'></div>";