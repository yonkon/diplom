<?php

use Components\Classes\Roles;
use Components\Classes\db;

use Components\Entity\Message;

if (isset($_REQUEST["read"])) {
  $message = Message::find(intval($_REQUEST["read"]));

  if ($message) 
  {
    $GUI->tmpls[] = $active_module_root . "read.tmpl.php";
    include("inc_read.php");
    return;
  } else {
    $GUI->ERR("Письмо не найдено");
    page_ReloadSubSec();
  }
}

///////////////////////////////////
// Временно костыль

$Filter = $GUI->FltrCol("messages_in", "");
$Filter->SrcTable = TBL_PREF . "messages";
$Filter->DstTable = "messages_tmp_" . $_SESSION["user"]["data"]["id"];

if (is_author($_SESSION['user']['data']['id']))
{
	
	define("MLS_SHOW_MODE_ALL", 0);
	define("MLS_SHOW_MODE_ORDER", 1);
	define("MLS_SHOW_MODE_REMIND", 2);
	
	function _add_button($capt, $name, $val)
	{
		global $GUI;
		
		$act = isset($_REQUEST[$name]) && ($_REQUEST[$name] == $val);
		$class = ($act ? 'show_as_active_button' : 'show_as_button');
		
		$uri = $_SERVER['QUERY_STRING'];
		
		$uri = preg_replace("/&".$name."=[^&]/", "", $uri);
		$uri .= "&".$name."=".$val;
			
		$link = "?" . $uri;
		
		$GUI->Vars['buttons'][] = '<a style="border: 1px solid #c9c9c9;" class="' . $class . '" href="' . $link . '">'.$capt.'</a>';
		
		return $act;	
	}
	
	$show_mode = MLS_SHOW_MODE_ALL;
	if (!isset($_REQUEST[mls_show_mode])) $_REQUEST[mls_show_mode] = 0;
	
	_add_button("Все входящие", "mls_show_mode", MLS_SHOW_MODE_ALL);
	_add_button("На вас назначен заказ", "mls_show_mode", MLS_SHOW_MODE_ORDER) && ($show_mode = MLS_SHOW_MODE_ORDER);
	_add_button("Напоминания по заказам", "mls_show_mode", MLS_SHOW_MODE_REMIND) && ($show_mode = MLS_SHOW_MODE_REMIND);
	
	if ($show_mode)
	{
		switch ($show_mode)
		{
		case MLS_SHOW_MODE_ORDER:
			$f_txt = $Filter->AddFilter("CGUI_FilterLikeText");
			$f_txt->name = "Тема";
			$f_txt->keyid = "subject";
			$f_txt->text = "На вас назначен заказ №%";
			
			$fts = $Filter->MakeTmpSet();
			$fts->UseFilter($f_txt->id);
			break;		
		
		case MLS_SHOW_MODE_REMIND:
			$f_txt = $Filter->AddFilter("CGUI_FilterLikeText");
			$f_txt->name = "Тема";
			$f_txt->keyid = "subject";
			$f_txt->text = "Напоминание по заказу №%";
			
			$fts = $Filter->MakeTmpSet();
			$fts->UseFilter($f_txt->id);
			break;
		}
	}
}

$Filter->Requests();
$Filter->Filtering();

///////////////////////////////////

$tbl = $GUI->Table("mls_in", array("cur_sort_up" => true));
$tbl->Width = "100%";
$tbl->DataMYSQL($Filter->DstTable);
$tbl->FilterMYSQL("addr='u" . $_SESSION["user"]["data"]["id"] . "' AND basket=0");
$tbl->OrderingMYSQL("readed");

if (!$show_mode)
	$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
	  10,
	  20,
	  50,
	  100,
	  0
	));


global $n;

if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Просмотр сообщения")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=mls&subsection=2&type=i&read=%var%\"";
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

// Панелька с групповыми кнопками
$gp = $GUI->BtnsPanel("_MlsGrpCmds", false, CGUI_ButtonsPanel::VISIBLE_HIDDEN); // id = 'cgui_bpanel_idu_MlsGrpCmds'
$gp->AddJsButton("Прочитано", "make_mls_readed()");
$gp->AddJsButton("В корзину", "move_mls_to_trash()");

// Фиксированная колонка для чекбокса
function tp_select_cb($v, $data)
{
	$ret = "<div class='mls_row_selector_box'><input type='hidden' id='message_id' value='".$data["id"]."'><input type='checkbox' class='mls_row_selector ";
	$ret .= ($data["readed"]) ? "mls_row_readed" : "mls_row_unreaded";
	$ret .= "'></div>";
	
	return $ret;
}

$r = $tbl->NewColumn();
$r->Caption = "";
$r->Process = "tp_select_cb";
$r->menu = new CGUI_TableMenu();
$r->menu->AddJsCommand("Все", "sel_all_messages()");
$r->menu->AddJsCommand("Все непрочитанные", "sel_unreaded_messages()");
$r->menu->AddJsCommand("Все прочитанные", "sel_readed_messages()");
$r->menu->AddJsCommand("Сбросить", "unsel_all_messages()");

foreach ($new_columns as $column) 
{
  if (isset($column['internal_name']) && in_array($column['internal_name'], $column_group_name)) 
  {
    continue;
  }
  
  $r = null;
  
  if (isset($column['custom']) && count($column['custom'])) 
  {
    $r = $tbl->NewColumn();
    		
    foreach ($column['custom'] as $custom_column) 
    {
      $r1 = new CGUI_TableColumn();
      $r1->Caption = $custom_column['name'];
      $r1->DoSort = $custom_column['do_sort'];
      $r1->Key = $custom_column['internal_name'];
      $r1->Align = $custom_column['align'];
      $r1->Process = $custom_column['on_execute'];
      $r->Custom[] = $r1;
    }
  }
  else
  {
    $r = $tbl->NewColumn();
	
    $r->Caption = $column['name'];
    $r->DoSort = $column['do_sort'];
    $r->Key = $column['internal_name'];
    $r->Align = $column['align'];
    $r->Process = $column['on_execute'];
  }
  
  	if ($column['internal_name'] == "id")
	{
		$tbl->DefaultSortBy($r, true);
	}
  
}
