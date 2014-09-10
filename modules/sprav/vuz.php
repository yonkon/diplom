<?php

use Components\Classes\db;

use Components\Entity\VUZ;

if (isset($_REQUEST["edit"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["edit"]);
  $vuz = VUZ::find($id);

  if ($vuz) {
    $frm = $GUI->Form("Редактировать", 400, 320);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Сохранить", 100, 260, 80, true);
    $frm->OnExecute = "editvuz_exec";
    $b = $frm->Button("К списку", 220, 260, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";

    $frm->Label("Название", 10, 10);
    $t = $frm->Text(10, 30, 378, $vuz["sname"]);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));
    $t->linkName = 'sname';

    $frm->Label("Полное название", 10, 60);
    $t = $frm->TextArea(10, 80, 378, 60, $vuz["name"]);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
    $t->linkName = 'name';

    $frm->Label("Адрес", 10, 150);
    $t = $frm->TextArea(10, 170, 378, 60, $vuz["addr"]);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
    $t->linkName = 'addr';

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
      $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=4&del=" . $id);
    }
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["add"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $frm = $GUI->Form("Добавить", 400, 320);
  $frm->Button("Добавить", 100, 260, 80, true);
  $frm->OnExecute = "addvuz_exec";
  $b = $frm->Button("К списку", 220, 260, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";

  $frm->Label("Название", 10, 10);
  $t = $frm->Text(10, 30, 378);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));
  $t->linkName = 'sname';

  $frm->Label("Полное название", 10, 60);
  $t = $frm->TextArea(10, 80, 378, 60);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
  $t->linkName = 'name';

  $frm->Label("Адрес", 10, 150);
  $t = $frm->TextArea(10, 170, 378, 60);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
  $t->linkName = 'addr';
} elseif (isset($_REQUEST["del"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["del"]);
  $vuz = VUZ::find($id);
  if ($vuz) {
    $frm = $GUI->Form("Удалить", 300, 100);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "delvuz_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $vuz["sname"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["imp"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }

  $frm = $GUI->Form("Импорт", 300, 150);
  $frm->Button("Выполнить", 60, 90, 80, true);
  $frm->OnExecute = "impvuz_exec";
  $b = $frm->Button("К списку", 160, 90, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'imp')) . "\"; return false;";
  $frm->Label('Excel-файл', 10, 10);
  $frm->File(10, 30, 278)->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $frm->Label('Очистить перед импортом', 10, 60);
  $frm->Checkbox(170, 60, false, 1);

  $GUI->Vars["info_excel"] = true;
} else {
  $tbl = $GUI->Table("sprav" . $n);
  $tbl->Width = "100%";
  $tbl->Pager(CGUI_PAGER_FLAG_ALL, 10, array(10, 20, 50, 0));

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=4&edit=%var%\"";
  }

  $r = $tbl->NewColumn();
  $r->Caption = "Номер";
  $r->DoSort = true;
  $r->Key = "id";

  $r = $tbl->NewColumn();
  $r->Caption = "Наименование";
  $r->DoSort = true;
  $r->Key = "sname";
  $r->Align = "left";

  $r = $tbl->NewColumn();
  $r->Caption = "Полное наименование";
  $r->DoSort = true;
  $r->Key = "name";
  $r->Align = "left";

  $r = $tbl->NewColumn();
  $r->Caption = "Адрес";
  $r->DoSort = true;
  $r->Key = "addr";
  $r->Align = "left";

  $r = $tbl->NewColumn();
  $r->Caption = "";
  $r->Process = "tp_vuz_cmds";

  foreach (VUZ::findAll() as $d) {
    $tbl->AddRow($d, "id");
  }

  $tbl->InlineSort(true);

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=4&add");
  }
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Импорт Excel")) {
    $GUI->cmdmenu->AddItem("Импорт Excel", "?section=sprav&subsection=4&imp");
  }
}