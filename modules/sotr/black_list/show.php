<?php

use Components\Entity\EmployeeBlack;
use Components\Classes\Author;

if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $GUI->informer->ERR(PERMISSION_DENIED);
  page_ReloadSubSec();
}

$id = intval($_REQUEST["edit"]);

$employer = EmployeeBlack::find($id);

if (!$employer) {
  $GUI->informer->ERR("Запись не найдена");
  page_ReloadSubSec();
}

need_data('data_napravl');
$frm = $GUI->Form("Данные по сотруднику", 600, 400);

$frm->Label("ФИО: <b>" . $employer["fio"] . "</b>", 		10, $ypos += 10);
$frm->Label("Email: <b>" . $employer["email"] . "</b>", 	10, $ypos += 30);
$frm->Label("Пароль: <b>" . $employer['password'] . "</b>", 10, $ypos += 30);
$frm->Label("Телефон: <b>". $employer["telnum"] ."</b>", 	10, $ypos += 30);
$frm->Label("Группа: <b>". (isset($groups[$employer["group_id"]]) ? $groups[$employer["group_id"]]["name"] : "неизвестно") ."</b>", 10, $ypos += 30);
$frm->Label("Филиал: <b>" . (isset($data_filials[$employer["filial_id"]]) ? $data_filials[$employer["filial_id"]]["name"] : "неизвестно") . "</b>", 10, $ypos += 30);

$frm->Label("Причина удаления:", 10, $ypos += 30);
$t = $frm->TextArea(10, $ypos += 30, 580, 100, $employer["comments"]);
$ypos += 100;

$frm->VLine(10, $ypos += 20, 580);
$b = $frm->Button("К списку", 260, $ypos+= 10, 80);
$b->Event = "document.location.href=\"?section=sotr&subsection=3\"; return false;";
