<?php

use Components\Classes\db;

use Components\Entity\Employee;
use Components\Entity\Message;

$id = intval($_REQUEST["msgs"]);
if (Employee::find($id)) {
  // Показать с какого по какое есть письма, выбрать дату и получить текстовый файл с перепиской
  // фильтр по адресату
  $messages = Message::findBy(array(
    'creator_id' => 'u' . $id,
  ));
  $date_min = 0;
  $date_max = 0;
  $rcps = array();
  foreach ($messages as $v) {
    if ($v["created"] > $date_max) {
      $date_max = $v["created"];
    }
    if (($v["created"] < $date_min) || ($date_min == 0)) {
      $date_min = $v["created"];
    }
  }

  $ypos = 10;
  $frm = $GUI->Form("История переписки сотрудника", 500, 480);
  $t = $frm->Hidden($id);
  $t->linkName = 'employer_id';

  $frm->Label("Сотрудник: " . sotr_getFullName($id), 10, $ypos);
  $frm->Label("Сообщений: " . count($messages) . "; с " . date("d.m.y", $date_min) . " по " . date("d.m.y", $date_max), 10, $ypos += 20);

  $t = $frm->Checkbox(10, $ypos += 20, true, 1);
  $t->linkName = 'with_employers';
  $frm->Label("Переписка с сотрудниками:", 30, $ypos);
  $frm->Label("Дата:", 330, $ypos);

  $data_users1 = array();
  foreach ($data_users as $v) {
    // Проверить что переписка существует
    if (db::get_single_value("SELECT COUNT(id) FROM " . TABLE_MESSAGES . " WHERE (addr='u" . $v["id"] . "' AND creator_id='u" . $id . "') OR (addr='u" . $id . "' AND creator_id='u" . $v["id"] . "')")) {
      $data_users1[$v["id"]] = sotr_getFullName($v["id"]);
    }
  }
  if (count($data_users1) > 1) {
    $data_users1 = array(0 => "-любым-") + $data_users1;
  }

  $s = $frm->Select(10, $ypos += 20, 300, $data_users1);
  $s->RowSize = 5;
  $s->linkName = 'employers';

  $frm->Label("c", 330, $ypos);
  $t = $frm->DatePic(350, $ypos, 100);
  $t->linkName = 'date_from';

  $frm->Label("по", 330, $ypos += 25);
  $t = $frm->DatePic(350, $ypos, 100);
  $t->linkName = 'date_to';

  $frm->Label("Направление", 330, $ypos += 25);
  $s = $frm->Select(330, $ypos += 20, 100, array(0 => "все", 1 => "входящие", 2 => "исходящие"));
  $s->linkName = 'direction';

  // С клиентами
  $clients1 = array();
  $clients = kln_getrawlist();
  foreach ($clients as $v) {
    if (db::get_single_value("SELECT COUNT(id) FROM " . TABLE_MESSAGES . " WHERE (addr='k" . $v["id"] . "' AND creator_id='u" . $id . "') OR (addr='u" . $id . "' AND creator_id='k" . $v["id"] . "')")) {
      $clients1[$v["id"]] = $v['fio'];
    }
  }
  if (count($clients1) > 1) {
    $clients1 = array(0 => "-любым-") + $clients1;
  }

  $t = $frm->Checkbox(10, $ypos += 30, false, 1);
  $t->linkName = 'with_clients';
  $frm->Label("Переписка с клиентами:", 30, $ypos);

  $s = $frm->Select(10, $ypos += 20, 480, $clients1);
  $s->RowSize = 10;
  $s->linkName = 'clients';

  $frm->VLine(10, $ypos += 200, 480);
  $frm->Button("Получить", 250 - 80 - 10, $ypos += 20, 80, true);
  $frm->OnExecute = "getmsgs_exec";
  $b = $frm->Button("Назад", 250 + 10, $ypos, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'msgs')) . "edit=" . $id . "\"; return false;";
} else {
  $GUI->informer->ERR("Запись не найдена");
  page_ReloadSubSec();
}