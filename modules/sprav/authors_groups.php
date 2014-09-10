<?php

need_data("authors_groups");
$authors_groups = $GLOBALS['authors_groups'];

if (isset($_REQUEST["edit"])) {
  $id = intval($_REQUEST["edit"]);

  if (isset($authors_groups[$id])) {
    $frm = $GUI->Form("Редактировать", 400, 120);
    $frm->Hidden($id);
    $frm->Button("Сохранить", 100, 70, 80, true);
    $frm->OnExecute = "edit_authors_group_exec";
    $b = $frm->Button("К списку", 220, 70, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";
    $frm->Label("Название", 10, 10);
    $t = $frm->Text(10, 30, 378, $authors_groups[$id]["name"]);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
      $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=4&del=" . $id);
    }
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["add"])) {
  $frm = $GUI->Form("Добавить", 400, 120);
  $frm->Button("Добавить", 100, 70, 80, true);
  $frm->OnExecute = "add_authors_group_exec";
  $b = $frm->Button("К списку", 220, 70, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";
  $frm->Label("Название", 10, 10);
  $t = $frm->Text(10, 30, 378);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));
} elseif (isset($_REQUEST["del"])) {
  $id = intval($_REQUEST["del"]);
  if (isset($authors_groups[$id])) {
    $frm = $GUI->Form("Удалить", 300, 100);
    $frm->Hidden($id);
    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "del_authors_group_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $authors_groups[$id]["name"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} else {
  $tbl = $GUI->Table("authors_groups" . $n);
  $tbl->Width = "50%";
  $tbl->Pager(CGUI_PAGER_FLAG_ALL, 10, array(10, 20, 50, 0));

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=" . $n . "&edit=%var%\"";
  }

  $r = $tbl->NewColumn();
  $r->Caption = "Номер";
  $r->DoSort = true;
  $r->Key = "id";

  $r = $tbl->NewColumn();
  $r->Caption = "Наименование";
  $r->DoSort = true;
  $r->Key = "name";
  $r->Align = "left";

  $r = $tbl->NewColumn();
  $r->Caption = "";
  $r->Process = "tp_authors_group_cmds";

  foreach ($authors_groups as $d) {
    $tbl->AddRow($d, "id");
  }

  $tbl->InlineSort(true);

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=" . $n . "&add");
  }
}

function del_authors_group_exec($Frm, $Err) {
  global $authors_groups;
  if (!$Err) {
    $id = intval($Frm->GetValue(0));
    if (isset($authors_groups[$id])) {
      $db->Delete("authors_groups", "WHERE id = " . $id);
      $Frm->_gui->informer->OK("Удалено");
      page_reloadSubSec();
    }
  }
}

function tp_authors_group_cmds($v, $d, $tbl) {
  global $n, $GUI;

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    return "<a href='?" . $tbl->_gui->Url() . "del=" . $d["id"] . "'>[удалить]</a>";
  } else {
    return 'Нет прав на удаление';
  }
}

function edit_authors_group_exec($Frm, $Err) {
  global $authors_groups;
  if (!$Err) {
    $id = intval($Frm->GetValue(0));
    $name = str_replace("'", '"', htmlspecialchars($Frm->GetValue(1)));
    if (isset($authors_groups[$id])) {
      $db->Update("authors_groups", "name", $name, "WHERE id=" . $id);
      $Frm->_gui->informer->OK("Сохранено");
      page_reloadAll();
    }
  }
}

function add_authors_group_exec($Frm, $Err) {
  if (!$Err) {
    $name = str_replace("'", '"', htmlspecialchars($Frm->GetValue(0)));

    $db->Select("authors_groups", "*", "WHERE name='" . $name . "'");
    if ($db->ResultCount) {
      $Frm->_gui->informer->ERR("Запись существует");
      page_reloadAll();
    }

    $db->Insert("authors_groups", "name", $name);
    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSubSec();
  }
}