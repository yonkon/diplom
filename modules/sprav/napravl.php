<?php

use Components\Classes\Napravls;

use Components\Entity\Napravl;

if (isset($_REQUEST["edit"])) {
  if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $GUI->informer->ERR(PERMISSION_DENIED);
    page_reload();
  }
  $id = intval($_REQUEST["edit"]);
  $napravl = Napravl::find($id);

  if ($napravl) {
    $frm = $GUI->Form("Редактировать", 300, 150);
    $frm->Button("Сохранить", 60, 90, 80, true);
    $frm->OnExecute = "editnapravl_exec";
    $b = $frm->Button("К списку", 160, 90, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $t = $frm->Label("Название", 10, 10);
    $t = $frm->Text(10, 30, 278, $napravl["name"]);
    $t->linkName = 'name';
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
      $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=2&del=" . $id);
    }
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["add"])) {
  if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->informer->ERR(PERMISSION_DENIED);
    page_reload();
  }
  $frm = $GUI->Form("Добавить", 300, 150);
  $frm->Button("Добавить", 60, 90, 80, true);
  $frm->OnExecute = "addnapravl_exec";
  $b = $frm->Button("К списку", 160, 90, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";

  $frm->Label("Название", 10, 10);
  $t = $frm->Text(10, 30, 278);
  $t->linkName = 'name';
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(80));
} elseif (isset($_REQUEST["del"])) {
  if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    $GUI->informer->ERR(PERMISSION_DENIED);
    page_reload();
  }
  $id = intval($_REQUEST["del"]);
  $napravl = Napravl::find($id);

  if ($napravl) {
    $frm = $GUI->Form("Удалить", 300, 100);
    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "delnapravl_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $t = $frm->Hidden($id);
    $t->linkName = 'id';
    $frm->Label("Удалить '" . $napravl["name"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} else {
  $tbl = $GUI->Table("sprav" . $n);
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=2&edit=%var%\"";
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
  $r->Caption = "Количество авторов";
  $r->DoSort = true;
  $r->Process = "get_authors_qt_for_napravl";

  $r = $tbl->NewColumn();
  $r->Caption = "";
  $r->Process = "tp_napravl_cmds";

  foreach (Napravl::findAll() as $d) {
    $tbl->AddRow($d, "id");
  }

  $tbl->InlineSort(true);

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=2&add");
  }
}

function get_authors_qt_for_napravl($value, $row, $table, $info) {
  return Napravls::getAuthorsQt($row['id']);
}