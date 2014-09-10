<?php

use Components\Entity\Module;
use Components\Entity\Submodule;
use Components\Entity\Command;
use Components\Entity\Column;

function add_module($Frm, $Err) {
  if (!$Err) {
    Module::create(array(
      'name' => $Frm->GetNmValueH("name"),
      'internal_name' => $Frm->GetNmValueH("internal_name"),
      'order' => $Frm->GetNmValueI("order"),
    ));

    $Frm->_gui->OK("Модуль добавлен");
    page_reloadSec();
  }
}

function edit_module($Frm, $Err) {
  if (!$Err) {
    Module::update($Frm->GetNmValueI('id'), array(
      'name' => $Frm->GetNmValueH("name"),
      'internal_name' => $Frm->GetNmValueH("internal_name"),
      'order' => $Frm->GetNmValueI("order"),
    ));

    $Frm->_gui->OK("Модуль обновлен");
    page_reloadSec();
  }
}

function add_submodule($Frm, $Err) {
  if (!$Err) {
    Submodule::create(array(
      'module_id' => $Frm->GetNmValueI("module_id"),
      'name' => $Frm->GetNmValueH("name"),
      'order' => $Frm->GetNmValueI("order"),
      'default' => $Frm->GetNmValueI("default"),
    ));

    $Frm->_gui->OK("Подмодуль добавлен");
    page_reloadSubSec();
  }
}

function edit_submodule($Frm, $Err) {
  if (!$Err) {
    Submodule::update($Frm->GetNmValueI('id'), array(
      'module_id' => $Frm->GetNmValueI("module_id"),
      'name' => $Frm->GetNmValueH("name"),
      'order' => $Frm->GetNmValueI("order"),
      'default' => $Frm->GetNmValueI("default"),
    ));

    $Frm->_gui->OK("Подмодуль обновлен");
    page_reloadSubSec();
  }
}

function add_command($Frm, $Err) {
  if (!$Err) {
    Command::create(array(
      'module_id' => $Frm->GetNmValueI("module_id"),
      'submodule_id' => $Frm->GetNmValueI("submodule_id"),
      'name' => $Frm->GetNmValueH("name"),
      'order' => $Frm->GetNmValueI("order"),
    ));
    $Frm->_gui->OK("Команда добавлена");
    page_reloadSubSec();
  }
}

function edit_command($Frm, $Err) {
  if (!$Err) {
    Command::update($Frm->GetNmValueI('id'), array(
      'module_id' => $Frm->GetNmValueI("module_id"),
      'submodule_id' => $Frm->GetNmValueI("submodule_id"),
      'name' => $Frm->GetNmValueH("name"),
      'order' => $Frm->GetNmValueI("order"),
    ));

    $Frm->_gui->OK("Команда обновлена");
    page_reloadSubSec();
  }
}

function add_column($Frm, $Err) {
  if (!$Err) {
    if ($Frm->GetNmValueH("align") == '') {
      $align = "left";
    } else {
      $align = $Frm->GetNmValueH("align");
    }

    Column::create(array(
      'module_id' => $Frm->GetNmValueI("module_id"),
      'submodule_id' => $Frm->GetNmValueI("submodule_id"),
      'name' => $Frm->GetNmValueH("name"),
      'internal_name' => $Frm->GetNmValueH("internal_name"),
      'order' => $Frm->GetNmValueI("order"),
      'on_execute' => $Frm->GetNmValueH("on_execute"),
      'align' => $align,
      'do_sort' => $Frm->GetNmValueI("do_sort"),
      'group_internal_name' => $Frm->GetNmValueH("group_internal_name"),
    ));

    $Frm->_gui->OK("Колонка добавлена");
    page_reloadSubSec();
  }
}

function edit_column($Frm, $Err) {
  if (!$Err) {
    if ($Frm->GetNmValueH("align") == '') {
      $align = "left";
    } else {
      $align = $Frm->GetNmValueH("align");
    }

    if ($Frm->GetNmValueH("do_sort") == '') {
      $do_sort = "false";
    } else {
      $do_sort = $Frm->GetNmValueH("do_sort");
    }

    Column::update($Frm->GetNmValueI('id'), array(
      'module_id' => $Frm->GetNmValueI("module_id"),
      'submodule_id' => $Frm->GetNmValueI("submodule_id"),
      'name' => $Frm->GetNmValueH("name"),
      'internal_name' => $Frm->GetNmValueH("internal_name"),
      'order' => $Frm->GetNmValueI("order"),
      'on_execute' => $Frm->GetNmValueH("on_execute"),
      'align' => $align,
      'do_sort' => $do_sort,
      'group_internal_name' => $Frm->GetNmValueH("group_internal_name"),
    ));

    $Frm->_gui->OK("Колонка обновлена");
    page_reloadSubSec();
  }
}

function get_module_name($module_id) {
  $module = Module::find($module_id);
  return $module['name'];
}

function get_submodule_name($submodule_id) {
  $submodule = Submodule::find($submodule_id);
  return $submodule['name'];
}