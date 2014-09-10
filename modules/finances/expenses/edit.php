<?php

use Components\Entity\Expenses;

page_scriptNeed('scripts.js', '/modules/finances/expenses/');
page_scriptNeed('jquery-ui-1.10.3.custom.min.js', '/js/jquery-ui/js/');
page_styleNeed('jquery-ui-1.10.3.custom.min.css', '/js/jquery-ui/css/ui-lightness/');

global $GUI;

if (empty($_REQUEST['id'])) {
  $GUI->ERR('Статья расходов не найдена');
  page_reloadSubSec();
}

$expenses_info = Expenses::find($_REQUEST['id']);
if (empty($expenses_info)) {
  $GUI->ERR('Статья расходов не найдена');
  page_reloadSubSec();
}

//$data

$h = 200;

$frm = $GUI->Form("Изменить статью расходов", 350, $h);
$frm->VLine(10, $h - 80, 330);
$frm->Button("Сохранить", 50, $h - 60, 100, true);
$frm->OnExecute = "update_expenses";
$b = $frm->Button("К списку", 200, $h - 60, 100);
$b->Event = "document.location.href=\"?section=finances&subsection=2\"; return false;";

$t = $frm->Hidden($_REQUEST['id']);
$t->linkName = 'id';

$ypos = 10;

$frm->Label("Наименование", 10, $ypos);
$t = $frm->Text(10, $ypos += 20, 330, $expenses_info['name']);
$t->linkName = "name";
$t->class = "autocomplete";

$ypos += 30;

$frm->Label("Сумма", 10, $ypos);
$frm->Label("Дата", 150, $ypos);

$ypos += 20;
$t = $frm->Text(10, $ypos, 100, $expenses_info['value']);
$t->linkName = "value";

$t = $frm->DatePic(150, $ypos, 80, $expenses_info['date']);
$t->linkName = "date";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_DDMMYYYY);

function update_expenses($Frm, $Err) {
  if (!$Err) {
    Expenses::update($Frm->GetNmValueI('id'), array(
      'name' => $Frm->GetNmValueH("name"),
      'value' => $Frm->GetNmValue("value"),
      'date' => utils_cvt_date2i($Frm->GetNmValue("date"), true),
    ));

    $Frm->_gui->OK('Запись обновлена');
    page_reloadSubSec();
  }

}