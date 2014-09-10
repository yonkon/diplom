<?php

use Components\Classes\Roles;

use Components\Entity\Client;

$GUI->mmenu->selected->selected->caption = "Редактирование";

$client = Client::find($_REQUEST["edit"]);
if (!$client) {
  $GUI->ERR("Клиент не найден");
  page_ReloadSec();
}

if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Назначить встречу")) {
  $GUI->cmdmenu->AddItem("Назначить встречу", "?section=vis&subsection=1&kln=" . $client["id"]);
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Принять заказ")) {
  $GUI->cmdmenu->AddItem("Принять заказ", "?section=ord&subsection=1&kln_id=" . $client["id"]);
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Заказы")) {
  $GUI->cmdmenu->AddItem("Заказы", "?section=ord&subsection=2&kln_id=" . $client["id"]);
  $GUI->cmdmenu->AddItem("Заказы (архив)", "?section=ord&subsection=3&kln_id=" . $client["id"]); // maxf 30.03.2014 Чтобы видеть арихивные заказы 
}
if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "История клиента")) {
  $GUI->cmdmenu->AddItem("История клиента", "?section=kln&subsection=2&action=history_table&kln_id=" . $client["id"]);
}

$ypos = 10;

$frm = $GUI->Form("Данные о клиенте #" . $client["id"], 400, 610);
$frm->OnExecute = "editclient_exec";

$h = $frm->Hidden($client["id"]);
$h->linkName = "id";

$frm->Label("Имя", 10, $ypos);
$t = $frm->Text(40, $ypos - 5, 350, $client["fio"]); //1
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
$t->linkName = "fio";

$frm->Label("Город", 10, $ypos += 30);
$t = $frm->Text(60, $ypos - 5, 120, $client["city"]); //8
$t->linkName = "city";


if ($_SESSION["user"]["data"]["group_id"] == 1 || $_SESSION["user"]["data"]["group_id"] == 0) {
  $aFilials = $data_filials;
  if (empty($client["filial_id"])) {
    $aFilials[0] = array('name' => 'не указан');
  }
  $frm->Label("Филиал", 210, $ypos);
  $t = $frm->Select(260, $ypos - 5, 130, $aFilials, "name", $client["filial_id"]); //1
  $t->linkName = "filial_id";
  $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
} else {
  $t = $frm->Hidden($_SESSION["user"]["data"]["filial_id"]);
  $t->linkName = "filial_id";
}

$frm->Label("Новый пароль", 10, $ypos += 30);
$t = $frm->Text(110, $ypos - 5, 180); //2
$t->linkName = "newpwd";

$frm->Label("Случайно", 320, $ypos);
$c = $frm->Checkbox(300, $ypos - 3, false, 1); //3
$c->linkName = "genpwd";
$c->AddJsEvent("change", " if (jQuery('#" . $c->idname . "').attr('checked')=='checked') { jQuery('#" . $t->idname . "').attr('disabled', 'disabled'); } else { jQuery('#" . $t->idname . "').removeAttr('disabled'); } ");

if ($frm->ExistInReq()) {
  $t->Disabled = $c->GetFromReq();
} else {
  $t->Disabled = false;
}

$frm->Label("Почта", 10, $ypos += 30);
$t = $frm->Text(70, $ypos - 5, 320, $client["email"]); //4
$t->linkName = "email";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
$t->AddValidator(new CGUI_VALIDATOR_EMAIL());

$frm->Label("Телефон", 10, $ypos += 30);
$t = $frm->Text(70, $ypos - 5, 320, $client["telnum"]); //5
$t->linkName = "telnum";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
$t->AddValidator(new CGUI_VALIDATOR_TELNUM());

$frm->Label("ICQ", 10, $ypos += 30);
$t = $frm->Text(50, $ypos - 5, 120, $client["icq"]); //6
$t->linkName = "icq";

$frm->Label("Skype", 200, $ypos);
$t = $frm->Text(250, $ypos - 5, 140, $client["skype"]); //7
$t->linkName = "skype";

$frm->Label("Другие контакты", 10, $ypos += 30);
$t = $frm->Text(120, $ypos - 5, 270, $client["contacts"]); //9
$t->linkName = "contacts";

$frm->Label("Заметки", 10, $ypos += 30);
$t = $frm->Text(120, $ypos - 5, 270, $client["about"]); //10
$t->linkName = "about";

$frm->VLine(10, $ypos += 30, 380);

$frm->Label("Кто привел клиента (ID)", 10, $ypos += 10);
$frm->Label("Партнерский код", 220, $ypos);

$ref = $frm->Text(10, $ypos += 20, 50, $client['ref_id']);
$ref->linkName = 'ref';

$t = $frm->Text(220, $ypos, 170, $client['referrer_code']);
$t->linkName = 'referral_code';
$t->Disabled = true;

$b = $frm->Button("Найти", 70, $ypos - 2, 80);
//$b->Event = 'jQuery("#' . $GUI->Vars["kln_search_modal_form"]->idname . '").modal();';
page_AddScriptText("custom_klient_select_event = function(id){ jQuery('#" . $ref->idname . "').val(id); };");

$frm->Label("Откуда клиент", 10, $ypos += 30);
$frm->Label("Партнерская ссылка", 220, $ypos);

$t = $frm->Select(10, $ypos += 20, 180, $client_sources, "name", $client['from_id']);
$t->linkName = 'client_from';

$t = $frm->Text(220, $ypos, 170, 'http://' . $client['referrer_code']);

$frm->VLine(10, $ypos += 30, 380);

$frm->Button("Сохранить", 110, $ypos += 10, 80, true);
$b = $frm->Button("К списку", 210, $ypos, 80);
$b->Event = "document.location.href=\"?section=kln&subsection=2\"; return false;";

$frm->height = $ypos + 60;