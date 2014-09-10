<?php

use Components\Entity\Message;
use Components\Entity\EmailNotification;

need_data('data_users');

$addr_book = array();
if (isset($data_mails[$_SESSION["user"]["data"]["group_id"]]["mails"])) {
  $grps = $data_mails[$_SESSION["user"]["data"]["group_id"]]["mails"];
  foreach ($data_users as $v) {
    if (in_array($v["group_id"], $grps)) {
      $addr_book[] = $v;
    }
  }
} else {
  $addr_book = $data_users;
}

page_scriptNeed("scripts.js", "modules/mls");

$frm = $GUI->Form("Новое сообщение", "600", "445");
$ypos = 10;

$need_to = true;
$ansv_to = false;

if (isset($_REQUEST["_to"])) {
  $n = substr($_REQUEST["_to"], 0, 1);
  $id = intval(substr($_REQUEST["_to"], 1));
  if (($n == 'u') && (isset($data_users[$id]))) {
    $h = $frm->Hidden($_REQUEST["_to"]);
    $h->linkName = "to";
    $need_to = false;
    $frm->Label("Кому: " . sotr_getFullName($id), 10, $ypos);
    $ypos += 30;
  }

  if ($n == 'k') {
    $kln = kln_get($id);
    if ($kln) {
      $h = $frm->Hidden($_REQUEST["_to"]);
      $h->linkName = "to";
      $need_to = false;
      $frm->Label("Кому: " . $kln["fio"], 10, $ypos);
      $ypos += 30;
    }
  }
}

if ($need_to) {
  $t = $frm->TextArea(50, $ypos, 530, 50);
  $t->linkName = "to";
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $t->AddJsEvent("keyup", "check_adr_field('" . $t->idname . "');");
  $frm->Label("<a href='#' style='color:black' onclick='show_adr_book(\"" . $t->idname . "\")'><b>Кому</b></a>", 10, $ypos);
  $ypos += 70;
}

if (isset($_REQUEST["_ans"])) {
  $id = intval($_REQUEST["_ans"]);
  $m = Message::find($id);
  if ($m && mls_userMaySee($m, $_SESSION["user"]["data"]["id"]) && ($m["creator_id"] == $_REQUEST["_to"])) {
    $ansv_to = $m;
    $h = $frm->Hidden($id);
    $h->linkName = "ans";
    $frm->Label("В ответ на письмо №" . $id . " от " . date("d.m.y", $m["created"]), 10, $ypos);
    $ypos += 30;
  }
}

$resend_to = false;
if (isset($_REQUEST["_rep"])) {
  $id = intval($_REQUEST["_rep"]);
  // Переслать. Получим письмо, убедимся что пользователь может его видеть
  $m = Message::find($id);
  if ($m && mls_userMaySee($m, $_SESSION["user"]["data"]["id"])) {
    $ansv_to = false;
    $need_to = false;
    $resend_to = $m;
  }
}

$frm->Label("Тема", 10, $ypos);
$s = "";
if ($ansv_to) {
  $s = "[Ответ на исх. №" . $ansv_to["id"] . " от " . date("d.m.y", $ansv_to["created"]) . "]";
}
if ($resend_to) {
  $s = $resend_to["subject"];
}
if (!empty($_REQUEST["_order"])) {
  $s = "Заказ №" . $_REQUEST["_order"];
}
$t = $frm->TextArea(50, $ypos, 530, 30, $s);
$t->linkName = "subj";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);

$ypos += 50;

$frm->Label("Текст", 10, $ypos);
$s = "";
if ($resend_to) {
  $s = $resend_to["text"];
}
$t = $frm->TextArea(50, $ypos, 530, 150, $s);
$t->linkName = "txt";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);

$ypos += 150;

$frm->VLine(10, $ypos += 20, 580);

$ypos += 20;

$frm->Label("Важность", 10, $ypos);
$s = $frm->Select(80, $ypos, 100, array("низкая", "нормальная", "высокая"));
$s->linkName = "prior";

$frm->Label("Ответить до", 200, $ypos);
$d = $frm->DatePic(280, $ypos, 100);
$d->linkName = "date";

$frm->VLine(10, $ypos += 40, 580);
$frm->OnExecute = "sendmessage_exec";

$m = false;
if ($ansv_to) {
  $m = $ansv_to;
}
if ($resend_to) {
  $m = $resend_to;
}

if ($m) {
  $frm->Button("Отправить", 190, $ypos += 20, 100, true);
  $b = $frm->Button("Назад", 310, $ypos, 100, false);
  $tp = "i";
  if ($m["addr"] != "u" . $_SESSION["user"]["data"]["id"]) {
    $tp = "o";
  }
  if ($m["basket"]) {
    $tp = "b";
  }
  $b->Event = "document.location.href='?section=mls&subsection=2&type=" . $tp . "&read=" . $m["id"] . "'";
} else {
  $frm->Button("Отправить", 250, $ypos += 20, 100, true);
}

$frm->height = $ypos + 60;

function sendmessage_exec($Frm, $Err) {
  global $data_users;
  if (!$Err) {
    // Авторам запрещено писать клиентам
    $to_kln = $_SESSION["user"]["data"]["group_id"] != 6;

    //Декодирование адресатов
    $adrs = strtolower($Frm->GetNmValue("to"));
    $adrs = preg_replace("[^uk0-9;]", "", $adrs);
    $adrs = explode(";", $adrs);
    $adrs_k = array();
    $adrs_u = array();
    foreach ($adrs as $v) {
      $ind = intval(substr($v, 1));
      $s = substr($v, 0, 1);

      if ($to_kln && ($s == 'k') && $ind) {
        $adrs_k[] = $ind;
      } else if (($s == 'u') && $ind) {
        $adrs_u[] = $ind;
      }
    }

    if (!count($adrs_u) && !count($adrs_k)) {
      $Frm->_gui->informer->ERR("Неверно указан получатель");
      return;
    }

    foreach ($adrs_u as $v) {
      if (!isset($data_users[$v])) {
        $Frm->_gui->informer->ERR("Указан несуществующий получатель");
        return;
      }
    }

    $srok = 0;
    if (strlen($Frm->GetNmValue("date"))) {
      $d = explode("-", $Frm->GetNmValue("date"));
      $srok = mktime(0, 0, 0, $d[1], $d[0], $d[2]);
      if ($srok < mktime(0, 0, 0, date("n"), date("j"), date("Y"))) {
        $Frm->_gui->informer->ERR("Неверно указан срок ответа");
        return;
      }
    }

    $subj = $Frm->GetNmValueH("subj");
    $text = $Frm->GetNmValueH("txt");
    $prior = $Frm->GetNmValueI("prior");

    $parent_id = 0;
    if ($Frm->GetNmValue("ans")) {
      $parent_id = $Frm->GetNmValue("ans");
    }
    // Для каждого получателя формирутеся свой экземпляр

    foreach ($adrs_k as $a) {
      $message_id = mls_Send("k" . $a, "u" . $_SESSION["user"]["data"]["id"], $subj, $text, $prior, $srok, $parent_id, 0, $a, 0, 0);
      $client = \Components\Entity\Client::find($a);
      if (!empty ($message_id) && !empty ($client['email'])) {
        enqueue_message_to_email($message_id, $client['email'], EmailNotification::TO_RECEIVER_ON_MESSAGE_COMMON);
      }
    }

    foreach ($adrs_u as $a) {
      $message_id = mls_Send("u" . $a, "u" . $_SESSION["user"]["data"]["id"], $subj, $text, $prior, $srok, $parent_id, 0, 0, 0, 0);
        \Components\Classes\Author::enqueue_message_to_email($message_id, array($a), EmailNotification::TO_RECEIVER_ON_MESSAGE_COMMON);
    }

    $Frm->_gui->informer->OK("Сообщение создано");
    page_reloadToSec(3);
  }
}