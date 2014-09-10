<?php
if (!defined('TEST_MODE')) {
  define('TEST_MODE', false);
}
if (!defined('TEST_PASSWORD')) {
  define('TEST_PASSWORD', '123123');
}

if (!defined('SITE_ROOT')) {
  define('SITE_ROOT', DIR_FS_DOCUMENT_ROOT . '/');
}
if (!defined('SITE_URL')) {
  define('SITE_URL', 'http://sessia-online.ru/');
}

define('ENVIRONMENT', 'prod');
define('MAINTENANCE_MODE', false);