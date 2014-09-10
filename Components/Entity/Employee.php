<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;
use Components\Classes\Filials;

class Employee extends EntityRepository {
  const TABLE = TABLE_USERS;

  public static function create(array $data) {
    if (empty($data) || !is_array($data) || empty($data['email']) || empty($data['password']) || self::exist($data['email'])) {
      return false;
    }

    $data['email'] = trim($data['email']);

    $default_parameters = array(
      'filial_id' => 0,
      'fio' => '',
      'hpwd' => md5($data['password'] . strtolower($data['email'])),
      'telnum' => '',
      'cont' => '',
      'group_id' => 0,
      'comments' => '',
      'payment_requisites' => '',
    );

    $data = array_merge($default_parameters, $data);

    $data['filial_id'] = Filials::check($data['filial_id']);

    return parent::create($data);
  }

  /**
   * @param $email
   *
   * @return bool
   */
  public static function exist($email) {
    return (bool)count(self::findBy(array(
      'email' => strtolower(trim($email)),
    )));
  }


  /**
   * @param $employer_id
   *
   * @return bool
   */
  public static function isBlocked($employer_id) {
    $employer = self::find($employer_id);
    if ($employer["blocked"] || $employer["black_list"]) {
      return true;
    } else {
      return false;
    }
  }
}

class EmployeeBlack extends Employee {
  const TABLE = TABLE_USERS_BLACK;
}
