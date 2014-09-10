<?php

use Components\Entity\Employee;
use Components\Classes\db;
use Components\Classes\Roles;

page_ScriptNeed("scripts.js", "modules/finances/referrer_system");
page_ScriptNeed("instant_edit.js", "js");

global $GUI;

if (isset($_REQUEST["ref_system_flds_cfg"])) {
  if (isset($_POST["flds"])) {
    $_SESSION["user"]["data"]["conf_ref_system_fld"] = serialize($_POST["flds"]);
  } else {
    $_SESSION["user"]["data"]["conf_ref_system_fld"] = serialize(array());
  }

  Employee::update($_SESSION["user"]["data"]["id"], array(
    'conf_ref_system_fld' => $_SESSION["user"]["data"]["conf_ref_system_fld"],
  ));
  $_SESSION["ref_system_flds_cfg"] = true;

  $GUI->OK("Выполнено");
  die("");
}

$temp_table = 'referrer_system_' . $_SESSION["user"]["data"]["id"];

$query = "
  SELECT o.*, ref.id AS referrer_id, ref.fio AS referrer_fio, ref.email AS referrer_email, ref.telnum AS referrer_phone, ref.city AS referrer_city, ref.referrer_code
  FROM " . TBL_PREF . "orders o
  JOIN " . TBL_PREF . "clients c ON c.id = o.klient_id
  JOIN " . TBL_PREF . "clients ref ON ref.id = c.ref_id
  WHERE c.ref_id != 0
  ORDER BY o.id ASC
";

db::query("CREATE TEMPORARY TABLE " . TBL_PREF . $temp_table . " AS (" . $query . ")");

//Search panel
$sp = $GUI->UPanel();
$sp->Caption = "Поиск партнера";
$searchWhere = '';
if (!empty($_REQUEST["kln_search"])) {
  $sp->defOpen = true;

  if (!empty($_REQUEST['search_id'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= "referrer_id = '" . db::input($_REQUEST['search_id']) . "'";
  }

  if (!empty($_REQUEST['search_name'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " referrer_fio LIKE '%" . db::input($_REQUEST['search_name']) . "%'";
  }

  if (!empty($_REQUEST['search_mail'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " referrer_email LIKE '%" . db::input($_REQUEST['search_mail']) . "%'";
  }

  if (!empty($_REQUEST['search_phone'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " referrer_phone LIKE '%" . db::input($_REQUEST['search_phone']) . "%'";
  }

  if (!empty($_REQUEST['search_city'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " referrer_city LIKE '%" . db::input($_REQUEST['search_city']) . "%'";
  }

  if (!empty($_REQUEST['search_referrer'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " referrer_code LIKE '%" . db::input($_REQUEST['search_referrer']) . "%'";
  }
}

$sp->AddHTML("<div style='margin-left: 4px; margin-bottom: 5px; text-align:left'>");
$sp->AddHTML("<form method='post'>");
$sp->AddHTML("<input type='hidden' name='kln_search' value='1'>");
$sp->AddHTML("<label class='search_field'>по номеру<br/>");
$sp->AddHTML("<input type='text' name='search_id' style='width:100px;' value='" . (!empty($_REQUEST['search_id']) ? $_REQUEST['search_id'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по имени<br/>");
$sp->AddHTML("<input type='text' name='search_name' style='width:100px;' value='" . (!empty($_REQUEST['search_name']) ? $_REQUEST['search_name'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по почте<br/>");
$sp->AddHTML("<input type='text' name='search_mail' style='width:100px;' value='" . (!empty($_REQUEST['search_mail']) ? $_REQUEST['search_mail'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по телефону<br/>");
$sp->AddHTML("<input type='text' name='search_phone' style='width:100px;' value='" . (!empty($_REQUEST['search_phone']) ? $_REQUEST['search_phone'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по городу<br/>");
$sp->AddHTML("<input type='text' name='search_city' style='width:100px;' value='" . (!empty($_REQUEST['search_city']) ? $_REQUEST['search_city'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по партнерскому коду<br/>");
$sp->AddHTML("<input type='text' name='search_referrer' style='width:100px;' value='" . (!empty($_REQUEST['search_referrer']) ? $_REQUEST['search_referrer'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<input type='submit' value='Искать' style='margin-left: 10px;margin-top: 17px;'>");
$sp->AddHTML("<input type='submit' value='Сброс' style='margin-left: 10px' onclick='document.location.href=\"?section=finances&subsection=3&kln_search=1\"; return false;'>");
$sp->AddHTML("</form>");
$sp->AddHTML("</div>");

//////////// Filters
$Filter = $GUI->FltrCol("ref_system", "data_users:conf_ref_system_fltr");
$Filter->SrcTable = null;
$Filter->DstTable = $temp_table;
$Filter->OpenPanel = true;

// Добавляем фильтры
$data_status = db::get_assoc_arrays("SELECT id, status_name FROM " . TABLE_ORDERS_STATUS);
$statusFilter = $Filter->AddFilter("CGUI_FilterSelect");
$statusFilter->name = "Статус заказа";
$statusFilter->keyid = "status_id";
$statusFilter->SetSelectData($data_status, "status_name");
$statusFilter->hidden = true;
$statusFilter->multisel = true;


$referrerPymentStatusFilter = $Filter->AddFilter("CGUI_FilterSelect");
$referrerPymentStatusFilter->name = "Статус выплат";
$referrerPymentStatusFilter->keyid = "referrer_payment_status";
$referrerPymentStatusFilter->SetSelectData(array(array('id' => 0, 'status_name' => 'Не оплачено'), array('id' => 1, 'status_name' => 'Оплачено')), "status_name");
$referrerPymentStatusFilter->hidden = true;


$dateFilter = $Filter->AddFilter("CGUI_FilterDate");
$dateFilter->name = "Срок получения работ";
$dateFilter->keyid = "time_kln";
$dateFilter->hidden = true;

$referrerPymentDateFilter = $Filter->AddFilter("CGUI_FilterDate");
$referrerPymentDateFilter->name = "Период произведенных выплат за реализованные заказы";
$referrerPymentDateFilter->keyid = "referrer_payment_date";
$referrerPymentDateFilter->hidden = true;
$referrerPymentDateFilter->use_mysql_timestamp = true;

$std = $Filter->MakeStdSet("");

$std->UseFilter($statusFilter->id, true, false);
$std->UseFilter($referrerPymentStatusFilter->id, true, false);
$std->UseFilter($dateFilter->id, true, false);
$std->UseFilter($referrerPymentDateFilter->id, true, false);
$Filter->_select_group('std' . $std->id);

$Filter->Requests();
$Filter->Filtering();

$pan1 = $GUI->UPanel();
$pan1->Caption = "Фильтры";
$pan1->defOpen = $Filter->OpenPanel;
$pan1->AddHTML($Filter->GetHTML());

$tbl = $GUI->Table("ref_system" . $n);
$tbl->Width = "100%";
$tbl->DataMYSQL($Filter->DstTable);
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10,
  20,
  50,
  100,
  0
));
$tbl->before_start_event = "_before_start_table";

if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Просмотр содержания")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=ord&subsection=2&order=%var%&p=1\"";
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
  if (isset($column['custom']) && count($column['custom'])) {
    $r = $tbl->NewColumn();
    foreach ($column['custom'] as $custom_column) {
      $r1 = new CGUI_TableColumn();
      $r1->Caption = str_replace(" ", " <br>", $custom_column['name']);
      $r1->DoSort = $custom_column['do_sort'];
      $r1->Key = $custom_column['internal_name'];
      $r1->Align = $custom_column['align'];
      $r1->Process = $custom_column['on_execute'];
      $r1->instantEdit = $custom_column['instant_edit'];
      $r->Custom[] = $r1;
    }
  } else {
    if ($i == 1) {
      $r = $tbl->NewColumn();
    } else {
      $r = new CGUI_TableColumn();
      $allcols[$column['order']] = $r;
    }
    $r->Caption = str_replace(" ", " <br>", $column['name']);;
    $r->DoSort = $column['do_sort'];
    $r->Key = $column['internal_name'];
    $r->Align = $column['align'];
    $r->Process = $column['on_execute'];
    $r->instantEdit = $column['instant_edit'];
    $i++;
  }
}

$tbl->FilterMYSQL($searchWhere);

$pan2 = $GUI->UPanel();
if (isset($_SESSION["ref_system_flds_cfg"])) {
  unset($_SESSION["ref_system_flds_cfg"]);
  $pan2->defOpen = true;
}

$pan2->Caption = "Поля таблицы";
$pan2->Html = "";

// Оставим только возможные
//$tmp = $allcols;
//$allcols = array();
//foreach ($tmp as $k => $v) {
//  if (!count($v["r"]) || user_has_right($v["r"])) {
//    $allcols[$k] = $v["c"];
//  }
//}

$flds_added = array();
if ($_SESSION["user"]["data"]["conf_ref_system_fld"] != "") {
  // Используемые колонки
  $tmp_flds_added = unserialize($_SESSION["user"]["data"]["conf_ref_system_fld"]);
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


$stat_tbl = $GUI->Table("referrer_system_stat" . $n);
$stat_tbl->Width = "50%";

$column = $stat_tbl->NewColumn();
$column->Caption = "Итого";
$column->Key = "id";

$column = $stat_tbl->NewColumn();
$column->Caption = "Стоимость";
$column->Key = "client_price";

$column = $stat_tbl->NewColumn();
$column->Caption = "Оплачено";
$column->Key = "client_paid";

$column = $stat_tbl->NewColumn();
$column->Caption = "Долг";
$column->Key = "client_debt";

$column = $stat_tbl->NewColumn();
$column->Caption = "Расход";
$column->Key = "consumption";

$column = $stat_tbl->NewColumn();
$column->Caption = "Статус выплат";
$column->Key = "referrer_payment_status_all";
if (is_director($_SESSION['user']['data']['id'])) {
  $column->instantEdit = true;
}

$column = $stat_tbl->NewColumn();
$column->Caption = "Заказы";
$column->Key = "orders";
$column->hidden = true;
$column->id = 'orders_list';

$result = array(
  'id' => '',
  'client_price' => 0,
  'client_paid' => 0,
  'client_debt' => 0,
  'consumption' => 0,
  'referrer_payment_status_all' => 0,
  'orders' => '',
);

$result['referrer_payment_status_all'] = 'Не оплачено';
foreach (db::get_arrays("SELECT id, cost_kln, oplata_kln, referrer_payment_status FROM " . TBL_PREF . $Filter->DstTable) as $row) {
  $info = null;
  $result['client_price'] += $row['cost_kln'];
  $result['client_paid'] += $row['oplata_kln'];
  $result['client_debt'] += $row['cost_kln'] - $row['oplata_kln'];
  $result['consumption'] += get_consumption(0, $row, null, $info);
  if ($row['referrer_payment_status'] == 1) {
    $result['referrer_payment_status_all'] = 'Оплачено';
  }
  $result['orders'] .= empty($result['orders']) ? $row['id'] : ',' . $row['id'];
}
$stat_tbl->AddRow($result);