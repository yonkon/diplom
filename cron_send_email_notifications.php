<?php

  use \Components\Classes\db;
  use \Components\Entity\Message;
  require_once('includes/application_top.php');

  define('EMAIL_NOTIFICATION_LIMIT', 50);
  define('MAX_EXEC_TIME', 58);
  define('DEF_EXEC_TIME', 20);

function cron_sendEmailNotifications() {
  $execution_time = ini_get('max_execution_time');
  if (empty ($execution_time))
  {
    $execution_time = DEF_EXEC_TIME;
  }
  else
  {
  	if ($execution_time > (MAX_EXEC_TIME )) $execution_time = MAX_EXEC_TIME;
  }
	
  $execution_time_start = time();
  $execution_time_end = $execution_time_start + $execution_time;

  $notifications = \Components\Entity\EmailNotification::findBy(
    array(),
    array('attempts_to_send' => 'ASC'),
    EMAIL_NOTIFICATION_LIMIT,
    0
  );



  $notification_index = 0;
  $good_cnt = 0;
  $notification_count = count($notifications);

  $debug_email = 'Date: ' . date('Y-m-d h:i:s') . "\n";
  $debug_email .= "Notifications:\n";
  $debug_email .= "Total: " . $notification_count . "\n";
  $debug_email .= "Starting process\n";
  $debug_email .= "Details:\n";

 
  while (time() <= $execution_time_end && $notification_index<$notification_count) 
  {
    $notification = $notifications[$notification_index];

    $debug_email .= "\n\nNotification #" . $notification_index ."\n";
    $debug_email .= "Notification ID" . $notification['id'] ."\n";
    $debug_email .= "Message ID" . $notification['message_id'] ."\n";

    $message = \Components\Entity\Message::find($notification['message_id']);
    if (!empty ($message) )
    {
      $debug_email .= "Message found:\n";

      //Прочитано, или не шлем - надо его сразу убить, чтобы потом когда включим не повалил шквал старых писем
      if($message['readed'] || !(\Components\Entity\EmailNotificationType::isSendable($notification['type'])))
      {
        \Components\Entity\EmailNotification::delete($notification['id']);
      }
      else
      {
      	$attachments = array();

        // надо только для писем типа \Components\Entity\EmailNotification::TO_SUBSCRIBED_AUTHORS_ON_DISTRIBUTION
        
        
        $all_added_size = 0;
		$fna = 0;
        
        if ( ($notification['type'] == \Components\Entity\EmailNotification::TO_SUBSCRIBED_AUTHORS_ON_DISTRIBUTION) && !empty ($message['order_id']))
        {
          $files = get_order_files($message['order_id']);
          foreach ($files as $file)
          {
        	$all_added_size += $file['size']; 
			if ($all_added_size > 16000000)
			{
				// write to body, but not add file
				$fna++;
			}	
			else		
            	$attachments[] = array('path'=>get_file_path($message['order_id'], $file), 'name'=>$file['name']);
          }
        }
		
		$subtext = "";
		if ($fna)
		{
			$subtext = "<br><br>-----------------------------------<br>" .
			"Еще " . $fna . " файла(ов) не были добавлены к письму из-за ограничения размеров" ;
		}
		
		// Это условие проверено выше
        //if ( \Components\Entity\EmailNotificationType::isSendable($notification['type']) )
        //{
          $email = new \Components\Classes\Email();
		  $email->IsHTML(true);

        if ($message['creator_id'][0] != 'u' && $message['creator_id'][0] != 'k') {
          $message['creator_id'] = 'u' .$message['creator_id'];
        }
        $reply_to = Message::getReceiverEmailAndName($message['creator_id']);
//        if (empty($reply_to['email'])) {
//          $reply_to['email'] = 'zakaz@sessia-online.ru';
//          $reply_to['name'] = 'zakaz@sessia-online.ru';
//
//        }
        $yonkon_str = "Email: " . $reply_to['email'] . "\n Name: " . $reply_to['name'] . "\n";
//        mail('yonkon.ru@gmail.com', 'Cron sender email and name', $yonkon_str );

        $debug_email .= "Message FROM" . $reply_to['email'] ."\n";
        $debug_email .= "Message TO" . $notification['receiver_email'] ."\n";

          $email->setData(
            array(
              'email' => $notification['receiver_email'],
              'name' => ''
            ),
            $message['subject'],
            $message['text'] . $subtext,
            $attachments,
            true,
            Message::getReceiverEmailAndName($message['creator_id']),
            Message::getReceiverEmailAndName($message['creator_id'])
          );
          try {
            $send_result = $email->send();
            if ( $send_result ) 
            {
              \Components\Entity\EmailNotification::delete($notification['id']);
			  $good_cnt++;
            } else {
              \Components\Entity\EmailNotification::update(
                $notification['id'],
                array(
                  'attempts_to_send' => ($notification['attempts_to_send'] + 1),
                  'last_attempt' => time(),
                  'last_error' => $email->ErrorInfo,
                )
              );
              $debug_email .= "SEND ERROR " . $email->ErrorInfo ."\n";

              mail('yonkon.ru@gmail.com', 'Cron send email notifications error', $email->ErrorInfo);
            }
          } 
          catch (\Components\Exceptions\Exception $e) 
          {
            \Components\Entity\EmailNotification::update(
              $notification['id'],
              array(
                'attempts_to_send' => ($notification['attempts_to_send'] + 1),
                'last_attempt' => time(),
              )
            );
            $debug_email .= "SEND EXCEPTION " . $e->getMessage() ."\n";
          }
		  
		  unset($email);
		  
        //}
      }
    } else {
      $debug_email .= "Message not found\n";
    }
    $notification_index++;
  }
  if ($notification_count >0) {
    mail('yonkon.ru@gmail.com', 'Cron email report', $debug_email);
  }
  db::query("update ofc_sys_log set p_value=" . $execution_time_start . " where p_name='email_notify_last_tm_start'" );
  db::query("update ofc_sys_log set p_value=" . (time() - $execution_time_start) . " where p_name='email_notify_last_tm_work'" );
  db::query("update ofc_sys_log set p_value=" . $notification_index . " where p_name='email_notify_last_all_cnt'" );
  db::query("update ofc_sys_log set p_value=" . $good_cnt . " where p_name='email_notify_last_good_cnt'" );
  
  
		
}

cron_sendEmailNotifications();