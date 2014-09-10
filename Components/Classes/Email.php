<?php

namespace Components\Classes;

require_once(DIR_FS_EXTENSIONS . 'PHPMailer/libphpmailer.php');

class Email extends \PHPMailer {
  public function __construct() {
    parent::__construct(false);
    $this->IsSMTP();
    $this->Host = MAIL_HOST;
    $this->Username = MAIL_USER;
    $this->Password = MAIL_PASW;
    $this->SMTPAuth = true;
    if (MAIL_HOST_SSL) {
      $this->SMTPSecure = "ssl";
      $this->Port = MAIL_HOST_SSL;
    }
    $this->CharSet = "UTF-8";
   // $this->SMTPDebug = true;

    return $this;
  }

  public function setData(array $receiver, $subj, $body, $attachments = array(), $isHTML = false, $replyTo = array(), $from = array()) {
    $this->AddAddress($receiver['email'], $receiver['name']);
    if (!empty($from) && is_array($from)) {
      $this->From = $from['email'];
      $this->FromName = $from['name'];
    } else {
      $this->From = FIRM_EMAIL;
      $this->FromName = FIRM_NAME;
    }
    $this->Subject = $subj;
    if (!empty($replyTo)) {
      $this->AddReplyTo($replyTo['email'], $replyTo['name']);
    }
    if ($isHTML) {
      $this->MsgHTML($body);
    } else {
      $this->Body = $body;
    }

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