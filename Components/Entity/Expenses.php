<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;

class Expenses extends EntityRepository {
  const TABLE = TABLE_EXPENSES;

  public static function create(array $data) {
    if (empty($data) || !is_array($data)) {
      return false;
    }

    if (empty($data['date'])) {
      $data['date'] = date('Y-m-d H:i:s');
    } else {
      $data['date'] = date('Y-m-d H:i:s', $data['date']);
    }

    return parent::create($data);
  }

  public static function update($id, array $data) {
    if (empty($data['date'])) {
      $data['date'] = date('Y-m-d H:i:s');
    } else {
      $data['date'] = date('Y-m-d H:i:s', $data['date']);
    }

    return parent::update($id, $data);
  }
}