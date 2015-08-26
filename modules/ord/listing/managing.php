<?php

use Components\Classes\Disciplines;
use Components\Exceptions\Exception;

use Components\Entity\AuthorOffer;
use Components\Entity\Order;

//$GUI->Vars["page_hdr"] = "Распределение. Тут назначаем менеджера (если нет то можно назначить себя, а если рук, то любого манагера и переопределить потом), задаем параметры  для авторов";
$GUI->Vars["page_hdr"] = "Распределение заказа №" . $order_id;

$order_status = get_order_status($order_id);
if (!empty($order_status) && !empty($order_info['manager_id'])) {
  if ($_SESSION["user"]["data"]["group_id"] <= 3) {
    //назначить автора
    $h = 120;
    if ($order_info['author_id'] != 0) {
      $h = 220;
    }
    $frm = $GUI->Form("Назначить автора", 300, $h, CGUI_FORM_FLAG_MODAL);
    $frm->OnExecute = "assign_order_to_author";
    $ypos = 0;
    $frm->Label("Автор: ", 10, $ypos += 10);
    $s = $frm->Select(10, $ypos, 280, array(0 => "-выберите-") + \Components\Classes\Author::getActiveAuthorsId_Fio(), "");
    $s->linkName = "author_id";

    if ($order_info['author_id'] != 0) {
      $frm->Label("У этого заказа уже есть автор.<br/>Укажите причину смены автора", 10, 40);
      $t = $frm->TextArea(10, 80, 275, 60);
      $t->linkName = "reason";
      $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
      $ypos += 100;
    }

    $orderHiddenField = $frm->Hidden($order_id);
    $orderHiddenField->linkName = "order_id";

    $h = $frm->Hidden(1);
    $h->linkName = "need_offer";

    $h = $frm->Hidden($order_info['author_id']);
    $h->linkName = "old_author_id";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Назначить", 110, $ypos += 20, 80, true);

    $GUI->Vars["page_top"] = "<a href='#' onclick='cgui_form_modal(\"" . $frm->idname . "\");return false;'>Назначить автора</a>";

    //назначить менеджера
    $h = 120;
    if ($order_info['manager_id'] != 0) {
      $h = 220;
    }
    $frm = $GUI->Form("Назначить менеджера", 300, $h, CGUI_FORM_FLAG_MODAL);
    $frm->OnExecute = "assign_order_to_manager";

    $ypos = 0;

    $manager_list = array();
    foreach(get_users_groups() as $group) {
      if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $group["id"], "Возможность вести заказ")) {
        foreach (get_users_by_group($group["id"], $order_info['filial_id']) as $u) {
          $manager_list[$u['id']] = $u['fio'];
        }
      }
    }

    $frm->Label("Менеджер: ", 10, $ypos += 10);
    $s = $frm->Select(10, $ypos, 280, array(0 => "-выберите-") + $manager_list, "");
    $s->linkName = "manager_id";

    if ($order_info['manager_id'] != 0) {
      $frm->Label("У этого заказа уже есть менеджер.<br/>Укажите причину смены", 10, 40);
      $t = $frm->TextArea(10, 80, 275, 60);
      $t->linkName = "reason";
      $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
      $ypos += 100;
    }

    $h = $frm->Hidden($order_id);
    $h->linkName = "order_id";

    $h = $frm->Hidden($order_info['manager_id']);
    $h->linkName = "old_manager_id";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Назначить", 110, $ypos += 20, 80, true);

    $GUI->Vars["page_top"] .= "<br><a href='#' onclick='cgui_form_modal(\"" . $frm->idname . "\");return false;'>Назначить менеджера</a>";

    //отправить заказ на почту
//          die;
    $frm2 = $GUI->Form("Отправить заказ на почту", 300, 320, CGUI_FORM_FLAG_MODAL);
    $frm2->OnExecute = "send_order_by_email";
    $ypos = 0;

    $frm2->Label("Выберите авторов для отправки заказа", 10, $ypos += 10);
    $frm2->VLine(10, $ypos += 20, 280);

    $discipline_id = $order_info['disc_id'];

    $authors = get_users_by_group_name('Автор', null, false, true);
    $authors = Disciplines::getAuthors($discipline_id);
    arsort($authors);

    if (count($authors) > 1) {
      $authors[join(", ", array_keys($authors))] = 'Отправить всем подписавшимся';
      $authors = array_reverse($authors, true);
    }

    $s = $frm2->Select(10, $ypos += 20, 280, $authors, '');
    $s->Multiple = true;
    $s->RowSize = 10;
    $s->linkName = 'authors';
    $s->name = 'authors';

    $hid = $frm2->Hidden($order_id);
    $hid->linkName = 'order_id';

    $frm2->VLine(10, $ypos += 180, 280);
    $frm2->Button("Отправить", 110, $ypos += 20, 80, true);

    $GUI->Vars["page_top"] .= "<br><a href='#' onclick='cgui_form_modal(\"" . $frm2->idname . "\");return false;'>Отправить заказ на почту</a>";
    //end отправить заказа на почту

    $tbl = $GUI->Table("authors_offers" . $p);
    $tbl->Width = "70%";
    $tbl->DataMYSQL("authors_offers");
    $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
      10, 20, 50, 100, 0
    ));

    $r = $tbl->NewColumn();
    $r->Caption = "Номер";
    $r->DoSort = true;
    $r->Key = "id";
    $r->Align = "center";

    $r = $tbl->NewColumn();
    $r->Caption = "Автор";
    $r->DoSort = true;
    $r->Key = "author_id";
    $r->Align = "center";
    $r->Process = "sotr_getFullNameWithLink";

    $r = $tbl->NewColumn();
    $r->Caption = "Цена";
    $r->DoSort = true;
    $r->Key = "price";
    $r->Align = "center";

    $r = $tbl->NewColumn();
    $r->Caption = "Комментарий автора";
    $r->Key = "comment";

    $r = $tbl->NewColumn();
    $r->Caption = "Отправить сообщение";
    $r->Process = "send_message_to_author";

    $r = $tbl->NewColumn();
    $r->Caption = "Назначить/снять";
    $r->Process = "generate_assign_button";

    $tbl->FilterMYSQL("order_id = " . $order_id);

  } elseif ($_SESSION["user"]["data"]["group_id"] == 6) {
    if ($order_status == "ASSIGNED") {
      $GUI->ERR("Этот заказ уже назначен");
      page_reloadSec();
    }

    if ($offer_info = AuthorOffer::findOneBy(array(
      'order_id' => $order_id,
      'author_id' => $_SESSION['user']['data']['id'],
    ))) {
      $frm = $GUI->Form("Редактировать предложение к заказу №" . $order_id, 400, 270);
      $frm->OnExecute = "edit_offer";
    } else {
      $offer_info = array('price' => '', 'comment' => '');
      $frm = $GUI->Form("Новое предложение к заказу №" . $order_id, 400, 270);
      $frm->OnExecute = "add_offer";
    }

    $ypos = 10;
    $h = $frm->Hidden($order_id);
    $h->linkName = "order_id";

    $h = $frm->Hidden($_SESSION["user"]["data"]["id"]);
    $h->linkName = "author_id";

    $frm->Label("Цена:", 10, $ypos);
    if ($order_info['cost_auth'] != 0) {
      $frm->Label($order_info['cost_auth'] . " " . $ofc_currency, 60, $ypos);
    } else {
      $t = $frm->Text(60, $ypos, 270, $offer_info['price']);
      $t->linkName = "price";
      $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
      $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(10));
      $t->AddValidator(new CGUI_VALIDATOR_09());
      $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
    }

    $ypos += 30;

    $frm->Label("Комментарий автора:", 10, $ypos);
    $t = $frm->TextArea(10, $ypos + 20, 370, 80, $offer_info['comment']);
    $t->linkName = "comment";

    $ypos += 100;

    $frm->VLine(10, $ypos += 40, 370);
    $frm->Button("Сохранить", 70, $ypos += 20, 150, true);
    $b = $frm->Button("К списку", 230, $ypos, 100, false);
    $b->Event = "document.location.href='?section=ord&subsection=2'";
  }
} else {
  if ($_SESSION['user']['data']['group_id'] > get_role_id_by_name('Менеджер')) {
    $GUI->ERR('Заказ еще не распределялся');
    page_reloadSubSec();
  }
  $frm = $GUI->Form("Распределение заказа №" . $order_id, 400, 340);
  $frm->OnExecute = "assign_order";
  $ypos = 10;
  $h = $frm->Hidden($order_id);
  $h->linkName = "order_id";

  $manager_list = array();

  foreach(get_users_groups() as $group) {
    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $group["id"], "Возможность вести заказ")) {
      foreach (get_users_by_group($group["id"], $order_info['filial_id']) as $u) {
        $manager_list[$u['id']] = $u['fio'];
      }
    }
  }

  $frm->Label("Менеджер:", 10, $ypos);
  $s = $frm->Select(10, $ypos += 20, 380, $manager_list);
  $s->linkName = "manager_id";

  $ypos += 40;

  $frm->Label("Дата для автора:", 10, $ypos);
  if ($order_info["time_kln"]) {
    $time_for_author = $order_info["time_kln"] - 172800;
  } else {
    $time_for_author = time();
  }
  $d = $frm->DatePic(140, $ypos - 3, 100, $time_for_author);
  $d->linkName = "time_auth";
  $d->AddValidator(new CGUI_VALIDATOR_NOEMPTY());

  $ypos += 40;

  $frm->Label("Распределить ДО:", 10, $ypos);
  $d = $frm->DatePic(140, $ypos - 3, 100, time() + 86400);
  $d->linkName = "raspred_srok";
  $d->AddValidator(new CGUI_VALIDATOR_NOEMPTY());

  $ypos += 40;

  $frm->Label("Гонорар автора:", 10, $ypos);
  $t = $frm->Text(140, $ypos, 100, $order_info["cost_auth"]);
  $t->linkName = "author_price";
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(10));
  $t->AddValidator(new CGUI_VALIDATOR_09());

  $ypos += 40;

  $frm->Label("Комментарий к оплате:", 10, $ypos);
  $s = $frm->Select(160, $ypos, 200, $data_author_payment_status, "", $order_info["payment_comment"]); //7
  $s->linkName = "payment_comment";
  
  $ypos += 30;
  
  $frm->Label("Выслать уведомления подписанным авторам", 30, $ypos+2);
  $s = $frm->Checkbox(10, $ypos, true);
  $s->linkName = "send_for_authors";
  
  //$s = $frm->Select(160, $ypos, 200, $data_author_payment_status, "", $order_info["payment_comment"]); //7
  
  
//        $t = $frm->TextArea(10, $ypos + 20, 370, 80, $order_info["payment_comment"]);
//        $t->linkName = "payment_comment";

//        $ypos += 100;

  $frm->VLine(10, $ypos += 40, 370);
  $frm->Button("Распределить", 90, $ypos += 20, 100, true);
  $b = $frm->Button("К списку", 210, $ypos, 100, false);
  $b->Event = "document.location.href='?section=ord&subsection=2'";
}
