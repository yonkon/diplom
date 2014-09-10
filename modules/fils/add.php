<?php
$frm = $GUI->Form("Добавить филиал", 600, 420);
$ypos = 10;

$frm->Label("Название", 10, $ypos);
$frm->Label("Руководитель", 310, $ypos);

$t = $frm->Text(10, $ypos += 20, 278);
$t->linkName = 'name';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));

$usrs = array();
$usrs[0] = "-выберите-";
$ruk_group_id = get_role_id_by_name('Руководитель');
$elder_manager_group_id = get_role_id_by_name('Старший менеджер');
foreach ($data_users as $u) {
  if ($u["black_list"]) {
    continue;
  }
  if ($u["group_id"] == $ruk_group_id || $u["group_id"] == $elder_manager_group_id) {
    $usrs[$u["id"]] = sotr_getFullName($u["id"]);
  }
}
$f = $frm->Select(310, $ypos, 278, $usrs);
$f->linkName = 'manager';
$f->AddValidator(new CGUI_VALIDATOR_NOZERO());

$h = $frm->Hidden('');
$h->linkName = 'city';
city_modal($h->idname);
$b = $frm->Button("Города", 10, $ypos += 30, 70);
$b->Event = 'jQuery("#' . $GUI->Vars["city_modal_form"]->idname . '").modal();';

$frm->Label("Email филиала", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 573);
$t->linkName = 'email';

$frm->Label("Адрес сайта", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 573, "http://");
$t->linkName = 'url';

$frm->Label("Путь к форме заказа", 10, $ypos += 30);
$t = $frm->Text(10, $ypos += 20, 573, "");
$t->linkName = 'order_form_path';

$frm->Label("Описание", 10, $ypos += 30);
$t = $frm->TextArea(10, $ypos += 20, 573, 50);
$t->linkName = 'description';

$frm->Label("Заметки", 10, $ypos += 70);
$t = $frm->TextArea(10, $ypos += 20, 573, 50);
$t->linkName = 'notes';

$frm->Label("Время работы", 10, $ypos += 70);
$frm->Label("Использовать как филиал по-умолчанию", 300, $ypos);

$frm->Label("c", 20, $ypos += 20);
$t = $frm->TimePic(30, $ypos, 80, "09:00");
$t->min_step = 10;
$t->linkName = 'time_from';
$frm->Label("по", 120, $ypos);
$t = $frm->TimePic(140, $ypos, 80, "18:00");
$t->min_step = 10;
$t->linkName = 'time_to';

$c = $frm->Checkbox(300, $ypos, false, 1);
$c->linkName = 'default';
$c->defval = 1;

$frm->Label("Доход, %", 10, $ypos += 50);
$frm->Label("Расход, %", 100, $ypos);

$t = $frm->Tracker(10, $ypos += 20, 80);
$t->linkName = 'profit';
$t->MaxVal = 100;
$t->MinVal = 0;

$t = $frm->Tracker(100, $ypos, 80);
$t->linkName = 'consumption';
$t->MaxVal = 100;
$t->MinVal = 0;

$frm->VLine(10, $ypos += 40, 580);
$frm->Button("Добавить", 210, $ypos += 20, 80, true);
$frm->OnExecute = "addfilial_exec";
$b = $frm->Button("К списку", 310, $ypos, 80);
$b->Event = "document.location.href=\"?section=fils&subsection=2\"; return false;";
$frm->height = $ypos + 70;