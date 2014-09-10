<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;

class Message extends EntityRepository {
  const TABLE = TABLE_MESSAGES;


  public static function getReceiverEmailAndName ($receiver_id) {
    if(strlen($receiver_id) <2 )
      return false;
    $receiver_type = substr($receiver_id, 0, 1);
    $id = substr($receiver_id, 1);
    $emailAndName = array('email'=>'', 'name'=>'');
    switch($receiver_type) {
      case 'u':
        need_data('data_users');
        global $data_users;
        if (!empty ($data_users[$id]['email'])) {
          $emailAndName['email'] =  $data_users[$id]['email'];
        }
        if (!empty ($data_users[$id]['fio'])) {
          $emailAndName['name'] =  $data_users[$id]['fio'];
        }
        break;
      case 'k':
        $client = Client::find($id);
        if (!empty ($client['email']) ) {
          $emailAndName['email'] = $client['email'];
        }
        if (!empty ($client['fio']) ) {
          $emailAndName['name'] = $client['fio'];
        }
        break;
    }
    return $emailAndName;
  }

}