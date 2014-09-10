<?php

use Components\Classes\Roles;

use Components\Entity\Order;

need_data('data_vuz');
need_data('data_worktypes');
need_data('data_napravl');
need_data('data_discip');
need_data('data_payments');
need_data('data_filials');

page_ScriptNeed("gui_table.js", "gui");

if (isset($_REQUEST["p"])) {
  $order_id = intval(@$_REQUEST["order"]);
  $order_info = get_order_info($order_id);
  if (!$order_info) {
    $GUI->ERR("Заказ не найден");
    page_ReloadSec();
  }
  $GUI->mmenu->selected->selected->caption = "Управление заказом #" . $order_id;

  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Просмотр содержания")) {
    $GUI->cmdmenu->AddItem("Просмотр содержания", "?section=ord&subsection=2&order=" . $order_id . "&p=1");
  }
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Правка содержания")) {
    $GUI->cmdmenu->AddItem("Правка содержания", "?section=ord&subsection=2&order=" . $order_id . "&p=2");
  }
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Распределение")) {
    $GUI->cmdmenu->AddItem("Распределение", "?section=ord&subsection=2&order=" . $order_id . "&p=3");
  }
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Файлы")) {
    $GUI->cmdmenu->AddItem("Файлы", "?section=ord&subsection=2&order=" . $order_id . "&p=4");
  }
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Назначить встречу")) {
    $GUI->cmdmenu->AddItem("Назначить встречу", "?section=vis&subsection=1&kln=" . $order_info["klient_id"] . "&ord=" . $order_id);
  }
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Показать встречи")) {
    $GUI->cmdmenu->AddItem("Показать встречи", "?section=vis&subsection=2&kln=" . $order_info["klient_id"] . "&ord=" . $order_id);
  }
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "История заказа")) {
    $GUI->cmdmenu->AddItem("История заказа", "?section=ord&subsection=2&order=" . $order_id . "&p=5");
  }
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Отправить сообщение")) {
    $GUI->cmdmenu->AddItem("Отправить сообщение", "?section=mls&subsection=1&_order=" . $order_id);
  }

  $p = 1;
  if (isset($_REQUEST["p"])) {
    $p = intval($_REQUEST["p"]);
  }

  $defdata = array();
  $defdata["klient"] = false;
  $defdata["vuz"] = 0;
  $defdata["vuz_usr"] = "";
  $defdata["kurs"] = 0;
  $defdata["work"] = 0;
  $defdata["work_usr"] = "";
  $defdata["napr"] = 0;
  $defdata["disc"] = 0;
  $defdata["disc_usr"] = "";
  $defdata["date"] = "";
  $defdata["prakt"] = 0;
  $defdata["pgmin"] = "";
  $defdata["pgmax"] = "";
  $defdata["srcmin"] = "";
  $defdata["srcmax"] = "";
  $defdata["opl"] = 0;
  $defdata["take"] = 0;
  $defdata["skid"] = 0;
  $defdata["skidt"] = 0;
  $defdata["cost"] = 0;
  $defdata["fontnm"] = 14;
  $defdata["fontsz"] = 14;
  $defdata["interval"] = 0;
  $defdata["links"] = 0;
  $defdata["pole_t"] = 20;
  $defdata["pole_b"] = 20;
  $defdata["pole_l"] = 30;
  $defdata["pole_r"] = 15;
  $defdata["pagenums"] = 0;
  $defdata["next_rel_date"] = "";

  switch ($p) {
    case 1:
      require_once('listing/view.php');
      break;
    case 2:
      require_once('listing/edit.php');
      break;
    case 3:
      require_once('listing/managing.php');
      break;
    case 4:
      require_once('listing/files.php');
      break;

    case 5:
      require_once('listing/change_history.php');
      break;

    case 6:
      $author_id = intval($_REQUEST["author"]);
      if ($order_info['author_id'] == $author_id) {
        $frm = $GUI->Form("Снять автора с заказа", 300, 200);
        $frm->OnExecute = "remove_author_from_order";
        $frm->Label("Снять " . sotr_getFullName($author_id) . " с заказа №" . $order_id . "?", 10, 10);
        $frm->Label("Причина", 10, 40);
        $t = $frm->TextArea(10, 60, 275, 60);
        $t->linkName = "reason";
        $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
        $y = 100;
        $frm->Button("Снять", 60, 40 + $y, 80, true);
      } else {
        $h = 100;
        if ($order_info['author_id'] != 0) {
          $h = 220;
        }
        $frm = $GUI->Form("Закрепить заказ за автором", 300, $h);
        $frm->OnExecute = "assign_order_to_author";
        $frm->Label("Закрепить заказ №" . $order_id . " за<br>" . sotr_getFullName($author_id) . "?", 10, 10);
        $y = 0;
        if ($order_info['author_id'] != 0) {
          $frm->Label("У этого заказа уже есть автор.<br/>Укажите причину смены автора", 10, 50);
          $t = $frm->TextArea(10, 90, 275, 60);
          $t->linkName = "reason";
          $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
          $y = 120;
        }

        $frm->Button("Закрепить", 60, 40 + $y, 80, true);
      }

      $h = $frm->Hidden($author_id);
      $h->linkName = "author_id";

      $h = $frm->Hidden($order_id);
      $h->linkName = "order_id";

      $h = $frm->Hidden($order_info['author_id']);
      $h->linkName = "old_author_id";

      $b = $frm->Button("К списку", 160, 40 + $y, 80);
      $b->Event = 'document.location.href="?section=ord&subsection=2&p=3&order=' . $order_id . '"; return false;';
      break;
  }
} else {
  require_once('archive/list.php');
}