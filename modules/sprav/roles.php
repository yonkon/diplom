<?php

use Components\Entity\Role;

if (isset($_REQUEST["edit"])) {
  $id = intval($_REQUEST["edit"]);
  $role = Role::find($id);

  if ($role) {
    $frm = $GUI->Form("Редактировать группу №" . $id, 400, 180);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Сохранить", 100, 120, 80, true);
    $frm->OnExecute = "edit_group_exec";
    $b = $frm->Button("К списку", 220, 120, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";

    $frm->Label("Название", 10, 10);
    $t = $frm->Text(10, 30, 300, $role["name"]);
    $t->linkName = "name";
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));

    $frm->Label("Краткое название", 10, 60);
    $t = $frm->Text(10, 80, 300, $role["sname"]);
    $t->linkName = "sname";
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
      $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=7&del=" . $id);
    }
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["add"])) {
  $frm = $GUI->Form("Добавить", 400, 180);
  $frm->Button("Добавить", 100, 120, 80, true);
  $frm->OnExecute = "add_group_exec";
  $b = $frm->Button("К списку", 220, 120, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";

  $frm->Label("Название", 10, 10);
  $t = $frm->Text(10, 30, 300);
  $t->linkName = "name";
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));

  $frm->Label("Краткое название", 10, 60);
  $t = $frm->Text(10, 80, 300);
  $t->linkName = "sname";
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
} elseif (isset($_REQUEST["del"])) {
  $id = intval($_REQUEST["del"]);
  $role = Role::find($id);
  if ($role) {
    $frm = $GUI->Form("Удалить", 300, 100);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "del_group_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $role["name"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} else {
  $tbl = $GUI->Table("roles" . $n);
  $tbl->DataMYSQL('roles');
  $tbl->FilterMYSQL("id != 0");
  $tbl->Width = "30%";
  $tbl->Pager(CGUI_PAGER_FLAG_ALL, 10, array(10, 20, 50, 0));

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=7&edit=%var%\"";
  }

  $r = $tbl->NewColumn();
  $r->Caption = "Номер";
  $r->DoSort = true;
  $r->Key = "id";

  $r = $tbl->NewColumn();
  $r->Caption = "Название";
  $r->DoSort = true;
  $r->Key = "name";
  $r->Align = "left";

  $r = $tbl->NewColumn();
  $r->Caption = "Краткое название";
  $r->DoSort = true;
  $r->Key = "sname";
  $r->Align = "left";

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=7&add");
  }
}