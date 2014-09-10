<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;

class EmailNotification extends EntityRepository {
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
  public static $NOTIFICATION_TYPES = array(1,2,3,4,5,6,7,8,9,10,11,12,13);

  const TABLE = TABLE_EMAIL_NOTIFICATIONS;
}