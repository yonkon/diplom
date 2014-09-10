<?php

use Components\Entity\Meeting;
use Components\Entity\Order;

//Редактирование встречи

//      if (!user_has_right("vis_w")) {
//        page_reloadSec();
//      }
$GUI->mmenu->selected->selected->caption = "Редактирование встречи";

$vis_id = intval($_REQUEST["visit"]);
$vis = Meeting::find($vis_id);
if (!$vis) {
  $GUI->ERR("Встреча не найдена");
  page_reloadSec();
}

$kln = kln_get($vis["client_id"]);

$ypos = 0;
$frm = $GUI->Form("Редактирование встречи №" . $vis["id"], "600", "0");
$frm->OnExecute = "editvisit_exec";
$h = $frm->Hidden($vis["id"]);
$h->linkName = "vid";

$frm->Label("Клиент: <b>" . $kln["fio"] . "</b>", 10, $ypos += 10);
$b = $frm->Button("Инфо", 520, $ypos, 70);
$b->Event = 'window.open("?section=kln&subsection=2&edit=' . $kln["id"] . '");';

$ord = false;
if ($vis["order_id"]) {
  $ord = Order::find($vis["order_id"]);
  $frm->Label("Заказ: <b>" . $ord["id"] . ". " . utils_crop_text($ord["subject"], 110) . "</b>", 10, $ypos += 30);
  $b = $frm->Button("Инфо", 520, $ypos, 70);
  $b->Event = 'window.open("?section=ord&subsection=2&p=1&order=' . $ord["id"] . '");';
}

if ($vis["filial_id"] > 0) {
  // филиал
  $s = "филиал '" . $data_filials[$vis["filial_id"]]["name"] . "'";
} else {
  // курьер
  $s = "с курьером";
}
$frm->Label("Место проведения: <b>" . $s . "</b>", 10, $ypos += 30);

$frm->Label("Проводит: <b>" . sotr_getFullName($vis["user_id"]) . "</b>", 10, $ypos += 30);

$frm->VLine(10, $ypos += 30, 580);

$frm->Label("Цель встречи:", 10, $ypos += 20);
$t = $frm->TextArea(25, $ypos += 20, 560, 80, $vis["about"]);
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->linkName = "about";
$ypos += 80;

$frm->Label("Статус", 10, $ypos += 20);
$s = $frm->Select(25, $ypos += 20, 150, $vis_statuses, "", $vis["status"]);
$s->linkName = "status";

$frm->Label("Отчет", 10, $ypos += 30);
$t = $frm->TextArea(25, $ypos += 20, 560, 80, $vis["report"]);
$t->linkName = "report";

$frm->Label("Деньги:", 10, $ypos += 90);
$dir = 1;
if ($vis["summa"] < 0) {
  $dir = -1;
}
$s = $frm->Select(25, $ypos += 20, 100, array(1 => "получить", -1 => "вернуть"), "", $dir);
$s->linkName = "money_dir";
$frm->Label("сумма: ", 130, $ypos + 3);
$t = $frm->Text(180, $ypos, 100, abs($vis["summa"]));
$t->linkName = "summa";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_09);

$frm->Label("фактически: ", 290, $ypos + 3);
$t = $frm->Text(370, $ypos, 100, abs($vis["summaf"]));
$t->linkName = "summaf";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_09);

$frm->Label("Дата встречи", 10, $ypos += 30);
$frm->Label("Начало", 150, $ypos);
$frm->Label("Окончание", 240, $ypos);

$d = $frm->DatePic(25, $ypos += 20, 100, ($vis["date"]));
$d->linkName = "date";
$d->AddValidator(new CGUI_VALIDATOR_NOEMPTY());

$t1 = $frm->TimePic(170, $ypos, 50, $vis["tm_start"]);
$t1->min_step = 5;
$t1->linkName = "start";
$t2 = $frm->TimePic(260, $ypos, 50, $vis["tm_finish"]);
$t2->min_step = 5;
$t2->linkName = "finish";

if ($vis["filial_id"] == -1) {
  $frm->Label("Станция", 320, $ypos - 20);
  need_data("subway_stations");
  $stations = array();
  foreach ($subway_stations as $station) {
    $stations[$station['id']] = $station['name'];
  }
  $ss = $frm->Select(340, $ypos, 250, array(0 => "-выберите-") + $stations, "", $vis['station_id']);
  $ss->AddValidator(new CGUI_VALIDATOR_NOZERO);
  $ss->linkName = "station";

  $frm->Label("Описание клиента:", 10, $ypos += 30);

  $t = $frm->TextArea(10, $ypos += 20, 280, 80, $vis['opisanie_klienta']);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $t->linkName = "opisanie_klienta";

  $frm->Label("Описание пути:", 300, $ypos - 20);

  $t = $frm->TextArea(310, $ypos, 280, 80, $vis['opisanie_pyti']);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $t->linkName = "opisanie_pyti";
  $ypos += 60;
}

$frm->VLine(10, $ypos += 40, 580);
$ypos += 20;
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $frm->Button("Сохранить", 190, $ypos, 100, true);
}
$b = $frm->Button("Назад", 310, $ypos, 100, false);
$b->Event = "document.location.href='?section=vis&subsection=2'";
$frm->height = $ypos + 60;