<?php

namespace Components\Classes;

require_once(DIR_FS_EXTENSIONS . 'PHPMailer'.DIRECTORY_SEPARATOR.'PHPMailerAutoload.php');

class Email extends \PHPMailer {
  public function __construct() {
    parent::__construct(false);
    $this->IsSMTP();
//    $this->IsSendmail();

    $this->Host = MAIL_HOST;
    $this->Username = MAIL_USER;
    $this->Password = MAIL_PASW;
    $this->Sender = MAIL_USER;
    $this->SMTPAuth = true;
    if (MAIL_HOST_SSL) {
      $this->SMTPSecure = "ssl";
      $this->Port = MAIL_HOST_SSL;
    }
    $this->CharSet = "UTF-8";
//    $this->SMTPDebug = 2;

    return $this;
  }

  public function setData(array $receiver, $subj, $body, $attachments = array(), $isHTML = false, $replyTo = array(), $from = array()) {
    $this->AddAddress($receiver['email'], $receiver['name']);
    if (strpos($receiver['email'], '@gmail.com') !== false) {
      $from['name'] = FIRM_NAME;
    }
    if (!empty($from) && is_array($from) && !empty($from['email']) ) {
      $this->From = MAIL_USER;
      $this->FromName = $from['email'];
    } else {
      $this->From = MAIL_USER;
      $this->FromName = FIRM_NAME;
    }
    $this->Subject = $subj;
    if (!empty($replyTo) && !empty($replyTo['email']) ) {
      $this->AddReplyTo($replyTo['email'], $replyTo['name']);
    } else {
      $this->AddReplyTo('5_s_plusom@mail.ru', '');
    }

    if ($isHTML) {
      $this->MsgHTML($body);
    } else {
      $this->Body = $body;
    }
    if(is_array($attachments))
    foreach($attachments as $file) {
      $this->addAttachment($file['path'], $file['name']);
    }
  }

  public function send() {
    parent::Send();

    if ($this->IsError()) {
      ErrorLogger::add('email', 'Email sending failed', $this->ErrorInfo);
      return false;
    }
    return true;
  }
}
