<?php

use Components\Classes\db;

use Components\Entity\Employee;

$usr = $_SESSION["user"]["data"];

if (isset($_REQUEST["visitfldscfg"])) {
  if (isset($_POST["flds"])) {
    $_SESSION["user"]["data"]["conf_visitfld"] = serialize($_POST["flds"]);
  } else {
    $_SESSION["user"]["data"]["conf_visitfld"] = serialize(array());
  }

  Employee::update($_SESSION["user"]["data"]["id"], array(
    'conf_visitfld' => $_SESSION["user"]["data"]["conf_visitfld"],
  ));

  $_SESSION["visitfldscfg"] = true;
  $GUI->OK("Выполнено");
  die("");
}

$Filter = $GUI->FltrCol("vis", "data_users:conf_visfltr");
$Filter->SrcTable = TABLE_VISITS;
$Filter->DstTable = "data_visits_tmp_" . $_SESSION["user"]["data"]["id"];

// Добавляем фильтры
$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Место";
$f->keyid = "filial_id";
$f->SetSelectData($data_filials + array('-1' => array('name' => 'с курьером')), "name");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Статус";
$f->keyid = "status";
$f->multisel = true;
$f->SetSelectData($vis_statuses, "");
$flt_status_id = $f->id;

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Проводит";
$f->keyid = "user_id";
$d = array();
foreach ($data_users as $k => $u) {
  if (in_array($u["group_id"], array(1, 2, 3, 4, 5))) {
    $d[$k] = sotr_getFullName($u["id"]);
  }
}
$f->SetSelectData($d, "");
$flt_user_id = $f->id;

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Клиент";
$f->keyid = "client_id";
$f->SetSelectData(kln_getrawlist(), "fio");
$flt_kln_id = $f->id;

$f = $Filter->AddFilter("CGUI_FilterLastDate");
$f->name = "Просроченные";
$f->keyid = "date";

$f = $Filter->AddFilter("CGUI_FilterCurDate");
$f->name = "На сегодня";
$f->keyid = "date";
$flt_today_id = $f->id;

$f = $Filter->AddFilter("CGUI_FilterCurMonth");
$f->name = "На этот месяц";
$f->keyid = "date";
$flt_month_id = $f->id;

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Дата";
$f->keyid = "date";

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Кто назначил";
$f->keyid = "creator_id";
$f->SetSelectData($data_users, "fio");

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Дата назначения";
$f->keyid = "created";

need_data('stations', 'subway_stations');
$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Станция";
$f->keyid = "station_id";
$f->SetSelectData($stations, "name");

$f = $Filter->AddFilter("CGUI_FilterInteger");
$f->name = "Заказ";
$f->keyid = "order_id";
$f->hidden = true;
$flt_ord_id = $f->id;

$Filter->MakeUserSets(10);

$std = $Filter->MakeStdSet("Мои на сегодня");
$uf = $std->UseFilter($flt_user_id);
$uf->filter->value = $_SESSION["user"]["data"]["id"];
$std->UseFilter($flt_today_id);

$std = $Filter->MakeStdSet("Новые на этот месяц");
$std->UseFilter($flt_month_id);
$uf = $std->UseFilter($flt_status_id);
$uf->filter->value = 0;

// Если надо показать клиента - создаем временный набор. Он сохраняется только в сессии
if (isset($_REQUEST["kln"])) {
  $kln = intval($_REQUEST["kln"]);
  $ts = $Filter->MakeTmpSet();
  $uf = $ts->UseFilter($flt_kln_id);
  $uf->SetConf(array($kln));

  if (isset($_REQUEST["ord"])) {
    $ord = intval($_REQUEST["ord"]);
    $uf = $ts->UseFilter($flt_ord_id);
    $uf->SetConf(array($ord));
  }
}

$Filter->Requests();
$Filter->Filtering();

$pan1 = $GUI->UPanel();
$pan1->Caption = "Фильтры";
$pan1->defOpen = $Filter->OpenPanel;
$pan1->AddHTML($Filter->GetHTML());

$tbl = $GUI->Table("vis_list", array("cur_sort_up" => true));
$tbl->Width = "100%";
$tbl->DataMYSQL($Filter->DstTable);
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10,
  20,
  50,
  100,
  0
));

if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать") || user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Просмотр")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=vis&subsection=2&visit=%var%\"";
}

$columns_resource = get_role_columns($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"]);

if (!is_resource($columns_resource)) {
  $GUI->ERR($columns_resource);
  page_reload();
}

$column_group_name = $allcols = $new_columns = array();
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
if (isset($_SESSION["visitfldscfg"])) {
  unset($_SESSION["visitfldscfg"]);
  $pan2->defOpen = true;
}

$pan2->Caption = "Поля таблицы";
$pan2->Html = "";

$flds_added = array();
if (!empty($_SESSION["user"]["data"]["conf_visitfld"])) {
  // Используемые колонки
  $tmp_flds_added = unserialize($_SESSION["user"]["data"]["conf_visitfld"]);
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
$pan2->Html .= "<td><select multiple id='visitfields_foradd' style='width:200px' size='8'>";
foreach ($flds_to_add as $v) {
  $pan2->Html .= "<option value='" . $v . "'>" . $allcols[$v]->Caption . "</option>";
}
$pan2->Html .= "</select></td><td style='text-align:center; padding:4px'>" . "<input type='button' value='>' onclick='add_visit_field(visitfields_foradd, visitfields_added)'><br>" . "<input type='button' value='<' onclick='remove_visit_field(visitfields_foradd, visitfields_added)'></td>";

$pan2->Html .= "<td><select multiple id='visitfields_added' style='width:200px' size='8' onchange='select_visit_field()'>";
foreach ($flds_added as $v) {
  $pan2->Html .= "<option value='" . $v . "'>" . $allcols[$v]->Caption . "</option>";
  $tbl->Columns[] = $allcols[$v];
}
$pan2->Html .= "</select></td><td align='center'>" . "<input type='button' value='&uarr;' id='visit_table_flds_btn_up' onclick='moveup_visit_field()' disabled><br>" . "<input type='button' value='&darr;' id='visit_table_flds_btn_down' onclick='movedown_visit_field()' disabled>" . "</td></tr></table>";

$pan2->Html .= "<div style='border-bottom: 1px solid white; border-top: 1px solid silver; height: 0px; margin-top:10px'></div>" . "<div style='text-align:left; margin-left: 20px; margin-bottom: 10px; margin-top: 10px'><input type='button' value='Применить' onclick='save_visit_fields(visitfields_added)'></div>";

$fltr = '';
if ($_SESSION["user"]["data"]["group_id"] == 5) {
  if ($fltr != "") {
    $fltr .= " AND ";
  }
  $fltr .= "user_id = " . $_SESSION["user"]["data"]["id"];
}

$tbl->FilterMYSQL($fltr);

$stat_tbl = $GUI->Table("visits_stat" . $n);
$stat_tbl->Width = "50%";

$column = $stat_tbl->NewColumn();
$column->Caption = "";
$column->Key = "id";

$column = $stat_tbl->NewColumn();
$column->Caption = "Ожидалось получить";
$column->Key = "summa";

$column = $stat_tbl->NewColumn();
$column->Caption = "Получено";
$column->Key = "summaf";

$result = array(
  'id' => 'Стоимость, руб.', 'summa' => 0, 'summaf' => 0,
);

foreach (db::get_arrays("SELECT summa, summaf FROM " . TBL_PREF . $Filter->DstTable) as $row) {
  $result['summa'] += $row['summa'];
  $result['summaf'] += $row['summaf'];
}
$stat_tbl->AddRow($result, "id");