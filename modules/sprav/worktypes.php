<?php

use Components\Classes\db;

use Components\Entity\Worktypes;

if (isset($_REQUEST["edit"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["edit"]);
  $worktype = Worktypes::find($id);

  if ($worktype) {
    $frm = $GUI->Form("Редактировать", 300, 250);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Сохранить", 60, 190, 80, true);
    $frm->OnExecute = "editworktypes_exec";
    $b = $frm->Button("К списку", 160, 190, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";

    $frm->Label("Название", 10, 10);
    $t = $frm->Text(10, 30, 278, $worktype["name"]);
    $t->linkName = 'name';
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));

    $frm->Label("Описание", 10, 60);
    $t = $frm->TextArea(10, 80, 278, 80, $worktype["rem"]);
    $t->linkName = 'rem';
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
      $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=3&del=" . $id);
    }
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["add"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $frm = $GUI->Form("Добавить", 300, 250);
  $frm->Button("Добавить", 60, 190, 80, true);
  $frm->OnExecute = "addworktypes_exec";
  $b = $frm->Button("К списку", 160, 190, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";

  $frm->Label("Название", 10, 10);
  $t = $frm->Text(10, 30, 278);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));
  $t->linkName = 'name';

  $frm->Label("Описание", 10, 60);
  $t = $frm->TextArea(10, 80, 278, 80);
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
  $t->linkName = 'rem';
} elseif (isset($_REQUEST["del"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["del"]);
  $worktype = Worktypes::find($id);
  if ($worktype) {
    $frm = $GUI->Form("Удалить", 300, 100);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "delworktypes_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $worktype["name"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} else {
  $tbl = $GUI->Table("sprav" . $n);
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=3&edit=%var%\"";
  }

  $r = $tbl->NewColumn();
  $r->Caption = "Номер";
  $r->DoSort = true;
  $r->Key = "id";

  $r = $tbl->NewColumn();
  $r->Caption = "Наименование";
  $r->DoSort = true;
  $r->Key = "name";

  $r = $tbl->NewColumn();
  $r->Caption = "";
  $r->Process = "tp_worktypes_cmds";

  foreach (Worktypes::findAll() as $d) {
    $tbl->AddRow($d, "id");
  }

  $tbl->InlineSort(true);

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=3&add");
  }
}