<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;
use Components\Classes\db;

class Order extends EntityRepository {
  const TABLE = TABLE_ORDERS;
  const NAPR_ID_UNKNOWN = 94;

  public static function create(array $data) {
    if (empty($data)) {
      return false;
    }

    $oform = serialize(array(14, 14, 0, 0, 20, 20, 30, 15, 0));
    $default_parameters = array(
      'filial_id' => 0,
      'created' => time(),
      'creator_id' => 0,
      'manager_id' => 0,
      'author_id' => 0,
      'klient_id' => 0,
      'parent_id' => 0,
      'vuz_id' => 0,
      'vuz_user' => '',
      'type_id' => 0,
      'type_user' => '',
      'napr_id' => Order::NAPR_ID_UNKNOWN,
      'disc_id' => 0,
      'disc_user' => '',
      'time_kln' => 0,
      'time_kln_r' => 0,
      'time_auth' => 0,
      'time_auth_r' => 0,
      'cost_kln' => 0,
      'cost_auth' => 0,
      'oplata_kln' => 0,
      'oplata_auth' => 0,
      'payment_id' => 0,
      'raspred_srok' => 0,
      'raspred_auth' => 0,
      'subject' => '',
      'about_kln' => '',
      'about_mng' => '',
      'kurs' => 0,
      'prakt_pc' => 0,
      'pages_min' => 0,
      'pages_max' => 0,
      'src_min' => 0,
      'src_max' => 0,
      'from_id' => 0,
      'oform' => $oform,
      'next_rel_date' => 0,
      'status_id' => 0,
      'ok_comment' => '',
      'ok_comment_date' => 0,
      'payment_comment' => '',
    );

    $data = array_merge($default_parameters, $data);

    return parent::create($data);
  }
}