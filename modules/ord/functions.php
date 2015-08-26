<?php

use Components\Classes\db;
use Components\Classes\Author;
use Components\Classes\Disciplines;
use Components\Classes\ErrorLogger;

use Components\Entity\Order;
use Components\Entity\Discipline;
use Components\Entity\AuthorOffer;
use Components\Entity\Employee;
use Components\Entity\OrderFile;
use Components\Exceptions\Exception;
use Components\Entity\Filial;
use Components\Entity\Napravl;
use Components\Entity\Worktypes;
use Components\Entity\AuthorNotification;

function tp_get_id($value, $row, $table, &$info) {
  if ($table instanceof CGUI_Table && $table->rowmenu) {
    $table->rowmenu->Vars[1] = $value;
    $table->rowmenu->Vars[2] = $row["klient_id"];
    $html = $table->rowmenu->GetHTML($value);
  } else {
    $html = $value;
  }

  if ($ref_id = db::get_single_value("SELECT ref_id FROM " . TBL_PREF . "clients WHERE id = " . $row["klient_id"])) {
    $info = new CGUI_TableCellInfo();
    $info->Text(
      "<p>Номер: #" . $ref_id . "</p>" .
      "<p>Имя: " . db::get_single_value("SELECT fio FROM " . TBL_PREF . "clients WHERE id = " . $ref_id) . "</p>"
    );
    $info->icon = '<img src="' . SITE_URL . '/gui/skin/coins.png"/>';
  }

  return $html;
}

function _get_payment_name($value, $row, $table, &$info) {
  global $data_payments;
  need_data("data_payments");
  if (isset($data_payments[$value]["name"])) {
    return $data_payments[$value]["name"];
  } else {
    return "<i>не определено</i>";
  }
}

function _get_course_name($value, $row, $table, &$info) {
  global $data_courses;
  if (isset($data_courses[$value]["name"])) {
    return $data_courses[$value]["name"];
  } else {
    return "<i>не определено</i>";
  }
}

function _get_fmt_cost($value, $row, $table, &$info) {
  if ($value == NULL) {
    return "<i>не определено</i>";
  }
  return $value;
}

function _get_worktype_name($value, $row, $table, &$info) {
  try {
    $worktype = Worktypes::find($value);
    return $worktype['name'];
  } catch(Exception $e) {
    if ($row["type_user"] == "") {
      return "<i>не определено</i>";
    } else {
      return $row["type_user"];
    }
  }
}

function _get_disc_name($value, $row, $table, &$info) {
  global $data_discip;
  need_data("data_discip");

  if (intval($value) == 0 || !isset($data_discip[$value])) {
    if ($row["disc_user"] == "") {
      return "<i>не определено</i>";
    } else {
      return $row["disc_user"];
    }
  } else {
    return $data_discip[$value]["name"];
  }
}

function _get_napr_name($value, $row, $table, &$info) {
  return get_naprav_name($value);
}

function _get_client_name($value, $row, $table, &$info) {
  global $clients_info;
  $info = new CGUI_TableCellInfo();
  $info->Text("<p>Номер: #" . $row["id"] . "</p>" . "<p>Телефон: " . $clients_info[$value]["telnum"] . "</p>" . "<p>Почта: " . $clients_info[$value]["email"] . "</p>" . "<p>Город: " . $clients_info[$value]["city"] . "</p>");
  return "<a href='?section=kln&subsection=2&edit=" . $value . "'>" . $clients_info[$value]["fio"] . "</a>";
}

function _get_user_name1($value, $row, $table, &$info) {
  global $data_users, $data_groups;
  if ($value == 0) {
    return "<i>сайт</i>";
  } else {
    return sotr_getFullName($value);
  }
}

function _get_user_name2($value, $row, $table, &$info) {
  global $data_users, $data_groups;
  if (intval($value) == 0) {
    return "<i>не определено</i>";
  } else {
    return sotr_getFullName($value);
  }
}

function get_author($value, $row, $table, &$info) {
  $order_status = get_status_iname($row['status_id']);
  if ($order_status == "ON_THE_DISTRIBUTION") {
    return "на распределении";
  } else {
    return sotr_getFullName($value);
  }
}

//function _get_fmt_date($v, $o) {
//  if (intval($v) == 0) {
//    return "<i>не определено</i>";
//  }
//  $days = 1 + floor(($v - mktime()) / 86400);
//  if ($days == 0) {
//    $days = "";
//  } else {
//    $days = " (" . $days . ")";
//  }
//  return date("d.m.Y", $v) . $days;
//}

function _get_filial_name($value, $row, $table, &$info) {
  global $data_filials;

  if (empty($value) || !isset($data_filials[$value])) {
    return "не указан";
  }
  return $data_filials[$value]["name"];
}

function _get_vuz_name($value, $row, $table, &$info) {
  $id = intval($value);
  if ($id == 0) {
    if ($row["vuz_user"] == "") {
      return "<i>не задан</i>";
    } else {
      return $row["vuz_user"];
    }
  } else {
    need_data("data_vuz");
    global $data_vuz;

    $info = new CGUI_TableCellInfo();
    $info->Text($data_vuz[$value]["name"]);

    return $data_vuz[$value]["sname"];
  }
}

function _before_start_table($tbl) {
  // get client names
  global $clients_info;

  $ids = $clients_info = array();
  foreach ($tbl->Rows as $r) {
    $ids[$r["data"]["klient_id"]] = $r["data"]["klient_id"];
  }

  if (count($ids)) {
    $clients = db::get_assoc_arrays("SELECT id, fio, telnum, email, city FROM " . TABLE_CLIENTS . " WHERE id IN (" . join(', ', $ids) . ")");

    foreach ($clients as $r) {
      $clients_info[$r["id"]] = $r;
    }
  }
}

function fp_loadfile($Frm, $Err) {
  if (!$Err) {
    $order_id = $Frm->GetNmValueI('order_id');
    $file = $Frm->GetNmValue('file');

    if (is_uploaded_file($file["tmp_name"])) {
      $extension = get_file_ext($file['name']);

      $new_name = trim($Frm->GetNmValueH('new_name'));
      if ($new_name == "") {
        $name = $file["name"];
      } else {
        $name = $new_name . '.' . $extension;
      }

      $file_id = OrderFile::create(array(
        'order_id' => $order_id,
        'creator_id' => $_SESSION["user"]["data"]["id"],
        'created' => time(),
        'name' => $name,
        'size' => $file["size"],
      ));

      if (!$file_id) {
        $Frm->_gui->ERR("Ошибка при загрузке");
      } else {
        $dir = DIR_FS_ORDER_FILES . $order_id . '/';
        if (!is_dir(DIR_FS_ORDER_FILES)) {
          create_path('order_files', DIR_FS_DOCUMENT_ROOT);
        }
        if (!is_dir($dir)) {
          create_path($order_id, DIR_FS_ORDER_FILES);
        }

        $file_name = $file_id . '.' . $extension;

        if (move_uploaded_file($file['tmp_name'], $dir . $file_name)) {
          $Frm->_gui->OK("Файл загружен");
          if ($_SESSION["user"]["data"]["group_id"] == 6 && get_order_author_id($order_id) == $_SESSION["user"]["data"]["id"]) {
            $res = change_order_status($order_id, 'RECEIVED_FILE_FROM_AUTHOR');
            if ($res == 1) {
              $Frm->_gui->OK("Статус заказа изменен");
            } else {
              $Frm->_gui->ERR($res);
            }
          }
        } else {
          OrderFile::delete($file_id);
          $Frm->_gui->ERR("Ошибка при сохранении файла");
        }
      }
      page_reloadAll();
    }
  }
}

function tp_fls_creator($value, $row, $table, &$info) {
  if ($value == 0) {
    return "Клиент";
  }
  return sotr_getFullName($value);
}

function tp_fls_created($value, $row, $table, &$info) {
  return date("d.m.Y H:i:s", $value);
}

function addorder_exec($Frm, $Err) {
  if (!$Err) {
    $klient = kln_get($Frm->GetNmValueI("klient"));
    if (!$klient) {
      $Frm->_gui->ERR("Клиент не найден");
      return;
    }

    // Филиал клиента соотв филиалу сотрудника, если сотрудник рук то неважно
    if (is_director($_SESSION["user"]["data"]["id"]) || ($_SESSION["user"]["data"]["filial_id"] == $klient["filial_id"])) {
    } else {
      $Frm->_gui->ERR("Филиал клиента и сотрудника не совпадают");
      return;
    }

    if (($Frm->GetNmValue("work") == 0) && !strlen($Frm->GetNmValue("work_usr"))) {
      $Frm->_gui->ERR("Не указан вид работы");
      return;
    }

    $disciplina = trim($Frm->GetNmValue("disc_usr"));
    if (!strlen($disciplina)) {
      $Frm->_gui->ERR("Не указана дисциплина");
      return;
    }

    $pmin = $Frm->GetNmValueI("pgmin");
    $pmax = $Frm->GetNmValueI("pgmax");
//    if ($pmax && ($pmax < $pmin)) {
//      $Frm->_gui->ERR("Неверно указано макс. число страниц");
//      return;
//    }

    $pmin = $Frm->GetNmValueI("srcmin");
    $pmax = $Frm->GetNmValueI("srcmax");
//    if ($pmax && ($pmax < $pmin)) {
//      $Frm->_gui->ERR("Неверно указано макс. число источников");
//      return;
//    }

    if (!$Frm->GetNmValueI("pole_t") || !$Frm->GetNmValueI("pole_b") || !$Frm->GetNmValueI("pole_l") || !$Frm->GetNmValueI("pole_r")) {
      $Frm->_gui->ERR("Не указаны размеры полей в оформлении");
      return;
    }

    if ($Frm->GetNmValueI("take") == 4) {
      $Frm->_gui->ERR("Этот заказ не может быть принят через форму заказа");
      return;
    }

    $kln_date = utils_cvt_date2i($Frm->GetNmValueH("date"));
    $rel_date = utils_cvt_date2i($Frm->GetNmValueH("next_rel_date"));

    $oform = array();
    $oform[] = $Frm->GetNmValueI("fontnm");
    $oform[] = $Frm->GetNmValueI("fontsz");
    $oform[] = $Frm->GetNmValueI("interval");
    $oform[] = $Frm->GetNmValueI("links");
    $oform[] = $Frm->GetNmValueI("pole_t");
    $oform[] = $Frm->GetNmValueI("pole_b");
    $oform[] = $Frm->GetNmValueI("pole_l");
    $oform[] = $Frm->GetNmValueI("pole_r");
    $oform[] = $Frm->GetNmValueI("pagenums");

    $oform = serialize($oform);

    try {
      $discipline = Discipline::find($disciplina);
    } catch(Exception $e) {
      $discipline = Discipline::findOneBy(array(
        'name' => $disciplina,
      ));
    }

    if (!$discipline) {
      $disc_id = Discipline::create(array(
        'name' => $disciplina,
        'code' => '',
      ));
      Disciplines::addToDefaultNaprav($disc_id);
    } else {
      $disc_id = $discipline['id'];
    }

    $order_id = Order::create(array(
      "filial_id" => $klient["filial_id"],
      "creator_id" => $_SESSION["user"]["data"]["id"],
      "klient_id" => $klient["id"],
      "vuz_id" => $Frm->GetNmValueI("vuz"),
      "vuz_user" => $Frm->GetNmValueH("vuz_usr"),
      "type_id" => $Frm->GetNmValueI("work"),
      "type_user" => $Frm->GetNmValueH("work_usr"),
      "napr_id" => $Frm->GetNmValueI("napr"),
      "disc_id" => $disc_id,
      "disc_user" => '',
      "time_kln" => $kln_date,// Дата сдачи
      "cost_kln" => $Frm->GetNmValueI("cost"),
      "payment_id" => $Frm->GetNmValueI("opl"),
      "subject" => $Frm->GetNmValueH("subj"),
      "about_kln" => $Frm->GetNmValueH("treb"),//treb
      "about_mng" => $Frm->GetNmValueH("rem"),//comments manager
      "kurs" => $Frm->GetNmValueI("kurs"),
      "prakt_pc" => $Frm->GetNmValueI("prakt"),
      "pages_min" => $Frm->GetNmValueI("pgmin"),
      "pages_max" => $Frm->GetNmValueI("pgmax"),
      "src_min" => $Frm->GetNmValueI("srcmin"),
      "src_max" => $Frm->GetNmValueI("srcmax"),
      "from_id" => $Frm->GetNmValueI("take"),
      "oform" => $oform,
      "next_rel_date" => $rel_date,
    ));

    $Frm->_gui->OK("Заказ добавлен");

    // 0=>"к списку заказов", 1=>"правка заказа", 2=>"повторить заказ" //22
    switch ($Frm->GetNmValueI("next")) {
      case 0:
        unset($_SESSION["repeat_order"]);
        header("location: /index.php?section=ord&subsection=2");
        die();
        break;
      case 1:
        unset($_SESSION["repeat_order"]);
        header("location: /index.php?section=ord&subsection=2&p=1&order=" . $order_id);
        die();
        break;
      case 2:
        $_SESSION["repeat_order"] = $Frm->GetAllNmValues();
        page_reloadSubSec();
        break;
    }
  }
}

function edit_order($Frm, $Err) {
  if (!$Err) {
    $order_id = $Frm->GetNmValueI('id');
    $order_info = Order::find($order_id);

    $klient = kln_get($Frm->GetNmValueI("klient"));
    if (!$klient) {
      $Frm->_gui->ERR("Клиент не найден");
      return;
    }

    // Филиал клиента соотв филиалу сотрудника, если сотрудник рук то неважно
    if ($_SESSION["user"]["data"]["group_id"] == 1 || $_SESSION["user"]["data"]["group_id"] == 0 || $_SESSION["user"]["data"]["filial_id"] == $klient["filial_id"]) {
    } else {
      $Frm->_gui->ERR("Филиал клиента и сотрудника не совпадают");
      return;
    }

    if (($Frm->GetNmValue("work") == 0) && !strlen($Frm->GetNmValue("work_usr"))) {
      $Frm->_gui->ERR("Не указан вид работы");
      return;
    }

    $disciplina = trim($Frm->GetNmValue("disc_usr"));
    if (!strlen($disciplina)) {
      $Frm->_gui->ERR("Не указана дисциплина");
      return;
    }

    $pmin = $Frm->GetNmValueI("pgmin");
    $pmax = $Frm->GetNmValueI("pgmax");
//    if ($pmax && ($pmax < $pmin)) {
//      $Frm->_gui->ERR("Неверно указано макс. число страниц");
//      return;
//    }

    $pmin = $Frm->GetNmValueI("srcmin");
    $pmax = $Frm->GetNmValueI("srcmax");
//    if ($pmax && ($pmax < $pmin)) {
//      $Frm->_gui->ERR("Неверно указано макс. число источников");
//      return;
//    }

    if (!$Frm->GetNmValueI("pole_t") || !$Frm->GetNmValueI("pole_b") || !$Frm->GetNmValueI("pole_l") || !$Frm->GetNmValueI("pole_r")) {
      $Frm->_gui->ERR("Не указаны размеры полей в оформлении");
      return;
    }

    $changes = array();
    $kln_date = utils_cvt_date2i($Frm->GetNmValueH("date"));
    $rel_date = utils_cvt_date2i($Frm->GetNmValueH("next_rel_date"));

    $showOtdelKcomment = is_otdel_K($_SESSION["user"]["data"]['id']) || is_director($_SESSION["user"]["data"]['id']) || is_manager($_SESSION["user"]["data"]['id']);

    if ($showOtdelKcomment) {
      $ok_comment = $Frm->GetNmValueH("ok_comment");
    } else {
      $ok_comment = $order_info['ok_comment'];
    }

    if ($ok_comment != $order_info['ok_comment']) {
      $changes[] = "'комментарий ОК' с " . $order_info['ok_comment'] . " на " . $ok_comment;
      $ok_comment_date = time();
    } else {
      $ok_comment_date = $order_info['ok_comment_date'];
    }

    $oform = array();
    $oform[] = $Frm->GetNmValueI("fontnm");
    $oform[] = $Frm->GetNmValueI("fontsz");
    $oform[] = $Frm->GetNmValueI("interval");
    $oform[] = $Frm->GetNmValueI("links");
    $oform[] = $Frm->GetNmValueI("pole_t");
    $oform[] = $Frm->GetNmValueI("pole_b");
    $oform[] = $Frm->GetNmValueI("pole_l");
    $oform[] = $Frm->GetNmValueI("pole_r");
    $oform[] = $Frm->GetNmValueI("pagenums");

    $oform = serialize($oform);

    try {
      $discipline = Discipline::find($disciplina);
    } catch(Exception $e) {
      $discipline = Discipline::findOneBy(array(
        'name' => $disciplina,
      ));
    }

    if (!$discipline) {
      $disc_id = Discipline::create(array(
        'name' => $disciplina,
        'code' => '',
      ));
      Disciplines::addToDefaultNaprav($disc_id);
    } else {
      $disc_id = $discipline['id'];
    }

    $order_status_id = $Frm->GetNmValueI("status_id");

    db::insert("orders_changes_history", array(
      'change_date' => time(),
      'change_user_id' => $_SESSION['user']['data']['id'],
      'order_id' => $order_id,
      'filial_id_new' => $klient["filial_id"],
      'klient_id_new' => $klient["id"],
      'vuz_id_new' => $Frm->GetNmValueI("vuz"),
      'vuz_user_new' => $Frm->GetNmValueH("vuz_usr"),
      'type_id_new' => $Frm->GetNmValueI("work"),
      'type_user_new' => $Frm->GetNmValueH("work_usr"),
      'napr_id_new' => $Frm->GetNmValueI("napr"),
      'disc_id_new' => $disc_id,
      'disc_user_new' => $Frm->GetNmValueH("disc_usr"),
      'time_kln_new' => $kln_date,
      'cost_kln_new' => $Frm->GetNmValueI("cost"),
      'payment_id_new' => $Frm->GetNmValueI("opl"),
      'subject_new' => $Frm->GetNmValueH("subj"),
      'about_kln_new' => $Frm->GetNmValueH("treb"),
      'about_mng_new' => $Frm->GetNmValueH("rem"),
      'kurs_new' => $Frm->GetNmValueI("kurs"),
      'prakt_pc_new' => $Frm->GetNmValueI("prakt"),
      'pages_min_new' => $Frm->GetNmValueI("pgmin"),
      'pages_max_new' => $Frm->GetNmValueI("pgmax"),
      'src_min_new' => $Frm->GetNmValueI("srcmin"),
      'src_max_new' => $Frm->GetNmValueI("srcmax"),
      'from_id_new' => $Frm->GetNmValueI("take"),
      'oform_new' => $oform,
      'next_rel_date_new' => $rel_date,
      'status_id_new' => $order_status_id,
      'ok_comment_new' => $ok_comment,
      'ok_comment_date_new' => $ok_comment_date,
      'payment_comment_new' => $Frm->GetNmValueH("payment_comment"),
      'cost_auth_new' => $Frm->GetNmValueI("cost_auth"),
      'time_auth_new' => $kln_date,
      'oplata_kln_new' => $Frm->GetNmValueI("oplata_kln"),
      'author_paid_new' => $Frm->GetNmValueI("author_paid"),
      'company_paid_new' => $order_info['company_paid'],
      'filial_id_old' => $order_info['filial_id'],
      'klient_id_old' => $order_info['klient_id'],
      'vuz_id_old' => $order_info['vuz_id'],
      'vuz_user_old' => $order_info['vuz_user'],
      'type_id_old' => $order_info['type_id'],
      'type_user_old' => $order_info['type_user'],
      'napr_id_old' => $order_info['napr_id'],
      'disc_id_old' => $order_info['disc_id'],
      'disc_user_old' => $order_info['disc_user'],
      'time_kln_old' => $order_info['time_kln'],
      'cost_kln_old' => $order_info['cost_kln'],
      'payment_id_old' => $order_info['payment_id'],
      'subject_old' => $order_info['subject'],
      'about_kln_old' => $order_info['about_kln'],
      'about_mng_old' => $order_info['about_mng'],
      'kurs_old' => $order_info['kurs'],
      'prakt_pc_old' => $order_info['prakt_pc'],
      'pages_min_old' => $order_info['pages_min'],
      'pages_max_old' => $order_info['pages_max'],
      'src_min_old' => $order_info['src_min'],
      'src_max_old' => $order_info['src_max'],
      'from_id_old' => $order_info['from_id'],
      'oform_old' => $order_info['oform'],
      'next_rel_date_old' => $order_info['next_rel_date'],
      'status_id_old' => $order_info['status_id'],
      'ok_comment_old' => $order_info['ok_comment'],
      'ok_comment_date_old' => $order_info['ok_comment_date'],
      'payment_comment_old' => $order_info['payment_comment'],
      'cost_auth_old' => $order_info['cost_auth'],
      'time_auth_old' => $order_info['time_auth'],
      'oplata_kln_old' => $order_info['oplata_kln'],
      'author_paid_old' => $order_info['author_paid'],
      'company_paid_old' => $order_info['company_paid'],
    ));

    $time_kln_r = 0;
    if (get_status_iname($order_status_id) == 'ORDER_GIVEN') {
      $time_kln_r = time();
    }

    if (get_order_status($order_id) == 'ORDER_GIVEN') {
      $time_kln_r = $order_info['time_kln_r'];
    }

    Order::update($order_id, array(
      "filial_id" => $klient["filial_id"],
      "manager_id" => $order_info['manager_id'],
      "author_id" => $order_info['author_id'],
      "klient_id" => $klient["id"],
      "parent_id" => 0,
      "vuz_id" => $Frm->GetNmValueI("vuz"),
      "vuz_user" => $Frm->GetNmValueH("vuz_usr"),
      "type_id" => $Frm->GetNmValueI("work"),
      "type_user" => $Frm->GetNmValueH("work_usr"),
      "napr_id" => $Frm->GetNmValueI("napr"),
      "disc_id" => $disc_id,
      "disc_user" => '',
      "time_kln" => $kln_date,      // Дата сдачи
      "time_kln_r" => $time_kln_r,      // Реальная дата сдачи
      "cost_kln" => $Frm->GetNmValueI("cost"),
      "cost_auth" => $Frm->GetNmValueI("cost_auth"),
      "oplata_kln" => $Frm->GetNmValueI("oplata_kln"),
      "payment_id" => $Frm->GetNmValueI("opl"),
      "raspred_srok" => 0,
      "raspred_auth" => "",
      "subject" => $Frm->GetNmValueH("subj"),
      "about_kln" => $Frm->GetNmValueH("treb"),//treb
      "about_mng" => $Frm->GetNmValueH("rem"),//comments manager
      "kurs" => $Frm->GetNmValueI("kurs"),
      "prakt_pc" => $Frm->GetNmValueI("prakt"),
      "pages_min" => $Frm->GetNmValueI("pgmin"),
      "pages_max" => $Frm->GetNmValueI("pgmax"),
      "src_min" => $Frm->GetNmValueI("srcmin"),
      "src_max" => $Frm->GetNmValueI("srcmax"),
      "from_id" => $Frm->GetNmValueI("take"),
      "oform" => $oform,
      "next_rel_date" => $rel_date,
      'status_id' => $order_status_id,
      'ok_comment' => $ok_comment,
      'ok_comment_date' => $ok_comment_date,
      'author_paid' => $Frm->GetNmValueI("author_paid"),
    ));

    $Frm->_gui->OK("Заказ обновлен");
    switch ($Frm->GetNmValueI("next")) {
      case 1:
        unset($_SESSION["repeat_order"]);
        header("location: /index.php?section=ord&subsection=2&p=2&order=" . $order_id);
        die();
        break;
      case 2:
        $_SESSION["repeat_order"] = $Frm->GetAllNmValues();
        page_reloadToSec(1);
        break;
      case 0:
      default:
        unset($_SESSION["repeat_order"]);
        header("location: /index.php?section=ord&subsection=2");
        die();
        break;
    }
  }
}

function assign_order($Frm, $Err) {
  if (!$Err) {
    $author_time = utils_cvt_date2i($Frm->GetNmValueH("time_auth"));
    $raspred_time = utils_cvt_date2i($Frm->GetNmValueH("raspred_srok"));
    if ($author_time < time() || $raspred_time < time()) {
      $Frm->_gui->ERR("Нельзя указывать дату прошлым числом");
      page_reloadAll();
    }

    $order_id = $Frm->GetNmValueI("order_id");
    $status_id = get_status_id_by_iname("ON_THE_DISTRIBUTION");
    $manager_id = $Frm->GetNmValueI("manager_id");

    Order::update($order_id, array(
      "manager_id" => $manager_id,
      "time_auth" => $author_time,
      "raspred_srok" => $raspred_time,
      "cost_auth" => $Frm->GetNmValueI("author_price"),
      "payment_comment" => $Frm->GetNmValueH("payment_comment"),
      "status_id" => $status_id,
    ));

    Author::saveMessageAndEnqueueEmail(
      $order_id,
      array($manager_id),
      'u'.$_SESSION['user']['data']['id'],
      "Распределение заказа №" . $order_id ,
      "Вас назначили менеджером заказа №" . $order_id,
      \Components\Entity\EmailNotification::TO_MANAGER_ON_FIRST_ASSIGN
    );
	
			
	$send_author_msgs = ((null !== $Frm->GetNmValue("send_for_authors")) ? true : false);
		
	if ($send_author_msgs)
	{
		$order_info = get_order_info($order_id);
		$discipline_id = $order_info['disc_id'];
		if ($discipline_id>0)
		{
			// Выслать уведомления авторам
			$authors = array_keys(Disciplines::getAuthors($discipline_id));
			send_order_by_email($Frm, $Err, $authors); // само генерит сообщение
		}
		else
			$Frm->_gui->OK("Заказ поставлен на распределение, уведомления не высланы - нет дисциплины");
	}
	else
	    $Frm->_gui->OK("Заказ поставлен на распределение без уведомлений");
	
    page_reloadAll();
  }
}

function add_offer($Frm, $Err) {
  if (!$Err) 
  {
    $order_id = $Frm->GetNmValueI("order_id");
    $order_info = Order::find($order_id);

    if ($order_info['cost_auth'] != 0) 
    {
      $price = $order_info['cost_auth'];
    }
    else 
    {
      $price = $Frm->GetNmValueI("price");
    }

    $author_id = $Frm->GetNmValueI("author_id");
    //$order_id = $Frm->GetNmValueI("order_id");

    AuthorOffer::create(array(
      'order_id' => $order_id,
      'author_id' => $author_id,
      'price' => $price,
      'comment' => $Frm->GetNmValueH("comment"),
    ));

    if (!empty($order_info['manager_id'])) 
    {
      $message_id = mls_Send("u" . $order_info['manager_id'], "u" . $author_id, "Новое предложение для заказа №" . $order_id, "Поступило новое предложение на заказ №" . $order_id . " от " . sotr_getFullName($author_id), 1, 0);
      
      if(!empty ($message_id)) 
      {
        Author::enqueue_message_to_email($message_id, array($order_info['manager_id']), \Components\Entity\EmailNotification::TO_MANAGER_ON_ORDER_CHANGE);
      }
    }

    $Frm->_gui->OK("Предложение добавлено");
    page_reloadSubSec();
  }
}

function edit_offer($Frm, $Err) {
  if (!$Err) {
    $order_id = $Frm->GetNmValueI("order_id");
    $order_info = Order::find($order_id);

    if ($order_info['cost_auth'] != 0) {
      $price = $order_info['cost_auth'];
    } else {
      $price = $Frm->GetNmValueI("price");
    }

    db::update(TABLE_AUTHOR_OFFERS, array(
      'price' => $price,
      'comment' => $Frm->GetNmValueH("comment"),
    ), "order_id = " . db::input($Frm->GetNmValueI("order_id")) . " AND author_id = " . db::input($Frm->GetNmValueI("author_id")));

    $Frm->_gui->OK("Предложение обновлено");
    page_reloadSubSec();
  }
}

function send_message_to_author($value, $row, $table, &$info) {
  global $GUI;
  return $value . " " . $GUI->getIcon("?section=mls&subsection=1&_to=u" . $row["author_id"], "msg", "Написать");
}

function generate_assign_button($value, $row, $table, &$info) {
  global $order_info;

  if ($order_info['author_id'] == $row['author_id']) {
    return "<a href='?section=ord&subsection=2&order=" . $row["order_id"] . "&p=6&author=" . $row["author_id"] . "'>Снять</a>";
  } else {
    return "<a href='?section=ord&subsection=2&order=" . $row["order_id"] . "&p=6&author=" . $row["author_id"] . "'>Закрепить</a>";
  }
}

function assign_order_to_author($Frm, $Err) {
  if (!$Err) {
    $order_id = $Frm->GetNmValueI("order_id");

    try {
      $order_info = Order::find($order_id);
    } catch(Exception $e) {
      ErrorLogger::add('assign_order_to_author', 'Sessia', $_SESSION);
      ErrorLogger::add('assign_order_to_author', 'REQUEST', $_REQUEST);
      ErrorLogger::add('assign_order_to_author', 'GET', $_GET);
      ErrorLogger::add('assign_order_to_author', 'POST', $_POST);
      $Frm->_gui->ERR("Произошла ошибка");
      page_reloadAll();
    }

    $author_id = $Frm->GetNmValueI("author_id");
    $status_id = get_status_id_by_iname("ASSIGNED");
    if (!$status_id) {
      $Frm->_gui->ERR("Статус не найден");
      page_reloadAll();
    }

    Order::update($order_id, array(
      'author_id' => $author_id,
      'status_id' => $status_id,
    ));

    if ($Frm->GetNmValueI("need_offer")) {
      if (!AuthorOffer::findOneBy(array(
        'order_id' => $order_id,
        'author_id' => $author_id,
      ))) {
        AuthorOffer::create(array(
          'order_id' => $order_id,
          'author_id' => $author_id,
          'price' => $order_info['cost_auth'],
          'comment' => '',
        ));
      }
    }

    if ($order_info['disc_id'] == 0) {
      $spec = $order_info['disc_user'];
    } else {
      $spec = get_discipline_name($order_info['disc_id']);
    }

	$txt = "Заказ закреплен";

    if (\Components\Entity\EmailNotificationType::isPersistable(\Components\Entity\EmailNotification::TO_AUTHOR_ON_ASSIGN))
	{
		$body = "На вас назначен заказ №" . $order_id . ' ' . ucfirst($spec) . "<br>" .
		"<b>Обязательно подтвердите закрепление за Вами данного заказа</b>, достаточно в ответном письме написать \"OK\" <p>При выполнении работы прошу учесть:<br>
 антиплагиат - стандарт 70% (прошу присылать с выполненной работой скриншот антиплагиата)</p>";
		
	    $message_id = mls_Send("u" . $author_id, "u" . $_SESSION["user"]["data"]["id"], "На вас назначен заказ №" . $order_id . ' ' . ucfirst($spec), $body, 1, 0);
    	Author::enqueue_message_to_email($message_id, array($author_id), \Components\Entity\EmailNotification::TO_AUTHOR_ON_ASSIGN);
		$txt = "Заказ закреплен, уведомление отправлено";
    }

    $old_author_id = $Frm->GetNmValueI("old_author_id");
    if ($old_author_id != 0) 
    {
    	
		if (\Components\Entity\EmailNotificationType::isPersistable(\Components\Entity\EmailNotification::TO_AUTHOR_ON_UNASSIGN))
		{		
    		$message_id = mls_Send("u" . $old_author_id, "u" . $_SESSION["user"]["data"]["id"], "Вы сняты с заказа №" . $order_id, "Вас сняли с заказа №" . $order_id . "<br>Причина: " . $Frm->GetNmValueH("reason"), 1, 0);
			Author::enqueue_message_to_email($message_id, array($old_author_id), \Components\Entity\EmailNotification::TO_AUTHOR_ON_UNASSIGN);
		}
    }

//    Author::sendEmail($order_id, array($author_id), 'Закрепление заказа №' . $order_id . ' ' . ucfirst($spec), 'Заказ №' . $order_id . ' ' . ucfirst($spec) . ' закреплен за вами');

    $Frm->_gui->OK($txt);
    redirect('index.php?section=ord&subsection=2&order=' . $order_id . '&p=3');
  }
}

function remove_author_from_order($Frm, $Err) {
  if (!$Err) {
    $status_id = get_status_id_by_iname("ON_THE_DISTRIBUTION");
    if (!$status_id) {
      $Frm->_gui->ERR("Статус не найден");
      page_reloadAll();
    }

    $order_id = $Frm->GetNmValueI("order_id");
    Order::update($order_id, array(
      'author_id' => 0,
      'status_id' => $status_id,
    ));
    
    if (\Components\Entity\EmailNotificationType::isPersistable(\Components\Entity\EmailNotification::TO_AUTHOR_ON_UNASSIGN))
    {
		$message_id = mls_Send("u" . $Frm->GetNmValueI("author_id"), "u" . $_SESSION["user"]["data"]["id"], "Вы сняты с заказа №" . $order_id, "Вас сняли с заказа №" . $order_id . "<br>Причина: " . $Frm->GetNmValueH("reason"), 1, 0);
		Author::enqueue_message_to_email($message_id, array($Frm->GetNmValueI("author_id")), \Components\Entity\EmailNotification::TO_AUTHOR_ON_UNASSIGN );
		$Frm->_gui->OK("Заказ снят, уведомление отправлено");
    }
    else
    	$Frm->_gui->OK("Заказ снят");

    redirect('index.php?section=ord&subsection=2&order=' . $order_id . '&p=3');
  }
}

function get_payment_comment($value, $row, $table, &$info) {
  global $data_author_payment_status;
  if (isset($data_author_payment_status[$row['payment_comment']])) {
    return $data_author_payment_status[$row['payment_comment']];
  }
}

function assign_order_to_manager($Frm, $Err) {
  if (!$Err) {
    $order_id = $Frm->GetNmValueI("order_id");
    $manager_id = $Frm->GetNmValueI("manager_id");

    Order::update($order_id, array(
      'manager_id' => $manager_id,
    ));

    $do_msg = \Components\Entity\EmailNotificationType::isPersistable(\Components\Entity\EmailNotification::TO_MANAGERS_ON_MANAGER_CHANGE);
 
    if ($do_msg)
    {
    	$message_id = mls_Send("u" . $manager_id, "u" . $_SESSION["user"]["data"]["id"], "На вас назначен заказ №" . $order_id, "На вас назначен заказ №" . $order_id, 1, 0);
    	Author::enqueue_message_to_email($message_id, array($manager_id), \Components\Entity\EmailNotification::TO_MANAGERS_ON_MANAGER_CHANGE );
    }

    $old_manager_id = $Frm->GetNmValueI("old_manager_id");
    
    if ($do_msg && ($old_manager_id != 0))
    {
    	$message_id = mls_Send("u" . $old_manager_id, "u" . $_SESSION["user"]["data"]["id"], "Вы сняты с заказа №" . $order_id, "Вас сняли с заказа №" . $order_id . "<br>Причина: " . $Frm->GetNmValueH("reason"), 1, 0);
	Author::enqueue_message_to_email($message_id, array($old_manager_id), \Components\Entity\EmailNotification::TO_MANAGERS_ON_MANAGER_CHANGE );
    }

    $txt = "Заказ закреплен";
    if ($do_msg) $txt .= ", уведомления отправлены";
    $Frm->_gui->OK($txt);
    page_reloadAll();
  }
}

function send_order_by_email($Frm, $Err, $_authors=null) {
  global $data_courses, $data_practica;
  if (!$Err) {
    $order_id = $Frm->GetNmValueI("order_id");
    $changes = array();
    $order_info = get_order_info($order_id);

    if ($order_info['vuz_id'] == 0) {
      $changes[] = "'вуз' " . $order_info['vuz_user'];
    } else {
      $vuz1 = get_vuz_name($order_info['vuz_id']);
      $changes[] = "'вуз' " . $vuz1['sname'] . "(" . $vuz1['name'] . ")";
    }

    if ($order_info['type_id'] == 0) {
      $changes[] = "'вид работы' " . $order_info['type_user'];
    } else {
      $changes[] = "'вид работы' " . get_worktype_name($order_info['type_id']);
    }

    $changes[] = "'факультет' " . get_naprav_name($order_info['napr_id']);

    if ($order_info['disc_id'] == 0) {
      $spec = $order_info['disc_user'];
    } else {
      $spec = get_discipline_name($order_info['disc_id']);
    }
    $changes[] = "Специальность: " . $spec;
    $changes[] = "Тема работы: " . $order_info['subject'];
    $changes[] = "Требования: " . $order_info['about_kln'];
    $changes[] = "Курс: " . $data_courses[$order_info['kurs']]['name'];
    $changes[] = "Практика: " . $data_practica[$order_info['prakt_pc']]['name'];
    $changes[] = "Минимальное число страниц: " . $order_info['pages_min'];
    $changes[] = "Максимальное число страниц: " . $order_info['pages_max'];
    $changes[] = "Минимальное число источников: " . $order_info['src_min'];
    $changes[] = "Максимальное число источников: " . $order_info['src_max'];
    $changes[] = "Гонорар автора: " . $order_info['cost_auth'];
    $changes[] = "Дата сдачи для автора: " . format_date($order_info['time_auth']);
	$changes[] = "";

    try {
      $manager = Employee::find($order_info['manager_id']);
    } catch(Exception $e) {
      $Frm->_gui->ERR("У заказа не указан менеджер");
      page_reloadAll();
    }

    try {
      $filial = Filial::find($order_info['filial_id']);
    } catch(Exception $e) {
      $Frm->_gui->ERR("У заказа не указан филиал");
      page_reloadAll();
    }

    if (empty($filial['email'])) {
      $Frm->_gui->ERR("У филиала к которому принадлежит заказ не указан email");
      page_reloadAll();
    }

    $changes[] = "Менеджер: " . $manager['fio'] . ". По данному заказу писать на почту " . $filial['email'];
    $changes[] = "Если заказ по данной дисциплине/специальности для Вас не является профильным, то Вы в любой момент можете отписаться от данной дисциплины/специальности в своем личном кабинете, расположенном по адресу: sessia-online.ru. Там же Вы так же можете подписаться на другие, интересные Вам дисциплины.";

    $msg_for_author = "Детали заказа №" . $order_id . ":\n";
    $msg_for_author .= join($changes, "<br>");

    $authors = array();
	
	if (is_array($_authors) && count($_authors))
	{
		$authors = $_authors;
	}
	else if (isset($_POST['authors'])) 
    {
      $authors = $_POST['authors'];
    }
	
    $failed = Author::saveMessageAndEnqueueEmail(
      $order_id,
      $authors,
      'u'.$_SESSION['user']['data']['id'],
      '№' . $order_id . ' ' . ucfirst($spec),
      str_replace(array('http://', 'https://'), '', $msg_for_author),
      \Components\Entity\EmailNotification::TO_SUBSCRIBED_AUTHORS_ON_DISTRIBUTION
    );

//    $failed = Author::sendEmail($order_id, $authors, '№' . $order_id . ' ' . ucfirst($spec), str_replace(array('http://', 'https://'), '', $msg_for_author), true, true);
    if (!count($failed)) {
      $Frm->_gui->OK("Заказ отправлен");
    } else {
      $failed_receivers = array();
      foreach($failed as $receiver) {
        $failed_receivers[] = $receiver['name'] . ' - ' . $receiver['email'];
      }
      $Frm->_gui->ERR("Вовремя отправки заказа возникли ошибки. Заказ не отправлен: " . join("\n", $failed_receivers));
    }
  }
}

function tp_author_notification($value, $row, $table, &$info) {
  global $GUI;

  try {
    $author = Employee::find($row['author_id']);

    $icon = '<div>';
    $icon .= $GUI->getIcon("?section=ord&subsection=2&order=" . $row['id'] . "&p=7&t=1", "msg", "Отправить напоминание");

    $icon .= '<span style="color:blue; position: relative; top: -2px;left:5px;">' . count(AuthorNotification::findBy(array(
      'author_id' => $author['id'],
      'order_id' => $row['id'],
      'type' => 1
    ))) . '</span>';
    $icon .= '</div>';

    $icon .= '<div>';
    $icon .= $GUI->getIcon("?section=ord&subsection=2&order=" . $row['id'] . "&p=7&t=2", "msg_red", "Отправить гневное напоминание");

    $icon .= '<span style="color:red; position: relative; top: -2px;left:5px;">' . count(AuthorNotification::findBy(array(
        'author_id' => $author['id'],
        'order_id' => $row['id'],
        'type' => 2
      ))) . '</span>';
    $icon .= '</div>';

  } catch(Exception $e) {
    $icon = '<div style="height: 20px;"></div>';
  }

  $offers_qt = '';
  if (!empty($row['manager_id']) && !empty($row['status_id'])) {
    $offers_qt = count(AuthorOffer::findBy(array(
      'order_id' => $row['id'],
    )));
  }

  return $icon . '<span style="color:red; position: relative; top: -26px;left:50px;">' . $offers_qt . '</span>';
}
