<?php

use Components\Classes\db;
use Components\Classes\ErrorLogger;

ob_start();
session_start();

//ini_set('error_reporting', E_ALL & ~E_DEPRECATED);
//ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

header("Content-Type: text/html; charset=utf-8");

if (!defined('DIR_FS_DOCUMENT_ROOT')) {
  define('DIR_FS_DOCUMENT_ROOT', dirname(dirname(__FILE__)));
}

require_once(DIR_FS_DOCUMENT_ROOT . "/config/config.php");
if (MAINTENANCE_MODE && isset($_SESSION["user"]['data'])) {
  if ($_SESSION["user"]["data"]["group_id"] != 0) {
    echo '<h1>Проводятся технические работы. Работа сайта будет восстановлена в ближайшее время</h1>';
    die;
  }
}
require_once(DIR_FS_DOCUMENT_ROOT . "/config/constants.php");
require_once(DIR_FS_CONFIGS . "db_config.php");
require_once(DIR_FS_CONFIGS . "email_config.php");
require_once(DIR_FS_CONFIGS . "tables.php");

require_once(DIR_FS_INCLUDES . "autoloader.php");

db::connect();

set_error_handler('Components\Classes\ErrorLogger::all_error_handler');
set_exception_handler('Components\Classes\ErrorLogger::exception_handler');

if (!isset(ErrorLogger::$hostname)) {
  if (!isset($_SERVER) || !isset($_SERVER["HTTP_HOST"]) || $_SERVER["HTTP_HOST"] == "") {
    ErrorLogger::$hostname = getenv("HOSTNAME");
  } else {
    ErrorLogger::$hostname = $_SERVER["HTTP_HOST"];
  }
}
ErrorLogger::create_path(DIR_FS_LOGFILES, DIR_FS_DOCUMENT_ROOT);

require_once(DIR_FS_INCLUDES . "functions.php");

if (get_magic_quotes_gpc()) {
  $_GET = stripslashes_array($_GET);
  $_POST = stripslashes_array($_POST);
  $_REQUEST = stripslashes_array($_REQUEST);
  $_COOKIE = stripslashes_array($_COOKIE);
}

require_once DIR_FS_DOCUMENT_ROOT . "/default_data.php";
require_once DIR_FS_DOCUMENT_ROOT . "/gui/gui.php";

if (ENVIRONMENT == 'dev') {
  require_once(DIR_FS_EXTENSIONS . 'PHPConsole/src/PhpConsole/__autoload.php');

  $handler = PhpConsole\Handler::getInstance();
  $handler->start();
  $handler->getConnector()->setSourcesBasePath($_SERVER['DOCUMENT_ROOT']);
}