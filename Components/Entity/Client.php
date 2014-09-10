<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;
use Components\Classes\Filials;

class Client extends EntityRepository {
  const TABLE = TABLE_CLIENTS;

  public static function create(array $data) {
    if (empty($data) || !is_array($data) || empty($data['email'])) {
      return false;
    }

    if (!empty($data['password'])) {
      $password = $data['password'];
    } else {
      $password = generate_pasw(5);
    }

    $default_parameters = array(
      'filial_id' => 0,
      'password' => $password,
      'hpwd' => md5($password . strtolower($data['email'])),
      'fio' => '',
      'liketel' => 0,
      'teltime' => '',
      'icq' => '',
      'skype' => '',
      'contacts' => '',
      'regdate' => time(),
      'blocked' => 0,
      'about' => '',
      'ocenka' => 0,
      'ref_id' => 0,
      'from_id' => 0,
      'added_by' => 0,
      'orderform' => 0,
      'referrer_code' => uniqid(),
    );

    $data = array_merge($default_parameters, $data);
    if (self::exist($data['email'])) {
      return false;
    }

    $data['filial_id'] = Filials::check($data['filial_id']);

    return parent::create($data);
  }

  public static function exist($email) {
    return (bool)count(self::findBy(array(
      'email' => strtolower(trim($email)),
    )));
  }
}