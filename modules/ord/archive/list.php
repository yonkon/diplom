<?php

use Components\Classes\db;
use Components\Classes\Roles;

use Components\Entity\Order;
use Components\Entity\Employee;
use Components\Entity\OrderStatus;

if (isset($_REQUEST["ordarchfldscfg"])) {
  if (isset($_POST["flds"])) {
    $_SESSION["user"]["data"]["conf_ordarchfld"] = serialize($_POST["flds"]);
  } else {
    $_SESSION["user"]["data"]["conf_ordarchfld"] = serialize(array());
  }

  Employee::update($_SESSION["user"]["data"]["id"], array(
    'conf_ordarchfld' => $_SESSION["user"]["data"]["conf_ordfld"],
  ));
  $_SESSION["ordarchfldscfg"] = true;

  $GUI->OK("Выполнено");
  die("");
}

global $data_users, $data_filials, $data_vuz, $data_payments, $data_napravl, $data_worktypes, $data_discip;
//////////// Filters
$Filter = $GUI->FltrCol("ordarch", "data_users:conf_ordarchfltr");
$Filter->SrcTable = TABLE_ORDERS;
$Filter->DstTable = "orders_arch_tmp_" . $_SESSION["user"]["data"]["id"];

// Добавляем фильтры
$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Клиент";
$f->keyid = "klient_id";
$f->SetSelectData(kln_getrawlist(), "fio");
$flt_kln_id = $f->id;

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Филиал";
$f->keyid = "filial_id";
$f->SetSelectData($data_filials, "name");

$createdFilter = $Filter->AddFilter("CGUI_FilterDate");
$createdFilter->name = "Принят";
$createdFilter->keyid = "created";

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Принял";
$f->keyid = "creator_id";
$f->SetSelectData($data_users, "fio");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Менеджер";
$f->keyid = "manager_id";
$f->SetSelectData(get_users_by_group_name('Менеджер') + array(0 => array('id' => 0, 'fio' => 'не определенно')), "fio");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Автор";
$f->keyid = "author_id";
$f->SetSelectData(get_users_by_group_name('Автор'), "fio");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "ВУЗ";
$f->keyid = "vuz_id";
$f->SetSelectData($data_vuz, "sname");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Вид работы";
$f->keyid = "type_id";
$f->SetSelectData($data_worktypes, "name");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Направление";
$f->keyid = "napr_id";
$f->SetSelectData($data_napravl, "name");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Дисциплина";
$f->keyid = "disc_id";
$f->SetSelectData($data_discip, "name");

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Сдать клиенту";
$f->keyid = "time_kln";

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Сдано клиенту";
$f->keyid = "time_kln_r";

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Дата для автора";
$f->keyid = "time_auth";

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Получено от автора";
$f->keyid = "time_auth_r";

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Способ оплаты";
$f->keyid = "payment_id";
$f->SetSelectData($data_payments, "name");

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Распределить до";
$f->keyid = "raspred_srok";

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Следующий контакт";
$f->keyid = "next_rel_date";

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Дата комментария ОК";
$f->keyid = "ok_comment_date";

$data_status = db::get_assoc_arrays("SELECT id, status_name FROM " . TABLE_ORDERS_STATUS);
$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Статус заказа";
$f->keyid = "status_id";
$f->SetSelectData($data_status, "status_name");

$f = $Filter->AddFilter("CGUI_FilterIntegerRange");
$f->name = "Гонорар автора";
$f->keyid = "cost_auth";

$Filter->MakeUserSets(20);

$debtorFilter = $Filter->AddFilter('CGUI_FilterFirstBiggerSecond');
$debtorFilter->name = "Должники";
$debtorFilter->keyid = "cost_kln";
$debtorFilter->secondField = "oplata_kln";
$debtorFilter->hidden = true;
$std = $Filter->MakeStdSet("Должники");
$uf = $std->UseFilter($debtorFilter->id);
$uf = $std->UseFilter($createdFilter->id);

/*
  $std = $Filter->MakeStdSet("Новые на этот месяц");
    $std->UseFilter($flt_month_id);
    $uf = $std->UseFilter($flt_status_id);
    $uf->filter->value = 0;
*/

// По клиенту
if (isset($_REQUEST["kln_id"])) {
  $kln = intval($_REQUEST["kln_id"]);
  $ts = $Filter->MakeTmpSet();
  $uf = $ts->UseFilter($flt_kln_id);
  $uf->SetConf(array($kln));
}

$Filter->Requests();
$Filter->Filtering();

$pan1 = $GUI->UPanel();
$pan1->Caption = "Фильтры";
$pan1->defOpen = $Filter->OpenPanel;
$pan1->AddHTML($Filter->GetHTML());

$tbl = $GUI->Table("ordarch" . $n);
$tbl->Width = "100%";
$tbl->DataMYSQL($Filter->DstTable);
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10,
  20,
  50,
  100,
  0
));

//search
$sp = $GUI->UPanel();
$sp->Caption = "Поиск";
$search_filter = '';
if (!empty($_REQUEST["order_search"])) {
  $sp->defOpen = true;

  if (!empty($_REQUEST['search_id'])) {
    $search_filter .= " id = " . db::input($_REQUEST['search_id']) . "";
  }

  if (!empty($_REQUEST['search_subj'])) {
    if (!empty($search_filter)) {
      $search_filter .= ' AND';
    }
    $search_filter .= " subject LIKE '%" . db::input($_REQUEST['search_subj']) . "%'";
  }
}
$sp->AddHTML("<div style='margin-left: 4px; margin-bottom: 5px; text-align:left'>");
$sp->AddHTML("<form method='post'>");
$sp->AddHTML("<input type='hidden' name='order_search' value='1'>");
$sp->AddHTML("<label class='search_field'>по номеру<br/>");
$sp->AddHTML("<input type='text' name='search_id' style='width:100px;' value='" . (!empty($_REQUEST['search_id']) ? $_REQUEST['search_id'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по теме<br/>");
$sp->AddHTML("<input type='text' name='search_subj' style='width:200px;' value='" . (!empty($_REQUEST['search_subj']) ? $_REQUEST['search_subj'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<input type='submit' value='Искать' style='margin-left: 10px;margin-top: 17px;'>");
$sp->AddHTML("<input type='submit' value='Сброс' style='margin-left: 10px' onclick='document.location.href=\"?section=ord&subsection=2&order_search=1\"; return false;'>");
$sp->AddHTML("</form>");
$sp->AddHTML("</div>");
//end search

$tbl->before_start_event = "_before_start_table";

if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Просмотр содержания")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=ord&subsection=2&order=%var%&p=1\"";
}


$rm = $tbl->CreateRowMenu();

if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Просмотр содержания")) {
  $rm->AddCommand("Просмотр содержания", "?section=ord&subsection=2&order=%1%&p=1");
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Правка содержания")) {
  $rm->AddCommand("Правка содержания", "?section=ord&subsection=2&order=%1%&p=2");
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Распределение")) {
  $rm->AddCommand("Распределение", "?section=ord&subsection=2&order=%1%&p=3");
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Файлы")) {
  $rm->AddCommand("Файлы", "?section=ord&subsection=2&order=%1%&p=4");
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Назначить встречу")) {
  $rm->AddCommand("Назначить встречу", "?section=vis&subsection=1&kln=%2%&ord=%1%");
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Показать встречи")) {
  $rm->AddCommand("Показать встречи", "?section=vis&subsection=2&kln=%2%&ord=%1%");
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "История заказа")) {
  $rm->AddCommand("История заказа", "?section=ord&subsection=2&order=%1%&p=5");
}

$allfltr = array();
$allfltr[1] = array("name" => "Номер клиента", array("соответствует", "не соответствует"), "_filter_client_num",);

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
    $i++;
  }
}

$pan2 = $GUI->UPanel();
if (isset($_SESSION["ordarchfldscfg"])) {
  unset($_SESSION["ordarchfldscfg"]);
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
if ($_SESSION["user"]["data"]["conf_ordarchfld"] != "") {
  // Используемые колонки
  $tmp_flds_added = unserialize($_SESSION["user"]["data"]["conf_ordarchfld"]);
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

$pan2->Html .= "<div style='border-bottom: 1px solid white; border-top: 1px solid silver; height: 0px; margin-top:10px'></div>" . "<div style='text-align:left; margin-left: 20px; margin-bottom: 10px; margin-top: 10px'><input type='button' value='Применить' onclick='save_order_archive_fields(orderfields_added)'></div>";

////////////////////////
$fltr = '(status_id = ' . get_status_id_by_iname('ORDER_CANCELED') . ' OR status_id = ' . get_status_id_by_iname('DONE') . ')';

if ($_SESSION["user"]["data"]["group_id"] > 1 && $_SESSION["user"]["data"]["group_id"] != get_role_id_by_name('Автор')) {
  if ($fltr != "") {
    $fltr .= " AND ";
  }
  $fltr .= "filial_id=" . $_SESSION["user"]["data"]["filial_id"];
}

if ($_SESSION["user"]["data"]["group_id"] == get_role_id_by_name('Отдел качества')) {
  $status_id = get_status_id_by_iname('RECEIVED_FILE_FROM_AUTHOR');
  if ($status_id) {
    if ($fltr != "") {
      $fltr .= " AND ";
    }
    $fltr .= "status_id = " . $status_id;
  }
} elseif ($_SESSION["user"]["data"]["group_id"] == get_role_id_by_name('Автор')) {
  $distribution_status_id = get_status_id_by_iname('ON_THE_DISTRIBUTION');

  if ($fltr != "") {
    $fltr .= " AND ";
  }

  $fltr .= "(status_id = " . $distribution_status_id . " OR author_id = " . $_SESSION["user"]["data"]["id"] . ")";
} elseif ($_SESSION["user"]["data"]["group_id"] == 5) {
  $delivery_boy_orders = array();

  foreach (db::get_arrays("SELECT order_id FROM " . TBL_PREF . "data_visits WHERE user_id = " . db::input($_SESSION["user"]["data"]["id"])) as $res_order) {
    $delivery_boy_orders[] = $res_order['order_id'];
  }

  if ($fltr != "") {
    $fltr .= " AND ";
  }
  $fltr .= "id IN (" . join(', ', $delivery_boy_orders) . ")";
}

if (!empty($fltr) && !empty($search_filter)) {
  $result_filter = $search_filter . ' AND ' . $fltr;
} elseif (!empty($fltr)) {
  $result_filter = $fltr;
} elseif (!empty($search_filter)) {
  $result_filter = $search_filter;
} else {
  $result_filter = '';
}

$tbl->FilterMYSQL($result_filter);

$stat_tbl = $GUI->Table("ord_stat" . $n);
$stat_tbl->Width = "50%";

$column = $stat_tbl->NewColumn();
$column->Caption = "";
$column->Key = "id";

if (!is_author($_SESSION["user"]["data"]["id"])) {
  $column = $stat_tbl->NewColumn();
  $column->Caption = "Цена клиенту";
  $column->Key = "client_price";

  $column = $stat_tbl->NewColumn();
  $column->Caption = "Оплачено клиентом";
  $column->Key = "client_payed";

  $column = $stat_tbl->NewColumn();
  $column->Caption = "Оплачено автору";
  $column->Key = "author_payed";
}

$column = $stat_tbl->NewColumn();
$column->Caption = "Гонорар автора";
$column->Key = "author_price";

$result = array(
  'id' => 'Стоимость, руб.',
  'client_price' => 0,
  'author_price' => 0,
  'client_payed' => 0,
  'author_payed' => 0,
);
foreach (db::get_arrays("SELECT cost_kln, cost_auth, oplata_kln, author_paid, id FROM " . TBL_PREF . $Filter->DstTable . " WHERE " . (!empty($result_filter) ? $result_filter : "1")) as $row) {
  $result['client_price'] += $row['cost_kln'];
  $result['author_price'] += $row['cost_auth'];
  $result['client_payed'] += $row['oplata_kln'];
  $result['author_payed'] += $row['author_paid'] ? $row['author_paid'] : 0;
}
$stat_tbl->AddRow($result, "id");