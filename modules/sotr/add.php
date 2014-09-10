<?php
//add
$ypos = 10;
$author_group_id = get_role_id_by_name('Автор');
need_data('data_napravl');

$height = 540;

$frm = $GUI->Form("Добавить сотрудника", 500, $height);

$frm->Label("Фамилия, Имя, Отчество", 10, $ypos);
$t = $frm->Text(10, $ypos += 20, 480);
$t->linkName = 'fio';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(60));

$frm->Label("Пароль (" . PASSWORD_MIN_CHARS . "-" . PASSWORD_MAX_CHARS . ")", 10, $ypos += 30);
$frm->Label("email", 260, $ypos);

$t = $frm->Text(10, $ypos += 20, 230);
$t->linkName = 'password';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_MINLEN(PASSWORD_MIN_CHARS));
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(PASSWORD_MAX_CHARS));
$t->AddValidator(new CGUI_VALIDATOR_AZaz09());

$t = $frm->Text(260, $ypos, 230);
$t->linkName = 'email';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_EMAIL);

$frm->Label("Телефон", 10, $ypos += 30);
$frm->Label("Группа", 260, $ypos);

$t = $frm->Text(10, $ypos += 20, 230);
$t->linkName = 'phone';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_TELNUM);

$group_field = $frm->Select(260, $ypos, 230, $groups, "name");
$group_field->linkName = 'group';
$group_field->AddValidator(new CGUI_VALIDATOR_NOZERO);

$frm->Label("Филиал", 10, $ypos += 30);
$l = $frm->Label("Направление автора", 260, $ypos);
$l->display = 'none';

$d = $data_filials;
$d[0] = array("name" => "- выберите");
$t = $frm->Select(10, $ypos += 20, 230, $d, "name");
$t->linkName = 'filial';

$t = $frm->Select(260, $ypos, 230, $data_napravl, "name");
$t->linkName = 'author_napravl';
$t->display = 'none';
$t->Multiple = true;
$t->RowSize = 4;
if (isset($_REQUEST["FORMS_DATA"][$group_field->fid]) && $_REQUEST["FORMS_DATA"][$group_field->fid][$group_field->id] == $author_group_id) {
  $l->display = 'block';
  $t->display = 'block';
  $height += 45;
  $ypos += 45;
}
$group_field->AddJsEvent('change', 'check_is_author(' . $author_group_id . ', ' . $group_field->idname . ', ' . $t->idname . ')');

$frm->Label("Дополнительные контакты", 10, $ypos += 30);
$t = $frm->TextArea(10, $ypos += 20, 480, 50);
$t->linkName = 'contacts';

$frm->Label("Комментарии", 10, $ypos += 60);
$t = $frm->TextArea(10, $ypos += 20, 480, 50);
$t->linkName = 'comments';

$frm->Label("Реквизиты для оплаты", 10, $ypos += 60);
$t = $frm->TextArea(10, $ypos += 20, 480, 50);
$t->linkName = 'payment_requisites';

$frm->VLine(10, $ypos += 60, 480);
$frm->Button("Добавить", 160, $ypos += 20, 80, true);
$frm->OnExecute = "addsotr_exec";
$b = $frm->Button("К списку", 260, $ypos, 80);
$b->Event = "document.location.href=\"?section=sotr&subsection=2\"; return false;";

$frm->height = $height;