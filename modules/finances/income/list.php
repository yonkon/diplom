<?php

use Components\Classes\db;
use Components\Classes\Roles;

use Components\Entity\Employee;

page_ScriptNeed("scripts.js", "modules/finances/income");

global $data_filials, $GUI;

if (isset($_REQUEST["income_flds_cfg"])) {
  if (isset($_POST["flds"])) {
    $_SESSION["user"]["data"]["conf_income_fld"] = serialize($_POST["flds"]);
  } else {
    $_SESSION["user"]["data"]["conf_income_fld"] = serialize(array());
  }

  Employee::update($_SESSION["user"]["data"]["id"], array(
    'conf_income_fld' => $_SESSION["user"]["data"]["conf_income_fld"],
  ));

  $_SESSION["income_flds_cfg"] = true;

  $GUI->OK("Выполнено");
  die("");
}

$temp_table = 'income_' . $_SESSION["user"]["data"]["id"];

$result_filter = 1;
//////////// Filters
$Filter = $GUI->FltrCol("income", "data_users:conf_income_fltr");
$Filter->SrcTable = null;
$Filter->DstTable = $temp_table;
$Filter->OpenPanel = true;

// Добавляем фильтры

$filialFilter = $Filter->AddFilter("CGUI_FilterSelect");
$filialFilter->name = "Филиал";
$filialFilter->keyid = "id";
$filialFilter->SetSelectData($data_filials + array(array('id' => 0, 'name' => 'не указан')), "name");
$filialFilter->hidden = true;

$dateFilter = $Filter->AddFilter("CGUI_FilterDate");
$dateFilter->name = "Срок получения работ";
$dateFilter->keyid = "time_kln";
$dateFilter->hidden = true;

$referrerPymentStatusFilter = $Filter->AddFilter("CGUI_FilterSelect");
$referrerPymentStatusFilter->name = "Статус выплат";
$referrerPymentStatusFilter->keyid = "referrer_payment_status";
$referrerPymentStatusFilter->SetSelectData(array(array('id' => 0, 'status_name' => 'Не оплачено'), array('id' => 1, 'status_name' => 'Оплачено')), "status_name");
$referrerPymentStatusFilter->hidden = true;

$data_status = db::get_assoc_arrays("SELECT id, status_name FROM " . TABLE_ORDERS_STATUS);
$statusFilter = $Filter->AddFilter("CGUI_FilterSelect");
$statusFilter->name = "Статус заказа";
$statusFilter->keyid = "status_id";
$statusFilter->SetSelectData($data_status, "status_name");
$statusFilter->hidden = true;
$statusFilter->multisel = true;

//$Filter->MakeUserSets(10);

$std = $Filter->MakeStdSet("");

if (is_director($_SESSION['user']['data']['id'])) {
  $filialFilter->multisel = true;
  $std->UseFilter($filialFilter->id, true, true);
} else {
  $filialFilter->value = $_SESSION['user']['data']['filial_id'];
  $std->UseFilter($filialFilter->id, true, true, true);
}

$std->UseFilter($dateFilter->id, true, true);
$std->UseFilter($statusFilter->id, true, true);
$std->UseFilter($referrerPymentStatusFilter->id, true, true);
$Filter->_select_group('std' . $std->id);

$Filter->Requests();
//$Filter->Filtering();

$pan1 = $GUI->UPanel();
$pan1->Caption = "Фильтры";
$pan1->defOpen = $Filter->OpenPanel;
$pan1->AddHTML($Filter->GetHTML());

$query = "
  SELECT f.id, o.time_kln, o.status_id,
    SUM(o.cost_kln) AS client_price,
    SUM(o.cost_auth) AS author_price,
    SUM(o.oplata_kln) AS client_paid,
    SUM(IFNULL(o.author_paid, 0)) AS author_paid,
    SUM(o.company_paid) AS company_paid,
    SUM(o.cost_kln - o.oplata_kln) AS client_debt,
    SUM(o.cost_auth - IFNULL(o.author_paid, 0)) AS debt_to_author,

    TRUNCATE(SUM((o.cost_kln - o.cost_auth) * f.profit), 2) AS company_profit,

    TRUNCATE(SUM((o.cost_kln - o.cost_auth) * f.profit - o.company_paid), 2) AS debt_to_company,

    IFNULL(TRUNCATE(((SELECT SUM(e.value)
    FROM " . TBL_PREF . "expenses e
    WHERE e.filial_id = f.id AND " . $dateFilter->getFilterString('e', 'date', true) . ") * (1 - f.consumption)), 2), 0) AS expenses,

    TRUNCATE((SUM((o.cost_kln - o.cost_auth) * f.profit) - IFNULL(((SELECT SUM(e.value)
    FROM " . TBL_PREF . "expenses e
    WHERE e.filial_id = f.id AND " . $dateFilter->getFilterString('e', 'date', true) . ") * (1 - f.consumption)), 0)), 2) AS company_profit_with_expenses

  FROM " . TBL_PREF . "data_filials f
  LEFT JOIN " . TBL_PREF . "orders o ON o.filial_id = f.id
  WHERE 1
  AND " . $filialFilter->getFilterString('f') . "
  AND " . $statusFilter->getFilterString('o') . "
  AND " . $dateFilter->getFilterString('o') . "
  AND " . $referrerPymentStatusFilter->getFilterString('o') . "
  GROUP BY f.id
";

db::query("CREATE TEMPORARY TABLE " . TBL_PREF . $temp_table . " AS (" . $query . ")");

$tbl = $GUI->Table("income" . $n);
$tbl->Width = "100%";
$tbl->DataMYSQL($Filter->DstTable);
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10,
  20,
  50,
  100,
  0
));

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

foreach ($new_columns as $column) {
  if (isset($column['internal_name']) && in_array($column['internal_name'], $column_group_name)) {
    continue;
  }
  $r = new CGUI_TableColumn();
  $allcols[$column['order']] = $r;
  $r->Caption = str_replace(" ", " <br>", $column['name']);;
  $r->DoSort = $column['do_sort'];
  $r->Key = $column['internal_name'];
  $r->Align = $column['align'];
  $r->Process = $column['on_execute'];
  $r->instantEdit = $column['instant_edit'];
}

$tbl->FilterMYSQL('');

$pan2 = $GUI->UPanel();
if (isset($_SESSION["income_flds_cfg"])) {
  unset($_SESSION["income_flds_cfg"]);
  $pan2->defOpen = true;
}

$pan2->Caption = "Поля таблицы";
$pan2->Html = "";

$flds_added = array();
if ($_SESSION["user"]["data"]["conf_income_fld"] != "") {
  // Используемые колонки
  $tmp_flds_added = unserialize($_SESSION["user"]["data"]["conf_income_fld"]);
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
$pan2->Html .= "<td><select multiple id='orderfields_foradd' style='width:200px' size='8'>";
foreach ($flds_to_add as $v) {
  $pan2->Html .= "<option value='" . $v . "'>" . $allcols[$v]->Caption . "</option>";
}
$pan2->Html .= "</select></td><td style='text-align:center; padding:4px'>" . "<input type='button' value='>' onclick='add_order_field(orderfields_foradd, orderfields_added)'><br>" . "<input type='button' value='<' onclick='remove_order_field(orderfields_foradd, orderfields_added)'></td>";

$pan2->Html .= "<td><select multiple id='orderfields_added' style='width:200px' size='8' onchange='select_order_field()'>";
foreach ($flds_added as $v) {
  $pan2->Html .= "<option value='" . $v . "'>" . $allcols[$v]->Caption . "</option>";
  $tbl->Columns[] = $allcols[$v];
}
$pan2->Html .= "</select></td><td align='center'>" . "<input type='button' value='&uarr;' id='ord_table_flds_btn_up' onclick='moveup_order_field()' disabled><br>" . "<input type='button' value='&darr;' id='ord_table_flds_btn_down' onclick='movedown_order_field()' disabled>" . "</td></tr></table>";

$pan2->Html .= "<div style='border-bottom: 1px solid white; border-top: 1px solid silver; height: 0px; margin-top:10px'></div>" . "<div style='text-align:left; margin-left: 20px; margin-bottom: 10px; margin-top: 10px'><input type='button' value='Применить' onclick='save_order_fields(orderfields_added)'></div>";