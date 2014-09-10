<?php

use Components\Entity\Employee;
use Components\Entity\Order;
use Components\Classes\db;

if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
  $GUI->informer->ERR(PERMISSION_DENIED);
  page_ReloadSubSec();
}
$id = intval($_REQUEST["del"]);

if ($_SESSION["user"]["data"]["id"] == $id) {
  $GUI->ERR("Нельзя удалить себя");
  page_ReloadSubSec();
}

$employer = Employee::find($id);
if ($employer) {
  if (count(Order::findBy(array(
    'manager_id' => $id,
  )))) {
    $GUI->informer->ERR("У сотрудника есть назначенные заказы");
    page_ReloadSubSec();
  }

  if (db::get_single_value("SELECT COUNT(id) FROM " . TBL_PREF . "data_visits WHERE user_id = '" . $id . "' AND status <> 1")) {
    $GUI->informer->ERR("У сотрудника есть назначенные встречи");
    page_ReloadSubSec();
  }

  $frm = $GUI->Form("В черный список", 300, 260);
  $t = $frm->Hidden($id);
  $t->linkName = 'id';
  $frm->VLine(10, 180, 280);
  $frm->Button("Выполнить", 60, 200, 80, true);
  $frm->OnExecute = "deluser_exec";
  $b = $frm->Button("Назад", 160, 200, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "edit=" . $id . "\"; return false;";
  $frm->Label("Перенести в черный список сотрудника", 10, 10);
  $frm->Label("'" . $employer["fio"] . "'?", 10, 30);
  $frm->Label("Причина:", 10, 60);
  $t = $frm->TextArea(10, 80, 280, 70);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->linkName = 'reason';
} else {
  $GUI->informer->ERR("Запись не найдена");
  page_ReloadSubSec();
}