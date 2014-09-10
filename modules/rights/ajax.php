<?php

use Components\Classes\db;

require_once('../../includes/application_top.php');

if (!$_SESSION["user"]["auth"]) {
  die("запрещено");
}

if (isset($_POST['action']) && !empty($_POST['action'])) {
  switch ($_POST['action']) {
    case 'get_role_rights':
      echo get_role_rights($_POST['role_id']);
      die;
      break;
    default:
      break;
  }
}

function get_role_rights($role_id) {
  $modules = get_modules();

  $module_rights = db::get_single_values_array("SELECT module_id FROM " . TABLE_ROLES_TO_MODULES . " WHERE role_id = " . $role_id);

  $submodule_rights = db::get_single_values_array("SELECT submodule_id FROM " . TABLE_ROLES_TO_SUBMODULES . " WHERE role_id = " . $role_id);

  $command_rights = db::get_single_values_array("SELECT command_id FROM " . TABLE_ROLES_TO_COMMANDS . " WHERE role_id = " . $role_id);

  $column_rights = db::get_single_values_array("SELECT column_id FROM " . TABLE_ROLES_TO_COLUMNS . " WHERE role_id = " . $role_id);

  $result = array();
  $result[] = '<table style="width: 100%;">';
  if (count($modules)) {
    foreach ($modules as $module_id => $module_name) {
      $result[] = '<tr style="background-color: #d3d3d3;">';
      $result[] = '<td style="width: 100px;">';
      $result[] = '<i>модуль</i>';
      $result[] = '</td>';
      $result[] = '<td colspan="100">';
//      $result[] = '<div class="module">';
      $result[] = '<div class="module_name"><label for="module[' . $module_id . ']">' . $module_name . '</label></div>';
      $result[] = '<input type="checkbox" ' . (in_array($module_id, $module_rights) ? 'checked="checked"' : '') . ' name="module[' . $module_id . ']" class="module_checkbox" id="module[' . $module_id . ']">';
      $result[] = '</td>';
//      $result[] = '<div class="clear"></div>';
      $result[] = '</tr>';
      $submodules = get_submodules($module_id);
      if (count($submodules)) {

        foreach ($submodules as $submodule_id => $submodule_name) {
          $result[] = '<tr>';
          $result[] = '<td>';
          $result[] = '</td>';
          $result[] = '<td style="width: 100px;">';
          $result[] = '<i>подмодуль</i>';
          $result[] = '</td>';
          $result[] = '<td colspan="2">';
//          $result[] = '<div class="submodule">';
          $result[] = '<div class="submodule_name"><label for="submodule[' . $submodule_id . ']">' . $submodule_name . '</label></div>';
          $result[] = '<input type="checkbox" ' . (in_array($submodule_id, $submodule_rights) ? 'checked="checked"' : '') . ' name="submodule[' . $submodule_id . ']" class="submodule_checkbox" id="submodule[' . $submodule_id . ']">';
//          $result[] = '</div>';
          $result[] = '</tr>';

          $commands = get_commands($submodule_id);
          if (count($commands)) {
            $result[] = '<tr>';
            $result[] = '<td>';
            $result[] = '</td>';
            $result[] = '<td>';
            $result[] = '</td>';
            $result[] = '<td style="width: 100px;">';
            $result[] = '<i>команды</i>';
            $result[] = '</td>';
            $result[] = '<td>';
            foreach ($commands as $command_id => $command_name) {
              $result[] = '<div class="command_wrap">';
              $result[] = '<div class="command_name"><label for="command[' . $command_id . ']">' . $command_name . '</label></div>';
              $result[] = '<input type="checkbox" ' . (in_array($command_id, $command_rights) ? 'checked="checked"' : '') . ' name="command[' . $command_id . ']" class="command_checkbox" id="command[' . $command_id . ']">';
              $result[] = '</div>';
            }
            $result[] = '</td>';
            $result[] = '</tr>';
          }

          $columns = get_columns($submodule_id);
          if (count($columns)) {
            $result[] = '<tr style="background-color: lightCyan;">';
            $result[] = '<td>';
            $result[] = '</td>';
            $result[] = '<td>';
            $result[] = '</td>';
            $result[] = '<td style="width: 100px;">';
            $result[] = '<i>колонки</i>';
            $result[] = '</td>';
            $result[] = '<td>';
            foreach ($columns as $column_id => $column_name) {
              $result[] = '<div class="column_wrap">';
              $result[] = '<div class="column_name"><label for="column[' . $column_id . ']">' . $column_name . '</label></div>';
              $result[] = '<input type="checkbox" ' . (in_array($column_id, $column_rights) ? 'checked="checked"' : '') . ' name="column[' . $column_id . ']" class="column_checkbox" id="column[' . $column_id . ']">';
              $result[] = '</div>';
            }
            $result[] = '</td>';
            $result[] = '</tr>';
          }

          $result[] = '</td>';
        }
//        $result[] = '</tr>';
      }
//      $result[] = '</div>';
//      $result[] = '</tr>';
//      $result[] = '<div class="clear"></div>';
    }
  }
  $result[] = '</table>';

  return join($result, "\n");
}