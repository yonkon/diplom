<?php

use Components\Classes\db;
use Components\Entity\Employee;

require_once('../../includes/application_top.php');

if (!$_SESSION["user"]["auth"]) {
  die("запрещено");
}
if (!isset($_POST["uid"]) || !isset($_POST["date"])) {
  die("нет данных");
}
$uid = intval($_POST["uid"]);
$date = intval($_POST["date"]);

if ($uid == 0) {
  if (!isset($_POST["ml"])) {
    die("нет данных");
  }
  $_users = explode(":", $_POST["ml"]);
  if (!count($_users)) {
    die("1");
  }
  $users = array();

  // получить все встречи всех манагеров
  foreach ($_users as $u) {
    $employer = Employee::find($u);
    $users[$u] = array();
    $users[$u]['fio'] = $employer['fio'];
    $users[$u]["visits"] = array();

    $users[$u]["visits_sum"] = 0;
    foreach (db::get_arrays("SELECT tm_start, tm_finish FROM " . TABLE_VISITS . " WHERE user_id=" . $u . " AND date=" . $date) as $r) {
      $users[$u]["visits"][] = $r;
      $users[$u]["visits_sum"] += $r["tm_finish"] - $r["tm_start"];
    }
  }

  print "<div style='overflow:auto; background:white; border: 1px solid gray; height: 78px'>" . "<table cellpadding=0 cellspacing=0 style='font-size:8pt; margin-left:2px;'>" . "<tr style='color:gray'><td>Сотрудник</td><td style='width:10px' nowrap></td><td>Занятость</td></tr>";
  // Филиал сотрудника. Если нет филиала, то используем 8 часов раб день
  $worklong = 0;
  if ($_SESSION["user"]["data"]["filial_id"]) {
    $fil = fils_get($_SESSION["user"]["data"]["filial_id"]);
    $dweek = date("w", $date) - 1;
    if ($dweek == -1) {
      $dweek = 6;
    }
    fils_getworktime($fil, $dweek, $st, $en);
    $worklong = $en - $st;
  }
  if (!$worklong) {
    $worklong = 480;
  }

  foreach ($users as $u) {
    print "<tr><td>" . $u["fio"] . "</td><td></td><td>" . round(100 * $u["visits_sum"] / $worklong) . "%</td></tr>";
  }
  print "</table></div>";
} else {
  $meetings = db::get_arrays("SELECT tm_start, tm_finish, station_id FROM " . TABLE_VISITS . " WHERE user_id = " . $uid . " AND date = " . $date);
  if ($meetings) {
    print "<div style='font-size:10pt'>";
    foreach($meetings as $r) {
      print utils_cvt_i2times($r["tm_start"]) . "-" . utils_cvt_i2times($r["tm_finish"]) . ' - ' . get_station_name($r['station_id']) . "<br>";
    }
    print "</div>";
  } else {
    print "на выбранную дату у сотрудника нет встреч";
  }
}