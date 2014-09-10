<?php

use Components\Classes\db;

use Components\Entity\Employee;


$id = intval($_REQUEST["zan"]);

$order_fields = array('id', 'klient_id', 'created', 'time_kln');
$order_by = ' ORDER BY id ASC';
if (isset($_REQUEST['sort_cgui_table_id_usotr_orders2'])) {
  $order_by = ' ORDER BY ' . $order_fields[$_REQUEST['sort_cgui_table_id_usotr_orders2']] . ' ASC';
} elseif (isset($_REQUEST['sort_cgui_table_id_usotr_orders2_up'])) {
  $order_by = ' ORDER BY ' . $order_fields[$_REQUEST['sort_cgui_table_id_usotr_orders2_up']] . ' DESC';
}

if (Employee::find($id)) {
  $orders = db::get_assoc_arrays("SELECT " . join(", ", $order_fields) . " FROM " . TABLE_ORDERS . " WHERE manager_id = " . $id . " OR author_id = " . $id . $order_by);

  $tbl = $GUI->Table("sotr_orders" . $n);
  $tbl->Width = "50%";

  $tbl->RowEvent2 = "document.location.href=\"?section=ord&subsection=2&order=%var%&p=1\"";

  $r = $tbl->NewColumn();
  $r->Caption = "Номер заказа";
  $r->DoSort = true;
  $r->Key = "id";

  $r = $tbl->NewColumn();
  $r->Caption = "Клиент";
  $r->DoSort = true;
  $r->Key = "klient_id";
  $r->Align = "left";
  $r->Process = "get_client_name";

  $r = $tbl->NewColumn();
  $r->Caption = "Принят";
  $r->DoSort = true;
  $r->Key = "created";
  $r->Align = "left";
  $r->Process = "format_date";

  $r = $tbl->NewColumn();
  $r->Caption = "Сдать клиенту";
  $r->DoSort = true;
  $r->Key = "time_kln";
  $r->Align = "left";
  $r->Process = "format_date";

  foreach ($orders as $row) {
    $tbl->AddRow($row, 'id');
  }

  $visits_fields = array('id', 'status', 'date', 'client_id');
  $visits_order_by = ' ORDER BY id ASC';
  if (isset($_REQUEST['sort_cgui_table_id_usotr_visits2'])) {
    $visits_order_by = ' ORDER BY ' . $visits_fields[$_REQUEST['sort_cgui_table_id_usotr_visits2']] . ' ASC';
  } elseif (isset($_REQUEST['sort_cgui_table_id_usotr_visits2_up'])) {
    $visits_order_by = ' ORDER BY ' . $visits_fields[$_REQUEST['sort_cgui_table_id_usotr_visits2_up']] . ' DESC';
  }

  $visits = db::get_assoc_arrays("SELECT " . join(", ", $visits_fields) . " FROM " . TABLE_VISITS . " WHERE user_id = " . $id . $visits_order_by);

  $tbl = $GUI->Table("sotr_visits" . $n);
  $tbl->Width = "50%";

  $tbl->RowEvent2 = "document.location.href=\"?section=vis&subsection=2&visit=%var%\"";

  $r = $tbl->NewColumn();
  $r->Caption = "Номер встречи";
  $r->DoSort = true;
  $r->Key = "id";

  $r = $tbl->NewColumn();
  $r->Caption = "Статус";
  $r->DoSort = true;
  $r->Key = "status";
  $r->Align = "left";
  $r->Process = "get_status";

  $r = $tbl->NewColumn();
  $r->Caption = "Дата";
  $r->DoSort = true;
  $r->Key = "date";
  $r->Align = "left";
  $r->Process = "format_date";

  $r = $tbl->NewColumn();
  $r->Caption = "Клиент";
  $r->DoSort = true;
  $r->Key = "client_id";
  $r->Align = "left";
  $r->Process = "get_client_name";

  foreach ($visits as $row) {
    $tbl->AddRow($row, 'id');
  }
}