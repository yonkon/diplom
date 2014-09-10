<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;
use Components\Classes\Disciplines;

class Discipline extends EntityRepository {
  const TABLE = TABLE_DISCIPLINE;

  public static function create(array $data) {
    if ($discipline = parent::findOneBy(array(
      'name' => trim($data['name']),
    ))) {
      return $discipline['id'];
    } else {
      return parent::create($data);
    }
  }

  public static function delete($discipline_id) {
    if (empty($discipline_id)) {
      return false;
    }

    Disciplines::deleteAllFromNaprav($discipline_id);

    return parent::delete($discipline_id);
  }
}