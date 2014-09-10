<?php

use Components\Classes\db;
use Components\Entity\Filial;

function tp_getusername($value, $row, $table, &$info) {
  global $data_users;
  return "<a href='?section=sotr&light=" . $value . "'>" . $data_users[$value]["fio"] . "</a>";
}

function tp_getopenclose($value, $row, $table, &$info) {
  return utils_cvt_i2times($row["tm_open"]) . " - " . utils_cvt_i2times($row["tm_close"]);
}

function addfilial_exec($Frm, $Err) {
  if (!$Err) {
    $name = $Frm->GetNmValueH('name');
    $manager = $Frm->GetNmValueI('manager');
    $url = $Frm->GetNmValueH('url');
    $description = $Frm->GetNmValueH('description');
    $notes = $Frm->GetNmValueH('notes');
    $email = $Frm->GetNmValueH('email');

    if (Filial::findOneBy(array(
      'name' => $name,
    ))) {
      $Frm->_gui->ERR("Филиал с таким названием уже существует");
      return;
    }

    $tm_open = $Frm->GetNmValueH('time_from');
    $tm_close = $Frm->GetNmValueH('time_to');

    if ($tm_close < $tm_open) {
      $Frm->_gui->ERR("Время закрытия не должно быть меньше времени открытия");
      return;
    }

    $filial_id = Filial::create(array(
      "user_id" => $manager,
      "name" => $name,
      "web" => $url,
      "about" => $description,
      "rem" => $notes,
      "tm_open" => $tm_open,
      "tm_close" => $tm_close,
      "tm_special" => '',
      'default' => $Frm->GetNmValueI('default'),
      'profit' => ($Frm->GetNmValueI('profit') / 100),
      'consumption' => ($Frm->GetNmValueI('consumption') / 100),
      'email' => $email,
      'order_form_path' => $Frm->GetNmValueH('order_form_path'),
    ));

    $cities = explode('_', $Frm->GetNmValue('city'));
    add_city_to_filial($filial_id, $cities);

    $Frm->_gui->informer->OK("Добавлено");
    page_reloadSec();
  }
}

function editfilial_exec($Frm, $Err) {
  global $data_filials;
  if (!$Err) {
    $id = $Frm->GetValueI(0);

    $tm_open = $Frm->GetNmValueH('time_from');
    $tm_close = $Frm->GetNmValueH('time_to');
    if ($tm_close < $tm_open) {
      $Frm->_gui->ERR("Время закрытия не должно быть меньше времени открытия");
      return;
    }

    $is_default = $Frm->GetNmValueI('default');

    if ($is_default) {
      foreach ($data_filials as $fil) {
        if ($fil['default'] == 1) {
          Filial::update($fil['id'], array(
            'default' => 0,
          ));
        }
      }
    }

    Filial::update($id, array(
      "user_id" => $Frm->GetNmValueI('manager'),
      "name" => $Frm->GetNmValueH('name'),
      "web" => $Frm->GetNmValueH('url'),
      "about" => $Frm->GetNmValueH('description'),
      "rem" => $Frm->GetNmValueH('notes'),
      "tm_open" => $tm_open,
      "tm_close" => $tm_close,
      'default' => $is_default,
      'profit' => ($Frm->GetNmValueI('profit') / 100),
      'consumption' => ($Frm->GetNmValueI('consumption') / 100),
      'email' => $Frm->GetNmValueH('email'),
      'order_form_path' => $Frm->GetNmValueH('order_form_path'),
    ));

    $cities = explode('_', $Frm->GetNmValue('city'));
    add_city_to_filial($id, $cities);

    $Frm->_gui->informer->OK("Сохранено");
    page_reloadAll();
  } else {
  }
}

function delfilial_exec($Frm, $Err) {
  if (!$Err) {
    $id = intval($Frm->GetValue(0));
    if (Filial::find($id)) {
      Filial::delete($id);
      delete_city_to_filial($id);
      $Frm->_gui->informer->OK("Удалено");
      page_reloadSec();
    }
  }
}

function city_modal($hidden_field_id, $filial_id = null) {
  global $GUI, $filial_module_root, $data_city;
  need_data('data_city');

  $GUI->tmpls[] = $filial_module_root . "city.tmpl.php";

  $frm = $GUI->ModalFormEx("Города филиала", 300, 280);
  $frm->Nosubmit = true;
  $GUI->Vars["city_modal_form"] = $frm;

  if (empty($filial_id)) {
    $exlude_cities = db::get_single_values_array("SELECT DISTINCT city_id FROM " . TBL_PREF . "filial_to_city");
  } else {
    $exlude_cities = db::get_single_values_array("SELECT DISTINCT city_id FROM " . TBL_PREF . "filial_to_city WHERE filial_id != " . $filial_id);
  }

  $ypos = 0;
  $cities = array();
  foreach ($data_city as $city) {
    if (in_array($city['id'], $exlude_cities)) {
      continue;
    }
    $cities[$city['id']] = $city['name'];
  }

  ksort($cities);
  $s = $frm->Select(10, $ypos += 20, 280, $cities, '', 0);
  $s->Multiple = true;
  $s->RowSize = 10;
  $s->linkName = 'cities';
  $s->name = 'cities[]';

  $b = $frm->Button("Выбрать", 50, $ypos += 190, 80);
  $b->Event = "check_cities('" . $hidden_field_id . "', '" . $frm->idname . "');";

  $b = $frm->Button("Отмена", 150, $ypos, 80);
  $b->Event = 'jQuery.modal.close();';
}

function add_city_to_filial($filial_id, $cities) {
  delete_city_to_filial($filial_id);

  foreach ($cities as $city_id) {
    if (empty($city_id)) {
      continue;
    }
    db::insert("filial_to_city", array(
      'filial_id' => $filial_id,
      'city_id' => $city_id,
    ));
  }
}

function delete_city_to_filial($filial_id) {
  db::delete('filial_to_city', 'filial_id = ' . db::input($filial_id));
}