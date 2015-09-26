<?php
/**
 * Created by PhpStorm.
 * User: shikon
 * Date: 21.12.14
 * Time: 19:58
 */
use \Components\Classes\db;
use \Components\Entity\Message;
require_once('includes/application_top.php');


//mail("yonkon.ru@gmail.com", 'Test', 'Test');
$email = new \Components\Classes\Email();
$email->IsHTML(true);
$reply_to = array();// Message::getReceiverEmailAndName('u2');
//if (empty($reply_to['email'])) {
//  $reply_to['email'] = 'admin@sessia-online.ru';
//}
$email->setData(
  array(
    'email' => 'yonkon.ru@gmail.com',
  ),
  'SPAM',
  "Чё вы не пропускаете мои письма, блин >_<!!",
  null,
  false,
  $reply_to,
  $reply_to
);
try {
  $send_result = $email->send();
  if ( $send_result )
  {
    echo 'SEND OK';
  } else {
    echo 'SEND ERROR';
  }
}
catch (\Components\Exceptions\Exception $e)
{
  echo 'SEND exception \n';
  echo $e->getMessage();
}

unset($email);