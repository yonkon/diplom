<?php
if (!defined('DIR_FS_CONFIGS')) {
  define('DIR_FS_CONFIGS', DIR_FS_DOCUMENT_ROOT .  DIRECTORY_SEPARATOR ."config". DIRECTORY_SEPARATOR );
}
if (!defined('DIR_FS_INCLUDES')) {
  define('DIR_FS_INCLUDES', DIR_FS_DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."includes". DIRECTORY_SEPARATOR );
}
if (!defined('DIR_FS_MODULES')) {
  define('DIR_FS_MODULES', DIR_FS_DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."modules". DIRECTORY_SEPARATOR );
}
if (!defined('DIR_FS_ORDER_FILES')) {
  define('DIR_FS_ORDER_FILES', DIR_FS_DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."order_files". DIRECTORY_SEPARATOR );
}
if (!defined('DIR_WS_ORDER_FILES')) {
  define('DIR_WS_ORDER_FILES', "order_files". DIRECTORY_SEPARATOR );

  if (!defined('DIR_FS_LOGFILES')) {
    define('DIR_FS_LOGFILES', DIR_FS_DOCUMENT_ROOT .  DIRECTORY_SEPARATOR ."logfiles". DIRECTORY_SEPARATOR );
  }
}
if (!defined('DIR_FS_EXTENSIONS')) {
  define('DIR_FS_EXTENSIONS', DIR_FS_DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."ext". DIRECTORY_SEPARATOR );
}
if (!defined('DIR_FS_JS')) {
  define('DIR_FS_JS', DIR_FS_DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."js". DIRECTORY_SEPARATOR );
}
if (!defined('DIR_WS_JS')) {
  define('DIR_WS_JS', SITE_URL . DIRECTORY_SEPARATOR ."js". DIRECTORY_SEPARATOR );
}
if (!defined('DIR_FS_FRAME')) {
  define('DIR_FS_FRAME', DIR_FS_DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."frame". DIRECTORY_SEPARATOR );
}