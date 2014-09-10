<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;
use Components\Classes\Napravls;

class Napravl extends EntityRepository {
  const TABLE = TABLE_NAPRAVL;

  public static function update($napravl_id, array $data) {
    if (empty($napravl_id) || empty($data) || !is_array($data) || empty($data['name'])) {
      return false;
    }

    if (Napravls::isDefault($napravl_id)) {
      return false;
    }

    return parent::update($napravl_id, $data);
  }

  public static function delete($napravl_id) {
    if (empty($napravl_id)) {
      return false;
    }

    if (Napravls::isDefault($napravl_id)) {
      return false;
    }

    parent::delete($napravl_id);

    Napravls::makeOrdersDefault($napravl_id);
    Napravls::makeDisciplinesDefault($napravl_id);
    Napravls::makeAuthorsDefault($napravl_id);

    return true;
  }
}