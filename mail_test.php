<?php
require_once('includes/application_top.php');

include_once(DIR_FS_EXTENSIONS . "PHPMailer/libphpmailer.php");

$m = new PHPMailer();
$m->IsSMTP();
$m->Host = MAIL_HOST;
$m->Username = MAIL_USER;
$m->Password = MAIL_PASW;
$m->SMTPAuth = true;
if (MAIL_HOST_SSL)
{
  $m->SMTPSecure = "ssl";
  $m->Port = MAIL_HOST_SSL;
}

$m->CharSet = "UTF-8";
$m->AddAddress('pixelpwnz@gmail.com', 'Tsetu');
$m->From = FIRM_EMAIL;
$m->FromName = FIRM_NAME;
$m->Subject = "Регистрация на сайте " . FIRM_NAME;

$m->MsgHTML('Hello');

$m->SMTPDebug = true;

var_dump($m);

if (!$m->Send()) die("Fail:" . $m->ErrorInfo);

echo 'sent';