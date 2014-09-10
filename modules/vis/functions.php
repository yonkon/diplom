<?php

use Components\Entity\Client;
use Components\Entity\SubwayStation;
use Components\Entity\Order;
use Components\Entity\Meeting;
use Components\Classes\db;

use Components\Exceptions\InvalidArgumentException;

function addvisit_1_exec($Frm, $Err) {
  if (!$Err) {
    $kln = kln_get($Frm->GetNmValueI("klient"));
    if (!$kln) {
      $Frm->_gui->ERR("Клиент не указан");
      return;
    }

    $sum = $Frm->GetNmValueI("summa");
    if ($Frm->GetNmValueI("money_dir") == -1) {
      $sum = -$sum;
    }

    $ord_id = $Frm->GetNmValueI("order");
    if (!$ord_id) {
      if ($sum) {
        $Frm->_gui->ERR("Сумма может быть указана только при наличии заказа");
        return;
      }
    } else {
      $ord = ord_get($ord_id);
      if (!$ord) {
        $Frm->_gui->ERR("Заказ не найден");
        return;
      }
      $getsum = $ord["cost_kln"] - $ord["oplata_kln"];
      $outsum = $ord["oplata_kln"];

      if (($sum > 0) && ($sum > $getsum)) {
        $Frm->_gui->ERR("Вы не можете получить более " . $getsum . " " . $GLOBALS["ofc_currency"]);
        return;
      }

      if (($sum < 0) && ((-$sum) > $outsum)) {
        $Frm->_gui->ERR("Вы не можете вернуть более " . $outsum . " " . $GLOBALS["ofc_currency"]);
        return;
      }
    }

    $_SESSION["make_visit_tmp"] = $Frm->GetAllNmValues();
    $_SESSION["make_visit_tmp"]["summa1"] = $sum;

    page_reloadToSec("1&step=2");
  }
}

function addvisit_2_exec($Frm, $Err) {
  if (!$Err) {
    if (!isset($_SESSION["make_visit_tmp"]["klient"])) {
      $Frm->_gui->ERR("Клиент не определен");
      return;
    }

    $kln = kln_get($_SESSION["make_visit_tmp"]["klient"]);
    if (!$kln) {
      $Frm->_gui->ERR("Клиент не определен");
      return;
    }

    $ord_id = 0;
    if (isset($_SESSION["make_visit_tmp"]["order"]) && intval($_SESSION["make_visit_tmp"]["order"])) {
      $ord_id = intval($_SESSION["make_visit_tmp"]["order"]);

      if (!Order::find($ord_id)) {
        $Frm->_gui->ERR("Заказ не найден");
        return;
      }
    }

    // Проверить время
    $t1 = $Frm->GetNmValue("start");
    $t2 = $Frm->GetNmValue("finish");

    if ($t2 <= $t1) {
      $Frm->_gui->ERR("Некорректно задано время");
      return;
    }

    $dt = explode("-", $_SESSION["make_visit_tmp"]["date"]);
    $dt = mktime(0, 0, 0, $dt[1], $dt[0], $dt[2]);

    $fil = intval($_SESSION["make_visit_tmp"]["filial_id"]);
    if ($fil <= 0) {
      $Frm->_gui->ERR("Некорректный филиал");
      return false;
    }
    $courier_id = $Frm->GetNmValueI("user");

    $visits = db::get_single_value("SELECT COUNT(*) FROM " . TABLE_VISITS . " WHERE user_id = " . db::input($courier_id) . " AND tm_start <= " . db::input($t1) . " AND tm_finish > " . db::input($t1) . " AND date = " . db::input($dt));
    if ($visits) {
      $Frm->_gui->ERR("На это время уже назначена встреча");
      return false;
    }

    $vis_id = Meeting::create(array(
      "user_id" => $courier_id,
      "date" => $dt,
      "status" => 0,
      "tm_start" => $t1,
      "tm_finish" => $t2,
      "client_id" => $kln["id"],
      "order_id" => $ord_id,
      "filial_id" => $fil,
      "created" => time(),
      "creator_id" => $_SESSION["user"]["data"]["id"],
      "summa" => $_SESSION["make_visit_tmp"]["summa1"],
      "about" => $_SESSION["make_visit_tmp"]["about"],
      "opisanie_klienta" => $Frm->GetNmValueH("opisanie_klienta"),
      "opisanie_pyti" => $Frm->GetNmValueH("opisanie_pyti"),
      "station_id" => $Frm->GetNmValueI("station"),
    ));

    $Frm->_gui->OK("Встреча создана");
    // Создаем сообщение при необходимости
    if ($courier_id != $_SESSION["user"]["data"]["id"]) {
      $prior = 1;
      $msg_id = mls_Send("u" . $courier_id, "u" . $_SESSION["user"]["data"]["id"], "Новая встреча №" . $vis_id, "Вы проводите встречу " . date("d.m.Y", $dt) . " c " . utils_cvt_i2times($t1) . " по " . utils_cvt_i2times($t2) . "." . " <a href='?section=vis&subsection=2&visit=" . $vis_id . "'>Подробнее...</a><br>Внимание! Ответ на данное сообщение обязателен!", $prior, 0, 0, 0, 0, $vis_id, 0);
      if ($msg_id) {
        \Components\Classes\Author::enqueue_message_to_email($msg_id, array($courier_id), \Components\Entity\EmailNotificationType::TO_CLIENT_ON_ORDER_CHANGE);
        Meeting::update($vis_id, array(
          'uved_umsg' => $msg_id,
        ));
      }
    }

    page_reloadToSec("2");
  }
}

function get_client_telnum($value, $row, $table, &$info) {
  try {
    $client = Client::find($row['client_id']);
    return $client['telnum'];
  } catch(InvalidArgumentException $e) {
    return 'не указан';
  }
}

function tp_getstatus($value, $row, $table, &$info) {
  global $vis_statuses;
  return $vis_statuses[$value];
}

function tp_about($value, $row, $table, &$info) {
  if (strlen($value) > 100) {
    return utils_crop_text($value, 100);
  } else {
    return $value;
  }
}

function tp_gettime($value, $row, $table, &$info) {
  return utils_cvt_i2times($row["tm_start"]) . " - " . utils_cvt_i2times($row["tm_finish"]);
}

function tp_getdate($value, $row, $table, &$info) {
  $t = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
  $d = floor(($value - $t) / 86400);

  $s = " (сегодня)";
  if ($d != 0) {
    $s = " (" . $d . ")";
  }

  return date("d.m.Y", $value) . $s;
}

function tp_getplace($value, $row, $table, &$info) {
  global $data_filials;
  if ($value == -1) {
    return "с курьером";
  }
  if (isset($data_filials[$value])) {
    return "филиал <a href='?section=fils&light=" . $value . "'>" . $data_filials[$value]["name"] . "</a>";
  }
  return "<i>не определено</i>";
}

function tp_getusername($value, $row, $table, &$info) {
  global $data_users;
  if (!isset($data_users[$value])) {
    return "<i>не определено</i>";
  }
  return "<a href='?section=sotr&light=" . $value . "'>" . sotr_getFullName($value) . "</a>";
}

function tp_getusername2($value, $row, $table, &$info) {
  global $data_users;
  if (!isset($data_users[$value])) {
    return "<i>не определено</i>";
  }
  $s = "<a href='?section=sotr&light=" . $value . "'>" . sotr_getFullName($value) . "</a>";
  if ($row["uved_umsg"]) {
    $m = mls_get($row["uved_umsg"]);
    if (!$m["readed"]) {
      $s .= "<sup><font color=red>не уведомлен</font></sup>";
    }
  }
  return $s;
}

function tp_getclientname($value, $row, $table, &$info) {
  try {
    $client = Client::find($value);
    return $value . ". <a href='?section=kln&subsection=2&edit=" . $value . "'>" . $client["fio"] . "</a>";
  } catch(InvalidArgumentException $e) {
    return '<i>не определено</i>';
  }
}

function tp_getorder($value, $row, $table, &$info) {
  if (!$value) {
    return "";
  }
  return "<a href='?section=ord&subsection=2&order=" . $value . "&p=1'>" . $value . "</a>";
}

function tp_get_station($value, $row, $table, &$info) {
  if (!$value) {
    return "";
  }
  $station = SubwayStation::find($value);
  return $station['name'];
}

function editvisit_exec($Frm, $Err) {
  global $vis_statuses;
  if (!$Err) {

    $changes = array();

    $vis = Meeting::find($Frm->GetNmValueI("vid"));
    if (!$vis) {
      $Frm->_gui->ERR("Встреча не найдена");
      return false;
    }

    $sum = $Frm->GetNmValueI("summa");
    $sumf = $Frm->GetNmValueI("summaf");
    $targ = $Frm->GetNmValueH("about");
    $stat = $Frm->GetNmValueI("status");
    $rep = $Frm->GetNmValueH("report");
    $dt = explode("-", $Frm->GetNmValue("date"));
    $dt = mktime(0, 0, 0, $dt[1], $dt[0], $dt[2]);
    $tms = $Frm->GetNmValueI("start");
    $tme = $Frm->GetNmValueI("finish");

    if ($stat != 1 && $sumf > 0) {
      $Frm->_gui->ERR("Фактическая сумма не может быть изменена, если встреча не проведена");
      return false;
    }

    if ($stat == 1 && $sum > $sumf) {
      $Frm->_gui->ERR("Фактическая сумма " . $sumf . " " . $GLOBALS["ofc_currency"] . " меньше суммы, которую нужно получить " . $sum . " " . $GLOBALS["ofc_currency"]);
      return false;
    }

    if ($sum != $vis["summa"]) {
      $changes[] = "'деньги' с " . $vis["summa"] . " на " . $sum . " " . $GLOBALS["ofc_currency"];
    }
    if ($sumf != $vis["summaf"]) {
      $changes[] = "'деньги фактически' с " . $vis["summaf"] . " на " . $sumf . " " . $GLOBALS["ofc_currency"];
    }
    if ($targ != $vis["about"]) {
      $changes[] = "'цель'";
    }
    if ($stat != $vis["status"]) {
      $changes[] = "'статус' с '" . $vis_statuses[$vis["status"]] . "' на '" . $vis_statuses[$stat] . "'";
    }
    if ($rep != $vis["report"]) {
      $changes[] = "'отчет'";
    }
    if ($dt != $vis["date"]) {
      $changes[] = "'дата встречи' с " . date("d-m-Y", $vis["date"]) . " на " . date("d-m-Y", $dt);
    }
    if ($tms != $vis["tm_start"]) {
      $changes[] = "'начало' с " . utils_cvt_i2times($vis["tm_start"]) . " на " . utils_cvt_i2times($tms);
    }
    if ($tme != $vis["tm_finish"]) {
      $changes[] = "'окончание' с " . utils_cvt_i2times($vis["tm_finish"]) . " на " . utils_cvt_i2times($tme);
    }

    $station_id = 0;
    $opisanie_klienta = "";
    $opisanie_pyti = "";
    if ($vis['filial_id'] == -1) {
      $station_id = $Frm->GetNmValueI("station");
      $opisanie_klienta = $Frm->GetNmValueH("opisanie_klienta");
      $opisanie_pyti = $Frm->GetNmValueH("opisanie_pyti");
      if ($station_id != $vis["station_id"]) {
        $changes[] = "'станция' с " . get_station_name($vis["station_id"]) . " на " . get_station_name($station_id);
      }
      if ($opisanie_klienta != $vis["opisanie_klienta"]) {
        $changes[] = "'описание клиента' с '" . $vis["opisanie_klienta"] . "' на '" . $opisanie_klienta . "'";
      }
      if ($opisanie_pyti != $vis["opisanie_pyti"]) {
        $changes[] = "'описание пути' с '" . $vis["opisanie_pyti"] . "' на '" . $opisanie_pyti . "'";
      }
    }

    if ($sumf > $sum) {
      $Frm->_gui->ERR("Фактическая сумма не может превышать " . $sum . " " . $GLOBALS["ofc_currency"]);
      return;
    }

    if ($tms > $tme) {
      $Frm->_gui->ERR("Некорректно задано время");
      return;
    }

    if ($stat && !$rep) {
      $Frm->_gui->ERR("Необходим отчет о встрече");
      return;
    }

    $msg = "Произошли следующие изменения по встрече №" . $vis["id"] . ":<br>";
    foreach ($changes as $c) {
      $msg .= $c . "<br>";
    }
    $msg .= "<a href='?section=vis&subsection=2&visit=" . $vis["id"] . "'>Перейти к просмотру</a>";

    Meeting::update($vis["id"], array(
      "date" => $dt,
      "status" => $stat,
      "tm_start" => $tms,
      "tm_finish" => $tme,
      "about" => $targ,
      "summa" => $sum,
      "summaf" => $sumf,
      "report" => $rep,
      "opisanie_klienta" => $opisanie_klienta,
      "opisanie_pyti" => $opisanie_pyti,
      "station_id" => $station_id,
    ));

    Order::update($vis["order_id"], array(
      'oplata_kln' => $sumf,
    ));

    $Frm->_gui->OK("Сохранено");
    // Письмо тому кто проводит если статус 0
    if (count($changes) && ($vis["status"] == 0)) {
      //$to, $from, $subj, $text, $prior, $srok, $parent=0, $order=0, $klient=0, $visit=0, $tender=0
      $msg_id = mls_Send("u" . $vis["user_id"], "u" . $_SESSION["user"]["data"]["id"], "Изменение параметров встречи №" . $vis["id"], $msg, 1, 0, 0, $vis["order_id"], $vis["client_id"], $vis["id"], 0);
      \Components\Classes\Author::enqueue_message_to_email($msg_id, array($vis["user_id"]), \Components\Entity\EmailNotificationType::TO_AUTHOR_ON_ORDER_CHANGE);
    }
  }
}