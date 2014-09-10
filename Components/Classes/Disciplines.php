<?php

namespace Components\Classes;

use Components\Classes\Napravls;

class Disciplines {
  public static function addToNaprav($disc_id, $naprav_id) {
    db::replace(TABLE_DISCIPLINE_TO_NAPRAVL, array(
      'napravl_id' => $naprav_id,
      'discipline_id' => $disc_id,
    ));
  }

  public static function addToDefaultNaprav($discipline_id) {
    db::replace(TABLE_DISCIPLINE_TO_NAPRAVL, array(
      'napravl_id' => Napravls::getDefaultID(),
      'discipline_id' => $discipline_id,
    ));
  }

  public static function addToNapravList($disc_id, $naprav_ids) {
    if (!is_array($naprav_ids) || empty($naprav_ids)) {
      return false;
    }

    self::deleteAllFromNaprav($disc_id);

    foreach($naprav_ids as $naprav_id) {
      db::insert(TABLE_DISCIPLINE_TO_NAPRAVL, array(
        'napravl_id' => $naprav_id,
        'discipline_id' => $disc_id,
      ));
    }

    return true;
  }

  public static function deleteAllFromNaprav($disc_id) {
    db::delete(TABLE_DISCIPLINE_TO_NAPRAVL, 'discipline_id = ' . $disc_id);
  }

  public static function get_napravl_list_as_string($discipline_id) {
    return db::get_single_values_string("
      SELECT n.name
      FROM " . TABLE_NAPRAVL . " n
      JOIN " . TABLE_DISCIPLINE_TO_NAPRAVL . " d ON d.napravl_id = n.id
      WHERE d.discipline_id = " . db::input($discipline_id) . "
    ");
  }

  public static function getNapravListAsArray($discipline_id) {
    return db::get_single_values_array("
      SELECT n.id
      FROM " . TABLE_NAPRAVL . " n
      JOIN " . TABLE_DISCIPLINE_TO_NAPRAVL . " d ON d.napravl_id = n.id
      WHERE d.discipline_id = " . db::input($discipline_id) . "
    ");
  }

  public static function getAuthorsQt($discipline_id) {
    return db::get_single_value("SELECT COUNT(author_id) FROM " . TABLE_USERS . " u
      JOIN " . TABLE_AUTHOR_TO_DISCIPLINE . " atd ON atd.author_id = u.id
      WHERE
        u.group_id in ( " . Author::ROLE_ID . " ) AND
        u.blocked = 0 AND
        u.black_list = 0 AND
        discipline_id = " . db::input($discipline_id));
  }

  public static function getName($discipline_id) {
    return db::get_single_value("SELECT name FROM " . TABLE_DISCIPLINE . " WHERE id = " . db::input($discipline_id));
  }

  public static function getAuthors($discipline_id) {
    return db::get_assoc("
      SELECT u.id, u.fio
      FROM " . TABLE_USERS . " u
      JOIN " . TABLE_AUTHOR_TO_DISCIPLINE . " atd ON atd.author_id = u.id
      WHERE
        u.group_id in ( " . Author::ROLE_ID . " ) AND
        u.blocked = 0 AND
        u.black_list = 0 AND
        atd.discipline_id = " . db::input($discipline_id));
  }
}