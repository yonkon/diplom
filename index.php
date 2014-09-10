<?php

use Components\Entity\Employee;
use Components\Entity\Module;
use Components\Classes\db;
use Components\Classes\Roles;

require_once('includes/application_top.php');

if (!isset($_SESSION["user"]["auth"])) {
  $_SESSION["user"]["auth"] = false;
}

if (isset($_REQUEST["logout"])) {
  $_SESSION["user"] = array();
  $_SESSION["user"]["auth"] = false;
  page_reload();
}

if ($_SESSION["user"]["auth"]) {
  if (Employee::isBlocked($_SESSION["user"]["data"]["id"])) {
    $_SESSION["user"] = array();
    $_SESSION["user"]["auth"] = false;
    page_reload();
  }

  Employee::update($_SESSION["user"]["data"]["id"], array(
    'last_act' => time(),
  ));

  $GUI->tmpls[0] = SITE_ROOT . "tmpls/mmenu.tmpl.php";

  if ($_SESSION["user"]["data"]["group_id"] != 0) {
    $office_modules = db::get_arrays("
      SELECT m.internal_name, m.name, m.id
      FROM " . TBL_PREF . "modules m
      JOIN " . TBL_PREF . "roles_to_modules rtm ON m.id = rtm.module_id
      WHERE rtm.role_id = " . $_SESSION["user"]["data"]["group_id"] . "
      ORDER BY m.order ASC
    ");
  } else {
    $office_modules = Module::findAll();
  }

  $i = 1;
  $default = false;
  foreach ($office_modules as $module) {
    $module_root = DIR_FS_MODULES . $module["internal_name"] . "/";
    if ($i == 1) {
      $default = true;
      $i++;
    }

    $module_tab = $GUI->mmenu->AddItem($module['id'], $module['name'], $module['internal_name'], $default);
    $module_tab->caption = $module['name'];

    $submodules = Roles::getSubmodules($_SESSION["user"]["data"]["group_id"], $module['id']);

    if (is_array($submodules)) {
      foreach ($submodules as $submodule) {
        $submodule_tab = $module_tab->AddItem($submodule['id'], $submodule['name'], $submodule['order'], $submodule['default']);
        $submodule_tab->caption = $submodule['name'];
      }
    } else {
      $GUI->ERR($modules);
    }

    include_once($module_root . "init.php");
  }

  // use selected module
  $GUI->mmenu->Update();
  $active_module_root = DIR_FS_MODULES . $GUI->mmenu->selected->section . "/";
  include_once($active_module_root . "inc.php");
  $page_title = $GUI->mmenu->selected->caption;
  if ($GUI->mmenu->selected->selected) {
    $page_title .= "|" . $GUI->mmenu->selected->selected->caption;
  }
} else {

  //login
  $GUI->mmenu = false;
  $GUI->tmpls[0] = SITE_ROOT . "tmpls/login.tmpl.php";

  function loginform_exec($Frm, $Err) {
    if ($Err) {
      $Frm->_gui->Vars["login_message"] = "Ошибки при заполнении формы";
      return;
    }
    // clr
    db::delete(TABLE_LOGIN_HOST, "time < " . (time() - 900));
    $last_login_time = db::get_arrays("SELECT time FROM " . TABLE_LOGIN_HOST . " WHERE ip = '" . db::input($_SERVER["REMOTE_ADDR"]) . "' ORDER BY time");

    $cnt = count($last_login_time);
    $rowx["time"] = 0;
    if ($cnt) {
      $rowx = $last_login_time[0];
    }
    $user = Employee::findOneBy(array(
      'email' => strtolower($Frm->GetValue(0)),
      'hpwd' => md5($Frm->GetValue(1) . $Frm->GetValue(0)),
    ));

    if (!$user || ($cnt > 2)) {
      if ($cnt > 1) {
        $t = 900 - time() + $rowx["time"];
        if ($t > 60) {
          $w = floor($t / 60) . " мин.";
        } else {
          $w = " минуту";
        }
        $Frm->_gui->Vars["login_message"] = "Попытки исчерпаны. Подождите " . $w;
      } else {
        $Frm->_gui->Vars["login_message"] = "Ошибка. Осталось попыток: " . (2 - $cnt);
        db::insert(TABLE_LOGIN_HOST, array(
          'ip' => $_SERVER['REMOTE_ADDR'],
          'time' => time(),
        ));
      }
      return;
    } else {
      // ok
      if ($user["blocked"] || $user["black_list"]) {
        $Frm->_gui->Vars["login_message"] = "Доступ запрещен";
        return;
      }

      $ll = array();
      if ($user["last_login"]) {
        $ll = unserialize($user["last_login"]);
        while (count($ll) > 99) {
          array_shift($ll);
        }
      }
      $ll[] = array("ip" => $_SERVER['REMOTE_ADDR'], "time" => time());

      Employee::update($user['id'], array(
        'last_act' => time(),
        'last_login' => serialize($ll),
      ));

      unset($user["last_act"]);
      unset($user["last_login"]);

      $_SESSION["user"]["auth"] = true;
      $_SESSION["user"]["data"] = $user;

      page_reload();
    }
  }

  $f = $GUI->Form("Office", 225, 160);
  $f->Rename("ofc_login");
  $f->Label("Login", 5, 5);

  $f->OnExecute = "loginform_exec";

  $login = $f->Text(10, 25, 200);
  $login->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $login->AddValidator(new CGUI_VALIDATOR_EMAIL);

  $f->Label("Password", 5, 55);
  $pwd = $f->Pasw(10, 75, 200);
  $pwd->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $pwd->AddValidator(new CGUI_VALIDATOR_AZaz09);

  $b = $f->Button("Вход", 85, 110, 60, true);
}

page_out();