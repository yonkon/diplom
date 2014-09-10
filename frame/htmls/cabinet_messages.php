<?php

use Components\Exceptions\Exception;
use Components\Entity\Employee;
use Components\Classes\db;
use Components\Entity\Message;

if (!is_client_logged() || $_SESSION["frame"]["client"]["blocked"]) {
  echo 'Доступ запрещен.';
} else {
  if (!empty($_REQUEST['new']) && !empty($_REQUEST['r'])) {
    try {
      $receiver = Employee::find($_REQUEST['r']);
    } catch(Exception $e) {
      redirect("?type=cabinet&messages");
    }

    if (!empty($_REQUEST['send'])) {
      if ($receiver['id'] != $_REQUEST['receiver']) {
        redirect("?type=cabinet&messages");
      }

      $message_id = mls_Send('u' . $receiver['id'], 'k' . $_SESSION['frame']['client']['id'], $_REQUEST['subject'], $_REQUEST['text'], 1, 0);
      \Components\Classes\Author::enqueue_message_to_email($message_id,array($receiver['id']), \Components\Entity\EmailNotificationType::TO_RECEIVER_ON_MESSAGE_COMMON);
      redirect("?type=cabinet&messages");
    }

    $receiver_full_name = db::get_single_value("SELECT sname FROM " . TABLE_ROLES . " WHERE id = " . $receiver['group_id']) . ' ' . $receiver['fio'];
    $subject = '';
    if (!empty($_REQUEST['o'])) {
      $subject = 'Вопрос по заказу №' . $_REQUEST['o'];
    }

    echo "<div style='margin-bottom: 5px'><a href='?type=cabinet&messages'><< к списку сообщений</a></div>";

  echo <<<HTML
<div id="cgui_form_0" class="cgui_form_box" style="width:600px; height: 270px; margin: 0 auto;">
<div class="cgui_form_capt">Новое сообщение</div>
<form class="cgui_form" method="post">
<input type="hidden" name="send" value="1"/>
<div class="cgui_form_text" style="width: 530px; height: 50px; margin-left:50px; margin-top: 10px;">
  <input type="text" value="{$receiver_full_name}" style="position: absolute; width:530px;">
  <input name="receiver" value="{$receiver['id']}" type="hidden">
</div>

<div class="cgui_form_label" style="margin-left: 10px; margin-top: 14px;">Кому</div>

<div class="cgui_form_label" style="margin-left: 10px; margin-top: 54px;">Тема</div>

<div class="cgui_form_text" style="width: 530px; height: 30px; margin-left:50px; margin-top: 50px;">
  <input type="text" value="{$subject}" name="subject" style="position: absolute; width:530px;">
</div>

<div class="cgui_form_label" style="margin-left: 10px; margin-top: 90px;">Текст</div>

<div class="cgui_form_text" style="width: 530px; height: 100px; margin-left:50px; margin-top: 90px;">
  <textarea name="text" style="position: absolute; width:530px; height:100px"></textarea>
</div>

<button class="cgui_form_button" style="width: 100px; margin-left: 250px; margin-top: 210px">Отправить</button>
</form></div>
HTML;

    die;
  }

  $messages = db::get_assoc_arrays("SELECT * FROM " . TABLE_MESSAGES . " WHERE addr = 'k" . db::input($_SESSION['frame']['client']["id"]) . "' OR creator_id = 'k" . db::input($_SESSION['frame']['client']["id"]) . "' ORDER BY created DESC");

  $output_messages = $readed_messages = $unreded_messages = array();
  foreach ($messages as $o) {
    if ($o["readed"]) {
      $readed_messages[$o["id"]] = $o;
    } else {
      $unreded_messages[$o["id"]] = $o;
    }
    if ($o['creator_id'] == 'k' . $_SESSION['frame']['client']["id"]) {
      $output_messages[$o["id"]] = $o;
    }
  }

  function print_messages_table($ms) {

    $cnt = 0;

    $out = "";

    foreach ($ms as $m) {
      $cnt++;

      $out .= "<div onclick='document.location.href=\"?type=cabinet&messages=" . $m["id"] . "\"' onmouseover='this.style.backgroundColor=\"#ddd\"' onmouseout='this.style.backgroundColor=\"\"' style='cursor: pointer; border-top: 1px solid silver; padding-bottom: 5px; padding-top: 5px;'>" . "<div style='margin-left: 5px; width: 45px; height: 18px; float: left;'>" . $m["id"] . "</div>" . "<div style='overflow: hidden; width: 540px; height: 18px; float: left;'>" . $m["subject"] . "</div>" . "<div style='margin-left: 10px; float: left'>" . date("d.m.Y H:i:s", $m["created"]) . "</div>" . "<div class='clear'></div></div>";
    }

    if ($cnt) {

      $h = $cnt * 30;
      if ($h > 180)
        $h = 180;

      print "<div style='height: " . $h . "px; overflow: auto;'>" . $out . "<div style='border-top: 1px solid silver;'></div></div>";
    }

  }

  if (isset($messages[intval($_REQUEST["messages"])])) {
    $m = $messages[intval($_REQUEST["messages"])];
    if (!$m["readed"]) {
      Message::update($m["id"], array(
        'readed' => 1,
      ));
    }

    if (isset($_REQUEST["cab_msg_answer"])) {
      $t = clearText($_REQUEST["cab_msg_answer"]);

      if (strlen($t)) {
        $t = substr($t, 0, 1000);
        $sbj = "[Re] " . $m["subject"];

        $message_id = Message::create(array(
          "parent_id" => $m["id"],
          "klient_id" => $m["klient_id"],
          "created" => time(),
          "creator_id" => $m["addr"],
          "addr" => $m["creator_id"],
          "subject" => $sbj,
          "text" => $t,
          "prior" => 1,
        ));

        if ($message_id) {
          enqueue_message_to_email($message_id, message_reciever_to_email($m['creator_id']), \Components\Entity\EmailNotificationType::TO_RECEIVER_ON_MESSAGE_COMMON );
          $_SESSION["cab_msg_answer_info"] = "<span style='color:green'>Сообщение отправлено</span>";
        } else {
          $_SESSION["cab_msg_answer_info"] = "<span style='color:red'>Не удалось отправить сообщение</span>";
        }

        header("location: ?type=cabinet&messages=" . $m["id"]);

        print $_SESSION["cab_msg_answer_info"];
        die();
      } else $_SESSION["cab_msg_answer_info"] = "Введите текст сообщения";
    }
  ?>
    <script>
      jQuery(function () {
        jQuery("#cab_msg_answer").bind("focus", function () {
          jQuery("#cab_msg_answer_btn").removeAttr("disabled");
          jQuery("#cab_msg_answer").css("color", "black");
          jQuery("#cab_msg_answer").text("");
          jQuery("#cab_msg_answer").unbind("focus");

        });
      });
    </script>

    <div style='margin-bottom: 5px'><a href='?type=cabinet&messages'><< к списку сообщений</a></div>
    <div style="font-size: 12pt; font-weight: bold; margin-bottom: 5px;"><?= $m["subject"] ?></div>
    <div style="color: gray; margin-bottom: 10px;">отправлено <?= date("d.m.Y H:i:s", $m["created"]) ?></div>
    <div style='border-top: 1px solid silver;'></div>
    <div style="height: 200px; overflow: auto; margin-top: 10px;"><?= $m["text"] ?></div>
    <div style='margin-top: 10px; border-top: 1px solid silver;'></div>

    <div style="margin-top: 10px; margin-left: 20px">

      <? if (strlen(@$_SESSION["cab_msg_answer_info"])): ?>
        <div style="margin-bottom: 5px;"><? print $_SESSION["cab_msg_answer_info"];
          $_SESSION["cab_msg_answer_info"] = ""; ?></div>   <? endif ?>
      <form method="post">
        <div style='margin-bottom: 10px;'>
          <textarea id="cab_msg_answer" name="cab_msg_answer" maxlength="1000" style="color: silver; width: 100%; height: 100px;">Ответ</textarea>
        </div>
        <input type="submit" id="cab_msg_answer_btn" disabled="disabled" value="Отправить">
      </form>
    </div>


  <? } else { ?>

    <div style="margin-bottom: 10px"><b>Новые</b></div>
    <? print_messages_table($unreded_messages); ?>

    <div style="margin-top: 20px; margin-bottom: 10px"><b>Прочитанные</b></div>
    <? print_messages_table($readed_messages); ?>

    <div style="margin-top: 20px; margin-bottom: 10px"><b>Исходящие</b></div>
    <? print_messages_table($output_messages); ?>

  <? }
}
?>