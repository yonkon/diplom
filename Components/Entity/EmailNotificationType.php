<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;
use Components\Classes\db;

class EmailNotificationType extends EntityRepository {
  const TO_AUTHOR_ON_REMIND_NORMAL                    = 1;
  const TO_AUTHOR_ON_REMIND_URGENT                    = 2;
  const TO_RECEIVER_ON_MESSAGE_COMMON                 = 3;
  const TO_MANAGER_ON_FIRST_ASSIGN                    = 4;
  const TO_SUBSCRIBED_AUTHORS_ON_DISTRIBUTION         = 5;
  const TO_MANAGERS_ON_MANAGER_CHANGE                 = 6;
  const TO_AUTHORS_ON_AUTHOR_CHANGE                   = 7;
  const TO_AUTHOR_ON_UNASSIGN                         = 8;
  const TO_AUTHOR_ON_ASSIGN                           = 9;
  const TO_MANAGER_ON_CLIENT_CREATED_ORDER            = 10;
  const TO_AUTHOR_ON_ORDER_CHANGE                     = 11;
  const TO_MANAGER_ON_ORDER_CHANGE                    = 12;
  const TO_CLIENT_ON_ORDER_CHANGE                     = 13;

  const TABLE = TABLE_EMAIL_NOTIFICATION_TYPES;

  public static $NOTIFICATION_TYPES = array(1,2,3,4,5,6,7,8,9,10,11,12,13);

  public static $_checkDbChanges = true;
  public static $_persistableIds = array();
  public static $_persistable = array();
  public static $_sendableIds = array();
  public static $_sendable = array();


  /**
   * @param bool $checkChanges Check for changes in DB on every call of getter functions
   * @return bool If argument is valid
   */
  public static function setCheckDbChanges($checkChanges) {
    if (is_bool($checkChanges)) {
      EmailNotificationType::$_checkDbChanges = $checkChanges;
      return true;
    }
    return false;
  }

  public static function isValidType($type) {
    return in_array($type, EmailNotificationType::$NOTIFICATION_TYPES);
  }

  public static function getPersistableIds($checkDb = null) {
    if ($checkDb == null) { $checkDb = EmailNotificationType::$_checkDbChanges; }
    if ($checkDb || empty(EmailNotificationType::$_persistableIds)) {
      $ids = array();
      EmailNotificationType::getPersistable($checkDb);
      foreach (EmailNotificationType::$_persistable as $type) {
        $ids[] =  $type['id'];
      }
      EmailNotificationType::$_persistableIds = $ids;
    }
    return EmailNotificationType::$_persistableIds;
  }

  public static function getPersistable($checkDb = null) {
    if ($checkDb == null) { $checkDb = EmailNotificationType::$_checkDbChanges; }
    if ($checkDb || empty(EmailNotificationType::$_persistable)) {
      //EmailNotificationType::$_persistable = EmailNotificationType::findBy(array('persist'=>1));
	  EmailNotificationType::$_persistable = db::get_single_values_array("SELECT id FROM " . TABLE_EMAIL_NOTIFICATION_TYPES . " WHERE action_type in (1,2)");
      //EmailNotificationType::$_persistable = EmailNotificationType::findBy(array('action_type'=>array(0,1,2)));
    }
    return EmailNotificationType::$_persistable;
  }

  public static function getSendableIds($checkDb = null) {
    if ($checkDb == null) { $checkDb = EmailNotificationType::$_checkDbChanges; }
    if( $checkDb || empty(EmailNotificationType::$_sendableIds)) {
      $ids = array();
      EmailNotificationType::getSendable($checkDb);
      foreach (EmailNotificationType::$_sendable as $type) {
        $ids[] =  $type['id'];
      }
      EmailNotificationType::$_sendableIds = $ids;
    }
    return EmailNotificationType::$_sendableIds;
  }

  public static function getSendable($checkDb = null) {
    if ($checkDb == null) { $checkDb = EmailNotificationType::$_checkDbChanges; }
    if ($checkDb || empty(EmailNotificationType::$_sendable)) {
      EmailNotificationType::$_sendable = EmailNotificationType::findBy(array('action_type'=>2));
    }
    return EmailNotificationType::$_sendable;
  }

  public static function isSendable($type_id) {
    if (EmailNotificationType::isValidType($type_id)) {
      if (in_array($type_id, EmailNotificationType::getSendableIds()) ) {
        return true;
      }
    }
    return false;
  }

  public static function isPersistable($type_id) {
    if (EmailNotificationType::isValidType($type_id)) {
      if (in_array($type_id, EmailNotificationType::getPersistableIds() ) ) {
        return true;
      }
    }
    return false;
  }

}