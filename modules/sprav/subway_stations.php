<?php

use Components\Entity\SubwayStation;

if (isset($_REQUEST["edit"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["edit"]);
  $station = SubwayStation::find($id);
  if ($station) {
    $frm = $GUI->Form("Редактировать", 400, 170);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Сохранить", 100, 110, 80, true);
    $frm->OnExecute = "edit_station_exec";
    $b = $frm->Button("К списку", 220, 110, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";

    $frm->Label("Название", 10, 10);
    $t = $frm->TextArea(10, 30, 378, 60, $station["name"]);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
    $t->linkName = 'name';

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
      $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=6&del=" . $id);
    }
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["add"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }

  $frm = $GUI->Form("Добавить", 400, 170);
  $frm->Button("Добавить", 100, 110, 80, true);
  $frm->OnExecute = "add_station_exec";
  $b = $frm->Button("К списку", 220, 110, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";

  $frm->Label("Название", 10, 10);
  $t = $frm->TextArea(10, 30, 378, 60);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(255));
  $t->linkName = 'name';
} elseif (isset($_REQUEST["del"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["del"]);
  $station = SubwayStation::find($id);
  if ($station) {
    $frm = $GUI->Form("Удалить", 300, 100);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "del_station_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $station["name"] . "'?", 10, 10);
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
  $frm->OnExecute = "imp_station_exec";
  $b = $frm->Button("К списку", 160, 90, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'imp')) . "\"; return false;";
  $frm->Label('Excel-файл', 10, 10);
  $frm->File(10, 30, 278)->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $frm->Label('Очистить перед импортом', 10, 60);
  $frm->Checkbox(170, 60, false, 1);

  $GUI->Vars["info_excel_stations"] = true;
} else {
  $tbl = $GUI->Table("stations" . $n);
  $tbl->DataMYSQL('subway_stations');
  $tbl->Width = "30%";
  $tbl->Pager(CGUI_PAGER_FLAG_ALL, 10, array(10, 20, 50, 0));

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=6&edit=%var%\"";
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

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=6&add");
  }

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Импорт Excel")) {
    $GUI->cmdmenu->AddItem("Импорт Excel", "?section=sprav&subsection=6&imp");
  }
}