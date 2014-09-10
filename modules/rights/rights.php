<?php

use Components\Classes\db;

require_once('../../includes/application_top.php');

if (!$_SESSION["user"]["auth"]) {
  die("запрещено");
}

if (isset($_POST['module']) && count($_POST['module'])) {
  global $GUI;

  $role_id = $_POST['role_id'];

  db::delete(TABLE_ROLES_TO_MODULES, "role_id = " . $role_id);
  db::delete(TABLE_ROLES_TO_SUBMODULES, "role_id = " . $role_id);
  db::delete(TABLE_ROLES_TO_COMMANDS, "role_id = " . $role_id);
  db::delete(TABLE_ROLES_TO_COLUMNS, "role_id = " . $role_id);

  foreach($_POST['module'] as $module_id => $enabled) {
    db::insert(TABLE_ROLES_TO_MODULES, array(
      'role_id' => $role_id,
      'module_id' => $module_id,
    ));
  }

  if (count($_POST['submodule'])) {
    foreach($_POST['submodule'] as $submodule_id => $enabled) {
      db::insert(TABLE_ROLES_TO_SUBMODULES, array(
        'role_id' => $role_id,
        'submodule_id' => $submodule_id,
      ));
    }
  }

  if (count($_POST['command'])) {
    foreach($_POST['command'] as $command_id => $enabled) {
      db::insert(TABLE_ROLES_TO_COMMANDS, array(
        'role_id' => $role_id,
        'command_id' => $command_id,
      ));
    }
  }

  if (count($_POST['column'])) {
    foreach($_POST['column'] as $column_id => $enabled) {
      db::insert(TABLE_ROLES_TO_COLUMNS, array(
        'role_id' => $role_id,
        'column_id' => $column_id,
      ));
    }
  }

  $GUI->OK("Права сохранены");
  header('Location: /index.php?section=rights');
}