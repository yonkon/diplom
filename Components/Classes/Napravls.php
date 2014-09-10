<?php

namespace Components\Classes;

use Components\Entity\Order;
use Components\Classes\Disciplines;

class Napravls {
  public static function update($napravl_id, $data) {
    if (empty($napravl_id) || empty($data) || !is_array($data) || empty($data['name'])) {
      return false;
    }

    if (self::isDefault($napravl_id)) {
      return false;
    }

    db::update(TABLE_NAPRAVL, $data, 'id = ' . $napravl_id);

    return true;
  }

  public static function delete($napravl_id) {
    if (empty($napravl_id)) {
      return false;
    }

    if (self::isDefault($napravl_id)) {
      return false;
    }

    db::delete(TABLE_NAPRAVL, 'id = ' . $napravl_id);

    self::makeOrdersDefault($napravl_id);
    self::makeDisciplinesDefault($napravl_id);
    self::makeAuthorsDefault($napravl_id);

    return true;
  }

  public static function isDefault($napravl_id) {
    if (db::get_single_value("SELECT name FROM " . TABLE_NAPRAVL . " WHERE id = " . db::input($napravl_id)) == 'Прочее') {
      return true;
    } else {
      return false;
    }
  }

  public static function getDefaultID() {
    return db::get_single_value("SELECT id FROM " . TABLE_NAPRAVL . " WHERE name = 'Прочее'");
  }

  public static function makeOrdersDefault($napravl_id) {
    foreach(db::get_single_values_array("SELECT id FROM " . TABLE_ORDERS . " WHERE napr_id = " . db::input($napravl_id)) as $order_id) {
      Order::update($order_id, array(
        'napr_id' => self::getDefaultID(),
      ));
    }
  }

  public static function makeDisciplinesDefault($napravl_id) {
    self::deleteAllDisciplines($napravl_id);
    foreach(self::getDisciplines($napravl_id) as $discipline_id) {
      Disciplines::addToNaprav($discipline_id, self::getDefaultID());
    }
  }

  public static function makeAuthorsDefault($napravl_id) {
    self::deleteAllAuthors($napravl_id);
    foreach(db::get_single_values_array("SELECT author_id FROM " . TABLE_AUTHOR_TO_NAPRAVL . " WHERE napravl_id = " . db::input($napravl_id)) as $author_id) {
      Author::addToNaprav($author_id, self::getDefaultID());
    }
  }

  public static function deleteAllAuthors($napravl_id) {
    db::delete(TABLE_AUTHOR_TO_NAPRAVL, 'napravl_id = ' . $napravl_id);
  }

  public static function deleteAllDisciplines($napravl_id) {
    db::delete(TABLE_DISCIPLINE_TO_NAPRAVL, 'napravl_id = ' . $napravl_id);
  }

  public static function exist($name) {
    if (db::get_single_value("SELECT COUNT(id) FROM " . TABLE_NAPRAVL . " WHERE name = '" . $name . "'")) {
      return true;
    } else {
      return false;
    }
  }

  public static function getAuthorsQt($napravl_id) {
    return db::get_single_value("SELECT COUNT(author_id) FROM " . TABLE_AUTHOR_TO_NAPRAVL . " WHERE napravl_id = " . db::input($napravl_id));
  }

  public static function getName($napravl_id) {
    return db::get_single_value("SELECT name FROM " . TABLE_NAPRAVL . " WHERE id = " . db::input($napravl_id));
  }

  public static function getDisciplines($napravl_id) {
    return db::get_single_values_array("SELECT discipline_id FROM " . TABLE_DISCIPLINE_TO_NAPRAVL . " WHERE napravl_id = " . db::input($napravl_id));
  }
}