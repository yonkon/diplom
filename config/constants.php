<?php
if (!defined('DIR_FS_CONFIGS')) {
  define('DIR_FS_CONFIGS', DIR_FS_DOCUMENT_ROOT . "/config/");
}
if (!defined('DIR_FS_INCLUDES')) {
  define('DIR_FS_INCLUDES', DIR_FS_DOCUMENT_ROOT . "/includes/");
}
if (!defined('DIR_FS_MODULES')) {
  define('DIR_FS_MODULES', DIR_FS_DOCUMENT_ROOT . "/modules/");
}
if (!defined('DIR_FS_ORDER_FILES')) {
  define('DIR_FS_ORDER_FILES', DIR_FS_DOCUMENT_ROOT . "/order_files/");
}
if (!defined('DIR_WS_ORDER_FILES')) {
  define('DIR_WS_ORDER_FILES', "order_files/");

  if (!defined('DIR_FS_LOGFILES')) {
    define('DIR_FS_LOGFILES', DIR_FS_DOCUMENT_ROOT . '/logfiles/');
  }
}
if (!defined('DIR_FS_EXTENSIONS')) {
  define('DIR_FS_EXTENSIONS', DIR_FS_DOCUMENT_ROOT . "/ext/");
}
if (!defined('DIR_FS_JS')) {
  define('DIR_FS_JS', DIR_FS_DOCUMENT_ROOT . "/js/");
}
if (!defined('DIR_WS_JS')) {
  define('DIR_WS_JS', SITE_URL . "/js/");
}
if (!defined('DIR_FS_FRAME')) {
  define('DIR_FS_FRAME', DIR_FS_DOCUMENT_ROOT . "/frame/");
}