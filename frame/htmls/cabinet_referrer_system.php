<?php

use Components\Classes\db;

if (!is_client_logged() || $_SESSION["frame"]["client"]["blocked"]) {
  echo 'Доступ запрещен.';
} else {
  $html = array();

  $html[] = '<div class="cabinet_sub_menu">';
  $html[] = '<a href="?type=cabinet&referrer=1&p=1">Принцип сотрудничества</a>';
  $html[] = '<a href="?type=cabinet&referrer=1&p=2">Мои доходы по партнерской программе</a>';
  $html[] = '</div>';

  echo join("\n", $html);

  if (empty($_REQUEST['p']) || $_REQUEST['p'] == 2) {
    include_once(DIR_FS_DOCUMENT_ROOT . "/gui/gui.php");
    include_once(DIR_FS_MODULES . "/finances/functions.php");
    $headers_already_printer = true;

    $temp_table = 'cabinet_referrer_system_' . $_SESSION["frame"]["client"]["id"];

    $query = "
      SELECT o.*, ref.id AS referrer_id, ref.fio AS referrer_fio, ref.email AS referrer_email, ref.telnum AS referrer_phone, ref.city AS referrer_city, ref.referrer_code
      FROM " . TBL_PREF . "orders o
      JOIN " . TBL_PREF . "clients c ON c.id = o.klient_id
      JOIN " . TBL_PREF . "clients ref ON ref.id = c.ref_id
      WHERE c.ref_id = " . $_SESSION["frame"]["client"]["id"] . "
      ORDER BY o.id ASC
    ";

    db::query("CREATE TEMPORARY TABLE " . TBL_PREF . $temp_table . " AS (" . $query . ")");

    $GUI->url = 'type=cabinet&referrer=1&p=2&';
    $Filter = $GUI->FltrCol("ref_system", "clients:ref_system_fltr");
    $Filter->SrcTable = null;
    $Filter->DstTable = $temp_table;
    $Filter->uri = '?type=cabinet&referrer=1&p=2';
    $Filter->OpenPanel = true;
    $Filter->can_clear = false;
    $Filter->show_sets = false;

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

    $std->UseFilter($statusFilter->id, true, true);
    $std->UseFilter($referrerPymentStatusFilter->id, true, true);
    $std->UseFilter($dateFilter->id, true, true);
    $std->UseFilter($referrerPymentDateFilter->id, true, true);
    $Filter->_select_group('std' . $std->id);

    $Filter->Requests();
    $Filter->Filtering();

    $pan1 = $GUI->UPanel();
    $pan1->Caption = "Фильтры";
    $pan1->defOpen = $Filter->OpenPanel;
    $pan1->AddHTML($Filter->GetHTML());

    echo '<div style="margin-top: 10px;" class="gui_style">';
    echo $GUI->panels[0]->GetHTML();
    echo '</div>';

    $tbl = $GUI->Table("cab_ref_system" . $_SESSION["frame"]["client"]["id"]);
    $tbl->Width = "100%";
    $tbl->DataMYSQL($Filter->DstTable);
    $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
      10,
      20,
      50,
      100,
      0
    ));

    $r = $tbl->NewColumn();
    $r->Caption = '№';
    $r->DoSort = true;
    $r->Key = 'id';

    $r = $tbl->NewColumn();
    $r->Caption = 'Ф.И.О. Клиента';
    $r->DoSort = true;
    $r->Key = 'klient_id';
    $r->Process = '_get_client_name';

    $r = $tbl->NewColumn();
    $r->Caption = 'Вид работы';
    $r->DoSort = true;
    $r->Key = 'type_id';
    $r->Process = '_get_worktype_name';

    $r = $tbl->NewColumn();
    $r->Caption = 'Тема';
    $r->DoSort = true;
    $r->Key = 'subject';

    $r = $tbl->NewColumn();
    $r->Caption = 'Статус';
    $r->DoSort = true;
    $r->Key = 'status_id';
    $r->Process = 'get_status_name';

    $r = $tbl->NewColumn();
    $r->Caption = 'Дата сдачи';
    $r->DoSort = true;
    $r->Key = 'time_kln';
    $r->Process = '_get_fmt_date';

    $r = $tbl->NewColumn();
    $r->Caption = 'Стоимость';
    $r->DoSort = true;
    $r->Key = 'cost_kln';
    $r->Process = '_get_fmt_cost';

    $r = $tbl->NewColumn();
    $r->Caption = 'Оплачено';
    $r->DoSort = true;
    $r->Key = 'oplata_kln';
    $r->Process = '_get_fmt_cost';

    $r = $tbl->NewColumn();
    $r->Caption = 'Долг';
    $r->Process = 'get_client_debt';

    $r = $tbl->NewColumn();
    $r->Caption = 'Мой доход';
    $r->Process = 'get_consumption';

    $r = $tbl->NewColumn();
    $r->Caption = 'Статус выплат';
    $r->DoSort = true;
    $r->Key = 'referrer_payment_status';
    $r->Process = 'get_referrer_payment_status';

    $r = $tbl->NewColumn();
    $r->Caption = 'Дата выплаты';
    $r->DoSort = true;
    $r->Key = 'referrer_payment_date';

    $r = $tbl->NewColumn();
    $r->Caption = 'Написать';
    $r->Process = 'tp_users_cmds_frame';

    $tbl->before_start_event = "_before_start_table";

    echo '<div style="margin-top: 10px;" class="gui_style">';
    echo $GUI->tables[0]->PrintTable();
    echo '</div>';

    $stat_tbl = $GUI->Table("cab_ref_system_stat" . $_SESSION["frame"]["client"]["id"]);
    $stat_tbl->Width = "100%";

    $column = $stat_tbl->NewColumn();
    $column->Caption = "Итого";

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
    $column->Caption = "Мой доход";
    $column->Key = "consumption";

    $result = array(
      'client_price' => 0,
      'client_paid' => 0,
      'client_debt' => 0,
      'consumption' => 0,
      'id' => 0,
    );
    foreach (db::get_arrays("SELECT id, cost_kln, oplata_kln FROM " . TBL_PREF . $Filter->DstTable) as $row) {
      $info = '';
      $result['client_price'] += $row['cost_kln'];
      $result['client_paid'] += $row['oplata_kln'];
      $result['client_debt'] += $row['cost_kln'] - $row['oplata_kln'];
      $result['consumption'] += get_consumption(0, $row, null, $info);
    }
    $stat_tbl->AddRow($result);
    echo '<div style="margin: 10px auto 0;width: 50%;" class="gui_style">';
    echo $GUI->tables[1]->PrintTable();
    echo '</div>';
  } else {
    echo 'text';
  }
}

function tp_users_cmds_frame($value, $row, $table, &$info) {
  global $GUI;

  return $value . " " . $GUI->getIcon(SITE_URL . "frame?type=cabinet&messages=1&new=1&r=" . $row["manager_id"] . "&o=" . $row['id'], "msg", "Написать");
}