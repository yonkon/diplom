<?php

use Components\Entity\Employee;
use Components\Entity\EmployeeBlack;
use Components\Classes\Author;
use Components\Entity\Order;
use Components\Classes\db;

use Components\Exceptions\Exception;

function editsotr_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('user_id');

    try {
      $user = Employee::find($id);
    } catch(Exception $e) {
      $Frm->_gui->informer->ERR("Сотрудник не найден");
      return;
    }

    if ($user['group_id'] == 0) {
      $Frm->_gui->informer->ERR("Нельзя редактировать Системного администратора");
      return;
    }
    $fil = $Frm->GetNmValueI('filial');
    $grp = $Frm->GetNmValueI('group');
    $password = $Frm->GetNmValue('password');
    $email = strtolower($Frm->GetNmValueH('email'));
    $ryk_group_id = get_role_id_by_name('Руководитель');
    $author_group_id = get_role_id_by_name('Автор');
    if ($fil == 0 && $grp != $ryk_group_id && $grp != $author_group_id) {
      $Frm->_gui->informer->ERR("Для данной группы необходимо указать филиал");
      return;
    }
    if (TEST_MODE && $email != $user['email']) {
      $password = TEST_PASSWORD;
    } elseif (!TEST_MODE && strlen($password) < PASSWORD_MIN_CHARS) {
      $Frm->_gui->informer->ERR("Пароль не может быть меньше " . PASSWORD_MIN_CHARS . " символов");
      return;
    }

    Employee::update($id, array(
      'filial_id' => $fil,
      'fio' => $Frm->GetNmValueH('fio'),
      'email' => $email,
      'group_id' => $grp,
      'telnum' => $Frm->GetNmValueH('phone'),
      'cont' => $Frm->GetNmValueH('contacts'),
      'comments' => $Frm->GetNmValueH('comments'),
      'blocked' => $Frm->GetNmValueI('blocked'),
      'payment_requisites' => $Frm->GetNmValueH('payment_requisites'),
      'password' => $password,
      'hpwd' => md5($password . $email)
    ));

    if ($author_group_id == $grp) {
      Author::add_napravl($id, $Frm->GetNmValue('author_napravl'));
    }
    if (TEST_MODE) {
      $Frm->_gui->informer->OK("Сохранено. (тестовый режим - пароль не сохраняется)");
    } else {
      $Frm->_gui->informer->OK("Сохранено");
    }
    page_reloadAll();
  }
}

function addsotr_exec($Frm, $Err) {
  if (!$Err) {
    $fil = $Frm->GetNmValueI('filial');
    $grp = $Frm->GetNmValueI('group');
    $password = $Frm->GetNmValue('password');
    $email = strtolower($Frm->GetNmValueH('email'));
    if (Employee::exist($email)) {
      $Frm->_gui->informer->ERR("Сотрудник с таким email существует");
      return;
    }
    $ryk_group_id = get_role_id_by_name('Руководитель');
    $author_group_id = get_role_id_by_name('Автор');
    if ($fil == 0 && $grp != $ryk_group_id && $grp != $author_group_id) {
      $Frm->_gui->informer->ERR("Для данной группы необходимо указать филиал");
      return;
    }
    if (TEST_MODE) {
      $password = TEST_PASSWORD;
    }
    $user_id = Employee::create(array(
      'filial_id' => $fil,
      'fio' => $Frm->GetNmValueH('fio'),
      'email' => $email,
      'password' => $password,
      'telnum' => $Frm->GetNmValueH('phone'),
      'cont' => $Frm->GetNmValueH('contacts'),
      'group_id' => $grp,
      'comments' => $Frm->GetNmValueH('comments'),
      'payment_requisites' => $Frm->GetNmValueH('payment_requisites'),
    ));
    if ($author_group_id == $grp) {
      Author::add_napravl($user_id, $Frm->GetNmValue('author_napravl'));
    }
    if (TEST_MODE) {
      $Frm->_gui->informer->OK("Добавлено (тестовый режим - пароль " . TEST_PASSWORD . ")");
    } else {
      $Frm->_gui->informer->OK("Добавлено");
    }
    page_reloadSec();
  }
}

function tp_users_groupname($value, $row, $table, &$info) {
  global $data_groups;
  if (isset($data_groups[$value]["name"])) {
    return $data_groups[$value]["name"];
  } else {
    return '';
  }
}

function tp_users_name($value, $row, $table, &$info) {
  If ($table instanceof CGUI_Table && $table->rowmenu) {
    $table->rowmenu->Vars[1] = $row['id'];
    return $table->rowmenu->GetHTML($value);
  } else {
    return $value;
  }
}

function tp_users_filname($value, $row, $table, &$info) {
  global $data_filials;
  if ($value == 0 || empty($data_filials) || !isset($data_filials[$value])) {
    return "<i>не указан</i>";
  } else {
    return "<a href='?section=fils&light=" . $data_filials[$value]["id"] . "'>" . $data_filials[$value]["name"] . "</a>";
  }
}

function tp_users_cmds($value, $row, $table, &$info) {
  global $GUI;
  return $value . " " . $GUI->getIcon("?section=mls&subsection=1&_to=u" . $row["id"], "msg", "Написать");
}

function deluser_exec($Frm, $Err) {
  if (!$Err) {
    if (is_director($_SESSION["user"]['data']['id'])) {
      $id = $Frm->GetNmValueI('id');
      if (count(Order::findBy(array(
        'manager_id' => $id,
      )))) {
        $Frm->_gui->informer->ERR("У сотрудника есть назначенные заказы");
        return;
      }

      if (db::get_single_value("SELECT COUNT(id) FROM " . TBL_PREF . "data_visits WHERE user_id = '" . $id . "' AND status <> 1")) {
        $Frm->_gui->informer->ERR("У сотрудника есть назначенные встречи");
        return;
      }
      $resone = $Frm->GetNmValueH('reason');

	db::query("insert into " . TABLE_USERS_BLACK . " select * from " . TABLE_USERS . " where id= " . $id);
	EmployeeBlack::update($id, array(
		'comments' => $resone,
		'blocked' => 1,
		'black_list' => 1,
		'removed_by' => $_SESSION['user']['data']['id'],
		'removed_time' => time(),
	));

	Employee::delete($id);

      $Frm->_gui->informer->OK("Сотрудник перенесен в черный список");
    } else {
      $Frm->_gui->informer->ERR("Перемещать сотрудников в черный список может только руководитель");
    }
    page_reloadSec();
  }
}

function getmsgs_exec($Frm, $Err) {
  if (!$Err) {
    $sotrudnik_id = $Frm->GetNmValueI('employer_id');
    $mail_with_sotrudnik = $Frm->GetNmValueI('with_employers');
    $mail_with_client = $Frm->GetNmValueI('with_clients');
    $mail_client_id = $mail_sotrudnik_id = 0;
    if ($mail_with_sotrudnik) {
      $mail_sotrudnik_id = $Frm->GetNmValueI('employers');
    } elseif ($mail_with_client) {
      $mail_client_id = $Frm->GetNmValueI('clients');
    } else {
      $Frm->_gui->informer->ERR("Выберите с кем хотите посмотреть переписку");
      page_reloadAll();
    }
    $date_from = $Frm->GetNmValue('date_from');
    $date_till = $Frm->GetNmValue('date_to');
    $mail_type = $Frm->GetNmValueI('direction');
    if (empty($date_from)) {
      $date_from = date('d-m-Y', strtotime('-1 year'));
    }
    if (empty($date_till)) {
      $date_till = date('d-m-Y');
    }
    redirect('index.php?section=mls&show_history=1&sotr_id=' . $sotrudnik_id . '&date_from=' . $date_from . '&date_till=' . $date_till . '&mail_type=' . $mail_type . '&mail_with_sotr=' . $mail_with_sotrudnik . '&mail_with_client=' . $mail_with_client . '&mail_sotr_id=' . $mail_sotrudnik_id . '&mail_client_id=' . $mail_client_id);
  }
}

function tp_user_lastact($value, $row, $table, &$info) {
  $d = time() - $value;
  if ($value == 0) {
    return "не входил";
  }
  if ($d <= 300) {
    return "на сайте";
  }
  if ($d < 24 * 3600) {
    $h = floor($d / 3600);
    $m = floor(($d - $h * 3600) / 60);
    return $h . "ч " . $m . "м назад";
  }
  return date("d.m.y H:i:s", $value);
}

function tp_user_blocked($value, $row, $table, &$info) {
  if ($value) {
    return "да";
  } else {
    return "";
  }
}

function _on_row_start(&$Row) {
  if ($Row["data"]["id"] == $_SESSION["user"]["data"]["id"]) {
    $Row["style"]["color"] = "green";
  }
}
