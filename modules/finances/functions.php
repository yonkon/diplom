<?php

use Components\Classes\db;
use Components\Entity\Client;

function tp_get_id($value, $row, $table, &$info) {
  if ($table instanceof CGUI_Table && $table->rowmenu) {
    $table->rowmenu->Vars[1] = $value;
    $table->rowmenu->Vars[2] = $row["klient_id"];
    $html = $table->rowmenu->GetHTML($value);
  } else {
    $html = $value;
  }

  $referrer = Client::find($row['referrer_id']);
  $info = new CGUI_TableCellInfo();
  $info->Text(
    "<p>Номер: #" . $referrer['id'] . "</p>" .
    "<p>Имя: " . $referrer['fio'] . "</p>"
  );
  $info->icon = '<img src="' . SITE_URL . '/gui/skin/coins.png"/>';

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

function _get_disc_name($value, $row, $table, &$info) {
  global $data_discip;
  need_data("data_discip");

  if (intval($value) == 0) {
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
  global $data_napravl;
  return $data_napravl[$value]["name"];
}

function _get_worktype_name($value, $row, $table, &$info) {
  global $data_worktypes;

  if (intval($value) == 0) {
    if ($row["type_user"] == "") {
      return "<i>не определено</i>";
    } else {
      return $row["type_user"];
    }
  } else {
    return $data_worktypes[$value]["name"];
  }
}

function _get_client_name($value, $row, $table, &$info) {
  global $clients_info;
  $info = new CGUI_TableCellInfo();
  $info->Text("<p>Номер: #" . $clients_info[$value]["id"] . "</p>" . "<p>Телефон: " . $clients_info[$value]["telnum"] . "</p>" . "<p>Почта: " . $clients_info[$value]["email"] . "</p>" . "<p>Город: " . $clients_info[$value]["city"] . "</p>");
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

  if (empty($value)) {
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
    $ids[$r["data"]["referrer_id"]] = $r["data"]["referrer_id"];
  }

  if (count($ids)) {
    $clients = db::get_assoc_arrays("SELECT id, fio, telnum, email, city FROM " . TABLE_CLIENTS . " WHERE id IN (" . join(', ', $ids) . ")");

    foreach ($clients as $r) {
      $clients_info[$r["id"]] = $r;
    }
  }
}

function fp_loadfile($Frm, $Err) {
  global $order_id;
  if (!$Err) {
    $f = $Frm->GetValue(0);
    //$f["tmp_name"]
    //$f["name"]
    //$f["size"]
    //$f["type"]
    if (is_uploaded_file($f["tmp_name"])) {
      $extension = get_file_ext($f['name']);
      if (trim($Frm->GetValueH(1)) == "") {
        $name = $f["name"];
      } else {
        $name = trim($Frm->GetValueH(1)) . '.' . $extension;
      }

      $file_id = Order::attachFile($order_id, $_SESSION["user"]["data"]["id"], $name, $f["size"]);

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

        if (move_uploaded_file($f['tmp_name'], $dir . $file_name)) {
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
          Order::deleteAttachedFile($file_id);
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

function get_consumption($value, $row, $table, &$info) {
  return $row['cost_kln'] * get_config_value_by_iname('REFERRER_SYSTEM_PROFIT') / 100;
}

function get_referrer_payment_status($value, $row, $table, &$info) {
  if ($value == 1) {
    return 'Оплачено';
  } else {
    return 'Не оплачено';
  }
}

function tp_users_cmds($value, $row, $table, &$info) {
  global $GUI;

  return $value . " " . $GUI->getIcon("?section=mls&subsection=1&_to=k" . $row["referrer_id"], "msg", "Написать");
}

function get_payment_comment($value, $row, $table, &$info) {
  global $data_author_payment_status;
  if (isset($data_author_payment_status[$row['payment_comment']])) {
    return $data_author_payment_status[$row['payment_comment']];
  }
}

function get_client_debt($value, $row, $table, &$info) {
  return $row['cost_kln'] - $row['oplata_kln'];
}