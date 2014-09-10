<?php
kln_search_modal();
$ypos = 10;

$frm = $GUI->Form("Новый клиент", 400, 680);
$frm->OnExecute = "addclient_exec";

$frm->Label("Имя", 10, $ypos);
$t = $frm->Text(40, 5, 350);
$t->linkName = 'name';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());

$frm->Label("Город", 10, $ypos += 30);
$t = $frm->Text(60, $ypos - 5, 120, "Москва");
$t->linkName = 'city';

if ($_SESSION["user"]["data"]["group_id"] == 1 || $_SESSION["user"]["data"]["group_id"] == 0) {
  $frm->Label("Филиал", 210, $ypos);
  $t = $frm->Select(260, $ypos - 5, 130, $data_filials, "name", $_SESSION['user']['data']['filial_id']); //1
  $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
} else {
  $t = $frm->Hidden($_SESSION["user"]["data"]["filial_id"]);
}
$t->linkName = 'filial_id';

$frm->Label("Пароль(" . PASSWORD_MIN_CHARS . "-" . PASSWORD_MAX_CHARS . ")", 10, $ypos += 30);
$t = $frm->Text(100, $ypos - 5, 190);
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
$t->AddValidator(new CGUI_VALIDATOR_MINLEN(PASSWORD_MIN_CHARS));
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(PASSWORD_MAX_CHARS));
$t->linkName = 'password';

$frm->Label("Случайно", 310, $ypos);
$c = $frm->Checkbox(370, $ypos, true, 1);
$c->linkName = 'random_password';
$c->AddJsEvent("change", " if (jQuery('#" . $c->idname . "').attr('checked')=='checked') { jQuery('#" . $t->idname . "').attr('disabled', 'disabled'); } else { jQuery('#" . $t->idname . "').removeAttr('disabled'); } ");
if ($frm->ExistInReq()) {
  $t->Disabled = $c->GetFromReq();
} else {
  $t->Disabled = true;
}

$frm->Label("Почта", 10, $ypos += 30);
$t = $frm->Text(70, $ypos - 5, 320);
$t->linkName = 'email';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
$t->AddValidator(new CGUI_VALIDATOR_EMAIL());

$frm->Label("Телефон", 10, $ypos += 30);
$t = $frm->Text(70, $ypos - 5, 320);
$t->linkName = 'phone';
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
$t->AddValidator(new CGUI_VALIDATOR_TELNUM());

$frm->Label("ICQ", 10, $ypos += 30);
$t = $frm->Text(50, $ypos - 5, 120);
$t->linkName = 'icq';

$frm->Label("Skype", 200, $ypos);
$t = $frm->Text(250, $ypos - 5, 140);
$t->linkName = 'skype';

$frm->Label("Другие контакты", 10, $ypos += 30);
$t = $frm->Text(120, $ypos - 5, 270);
$t->linkName = 'contacts';

$frm->Label("Заметки", 10, $ypos += 30);
$frm->Text(120, $ypos - 5, 270);
$t->linkName = 'about';

$frm->VLine(10, $ypos += 30, 380);

$frm->Label("Кто привел клиента (ID)", 10, $ypos += 10);
$ref = $frm->Text(10, $ypos += 20, 50);
$ref->linkName = 'ref';

$b = $frm->Button("Найти", 70, $ypos - 2, 80);
$b->Event = 'jQuery("#' . $GUI->Vars["kln_search_modal_form"]->idname . '").modal();';
page_AddScriptText("custom_klient_select_event = function(id){ jQuery('#" . $ref->idname . "').val(id); };");


$frm->Label("Откуда клиент", 210, $ypos - 20);
$t = $frm->Select(210, $ypos, 180, $client_sources, "name");
$t->linkName = 'client_from';

$frm->VLine(10, $ypos += 40, 380);

$frm->Label("Далее", 40, $ypos += 15);
$t = $frm->Select(90, $ypos - 5, 250, array(
  "перейти к списку",
  "перейти к редактированию",
  "перейти к оформлению заказа"
));
$t->linkName = 'next';

$frm->Button("Добавить", 110, $ypos += 30, 80, true);
$b = $frm->Button("К списку", 210, $ypos, 80);
$b->Event = "document.location.href=\"?section=kln&subsection=2\"; return false;";

$frm->height = $ypos + 60;