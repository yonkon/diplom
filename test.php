<?php

use Components\Classes\Email;

require_once('includes/application_top.php');

include_once(DIR_FS_EXTENSIONS . "PHPMailer/libphpmailer.php");

$m = new Email();
$m->setData(array(
  'email' => 'pixelpwnz@gmail.com',
  'name' => 'test',
), 'test', 'qq');

$res = $m->send();

obj_dump($res);
die;