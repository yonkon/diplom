<?php

use Components\Classes\Roles;
use Components\Exceptions\Exception;
use Components\Entity\Order;
use Components\Classes\Author;
use Components\Entity\AuthorNotification;

need_data('data_vuz');
need_data('data_worktypes');
need_data('data_napravl');
need_data('data_discip');
need_data('data_payments');
need_data('data_filials');

  const LISTING_PAGE_VIEW = 1;
  const LISTING_PAGE_EDIT = 2;
  const LISTING_PAGE_MANAGING = 3;
  const LISTING_PAGE_FILES = 4;
  const LISTING_PAGE_CHANGE_HISTORY = 5;
  const LISTING_PAGE_REMOVE_OR_ASSIGN_AUTHOR = 6;
  const LISTING_PAGE_NOTIFY = 7;

  const NOTIFICATION_TYPE_NORMAL = 1;
  const NOTIFICATION_TYPE_URGENT = 2;


page_ScriptNeed("gui_table.js", "gui");

if (isset($_REQUEST["p"])) {
  $order_id = intval(@$_REQUEST["order"]);
  try {
    $order_info = Order::find($order_id);
  } catch(Exception $e) {
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
    case LISTING_PAGE_VIEW:
      require_once('listing/view.php');
      break;
    case LISTING_PAGE_EDIT:
      require_once('listing/edit.php');
      break;
    case LISTING_PAGE_MANAGING:
      require_once('listing/managing.php');
      break;
    case LISTING_PAGE_FILES:
      require_once('listing/files.php');
      break;

    case LISTING_PAGE_CHANGE_HISTORY:
      require_once('listing/change_history.php');
      break;

    case LISTING_PAGE_REMOVE_OR_ASSIGN_AUTHOR:
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

      $orderHiddenField = $frm->Hidden($order_id);
      $orderHiddenField->linkName = "order_id";

      $h = $frm->Hidden($order_info['author_id']);
      $h->linkName = "old_author_id";

      $b = $frm->Button("К списку", 160, 40 + $y, 80);
      $b->Event = 'document.location.href="?section=ord&subsection=2&p=3&order=' . $order_id . '"; return false;';
      break;

    case LISTING_PAGE_NOTIFY:
      //send author notification
      if (empty($order_info['author_id'])) {
        page_ReloadSec();
      }

      $type = NOTIFICATION_TYPE_NORMAL;

      if (!empty($_GET['t'])) {
        $type = $_GET['t'];
      }

      switch($type) {
        case NOTIFICATION_TYPE_NORMAL:
        default:

		if(!\Components\Entity\EmailNotificationType::isPersistable(\Components\Entity\EmailNotification::TO_AUTHOR_ON_REMIND_NORMAL))
		{
			$GUI->ERR("Напоминание отключено!");
		        page_ReloadSec();
			return;
		}

          $failed_emails = Author::saveMessageAndEnqueueEmail(
            $order_id,
            array($order_info['author_id']),
            'u'.$_SESSION['user']['data']['id'],
            'Напоминание по заказу №' . $order_id . ' ' . $order_info['subject'],
            'Уважаемый автор, напоминаем Вам о том, что данный заказ должен быть прислан Вами на почту или прикреплен на сайте в личном кабинете сегодня. Сообщите о состоянии заказа.
С уважением, ' . sotr_getFullName($_SESSION['user']['data']['id']),
            \Components\Entity\EmailNotification::TO_AUTHOR_ON_REMIND_NORMAL
          );
          break;

        case NOTIFICATION_TYPE_URGENT:

		if(!\Components\Entity\EmailNotificationType::isPersistable(\Components\Entity\EmailNotification::TO_AUTHOR_ON_REMIND_URGENT))
		{
			$GUI->ERR("Напоминание отключено!");
		        page_ReloadSec();
			return;
		}

          $failed_emails = Author::saveMessageAndEnqueueEmail(
            $order_id,
            array($order_info['author_id']),
            'u'.$_SESSION['user']['data']['id'],
            'СРОЧНО ответьте по заказу №' . $order_id . ' ' . $order_info['subject'],
            'Срочно ответьте о состоянии данного заказа, по которому дата сдачи Вами сорвана. Предупреждаем что срыв срока заказа позволит нам не выплатить Вам гонорар и/или наложить штраф. Мы всегда выполняем свои обязательства по оплате перед Вами и ждем с Вашей стороны того же, а именно соблюдение сроков и требований. Спасибо за понимание. С уважением, ' . sotr_getFullName($_SESSION['user']['data']['id']),
            \Components\Entity\EmailNotification::TO_AUTHOR_ON_REMIND_URGENT
          );
          break;
      }

      if (!empty($failed_emails)) {
        $GUI->ERR("Не удалось отправить заказ на " . $failed_emails[0]['email']);
        page_ReloadSec();
      }

      AuthorNotification::create(array(
        'author_id' => $order_info['author_id'],
        'order_id' => $order_id,
        'date' => date('Y-m-d H:i:s'),
        'type' => $type,
      ));
      $GUI->OK("Напоминание отправлено");
      page_ReloadSec();
      break;
  }
} else {
  require_once('listing/list.php');
}