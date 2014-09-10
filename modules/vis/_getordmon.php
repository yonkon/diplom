<?php

require_once('../../includes/application_top.php');

if (!$_SESSION["user"]["auth"]) die("");
//if (!user_has_right("ord_r")) die("");
if (!isset($_POST["oid"])) die("");

$ord = ord_get(intval($_POST["oid"]));
if ($ord) {
  die("".($ord["cost_kln"]-$ord["oplata_kln"]));
}