<?php

use Components\Classes\db;

require_once('../../includes/application_top.php');

if (!$_SESSION["user"]["auth"]) die("запрещено");

if (db::get_single_value("SELECT id FROM " . TABLE_MESSAGES . " WHERE addr = 'u" . db::input($_SESSION["user"]["data"]["id"]) . "' AND readed = '0' AND basket = '0'")) {
  die("1");
} else {
  die("0");
}