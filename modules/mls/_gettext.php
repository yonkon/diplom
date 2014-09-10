<?php

use Components\Classes\db;

require_once('../../includes/application_top.php');

if (!$_SESSION["user"]["auth"]) die("запрещено");

if (!isset($_REQUEST["num"])){
	die("нет данных");
}

$num = intval($_REQUEST["num"]);

$message = db::get_single_row("SELECT * FROM " . TABLE_MESSAGES . " WHERE id = " . db::input($num) . " AND (creator_id = 'u" . $_SESSION["user"]["data"]["id"] . "' OR addr = 'u".$_SESSION["user"]["data"]["id"] . "')");

if (!$message){
	die("сообщение не найдено");
}

$tp = "i";
if ($message["addr"] != "u".$_SESSION["user"]["data"]["id"]) $tp = "o";
if ($message["basket"]) $tp = "b";

die("<p><input type='button' value='Перейти' onclick='document.location.href=\"?section=mls&subsection=2&type=".$tp."&read=".$num."\"'></p>".text_to_html($message["text"]));