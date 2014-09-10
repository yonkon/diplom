<?php

use Components\Classes\db;

need_data("data_city");

if (isset($_REQUEST["edit"])) {
  $id = intval($_REQUEST["edit"]);

  if (isset($data_city[$id])) {
    $frm = $GUI->Form("Редактировать", 400, 120);

    $frm->Hidden($id);
    $frm->Button("Сохранить", 100, 70, 80, true);
    $frm->OnExecute = "edit_city_exec";
    $b = $frm->Button("К списку", 220, 70, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";
    $frm->Label("Название", 10, 10);
    $t = $frm->Text(10, 30, 378, $data_city[$id]["name"]);
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
  $frm->OnExecute = "add_city_exec";
  $b = $frm->Button("К списку", 220, 70, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";
  $frm->Label("Название", 10, 10);
  $t = $frm->Text(10, 30, 378);
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));
} elseif (isset($_REQUEST["del"])) {
  $id = intval($_REQUEST["del"]);
  if (isset($data_city[$id])) {
    $frm = $GUI->Form("Удалить", 300, 100);
    $frm->Hidden($id);
    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "del_city_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $data_city[$id]["name"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["imp"])) {
  $frm = $GUI->Form("Импорт", 300, 150);
  $frm->Button("Выполнить", 60, 90, 80, true);
  $frm->OnExecute = "import_city_exec";
  $b = $frm->Button("К списку", 160, 90, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'imp')) . "\"; return false;";
  $frm->Label('Excel-файл', 10, 10);
  $frm->File(10, 30, 278)->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
  $frm->Label('Очистить перед импортом', 10, 60);
  $frm->Checkbox(170, 60, false, 1);

  $GUI->Vars["info_excel_city"] = true;
} else {
  $tbl = $GUI->Table("city" . $n);
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
  $r->Process = "tp_city_cmds";

  foreach ($data_city as $d) {
    $tbl->AddRow($d, "id");
  }

  $tbl->InlineSort(true);

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=" . $n . "&add");
  }
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Импорт Excel")) {
    $GUI->cmdmenu->AddItem("Импорт Excel", "?section=sprav&subsection=" . $n . "&imp");
  }
}

function del_city_exec($Frm, $Err) {
  global $data_city;
  if (!$Err) {
    $id = intval($Frm->GetValue(0));
    if (isset($data_city[$id])) {
      db::delete(TABLE_CITIES, "id = " . $id);
      $Frm->_gui->informer->OK("Удалено");
      page_reloadSubSec();
    }
  }
}

function tp_city_cmds($v, $d, $tbl) {
  global $n, $GUI;

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    return "<a href='?" . $tbl->_gui->Url() . "del=" . $d["id"] . "'>[удалить]</a>";
  } else {
    return 'Нет прав на удаление';
  }
}

function edit_city_exec($Frm, $Err) {
  global $data_city;
  if (!$Err) {
    $id = intval($Frm->GetValue(0));
    $name = str_replace("'", '"', htmlspecialchars($Frm->GetValue(1)));
    if (isset($data_city[$id])) {
      db::update(TABLE_CITIES, array(
        'name' => $name,
      ), "id=" . $id);
      $Frm->_gui->informer->OK("Сохранено");
      page_reloadAll();
    }
  }
}

function add_city_exec($Frm, $Err) {
  if (!$Err) {
    $name = str_replace("'", '"', htmlspecialchars($Frm->GetValue(0)));

    if (db::get_single_value("SELECT COUNT(*) FROM " . TABLE_CITIES . " WHERE name = '" . db::input($name) . "'")) {
      $Frm->_gui->informer->ERR("Запись существует");
      page_reloadAll();
    }

    db::insert(TABLE_CITIES, array(
      'name' => $name
    ));
    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSubSec();
  }
}

function import_city_exec($Frm, $Err) {
  if (!$Err) {
    $v = $Frm->GetValue(0);
    if (!strpos($v["type"], "ms-excel")) {
      $Frm->_gui->informer->ERR("Неправильный тип файла");
      page_reloadSubSec();
    } else {
      $s = "";
      if ($Frm->GetValue(1)) {
        db::truncate(TABLE_CITIES);
        $s = "Таблица очищена. ";
      }

      include_once "ext/Excel/reader.php";
      $data = new Spreadsheet_Excel_Reader($v["tmp_name"]);
      if ($data->sheets[0]['numCols'] != 1) {
        $Frm->_gui->informer->ERR("В таблице должна быть 1 колонка");
        page_reloadSubSec();
        return;
      }

      for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
        db::insert(TABLE_CITIES, array(
          'name' => htmlspecialchars($data->sheets[0]['cells'][$i][1]),
        ));
      }

      $Frm->_gui->informer->OK($s . "Добавлено " . $data->sheets[0]['numRows'] . " строк");
      page_reloadSubSec();
    }
  }
}