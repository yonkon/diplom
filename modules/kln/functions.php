<?php

use Components\Classes\db;
use Components\Classes\Filials;

use Components\Entity\Client;
use Components\Entity\Order;

function delclient_exec($Frm, $Err) {
  if (!$Err) {
    $client = Client::find($Frm->GetNmValue('id'));
    if (!$client) {
      $Frm->_gui->ERR("Клиент не найден");
      page_ReloadToSec(2);
    }

    $ords = Order::findBy(array(
      'klient_id' => $client["id"],
    ));

    if ($ords && count($ords)) {
      $Frm->_gui->ERR("У клиента есть заказы, его нельзя удалить");
      page_ReloadToSec(2);
    }

    if (Client::delete($client["id"])) {
      $Frm->_gui->OK("Клиент удален");
    } else {
      $Frm->_gui->ERR("Ошибка при попытке удалить клиента");
    }

    page_reloadToSec("2");
  }
}

function editclient_exec($Frm, $Err) {
  if (!$Err) {
    $client_info = Client::find($Frm->GetNmValueI("id"));
    if (!$client_info) {
      $Frm->_gui->ERR("Клиент не найден");
      page_reloadToSec(2);
    }

    $filial_id = $Frm->GetNmValueI("filial_id");
    if (empty($filial_id)) {
      $Frm->_gui->ERR("Не указан филиал");
      return;
    }

    $new_password = $client_info['hpwd'];
    $pwd = $client_info['password'];
    if ($Frm->GetNmValueH("newpwd") != "") {
      if ($Frm->GetNmValueI("genpwd")) {
        $pwd = generate_pasw(5);
      } else {
        $pwd = $Frm->GetNmValueI("newpwd");
      }
      if (strlen($pwd) < 5) {
        $Frm->_gui->ERR("Длина пароля должна быть не менее 5 символов");
        return;
      }
      if (strlen($pwd) > 20) {
        $Frm->_gui->ERR("Длина пароля должна быть не более 20 символов");
        return;
      }
      $eml = strtolower($Frm->GetNmValueH("email"));
      $new_password = md5($pwd . $eml);
    }

    db::insert(TABLE_CLIENTS_HISTORY, array(
      'change_date' => time(),
      'change_user_id' => $_SESSION['user']['data']['id'],
      'client_id' => $client_info["id"],
      'filial_id_new' => $filial_id,
      'fio_new' => $Frm->GetNmValueH("fio"),
      'hpwd_new' => $new_password,
      'email_new' => $Frm->GetNmValueH("email"),
      'telnum_new' => $Frm->GetNmValueH("telnum"),
      'city_new' => $Frm->GetNmValueH("city"),
      'icq_new' => $Frm->GetNmValueH("icq"),
      'skype_new' => $Frm->GetNmValueH("skype"),
      'contacts_new' => $Frm->GetNmValueH("contacts"),
      'about_new' => $Frm->GetNmValueH("about"),
      'filial_id_old' => $client_info['filial_id'],
      'fio_old' => $client_info['fio'],
      'hpwd_old' => $client_info['hpwd'],
      'email_old' => $client_info['email'],
      'telnum_old' => $client_info['telnum'],
      'city_old' => $client_info['city'],
      'icq_old' => $client_info['icq'],
      'skype_old' => $client_info['skype'],
      'contacts_old' => $client_info['contacts'],
      'about_old' => $client_info['about'],
    ));

    Client::update($client_info['id'], array(
      'filial_id' => $filial_id,
      'fio' => $Frm->GetNmValueH("fio"),
      'email' => $Frm->GetNmValueH("email"),
      'telnum' => $Frm->GetNmValueH("telnum"),
      'city' => $Frm->GetNmValueH("city"),
      'hpwd' => $new_password,
      'password' => $pwd,
      'icq' => $Frm->GetNmValueH("icq"),
      'skype' => $Frm->GetNmValueH("skype"),
      'contacts' => $Frm->GetNmValueH("contacts"),
      'about' => $Frm->GetNmValueH("about"),
      'ref_id' => $Frm->GetNmValueI('ref'),
      'from_id' => $Frm->GetNmValueI('client_from'),
    ));

    db::update(TABLE_ORDERS, array(
      'filial_id' => $filial_id,
    ), 'klient_id = ' . $client_info["id"]);

    $Frm->_gui->OK("Сохранено");
    page_reloadAll();
  }
}

function addclient_exec($Frm, $Err) {
  if (!$Err) {
    $rnd_pwd = $Frm->GetNmValueI('random_password');

    if ($rnd_pwd) {
      $pwd = generate_pasw(5);
    } else {
      $pwd = $Frm->GetNmValueH('password');
    }

    $eml = trim(strtolower($Frm->GetNmValue('email')));

    if (Client::exist($eml)) {
      $Frm->_gui->informer->ERR("Клиент с таким email уже существует");
      page_reloadAll();
    }

    $filial_id = Filials::check($Frm->GetNmValueI('filial_id'));
    $client_id = Client::create(array(
      'filial_id' => $filial_id,
      'fio' => $Frm->GetNmValueH('name'),
      'email' => $eml,
      'telnum' => $Frm->GetNmValueH('phone'),
      'city' => $Frm->GetNmValueH('city'),
      'icq' => $Frm->GetNmValueH('icq'),
      'skype' => $Frm->GetNmValueH('skype'),
      'contacts' => $Frm->GetNmValueH('contacts'),
      'about' => $Frm->GetNmValueH('about'),
      'ref_id' => $Frm->GetNmValueI('ref'),
      'from_id' => $Frm->GetNmValueI('client_from'),
      'added_by' => $_SESSION["user"]["data"]["id"],
      'password' => $pwd,
    ));

    $Frm->_gui->OK("Добавлено");

    switch ($Frm->GetNmValueI('next')) {
      case 0:
        page_reloadSec();
        break;
      case 1:
        header("location: ?section=kln&subsection=2&edit=" . $client_id);
        die();
        break;
      case 2:
        header("location: ?section=ord&subsection=1&kln_id=" . $client_id);
        die();
        break;
    }
  }
}
/////////////////////
function tp_filial_name($v) {
  global $data_filials;
  if (isset($data_filials[$v])) {
    return $data_filials[$v]["name"];
  } else {
    return "<i>не определено</i>";
  }
}

function tp_datefmt($v) {
  if ($v) {
    return date("d.m.Y H:i", $v);
  } else {
    return "<i>не определено</i>";
  }
}

function tp_get_blocked($v) {
  if ($v) {
    return "Да";
  } else {
    return "<font color='gray'>Нет</font>";
  }
}

function tp_show_ocenka($v) {
  if ($v) {
    return $v;
  } else {
    return "<i>не оценен</i>";
  }
}

function tp_show_referal($v, $d) {
//  if ($d["added_by"]) {
//    return sotr_getFullName($d["added_by"]);
//  } else {
    return "<i>не указано</i>";
//  }
}

function tp_show_from($v) {
  global $client_sources;
  if ($v) {
    return $client_sources[$v]["name"];
  } else {
    return "<i>не указано</i>";
  }
}

function tp_show_user($v) {
  global $data_users, $data_groups;
  if ($v) {
    return $data_groups[$data_users[$v]["group_id"]]["sname"] . $data_users[$v]["fio"];
  } else {
    return "";
  }
}

function tp_client_cmds($v, $d, $tbl) {
  global $GUI, $n;
  $out = "[<a href='?section=ord&subsection=1&kln_id=" . $d["id"] . "'>заказ</a>]";
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    $out .= "[<a href='?section=kln&subsection=3&kln_id=" . $d["id"] . "'>удалить</a>]";
  }
  $out .= $GUI->getIcon("?section=mls&subsection=1&_to=k" . $d["id"], "msg", "Написать");
  return $out;
}