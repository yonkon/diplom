<?php
include_once "../../mysql.class.php";
include_once "../../config.php";
include_once "../../utils.php";
include_once "../../default_data.php";
session_start();

if (!$_SESSION["user"]["auth"] || ($_SESSION["user"]["data"]["group_id"]!=1)) die("-1");
if (!isset($_REQUEST["fid"])) die("-2");

$fid = intval($_REQUEST["fid"]);

$db->Select("data_filials", "*", "WHERE id=".$fid);
if (!$db->ResultCount) die("-3");
$fil = $db->Result[0];
if ($fil["tm_special"])
	$fil["tm_special"] = @unserialize($fil["tm_special"]);
else
	$fil["tm_special"] = array();

$day = intval($_REQUEST["d"]);
if (($day<0)||($day>6)) die("-4");

if ($_POST["cmd"]=="1"){
		
	// проверим что день еще не определен
	if (isset($fil["tm_special"][$day])) die("-5");
	
	$w = intval($_REQUEST["w"]);
	$tm_open = utils_cvt_time2i($_REQUEST["o"]);
	$tm_close = utils_cvt_time2i($_REQUEST["c"]);
	
	if (!$w && ($tm_open==$fil["tm_open"]) && ($tm_close==$fil["tm_close"])) die("");
	if (!$w && ($tm_open>=$tm_close)) die("");
	
	
	$fil["tm_special"][$day]["s"] = $tm_open;
	$fil["tm_special"][$day]["e"] = $tm_close;
	$fil["tm_special"][$day]["w"] = $w;

	$db->Update("data_filials", array("tm_special"), array(serialize($fil["tm_special"])), "WHERE id=".$fid);
	
	die("");
}


if ($_POST["cmd"]=="2"){
	
	if (isset($fil["tm_special"][$day])){
		unset($fil["tm_special"][$day]);
		$db->Update("data_filials", array("tm_special"), array(serialize($fil["tm_special"])), "WHERE id=".$fid);
	}
	
	die("");
}


?>