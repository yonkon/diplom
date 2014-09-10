<?php

use Components\Classes\Disciplines;

use Components\Entity\Discipline;

need_data('data_napravl');

if (isset($_REQUEST["edit"])) {
  if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $GUI->informer->ERR(PERMISSION_DENIED);
    page_reload();
  }
  $id = intval($_REQUEST["edit"]);
  $discipline = Discipline::find($id);
  if ($discipline) {
    $frm = $GUI->Form("Редактировать", 400, 350);
    $frm->Button("Сохранить", 100, 290, 80, true);
    $frm->OnExecute = "editdiscip_exec";
    $b = $frm->Button("К списку", 220, 290, 80);

    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";


    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Label("Код", 10, 10);
    $t = $frm->Text(10, 30, 378, $discipline["code"]);
    $t->linkName = 'code';
    $t->AddValidator(new CGUI_VALIDATOR_09());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(20));

    $frm->Label("Название", 10, 60);
    $t = $frm->Text(10, 80, 378, $discipline["name"]);
    $t->linkName = 'name';
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));

    $frm->Label("Направление", 10, 110);
    $t = $frm->Select(10, 130, 378, $data_napravl, "name", Disciplines::getNapravListAsArray($id));
    $t->linkName = 'napravl';
    $t->Multiple = true;
    $t->RowSize = 8;
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
      $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=5&del=" . $id);
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
  $frm = $GUI->Form("Добавить", 400, 350);
  $frm->Button("Добавить", 100, 290, 80, true);
  $frm->OnExecute = "adddiscip_exec";
  $b = $frm->Button("К списку", 220, 290, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";

  $frm->Label("Код", 10, 10);
  $t = $frm->Text(10, 30, 378);
  $t->linkName = 'code';
  $t->AddValidator(new CGUI_VALIDATOR_09());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(20));

  $frm->Label("Название", 10, 60);
  $t = $frm->Text(10, 80, 378);
  $t->linkName = 'name';
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));

  $frm->Label("Направление", 10, 110);
  $t = $frm->Select(10, 130, 378, $data_napravl, "name");
  $t->linkName = 'napravl';
  $t->Multiple = true;
  $t->RowSize = 8;
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
} elseif (isset($_REQUEST["del"])) {
  if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    $GUI->informer->ERR(PERMISSION_DENIED);
    page_reload();
  }
  $id = intval($_REQUEST["del"]);
  $discipline = Discipline::find($id);
  if (isset($discipline)) {
    $frm = $GUI->Form("Удалить", 300, 100);
    $t = $frm->Hidden($id);
    $t->linkName = 'id';
    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "deldiscip_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $discipline["name"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["imp"])) {
  if (!user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Импорт Excel")) {
    $GUI->informer->ERR(PERMISSION_DENIED);
    page_reload();
  }

  $frm = $GUI->Form("Импорт", 300, 150);
  $frm->Button("Выполнить", 60, 90, 80, true);
  $frm->OnExecute = "impdiscip_exec";
  $b = $frm->Button("К списку", 160, 90, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'imp')) . "\"; return false;";
  $frm->Label('Excel-файл', 10, 10);
  $frm->File(10, 30, 278)->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $frm->Label('Очистить перед импортом', 10, 60);
  $frm->Checkbox(170, 60, false, 1);

  $GUI->Vars["info_excel1"] = true;
} else {
  $tbl = $GUI->Table("sprav" . $n);
  $tbl->Width = "100%";
  $tbl->Pager(CGUI_PAGER_FLAG_ALL, 10, array(10, 20, 50, 0));

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=5&edit=%var%\"";
  }

  $r = $tbl->NewColumn();
  $r->Caption = "Номер";
  $r->DoSort = true;
  $r->Key = "id";

  $r = $tbl->NewColumn();
  $r->Caption = "Код";
  $r->DoSort = true;
  $r->Key = "code";
  $r->Align = "left";

  $r = $tbl->NewColumn();
  $r->Caption = "Название";
  $r->DoSort = true;
  $r->Key = "name";
  $r->Align = "left";

  $r = $tbl->NewColumn();
  $r->Caption = "Направление";
  $r->Align = "left";
  $r->Process = "get_discipline_napravl";

  $r = $tbl->NewColumn();
  $r->Caption = "Количество авторов";
  $r->Process = "get_authors_qt_for_discipline";

  $r = $tbl->NewColumn();
  $r->Caption = "";
  $r->Process = "tp_discip_cmds";

  foreach (Discipline::findAll() as $d) {
    $tbl->AddRow($d, "id");
  }

  $tbl->InlineSort(true);

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=5&add");
  }
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Импорт Excel")) {
    $GUI->cmdmenu->AddItem("Импорт Excel", "?section=sprav&subsection=5&imp");
  }
}

function get_discipline_napravl($value, $row, $table, $info) {
  return Disciplines::get_napravl_list_as_string($row['id']);
}
function get_authors_qt_for_discipline($value, $row, $table, $info) {
  return Disciplines::getAuthorsQt($row['id']);
}