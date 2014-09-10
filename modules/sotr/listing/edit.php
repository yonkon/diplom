<?php

use Components\Entity\Employee;
use Components\Entity\EmployeeBlack;
use Components\Classes\Author;

if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $GUI->informer->ERR(PERMISSION_DENIED);
  page_ReloadSubSec();
}

$id = intval($_REQUEST["edit"]);

$employer = Employee::find($id);

if (!$employer) {
  $GUI->informer->ERR("Запись не найдена");
  page_ReloadSubSec();
}

need_data('data_napravl');

if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
  if ($_SESSION["user"]["data"]["id"] != $id) {
    $GUI->cmdmenu->AddItem("Удалить", "?section=sotr&subsection=2&del=" . $id);
  }
}
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Написать")) {
  $GUI->cmdmenu->AddItem("Написать", "?section=mls&subsection=1&_to=u" . $id);
}
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "История переписки")) {
  $GUI->cmdmenu->AddItem("История переписки", "?section=sotr&subsection=2&msgs=" . $id);
}
if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Занятость")) {
  $GUI->cmdmenu->AddItem("Занятость", "?section=sotr&subsection=2&zan=" . $id);
}

$c = text_lines_count($employer["comments"]);
if ($c < 4) {
  $c = 4;
}
if ($c > 10) {
  $c = 10;
}
$wc = 16 * $c;
$c = text_lines_count($employer["cont"]);
if ($c < 4) {
  $c = 4;
}
if ($c > 10) {
  $c = 10;
}
$wcn = 16 * $c;
$author_group_id = get_role_id_by_name('Автор');

$ypos = 10;
$height = 480;
if ($employer["group_id"] == $author_group_id) {
  $height += 45;
}
$frm = $GUI->Form("Редактировать сотрудника", 500, $height + $wc + $wcn);
$h = $frm->Hidden($id);
$h->linkName = 'user_id';

$frm->Label("Фамилия, Имя, Отчество", 10, $ypos);
$t = $frm->Text(10, $ypos += 20, 480, $employer["fio"]);
$t->linkName = 'fio';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(60));

$frm->Label("Пароль (" . PASSWORD_MIN_CHARS . "-" . PASSWORD_MAX_CHARS . ") (оставить пустым)", 10, $ypos += 30);
$frm->Label("email", 260, $ypos);

$t = $frm->Text(10, $ypos += 20, 230, $employer['password']);
$t->linkName = 'password';
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(PASSWORD_MAX_CHARS));
$t->AddValidator(new CGUI_VALIDATOR_AZaz09());

$t = $frm->Text(260, $ypos, 230, $employer["email"]);
$t->linkName = 'email';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_EMAIL);

$frm->Label("Телефон", 10, $ypos += 30);
$frm->Label("Группа", 260, $ypos);

$t = $frm->Text(10, $ypos += 20, 230, $employer["telnum"]);
$t->linkName = 'phone';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_TELNUM);

$group_field = $frm->Select(260, $ypos, 230, $groups, "name", $employer["group_id"]);
$group_field->linkName = 'group';
$group_field->AddValidator(new CGUI_VALIDATOR_NOZERO);

$frm->Label("Филиал", 10, $ypos += 30);
$l = $frm->Label("Направление автора", 260, $ypos);
$l->display = 'none';

$d = $data_filials;
$d[0] = array("name" => "- выберите");
$t = $frm->Select(10, $ypos += 20, 230, $d, "name", $employer["filial_id"]);
$t->linkName = 'filial';

$t = $frm->Select(260, $ypos, 230, $data_napravl, "name", Author::get_napravl($id));
$t->linkName = 'author_napravl';
$t->display = 'none';
$t->Multiple = true;
$t->RowSize = 4;
if ($employer["group_id"] == $author_group_id) {
  $t->display = 'block';
  $l->display = 'block';
  $ypos += 45;
}
$group_field->AddJsEvent('change', 'check_is_author(' . $author_group_id . ', ' . $group_field->idname . ', ' . $t->idname . ')');

$frm->Label("Дополнительные контакты", 10, $ypos += 30);
$t = $frm->TextArea(10, $ypos += 20, 480, $wcn, $employer["cont"]);
$t->linkName = 'contacts';

$frm->Label("Комментарии", 10, $ypos += ($wcn + 30));
$t = $frm->TextArea(10, $ypos += 20, 480, $wc, $employer["comments"]);
$t->linkName = 'comments';

$frm->Label("Реквизиты для оплаты", 10, $ypos += ($wc + 10));
$t = $frm->TextArea(10, $ypos += 20, 480, 50, $employer["payment_requisites"]);
$t->linkName = 'payment_requisites';

$frm->Label("Блокировка", 10, $ypos += 60);
$t = $frm->Checkbox(100, $ypos, $employer["blocked"], 1);
$t->linkName = 'blocked';

$frm->VLine(10, $ypos += 40, 480);
$frm->Button("Сохранить", 160, $ypos += 20, 80, true);
$frm->OnExecute = "editsotr_exec";
$b = $frm->Button("К списку", 260, $ypos, 80);
$b->Event = "document.location.href=\"?section=sotr&subsection=2\"; return false;";
