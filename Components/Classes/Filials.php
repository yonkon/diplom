<?php

namespace Components\Classes;

class Filials {
  public static function search($domain = null, $city = null) {
    if (!empty($domain)) {
      $filial_id = db::get_single_value("
        SELECT id
        FROM " . TBL_PREF . "data_filials
        WHERE `web` LIKE '%" . $domain . "%'
      ");
    }

    if (empty($filial_id) && !empty($city)) {
      $filial_id = db::get_single_value("
        SELECT f.filial_id
        FROM " . TBL_PREF . "data_city c
        JOIN " . TBL_PREF . "filial_to_city f ON f.city_id = c.id
        WHERE LOWER(c.name) = '" . strtolower($city) . "'
      ");
    }

    $filial_id = self::check($filial_id);

    return $filial_id;
  }

  public static function check($filial_id) {
    if (intval($filial_id) == 0) {
      $filial_id = db::get_single_value("SELECT id FROM " . TBL_PREF . "data_filials WHERE `default` = 1");
    } else {
      $filial = db::get_single_value("SELECT COUNT(*) FROM " . TBL_PREF . "data_filials WHERE id = " . db::input($filial_id));
      if (!$filial) {
        $filial_id = db::get_single_value("SELECT id FROM " . TBL_PREF . "data_filials WHERE `default` = 1");
      }
    }

    return $filial_id;
  }

  public static function getEmail($filial_id) {
    $email = db::get_single_value("SELECT email FROM " . TBL_PREF . "data_filials WHERE id = " . db::input($filial_id));
    if (empty($email) && defined('FIRM_EMAIL')) {
      return FIRM_EMAIL;
    } else {
      return $email;
    }
  }

  public static function getName($filial_id) {
    $name = db::get_single_value("SELECT name FROM " . TBL_PREF . "data_filials WHERE id = " . db::input($filial_id));
    if (empty($name) && defined('FIRM_NAME')) {
      return FIRM_NAME;
    } else {
      return $name;
    }
  }

  public static function getDefault() {
    return db::get_single_row("SELECT * FROM " . TABLE_FILIALS . " WHERE `default` = 1");
  }
}