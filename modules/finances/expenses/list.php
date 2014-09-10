<?php

use Components\Classes\db;
use Components\Classes\Roles;

global $data_filials, $GUI;

$result_filter = 1;
//////////// Filters
$Filter = $GUI->FltrCol("expenses", "data_users:conf_expenses_fltr");
$Filter->SrcTable = TABLE_EXPENSES;
$Filter->DstTable = "expenses_tmp_" . $_SESSION["user"]["data"]["id"];;

// Добавляем фильтры

$filialFilter = $Filter->AddFilter("CGUI_FilterSelect");
$filialFilter->name = "Филиал";
$filialFilter->keyid = "filial_id";
$filialFilter->multisel = true;
$filialFilter->SetSelectData($data_filials + array(array('id' => 0, 'name' => 'не указан')), "name");
if (is_elder_manager($_SESSION['user']['data']['id'])) {
  $filialFilter->hidden = true;
  $result_filter = 'filial_id = ' . $_SESSION['user']['data']['filial_id'];
}

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Дата";
$f->keyid = "date";
$f->use_mysql_timestamp = true;

$Filter->MakeUserSets(10);
$Filter->Requests();
$Filter->Filtering();

$pan1 = $GUI->UPanel();
$pan1->Caption = "Фильтры";
$pan1->defOpen = $Filter->OpenPanel;
$pan1->AddHTML($Filter->GetHTML());

$GUI->Vars['add_expenses'] = '<a style="float: left;" class="show_as_button" href="/index.php?section=finances&subsection=2&action=add">Добавить статью расходов</a>';

$tbl = $GUI->Table("expenses" . $n);
$tbl->Width = "100%";
$tbl->DataMYSQL($Filter->DstTable);
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10,
  20,
  50,
  100,
  0
));

if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=finances&subsection=2&action=edit&id=%var%\"";
}

$allcols = array();

$columns_resource = Roles::getColumns($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"]);

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

$i = 1;
foreach ($new_columns as $column) {
  if (isset($column['internal_name']) && in_array($column['internal_name'], $column_group_name)) {
    continue;
  }
  $r = $tbl->NewColumn();
  $r->Caption = str_replace(" ", " <br>", $column['name']);;
  $r->DoSort = $column['do_sort'];
  $r->Key = $column['internal_name'];
  $r->Align = $column['align'];
  $r->Process = $column['on_execute'];
  $r->instantEdit = $column['instant_edit'];
  $i++;
}

$tbl->FilterMYSQL($result_filter);

$totals = db::get_arrays("
  SELECT filial_id, SUM(`value`) as total_expenses
  FROM " . TBL_PREF . $Filter->DstTable . "
  WHERE " . $result_filter . "
  GROUP BY filial_id
");

if ($totals) {
  $stat_tbl = $GUI->Table("expenses_stat" . $n);
  $stat_tbl->Width = "50%";

  $column = $stat_tbl->NewColumn();
  $column->Caption = "Филиал";
  $column->Key = "id";

  $column = $stat_tbl->NewColumn();
  $column->Caption = "Общий расход";
  $column->Key = "total_expenses";

  if (is_director($_SESSION['user']['data']['id'])) {
    $column = $stat_tbl->NewColumn();
    $column->Caption = "Итого расход руководителя";
    $column->Key = "director_expenses";
  }

  foreach ($totals as $row) {
    $res = array();

    if ($row['filial_id'] == 0) {
      $res['id'] = 'Руководитель';
      $res['director_expenses'] = '';
    } else {
      $res['id'] = get_filial_name($row['filial_id']);
      $res['director_expenses'] = $row['total_expenses'] * (1 - db::get_single_value("SELECT consumption FROM " . TBL_PREF . "data_filials WHERE id = " . $row['filial_id']));
    }

    $res['total_expenses'] = $row['total_expenses'];

    $stat_tbl->AddRow($res);
  }
}