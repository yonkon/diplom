<?php

use Components\Classes\db;

$client_change_history = db::get_single_row("SELECT * FROM " . TABLE_CLIENTS_HISTORY . " WHERE id = " . db::input($_REQUEST['change']));

/******************** before edit start ********************/

$h = 580;

$frm = $GUI->Form("Данные о клиенте до изменений", 400, $h);
$frm->VLine(10, $h - 80, 380);
$b = $frm->Button("Назад", 155, $h - 60, 100);
$b->Event = "document.location.href=\"?section=kln&subsection=2&kln_id=" . $_REQUEST['kln_id'] . "&action=history_table\"";

$ypos = 10;

$frm->Label("Имя", 10, $ypos);
$t = $frm->Text(10, $ypos += 20, 380, $client_change_history["fio_old"]); //1
$t->linkName = "fio";

$frm->Label("Филиал", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 380, get_filial_name($client_change_history["filial_id_old"])); //1
$t->linkName = "filial_id";

$before_edit_pass = 'Старый пароль';
$frm->Label("Пароль", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 380, $before_edit_pass); //1
$t->linkName = "hpwd";

$frm->Label("Почта", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 180, $client_change_history["email_old"]); //4
$t->linkName = "email";

$frm->Label("Телефон", 210, $ypos - 20);
$t = $frm->Text(210, $ypos, 180, $client_change_history["telnum_old"]); //5
$t->linkName = "telnum";

$frm->Label("ICQ", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 180, $client_change_history["icq_old"]); //6
$t->linkName = "icq";

$frm->Label("Skype", 210, $ypos - 20);
$t = $frm->Text(210, $ypos, 180, $client_change_history["skype_old"]); //7
$t->linkName = "skype";

$frm->Label("Город", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 380, $client_change_history["city_old"]); //8
$t->linkName = "city";

$frm->Label("Другие контакты", 10, $ypos += 30);
$t = $frm->TextArea(10, $ypos += 20, 380, 60, $client_change_history["contacts_old"]); //9
$t->linkName = "contacts";

$frm->Label("Заметки", 10, $ypos += 70);
$t = $frm->TextArea(10, $ypos += 20, 380, 60, $client_change_history["about_old"]); //10
$t->linkName = "about";

/******************** before edit end ********************/
/******************** after edit start ********************/

$frm = $GUI->Form("Данные о клиенте после изменений", 400, $h);
$frm->VLine(10, $h - 80, 380);
$b = $frm->Button("Назад", 155, $h - 60, 100);
$b->Event = "document.location.href=\"?section=kln&subsection=2&kln_id=" . $_REQUEST['kln_id'] . "&action=history_table\"";

$ypos = 10;

$frm->Label("Имя", 10, $ypos);
$t = $frm->Text(10, $ypos += 20, 380, $client_change_history["fio_new"]); //1
$t->linkName = "fio";

$frm->Label("Филиал", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 380, get_filial_name($client_change_history["filial_id_new"])); //1
$t->linkName = "filial_id";

if ($client_change_history['hpwd_old'] == $client_change_history['hpwd_new']) {
  $after_edit_pass = $before_edit_pass;
} else {
  $after_edit_pass = 'Был изменен';
}
$frm->Label("Пароль", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 380, $after_edit_pass); //1
$t->linkName = "hpwd";

$frm->Label("Почта", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 180, $client_change_history["email_new"]); //4
$t->linkName = "email";

$frm->Label("Телефон", 210, $ypos - 20);
$t = $frm->Text(210, $ypos, 180, $client_change_history["telnum_new"]); //5
$t->linkName = "telnum";

$frm->Label("ICQ", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 180, $client_change_history["icq_new"]); //6
$t->linkName = "icq";

$frm->Label("Skype", 210, $ypos - 20);
$t = $frm->Text(210, $ypos, 180, $client_change_history["skype_new"]); //7
$t->linkName = "skype";

$frm->Label("Город", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 380, $client_change_history["city_new"]); //8
$t->linkName = "city";

$frm->Label("Другие контакты", 10, $ypos += 30);
$t = $frm->TextArea(10, $ypos += 20, 380, 60, $client_change_history["contacts_new"]); //9
$t->linkName = "contacts";

$frm->Label("Заметки", 10, $ypos += 70);
$t = $frm->TextArea(10, $ypos += 20, 380, 60, $client_change_history["about_new"]); //10
$t->linkName = "about";
/******************** after edit end ********************/