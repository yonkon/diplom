<?php

$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . "1.tmpl.php";

page_ScriptNeed("scripts.js", "modules/kln");

switch ($n) {
  case 1:
    require_once('add.php');
    break;

  case 2:
    if (isset($_REQUEST['edit'])) {
      require_once('edit.php');
    } elseif (!empty($_REQUEST['action'])) {
      $client_id = $_REQUEST['kln_id'];
      switch ($_REQUEST['action']) {
        case 'history_table':
        default:
          $GUI->Vars["page_hdr"] = "История изменений данных о клиенте №" . $client_id;
          $tbl = $GUI->Table("client_history" . $n);
          $tbl->Width = "50%";
          $tbl->DataMYSQL("clients_changes_history");
          $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
            10, 20, 50, 100, 0
          ));
          $tbl->FilterMYSQL("client_id = " . $client_id);

          $tbl->RowEvent2 = "document.location.href=\"?section=kln&subsection=2&kln_id=" . $client_id . "&change=%var%&action=show_history\"";

          $column = $tbl->NewColumn();
          $column->Caption = "Автор изменений";
          $column->DoSort = true;
          $column->Key = "change_user_id";
          $column->Process = "sotr_getFullName";

          $column = $tbl->NewColumn();
          $column->Caption = "Дата изменений";
          $column->DoSort = true;
          $column->Key = "change_date";
          $column->Process = "format_date";
          break;

        case 'show_history':
          require_once('history.php');
          break;
      }
    } else {
      require_once('list.php');
    }

    break;

  case 3:
    require_once('delete.php');
    break;

  case 4:
    break;
}

require_once('functions.php');