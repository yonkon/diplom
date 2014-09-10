<?php

use Components\Entity\Client;

require_once('../../includes/application_top.php');

need_data("data_users");

$addr_book = array();
if (isset($data_mails[$_SESSION["user"]["data"]["group_id"]]["mails"])) {
  $grps = $data_mails[$_SESSION["user"]["data"]["group_id"]]["mails"];
  foreach ($data_users as $v) {
    if (in_array($v["group_id"], $grps)) {
      $addr_book[] = $v;
    }
  }
} else {
  $addr_book = $data_users;
}

// Авторам запрещено писать клиентам
$to_kln = $_SESSION["user"]["data"]["group_id"] != 6;

$script = array();
$script[] = '<script type="text/javascript"> var users_list=[]; var clients_list=[];';
$k = 0;
foreach ($addr_book as $v) {
  $script[] = "users_list[" . $k++ . "]={id:" . $v["id"] . ",name:'" . $data_groups[$v["group_id"]]["sname"] . " " . $v["fio"] . "'};";
}

$script[] = "selected_users = []; selected_clients = [];";

if (isset($_REQUEST["a"]) && strlen($_REQUEST["a"])) {
  $a = explode(";", $_REQUEST["a"]);
  $u = 0;
  $k = 0;
  foreach ($a as $v) {
    if (strtolower(substr($v, 0, 1)) == "u") {
      $script[] = "selected_users[" . $u++ . "]=" . intval(substr($v, 1)) . ";";
    }
    if (strtolower(substr($v, 0, 1)) == "k") {
      $script[] = "selected_clients[" . $k++ . "]=" . intval(substr($v, 1)) . ";";
    }
  }
}

if ($to_kln) {
  if (is_director($_SESSION["user"]["data"]["id"])) {
    $clients = Client::findAll();
  } else {
    $clients = Client::findBy(array(
      'filial_id' => $_SESSION["user"]["data"]["filial_id"],
    ));
  }

  $k = 0;
  foreach ($clients as $v) {
    $script[] = "clients_list[" . $k++ . "]={id:" . $v["id"] . ",name:'" . $v["fio"] . "'};";
  }
}

$script[] = "</script>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <script type='text/javascript' src='/js/1.7.2.jquery.js'></script>
  <script type='text/javascript' src='/modules/mls/scripts.js'></script>
  <? echo join("\n", $script); ?>

  <style>
    body {
      background-color: #EEF;
    }
  </style>

  <title>Адресная книга</title>
</head>

<body>


<table width="100%" height="100%">
  <tr>
    <td>
      <input id="search_field_id" type="text" style="width: 100%; color: silver; border: 1px solid silver" value="Поиск" onfocus="search_fld_focus(this)" onblur="search_fld_blur(this)" onkeyup="search_fld_keyup(this)">
    </td>
  </tr>
  <? if ($to_kln): ?>
  <tr>
    <td><select style="width: 100%; border: 1px solid silver" onchange="onselbooktype(this.value);">
      <option value="0">Сотрудники</option>
      <option value="1">Клиенты</option>
    </select>
    </td>
  </tr>
  <? endif; ?>
  <tr>
    <td style="height: 100%" valign="top">
      <div id="adr_list_id" style="width: 100%; font-size: 9pt; font-family: arial; background-color: white; padding: 4px; border: 1px solid silver; overflow:auto; height: 250px;"></div>
    </td>
  </tr>
  <tr>
    <td align="right" style="height: 50px">
      <input type="button" value="Выбрать" onclick="insert_addresses('<?=$_REQUEST["n"]?>'); window.close();">
      <input type="button" value="Отмена" onclick="window.close();">
    </td>
  </tr>
</table>


<script type='text/javascript'>
  jQuery(function () {
    onselbooktype('0');
  });
</script>

</body>
</html>