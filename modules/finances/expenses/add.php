<?php

use Components\Entity\Expenses;

page_scriptNeed('scripts.js', '/modules/finances/expenses/');
page_scriptNeed('jquery-ui-1.10.3.custom.min.js', '/js/jquery-ui/js/');
page_styleNeed('jquery-ui-1.10.3.custom.min.css', '/js/jquery-ui/css/ui-lightness/');

$h = 200;

$frm = $GUI->Form("Добавить статью расходов", 350, $h);
$frm->VLine(10, $h - 80, 330);
$frm->Button("Добавить", 50, $h - 60, 100, true);
$frm->OnExecute = "add_expenses";
$b = $frm->Button("К списку", 200, $h - 60, 100);
$b->Event = "document.location.href=\"?section=finances&subsection=2\"; return false;";

if (is_elder_manager($_SESSION['user']['data']['id'])) {
  $t = $frm->Hidden($_SESSION['user']['data']['filial_id']);
} else {
  $t = $frm->Hidden(0);
}
$t->linkName = 'filial_id';

$ypos = 10;

$frm->Label("Наименование", 10, $ypos);
$t = $frm->Text(10, $ypos += 20, 330);
$t->linkName = "name";
$t->class = "autocomplete";

$ypos += 30;

$frm->Label("Сумма", 10, $ypos);
$frm->Label("Дата", 150, $ypos);

$ypos += 20;
$t = $frm->Text(10, $ypos, 100);
$t->linkName = "value";

$t = $frm->DatePic(150, $ypos, 80, time());
$t->linkName = "date";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_DDMMYYYY);

function add_expenses($Frm, $Err) {
  if (!$Err) {
    Expenses::create(array(
      'name' => $Frm->GetNmValueH("name"),
      'value' => $Frm->GetNmValue("value"),
      'date' => utils_cvt_date2i($Frm->GetNmValue("date"), true),
      'filial_id' => $Frm->GetNmValueI("filial_id"),
    ));

    $Frm->_gui->OK('Запись добавлена');

    page_reloadSubSec();
  }
}