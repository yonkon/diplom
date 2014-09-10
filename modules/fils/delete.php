<?php

use Components\Classes\Roles;
use Components\Entity\Filial;
use Components\Exceptions\AccessDeniedException;

if (!Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
  throw new AccessDeniedException;
}
$id = intval($_REQUEST["del"]);
$filial = Filial::find($id);
if ($filial) {
  $frm = $GUI->Form("Удалить", 400, 120);
  $frm->Hidden($id);
  $frm->VLine(10, 40, 380);
  $frm->Button("Удалить", 110, 60, 80, true);
  $frm->OnExecute = "delfilial_exec";
  $b = $frm->Button("Назад", 210, 60, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "edit=" . $id . "\"; return false;";
  $frm->Label("Удалить филиал '" . $filial["name"] . "'?", 10, 10);
} else {
  $GUI->informer->ERR("Запись не найдена");
  page_ReloadSubSec();
}