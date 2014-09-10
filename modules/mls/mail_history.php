<?php

switch($_GET['mail_type']) {
  case 0://все
    emails_in_table();
    emails_out_table();
    break;

  case 1://входящие
    emails_in_table();
    break;

  case 2://исходящие
    emails_out_table();
    break;
}

function emails_in_table() {
  global $GUI;

  $tbl = $GUI->Table("mls_in", array("cur_sort_up" => true));
  $tbl->Width = "100%";
  $tbl->DataMYSQL("messages");
  $where = '';
  if ($_GET['mail_with_sotr'] == 1) {
    if (!empty($_GET['mail_sotr_id'])) {
      $where .= " AND creator_id = 'u" . $_GET['mail_sotr_id'] . "'";
    } else {
      $where .= " AND creator_id LIKE '%u%'";
    }
  }
  if ($_GET['mail_with_client'] == 1) {
    if (!empty($_GET['mail_client_id'])) {
      $where .= (!empty($where) ? " OR " : " AND ") . "creator_id = 'k" . $_GET['mail_client_id'] . "'";
    } else {
      $where .= (!empty($where) ? " OR " : " AND ") . "creator_id LIKE '%k%'";
    }
  }

  $tbl->FilterMYSQL("addr='u" . $_GET['sotr_id'] . "' AND (created BETWEEN " . utils_cvt_date2i($_GET['date_from']) . " AND " . utils_cvt_date2i($_GET['date_till']) . ")" . $where);
  $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
    10,
    20,
    50,
    100,
    0
  ));

  $tbl->RowEvent2 = "document.location.href=\"?section=mls&subsection=2&type=o&read=%var%\"";

  $r = $tbl->NewColumn();
  $r->Caption = "Номер";
  $r->DoSort = true;
  $r->Key = "id";
  $r->Align = "left";
  $r->Process = "_get_message_num";

  $r = $tbl->NewColumn();
  $r->Caption = "Приоритет";
  $r->DoSort = true;
  $r->Key = "prior";
  $r->Align = "left";
  $r->Process = "_get_prior_name";

  $r = $tbl->NewColumn();
  $r->Caption = "От кого";
  $r->DoSort = true;
  $r->Align = "left";
  $r->Key = "creator_id";
  $r->Process = "_get_ukname";

  $r = $tbl->NewColumn();
  $r->Caption = "Создано";
  $r->DoSort = true;
  $r->Key = "created";
  $r->Align = "left";
  $r->Process = "_get_fmt_date";

  $r = $tbl->NewColumn();
  $r->Caption = "Тема";
  $r->DoSort = true;
  $r->Key = "subject";
  $r->Align = "left";
}

function emails_out_table() {
  global $GUI;

  $tbl2 = $GUI->Table("mls_out", array("cur_sort_up" => true));
  $tbl2->Width = "100%";
  $tbl2->DataMYSQL("messages");
  $where = '';

  if ($_GET['mail_with_sotr'] == 1) {
    if (!empty($_GET['mail_sotr_id'])) {
      $where = " AND addr = 'u" . $_GET['mail_sotr_id'] . "'";
    } else {
      $where = " AND addr LIKE '%u%'";
    }
  }
  if ($_GET['mail_with_client'] == 1) {
    if (!empty($_GET['mail_client_id'])) {
      $where .= (!empty($where) ? " OR " : " AND ") . "addr = 'k" . $_GET['mail_client_id'] . "'";
    } else {
      $where .= (!empty($where) ? " OR " : " AND ") . "addr LIKE '%k%'";
    }
  }

  $tbl2->FilterMYSQL("creator_id='u" . $_GET['sotr_id'] . "' AND (created BETWEEN " . utils_cvt_date2i($_GET['date_from']) . " AND " . utils_cvt_date2i($_GET['date_till']) . ")" . $where);
  $tbl2->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
    10,
    20,
    50,
    100,
    0
  ));

  $tbl2->RowEvent2 = "document.location.href=\"?section=mls&subsection=2&type=o&read=%var%\"";

  $r = $tbl2->NewColumn();
  $r->Caption = "Номер";
  $r->DoSort = true;
  $r->Key = "id";
  $r->Align = "left";

  $r = $tbl2->NewColumn();
  $r->Caption = "Кому";
  $r->DoSort = true;
  $r->Key = "addr";
  $r->Process = "_get_ukname";
  $r->Align = "left";

  $r = $tbl2->NewColumn();
  $r->Caption = "Создано";
  $r->DoSort = true;
  $r->Key = "created";
  $r->Align = "left";
  $r->Process = "_get_fmt_date";

  $r = $tbl2->NewColumn();
  $r->Caption = "Тема";
  $r->DoSort = true;
  $r->Key = "subject";
  $r->Align = "left";

  $r = $tbl2->NewColumn();
  $r->Caption = "Статус";
  $r->DoSort = true;
  $r->Key = "readed";
  $r->Align = "left";
  $r->Process = "_get_readed";
}

//die('jhk');