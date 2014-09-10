<?php

use Components\Classes\db;
use Components\Classes\Napravls;
use Components\Classes\Disciplines;

use Components\Entity\Napravl;
use Components\Entity\PaymentMethod;
use Components\Entity\Worktypes;
use Components\Entity\VUZ;
use Components\Entity\Discipline;
use Components\Entity\SubwayStation;
use Components\Entity\Role;

function tp_email_notify_actions($v, $d, $tbl)
{
switch ($v){
case 0:
 return "игнорировать";
case 1:
 return "внутреннее сообщение";
case 2:
 return "внутреннее сообщение + email";
default:
 return "недопустимое значение";
}
}

function tp_payments_onsite($v, $d, $tbl) {
  return $v ? "Да" : "Нет";
}

function tp_payments_cmds($v, $d, $tbl) {
  global $n, $GUI;
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    return "<a href='?section=sprav&subsection=1&del=" . $d["id"] . "'>[удалить]</a>";
  } else {
    return 'Нет прав на удаление';
  }
}

function editpayment_exec($Frm, $Err) {
  if (!$Err) {
    PaymentMethod::update($Frm->GetNmValueI('id'), array(
      'name' => $Frm->GetNmValueH('name'),
      'onsite' => $Frm->GetNmValueI('onsite'),
    ));

    $Frm->_gui->informer->OK("Сохранено");
    page_reloadAll();
  }
}

function addpayment_exec($Frm, $Err) {
  if (!$Err) {
    PaymentMethod::create(array(
      'name' => $Frm->GetNmValueH('name'),
      'onsite' => $Frm->GetNmValueI('onsite'),
    ));

    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSubSec();
  }
}

function delpayment_exec($Frm, $Err) {
  if (!$Err) {
    PaymentMethod::delete($Frm->GetNmValueI('id'));

    $Frm->_gui->informer->OK("Удалено");
    page_reloadSubSec();
  }
}

/// napr
function tp_napravl_cmds($v, $d, $tbl) {
  global $n, $GUI;

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    return "<a href='?" . $tbl->_gui->Url() . "del=" . $d["id"] . "'>[удалить]</a>";
  } else {
    return 'Нет прав на удаление';
  }
}

function editnapravl_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');
    $name = $Frm->GetNmValueH('name');

    if (Napravls::exist($name)) {
      $Frm->_gui->informer->ERR("Такое название уже используется");
    } elseif (Napravls::isDefault($id)) {
      $Frm->_gui->informer->ERR("Нельзя переименовывать/удалять направление Прочее");
    } else {
      if (Napravl::update($id, array(
        'name' => $name,
      ))) {
        $Frm->_gui->informer->OK("Сохранено");
        page_reloadSubSec();
      } else {
        $Frm->_gui->informer->ERR("Произошла ошибка. Запись не обновлена");
      }
    }
  }
}

function addnapravl_exec($Frm, $Err) {
  if (!$Err) {
    $name = $Frm->GetNmValueH('name');

    if (Napravls::exist($name)) {
      $Frm->_gui->informer->ERR("Такая запись уже существует");
    } else {
      if (Napravl::create(array(
        'name' => $name,
      ))) {
        $Frm->_gui->informer->OK("Добавлено");
      } else {
        $Frm->_gui->informer->ERR("Произошла ошибка. Запись не добавлена");
      }
      page_reloadSubSec();
    }
  }
}

function delnapravl_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');

    if (Napravls::isDefault($id)) {
      $Frm->_gui->informer->ERR("Нельзя переименовывать/удалять направление Прочее");
    } else {
      if (Napravl::delete($id)) {
        $Frm->_gui->informer->OK("Удалено");
      } else {
        $Frm->_gui->informer->ERR("Произошла ошибка. Запись не удалена");
      }
      page_reloadSubSec();
    }
  }
}

/// types
function tp_worktypes_cmds($v, $d, $tbl) {
  global $n, $GUI;

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    return "<a href='?" . $tbl->_gui->Url() . "del=" . $d["id"] . "'>[удалить]</a>";
  } else {
    return 'Нет прав на удаление';
  }
}

function editworktypes_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');

    Worktypes::update($id, array(
      'name' => $Frm->GetNmValueH('name'),
      'rem' => $Frm->GetNmValueH('rem'),
    ));

    $Frm->_gui->informer->OK("Сохранено");
    page_reloadAll();
  }
}

function addworktypes_exec($Frm, $Err) {
  if (!$Err) {
    if (Worktypes::findOneBy(array(
      'name' => $Frm->GetNmValueH('name')
    ))) {
      $Frm->_gui->informer->ERR("Такая запись уже существует");
    }

    Worktypes::create(array(
      'name' => $Frm->GetNmValueH('name'),
      'rem' => $Frm->GetNmValueH('rem'),
    ));

    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSubSec();
  }
}

function delworktypes_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');
    if (Worktypes::find($id)) {
      Worktypes::delete($id);
      $Frm->_gui->informer->OK("Удалено");
      page_reloadSubSec();
    }
  }
}

//// vuz
function tp_vuz_cmds($v, $d, $tbl) {
  global $n, $GUI;

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    return "<a href='?" . $tbl->_gui->Url() . "del=" . $d["id"] . "'>[удалить]</a>";
  } else {
    return 'Нет прав на удаление';
  }
}

function editvuz_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');
    $vuz = VUZ::find($id);

    if ($vuz) {
      VUZ::update($id, array(
        'name' => $Frm->GetNmValueH('name'),
        'sname' => $Frm->GetNmValueH('sname'),
        'addr' => $Frm->GetNmValueH('addr'),
      ));

      $Frm->_gui->informer->OK("Сохранено");
      page_reloadAll();
    }
  }
}

function addvuz_exec($Frm, $Err) {
  if (!$Err) {
    $sname = $Frm->GetNmValueH('sname');
    if (VUZ::findOneBy(array(
      'sname' => $sname,
    ))) {
      $Frm->_gui->informer->ERR("Запись существует");
      page_reloadSubSec();
    }

    VUZ::create(array(
      'name' => $Frm->GetNmValueH('name'),
      'sname' => $Frm->GetNmValueH('sname'),
      'addr' => $Frm->GetNmValueH('addr'),
    ));

    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSubSec();
  }
}

function delvuz_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');

    if (VUZ::find($id)) {
      VUZ::delete($id);
      $Frm->_gui->informer->OK("Удалено");
      page_reloadSubSec();
    }
  }
}

function impvuz_exec($Frm, $Err) {
  if (!$Err) {
    $v = $Frm->GetValue(0);
    if (!strpos($v["type"], "ms-excel")) {
      $Frm->_gui->informer->ERR("Неправильный тип файла");
      page_reloadSubSec();
    } else {

      $s = "";

      if ($Frm->GetValue(1)) {
        db::truncate(TABLE_VUZ);
        $s = "Таблица очищена. ";
      }

      include_once "ext/Excel/reader.php";
      $data = new Spreadsheet_Excel_Reader($v["tmp_name"]);
      if ($data->sheets[0]['numCols'] != 3) {
        $Frm->_gui->informer->ERR("В таблице должно быть 3 колонки");
        page_reloadSubSec();
        return;
      }

      for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
        VUZ::create(array(
          'name' => htmlspecialchars($data->sheets[0]['cells'][$i][1]),
          'sname' => htmlspecialchars($data->sheets[0]['cells'][$i][2]),
          'addr' => htmlspecialchars($data->sheets[0]['cells'][$i][3]),
        ));
      }

      $Frm->_gui->informer->OK($s . "Добавлено " . $data->sheets[0]['numRows'] . " строк");
      page_reloadSubSec();
    }
  }
}

//// discip
function tp_discip_cmds($v, $d, $tbl) {
  global $n, $GUI;

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    return "<a href='?" . $tbl->_gui->Url() . "del=" . $d["id"] . "'>[удалить]</a>";
  } else {
    return 'Нет прав на удаление';
  }
}

function editdiscip_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');
    if (empty($id)) {
      $Frm->_gui->informer->ERR('Произошла ошибка. Попробуйте перезагрузить страницу');
      page_reloadAll();
    }

    $name = trim($Frm->GetNmValueH('name'));
    $napravl = $Frm->GetNmValue('napravl');
    if (empty($napravl) || empty($name)) {
      $Frm->_gui->informer->ERR('Заполните Название и выберите Направление');
      page_reloadAll();
    }

    if (db::get_single_value("SELECT COUNT(*) FROM " . TABLE_DISCIPLINE . " WHERE LOWER(name) = '" . db::input(strtolower($name)) . "' AND id != " . db::input($id))) {
      $Frm->_gui->informer->ERR('Дисциплина с таким именем уже существует');
      page_reloadAll();
    }

    Discipline::update($id, array(
      'code' => $Frm->GetNmValueI('code'),
      'name' => $name,
    ));

    Disciplines::addToNapravList($id, $napravl);

    $Frm->_gui->informer->OK("Сохранено");
    page_reloadAll();
  }
}

function adddiscip_exec($Frm, $Err) {
  if (!$Err) {
    $name = trim($Frm->GetNmValueH('name'));
    $napravl = $Frm->GetNmValue('napravl');
    if (empty($napravl) || empty($name)) {
      $Frm->_gui->informer->ERR('Заполните Название и выберите Направление');
      page_reloadAll();
    }

    if (Discipline::findOneBy(array(
      'name' => $name,
    ))) {
      $Frm->_gui->informer->ERR('Дисциплина с таким именем уже существует');
      page_reloadAll();
    }
    $id = Discipline::create(array(
      'code' => $Frm->GetNmValueI('code'),
      'name' => $name,
    ));

    Disciplines::addToNapravList($id, $napravl);

    if ($id) {
      $Frm->_gui->informer->OK("Добавлено");
    } else {
      $Frm->_gui->informer->ERR('Произошла ошибка. Запись не добавлена');
    }
    page_reloadSubSec();
  }
}

function deldiscip_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');

    if (Discipline::delete($id)) {
      $Frm->_gui->informer->OK("Удалено");
    } else {
      $Frm->_gui->informer->ERR('Произошла ошибка. Попробуйте еще раз');
    }

    page_reloadSubSec();
  }
}

function impdiscip_exec($Frm, $Err) {
  if (!$Err) {
    $v = $Frm->GetValue(0);
    if (!strpos($v["type"], "ms-excel")) {
      $Frm->_gui->informer->ERR("Неправильный тип файла");
      page_reloadSubSec();
    } else {

      $s = "";

      if ($Frm->GetValue(1)) {
        db::truncate(TABLE_DISCIPLINE);
        db::truncate(TABLE_DISCIPLINE_TO_NAPRAVL);
        db::truncate(TABLE_AUTHOR_TO_DISCIPLINE);
        $s = "Таблица очищена. ";
      }

      require_once(DIR_FS_DOCUMENT_ROOT . "/ext/PHPExcel/PHPExcel.php");

      $loader = PHPExcel_IOFactory::load($v['tmp_name']);
      $loader->setActiveSheetIndex(0);
      $sheet = $loader->getActiveSheet();

      $colNumber = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
      if ($colNumber < 1 or $colNumber > 2) {
        $Frm->_gui->informer->ERR("В таблице должно быть от 1 до 2 колонок(сейчас " . $colNumber . ")");
        page_reloadSubSec();
      }

      $rowIterator = $sheet->getRowIterator();
      $addcnt = 0;
      foreach ($rowIterator as $row) {
        // Получили ячейки текущей строки и обойдем их в цикле
        $cellIterator = $row->getCellIterator();
        foreach ($cellIterator as $cell) {
          $value = trim($cell->getValue());
          $color = $sheet->getStyle($cell->getCoordinate())->getFill()->getEndColor()->getRGB();
          if ($color == '000000') {
            $napravl = db::get_single_row("SELECT * FROM " . TABLE_NAPRAVL . " WHERE LOWER(name) = '" . db::input(strtolower($value)) . "'");

            if ($napravl) {
              $napravl_id = $napravl['id'];
            } else {
              $napravl_id = Napravl::create(array(
                'name' => $value,
              ));
            }
          } else {
            if (empty($napravl_id)) {
              continue 2;
            }
            $discipline = db::get_single_row("SELECT * FROM " . TABLE_DISCIPLINE . " WHERE LOWER(name) = '" . db::input(strtolower($value)) . "'");

            if ($discipline) {
              Disciplines::addToNaprav($discipline['id'], $napravl_id);
              continue 2;
            } else {
              $discipline_id = Discipline::create(array(
                'name' => $value,
              ));
              Disciplines::addToNaprav($discipline_id, $napravl_id);
              $addcnt++;
            }
          }
        }
      }

      $Frm->_gui->informer->OK($s . "Добавлено " . $addcnt . " строк");
      page_reloadSubSec();
    }
  }
}

function add_station_exec($Frm, $Err) {
  if (!$Err) {
    $name = $Frm->GetNmValueH('name');

    if (SubwayStation::findOneBy(array(
      'name' => $name,
    ))) {
      $Frm->_gui->informer->ERR("Запись существует");
      page_reloadAll();
    }

    SubwayStation::create(array(
      'name' => $name,
    ));

    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSubSec();
  }
}

function imp_station_exec($Frm, $Err) {
  if (!$Err) {
    $file = $Frm->GetValue(0);
    if (!strpos($file["type"], "ms-excel")) {
      $Frm->_gui->informer->ERR("Неправильный тип файла");
      page_reloadSubSec();
    } else {

      $trancate = "";
      if ($Frm->GetValue(1)) {
        db::truncate(TABLE_SUBWAY_STATIONS);
        $trancate = "Таблица очищена. ";
      }

      include_once "ext/Excel/reader.php";
      $data = new Spreadsheet_Excel_Reader($file["tmp_name"]);
      if ($data->sheets[0]['numCols'] != 1) {
        $Frm->_gui->informer->ERR("В таблице должна быть 1 колонка (" . $data->sheets[0]['numCols'] . ")");
        page_reloadSubSec();
        return;
      }

      $names = array();
      $addcnt = 0;
      for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
        $name = $data->sheets[0]['cells'][$i][1];
        if (!isset($names[$name])) {
          SubwayStation::create(array(
            'name' => $name,
          ));
          $names[$name] = true;
          $addcnt++;
        }
      }

      $Frm->_gui->informer->OK($trancate . "Добавлено " . $addcnt . " строк");
      page_reloadSubSec();
    }
  }
}

function edit_station_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');
    $station = SubwayStation::find($id);
    if ($station) {
      SubwayStation::update($id, array(
        'name' => $Frm->GetNmValueH('name'),
      ));
      $Frm->_gui->informer->OK("Сохранено");
      page_reloadAll();
    }
  }
}

function del_station_exec($Frm, $Err) {
  if (!$Err) {
    SubwayStation::delete($Frm->GetNmValueI('id'));
    $Frm->_gui->informer->OK("Удалено");
    page_reloadSubSec();
  }
}

function add_group_exec($Frm, $Err) {
  if (!$Err) {
    $name = $Frm->GetNmValueH('name');
    $sname = $Frm->GetNmValueH('sname');

    if (Role::findOneBy(array(
      'name' => $name,
      'sname' => $sname,
    ))) {
      $Frm->_gui->informer->ERR("Запись существует");
      page_reloadAll();
    }

    Role::create(array(
      'name' => $name,
      'sname' => $sname,
    ));

    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSubSec();
  }
}

function edit_group_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');
    $role = Role::find($id);

    $name = $Frm->GetNmValueH('name');
    $sname = $Frm->GetNmValueH('sname');
    if ($role) {
      Role::update($id, array(
        'name' => $name,
        'sname' => $sname,
      ));

      $Frm->_gui->informer->OK("Сохранено");
      page_reloadSubSec();
    }
  }
}

function del_group_exec($Frm, $Err) {
  if (!$Err) {
    $id = $Frm->GetNmValueI('id');
    Role::delete($id);

    $Frm->_gui->informer->OK("Удалено");
    page_reloadSubSec();
  }
}