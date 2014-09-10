<?php

namespace Components\Classes;

class Roles {
  public static function getSubmodules($role_id, $module_id = null) {
    $where_module = 1;
    if (!is_null($module_id)) {
      $where_module = "s.module_id = " . db::input($module_id);
    }
    if ($role_id == 0 || $role_id == 1) {
      $sql = "
        SELECT s.*
        FROM " . TABLE_SUBMODULES . " s
        WHERE " . $where_module . "
        ORDER BY s.order ASC
      ";
    } else {
      $sql = "
        SELECT s.*
        FROM " . TABLE_SUBMODULES . " s
        JOIN " . TABLE_ROLES_TO_SUBMODULES . " rts ON s.id = rts.submodule_id
        WHERE rts.role_id = " . $role_id . "
          AND " . $where_module . "
        ORDER BY s.order ASC
      ";
    }
    return db::get_arrays($sql);
  }

  public static function isActionAllowed($module_id, $submodule_id, $role_id, $command_name) {
    if ($role_id == 0 || $role_id == 1) {
      return true;
    }
    if (db::get_single_value("
      SELECT COUNT(sc.id)
      FROM " . TABLE_SUBMODULES_COMMANDS . " sc
      JOIN " . TABLE_MODULES . " m ON m.id = sc.module_id
      JOIN " . TABLE_SUBMODULES . " s ON s.id = sc.submodule_id
      JOIN " . TABLE_ROLES_TO_COMMANDS . " rtc ON rtc.command_id = sc.id
      WHERE rtc.role_id = " . db::input($role_id) . "
        AND m.id = " . db::input($module_id) . "
        AND s.id = " . db::input($submodule_id) . "
        AND sc.name = '" . db::input($command_name) . "'
    ")) {
      return true;
    } else {
      return false;
    }
  }

  public static function getColumns($module_id, $submodule_id, $role_id) {
    if ($role_id == 0 || $role_id == 1) {
      $sql = "
        SELECT sc.*
        FROM " . TABLE_SUBMODULES_COLUMNS . " sc
        JOIN " . TABLE_MODULES . " m ON m.id = sc.module_id
        JOIN " . TABLE_SUBMODULES . " s ON s.id = sc.submodule_id
        WHERE m.id = " . db::input($module_id) . "
          AND s.id = " . db::input($submodule_id) . "
        ORDER BY sc.order ASC
      ";
    } else {
      $sql = "
        SELECT sc.*
        FROM " . TABLE_SUBMODULES_COLUMNS . " sc
        JOIN " . TABLE_MODULES . " m ON m.id = sc.module_id
        JOIN " . TABLE_SUBMODULES . " s ON s.id = sc.submodule_id
        JOIN " . TABLE_ROLES_TO_COLUMNS . " rtc ON rtc.column_id = sc.id
        WHERE rtc.role_id = " . db::input($role_id) . "
          AND m.id = " . db::input($module_id) . "
          AND s.id = " . db::input($submodule_id) . "
        ORDER BY sc.order ASC
      ";
    }

    return db::query($sql);
  }
}