<?php

namespace Components\Classes;

use Components\Entity\EmailNotification;
use Components\Entity\EmailNotificationType;
use Components\Entity\Employee;
use Components\Entity\Filial;
use Components\Entity\Message;
use Components\Exceptions\EntityNotFoundException;
use Components\Entity\Order;
use Components\Exceptions\Exception;
use Components\Entity\OrderFile;
use Components\Classes\Disciplines;

class Author {
  const ROLE_ID = 6;

  public static function add_napravl($author_id, $napravls) {
    if (!is_array($napravls)) {
      return false;
    }
    self::delete_napravl_all($author_id);

    foreach ($napravls as $napravl_id) {
      if (empty($napravl_id)) {
        continue;
      }
      db::insert(TABLE_AUTHOR_TO_NAPRAVL, array(
        'author_id' => $author_id,
        'napravl_id' => $napravl_id,
      ));
    }

    return true;
  }

  public static function delete_napravl($author_id, $napravl_id) {
    return db::delete(TABLE_AUTHOR_TO_NAPRAVL, 'author_id = ' . db::input($author_id) . ' AND napravl_id = ' . db::input($napravl_id));
  }

  public static function delete_napravl_all($author_id) {
    db::delete(TABLE_AUTHOR_TO_NAPRAVL, 'author_id = ' . db::input($author_id));
  }

  public static function get_napravl($author_id) {
    return db::get_single_values_array("SELECT napravl_id FROM " . TABLE_AUTHOR_TO_NAPRAVL . " WHERE author_id = " . db::input($author_id));
  }

  public static function addToNaprav($author_id, $napravl_id) {
    db::replace(TABLE_AUTHOR_TO_NAPRAVL, array(
      'napravl_id' => $napravl_id,
      'author_id' => $author_id,
    ));
  }

  public static function getDisciplines($author_id) {
    return db::get_single_values_array("SELECT discipline_id FROM " . TABLE_AUTHOR_TO_DISCIPLINE . " WHERE author_id = " . db::input($author_id));
  }

  public static function addDisciplines($author_id, $disciplines_ids) {
    self::deleteDisciplines($author_id);

    foreach($disciplines_ids as $discipline_id) {
      if (empty($discipline_id)) {
        continue;
      }
      db::insert(TABLE_AUTHOR_TO_DISCIPLINE, array(
        'author_id' => $author_id,
        'discipline_id' => $discipline_id,
      ));

      $napravls_ids = Disciplines::getNapravListAsArray($discipline_id);
      foreach($napravls_ids as $napravl_id) {
        db::replace(TABLE_AUTHOR_TO_NAPRAVL, array(
          'author_id' => $author_id,
          'napravl_id' => $napravl_id,
        ));
      }
    }
  }

  public static function deleteDisciplines($author_id) {
    db::delete(TABLE_AUTHOR_TO_DISCIPLINE, 'author_id = ' . db::input($author_id));
  }

  public static function sendEmail($order_id, array $authors_ids, $subject, $body, $needAttachments = false, $isHTML = false) {
    static $order, $manager, $filial, $attachments;

    if (!is_array($authors_ids)) {
      return false;
    }

    if (empty($order)) {
      try {
        $order = Order::find($order_id);
      } catch(Exception $e) {
        return false;
      }
    }

    if (empty($manager)) {
      try {
        $manager = Employee::find($order['manager_id']);
      } catch(Exception $e) {
        return false;
      }
    }
    $replyTo = array(
      'email' => $manager['email'],
      'name' => $manager['fio'],
    );

    if (empty($filial)) {
      try {
        $filial = Filial::find($order['filial_id']);
      } catch(Exception $e) {
        return false;
      }
    }
    $from = array(
      'email' => $filial['email'],
      'name' => $filial['name'],
    );


    if ($needAttachments && !is_array($attachments)) {
      $attachments = OrderFile::findBy(array(
        'order_id' => $order_id,
      ));

      if (count($attachments)) {
        foreach($attachments as &$file) {
          $file['path'] = get_file_path($order_id, $file);
        }
        unset($file);
      }
    } else {
      $attachments = is_array($attachments) ? $attachments : array();
    }

    $result = array();
    foreach($authors_ids as $id) {
      if (is_numeric($id)) {
        try {
          $author = Employee::find($id);
        } catch(Exception $e) {
          continue;
        }

        $receiver = array(
          'email' => $author['email'],
          'name' => $author['fio'],
        );

        $email = new Email();
        $email->setData($receiver, $subject, $body, $attachments, $isHTML, $replyTo, $from);
        if (!$email->send()) {
          $result[] = $receiver;
        }
      } else {
        $ids = explode(', ', $id);
        $temp_result = self::sendEmail($order_id, $ids, $subject, $body, $needAttachments, $isHTML);
        if (count($temp_result)) {
          array_push($result, $temp_result);
        }
      }
    }
    return $result;
  }

  /**
   * @param int $order_id Номер заказа
   * @param array $authors_ids id получателей
   * @param string $sender_id id отправителя с префиксом клиент/сотрудник (н-р "u330")
   * @param string $subject тема сообщения
   * @param string $body текст сообщения
   * @param int $notification_type тип сообщения, из набора EmailNotificationType::$NOTIFICATION_TYPES
   * @see EmailNotificationType
   * @return array|bool Добавляет внутреннее сообщение и ставит его в очередь рассылки по cron<br/>Массив ассоциативных массивов с ключами 'name' и 'email' тех, для кого сообщение не было создано или не поставлено в очередь рассылки | false в случае ошибки
   */
  public static function saveMessageAndEnqueueEmail($order_id, array $authors_ids, $sender_id, $subject, $body, $notification_type) {
    static $order, $manager, $filial, $attachments;

    // Если не надо пихать в ядро - выходим типа все ок
    if(!EmailNotificationType::isPersistable($notification_type))
    {
      return array();
    }

    if (!is_array($authors_ids)) {
      if (is_numeric($authors_ids)) {
        $authors_ids = array($authors_ids);
      } else {
        return false;
      }
    }

    if (empty($order)) {
      try {
        $order = Order::find($order_id);
      } catch(Exception $e) {
        return false;
      }
    }

    if (empty($manager)) {
      try {
        $manager = Employee::find($order['manager_id']);
      } catch(Exception $e) {
        return false;
      }
    }
    $replyTo = array(
      'email' => $manager['email'],
      'name' => $manager['fio'],
    );

    $result = array();
    foreach($authors_ids as $id) {
      if (is_numeric($id)) {
        try {
          $author = Employee::find($id);
        } catch(Exception $e) {
          continue;
        }

        $receiver = array(
          'email' => $author['email'],
          'name' => $author['fio'],
        );

        //Сохраняем уведомление
        $message_id = Message::create(array(
          'parent_id'     =>  0,
          'order_id'      =>  $order_id,
          'klient_id'     =>  $order['klient_id'],
          'visit_id'      =>  0,
          'tender_id'     =>  0,
          'created'       =>  time(),
          'creator_id'    =>  $sender_id,
          'addr'          =>  'u'.$id,
          'subject'       =>  $subject,
          'text'          =>  $body,
          'prior'         =>  1,
          'uvedom'        =>  1,
          'readed'        =>  0,
          'needansv'      =>  0,
          'basket'        =>  0,
        ));

        if (!empty ($message_id)) {
          //Ставим в очередь на отправку по cron
          $email_notification = enqueue_message_to_email($message_id, $author['email'], $notification_type);
        }
        if (empty ($email_notification)) {
          $result[] = $receiver;
        }
      } else {
        $ids = explode(', ', $id);
        $temp_result = self::saveMessageAndEnqueueEmail($order_id, $ids, $sender_id, $subject, $body, $notification_type);
        if (count($temp_result)) {
          array_push($result, $temp_result);
        }
      }
    }
    return $result;
  }

  public static function enqueue_message_to_email($message_id, $authors_ids, $notification_type = EmailNotification::TO_AUTHOR_ON_ASSIGN) {
    if (empty ($message_id) || !is_numeric($message_id))
      return false;
    assert( in_array($notification_type, EmailNotification::$NOTIFICATION_TYPES) );
    // Если не надо уведомлять - выходим типа все ок
    if(!EmailNotificationType::isSendable($notification_type))
    {
      return array();
    }
    $result = array();
    if(!is_array($authors_ids) && is_numeric($authors_ids)) {
      $authors_ids = array($authors_ids);
    }
    foreach($authors_ids as $id) {
      if (is_numeric($id)) {
        try {
          $author = Employee::find($id);
        } catch(Exception $e) {
          $result['error'][] = $id;
          continue;
        }
        $notification_id = EmailNotification::create(array(
          'message_id' => $message_id,
          'receiver_email' => $author['email'],
          'type' => $notification_type
        ));
        if ($notification_id) {
          $result['success'][] = $id;
        }
      } else {
        $ids = explode(', ', $id);
        $temp_result = self::enqueue_message_to_email($message_id, $ids, $notification_type);
        if (count($temp_result['success'])) {
          array_push($result['success'], $temp_result['success']);
        }
        if (count($temp_result['error'])) {
          array_push($result['error'], $temp_result['error']);
        }
      }
    }
    return $result;
  }


  public static function getActiveAuthorsId_Fio() {
    $activeAuthorsId_Fio = db::get_assoc('SELECT id, fio
     FROM ' . TABLE_USERS . '
     WHERE group_id=' . Author::ROLE_ID . ' AND
      blocked = 0 AND
      black_list = 0'
    );
    if (is_array($activeAuthorsId_Fio)) {
      asort($activeAuthorsId_Fio);
    } else {
      $activeAuthorsId_Fio = array();
    }
    return $activeAuthorsId_Fio;
  }


  }


