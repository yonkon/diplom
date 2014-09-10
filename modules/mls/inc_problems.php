<?php

  use Components\Classes\Roles;
  use Components\Classes\db;

  use Components\Entity\Message;
  include_once("functions.php");

  if (isset($_REQUEST["read"])) {
    $message = Message::find(intval($_REQUEST["read"]));

    if ($message) {
      $GUI->tmpls[] = $active_module_root . "read.tmpl.php";
      include("inc_read.php");
      return;
    } else {
      $GUI->ERR("Письмо не найдено");
      page_ReloadSubSec();
    }
  }

  if (isset($_REQUEST['delete']) ) {
    \Components\Entity\EmailNotification::delete($_REQUEST['delete']);
  }
  
    
  $tbl = $GUI->Table("mls_problems", array("cur_sort_up" => true));
  $tbl->Width = "100%";
  $tbl->DataMYSQL('email_notifications en JOIN ' . TABLE_MESSAGES . ' m ON m.id=en.message_id', 'en.*, m.id AS mid', 'en');
  $tbl->FilterMYSQL("en.attempts_to_send>0");

  $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
    10,
    20,
    50,
    100,
    0
  ));

  global $n;

  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Просмотр сообщения")) {
    $tbl->RowEvent2 = "if(window.locationLocked === undefined || window.locationLocked ==false) { document.location.href=\"?section=mls&subsection=" . MLS_SELECTED_INBOX . "&type=o&read=%var.message_id%\";} else window.locationLocked = false;";
  }

  $tbl->OnRowStart = "_set_row_color";

  $columns_resource = Roles::getColumns($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"]);

  if (!is_resource($columns_resource)) {
    $GUI->ERR($columns_resource);
    page_reload();
  }

  $new_columns= array();
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


	
	$GUI->Vars['tm_started'] = db::get_single_value("select p_value from ofc_sys_log where p_name='email_notify_last_tm_start'");
	$ds = time() - $GUI->Vars['tm_started'];
	if ($ds < 60)
		$GUI->Vars['tm_info'] = "менее минуты";
	else if ($ds < 60*15)
		$GUI->Vars['tm_info'] = ceil($ds/60) . " мин.";
	else 
		$GUI->Vars['tm_info'] = "более 15 мнут";
	
	$GUI->Vars['tm_needcnt'] = db::get_single_value("select count(id) from ofc_email_notifications");
	$GUI->Vars['tm_working'] = db::get_single_value("select p_value from ofc_sys_log where p_name='email_notify_last_tm_work'");
	$GUI->Vars['tm_allcnt'] = db::get_single_value("select p_value from ofc_sys_log where p_name='email_notify_last_all_cnt'");
	$GUI->Vars['tm_goodcnt'] = db::get_single_value("select p_value from ofc_sys_log where p_name='email_notify_last_good_cnt'");
	
  
?>