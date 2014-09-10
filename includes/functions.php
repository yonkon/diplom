<?php
use Components\Classes\ErrorLogger;
use Components\Classes\db;
use Components\Classes\Roles;

use Components\Entity\Role;
use Components\Entity\OrderStatus;
use Components\Entity\Filial;
use Components\Entity\Client;
use Components\Entity\Order;
use Components\Entity\Message;
use Components\Entity\Employee;
use Components\Entity\VUZ;
use Components\Entity\Worktypes;
use Components\Entity\Napravl;
use Components\Entity\Discipline;
use Components\Entity\PaymentMethod;
use Components\Entity\SubwayStation;
use Components\Entity\EmailNotification;
use Components\Entity\EmailNotificationType;

use Components\Exceptions\EntityNotFoundException;
use Components\Exceptions\InvalidArgumentException;
use Components\Exceptions\Exception;

/////////////////////////////////////////////
/////// Заслешивание данных в запросах  /////
/////////////////////////////////////////////
function stripslashes_array($array) {
  return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
}

// Заголовки страницы уже выведены 
$headers_already_printer = false;
// Скрипты, которые нужно подключить
$page_scripts = array();
function page_scriptNeed($name, $path = "js") {
  global $page_scripts, $headers_already_printer;
  $name = /*"/" .*/ trim($path, "/\\") . "/" . $name;
  if (in_array($name, $page_scripts)) {
    return;
  }
  $page_scripts[] = $name;
  if ($headers_already_printer) {
    print "<script type='text/javascript' src='" . str_replace("//", "/", $name) . "'></script>";
  }
}

// Стили, которые нужно подключить
$page_styles = array();
function page_styleNeed($name, $path = "css") {
  global $page_styles, $headers_already_printer;
  $name = /*"/" .*/ trim($path, "/\\") . "/" . $name;
  if (in_array($name, $page_styles)) {
    return;
  }
  $page_styles[] = $name;
  if ($headers_already_printer) {
    print "<link rel='stylesheet' href='" . str_replace("//", "/", $name) . "' type='text/css'/>";
  }
}

// Встраиваемые в код страницы скрипты
$page_script_blocks = array();
function page_AddScriptText($code) {
  global $page_script_blocks;
  $page_script_blocks[] = $code;
}

// Вывод заголовков страницы, скриптов
function page_headers() {
  global $page_scripts, $page_styles, $headers_already_printer, $page_script_blocks;
  print '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n";
  foreach($page_styles as $v) {
    print "<link rel='stylesheet' href='" . str_replace("//", "/", $v) . "' type='text/css'/>" . "\n";
  }
  $headers_already_printer = true;
  foreach($page_scripts as $v) {
    print "<script type='text/javascript' src='" . str_replace("//", "/", $v) . "'></script>" . "\n";
  }
  print "<script type='text/javascript'>" . "\n";
  foreach($page_script_blocks as $b) {
    print $b . "\n";
  }
  print "</script>" . "\n";
}

// Вывод страницы клиенту
function page_out() {
  global $page_title, $GUI;
  ob_start();
  $GUI->HTML();
  $text = ob_get_contents();
  ob_end_clean();
  print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head>' . "\n";
  page_headers();
  print "<title>" . $page_title . "</title>\n</head>\n<body>\n" . $text . "</body>\n</html>";
}

///////////////////////////////////////////////
/////////// Перезагрузка страницы /////////////
///////////////////////////////////////////////
// Перезагрузка на главную страницу
function page_reload() {
  header("Location: index.php");
  exit();
}

// Перезарузка текущего модуля (секция) на его раздел по умолчанию
function page_reloadSec() {
  $s = "";
  if (isset($_REQUEST["section"])) {
    $s = "?section=" . $_REQUEST["section"];
  }
  header("Location: index.php" . $s);
  exit();
}

// Перезагрузка текущей страницы модуля
function page_reloadSubSec() {
  $s = "";
  if (isset($_REQUEST["section"])) {
    $s = "?section=" . $_REQUEST["section"];
    if (isset($_REQUEST["subsection"])) {
      $s .= "&subsection=" . $_REQUEST["subsection"];
    }
  }
  header("Location: index.php" . $s);
  exit();
}

// Перезагрузка с переходом на указанный модуль
function page_reloadToSec($sec) {
  $s = "";
  if (isset($_REQUEST["section"])) {
    $s = "?section=" . $_REQUEST["section"];
    $s .= "&subsection=" . $sec;
  }
  header("Location: index.php" . $s);
  exit();
}

// Перезагрузка с текущим URI
function page_reloadAll() {
  header("Location: " . $_SERVER["REQUEST_URI"]);
  exit();
}

////////////////////////////////////////////////////////
function redirect($url) {
  header('Location: ' . str_replace('&amp;', '&', $url));
  exit();
}

// Проверка, есть ли у пользователя разрешения $r
// принимает массив разрешений или одно разрешение
function user_has_right($r) {
  if (is_array($r)) {
    foreach($r as $v) {
      if (user_has_right($v)) {
        return true;
      }
    }
    return false;
  } else {
    return in_array($r, $_SESSION["user"]["rights"]);
  }
}

// Делает http - запрос
function http_request($verb = 'GET', /* HTTP Request Method (GET and POST supported) */
  $ip, /* Target IP/Hostname */
  $port = 80, /* Target TCP port */
  $uri = '/', /* Target URI */
  $getdata = array(), /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */
  $postdata = array(), /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */
  $cookie = array(), /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */
  $custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */
  $timeout = 1000, /* Socket timeout in milliseconds */
  $req_hdr = false, /* Include HTTP request headers */
  $res_hdr = false /* Include HTTP response headers */) {
  $ret = '';
  $verb = strtoupper($verb);
  $cookie_str = '';
  $getdata_str = count($getdata) ? '?' : '';
  $postdata_str = '';
  foreach($getdata as $k => $v) {
    $getdata_str .= urlencode($k) . '=' . urlencode($v) . '&';
  }
  foreach($postdata as $k => $v) {
    $postdata_str .= urlencode($k) . '=' . urlencode($v) . '&';
  }
  foreach($cookie as $k => $v) {
    $cookie_str .= urlencode($k) . '=' . urlencode($v) . '; ';
  }
  $crlf = "\r\n";
  $req = $verb . ' ' . $uri . $getdata_str . ' HTTP/1.1' . $crlf;
  $req .= 'Host: ' . $ip . $crlf;
  $req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf;
  $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
  $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf;
  $req .= 'Accept-Encoding: deflate' . $crlf;
  $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf;
  foreach($custom_headers as $k => $v) {
    $req .= $k . ': ' . $v . $crlf;
  }
  if (!empty($cookie_str)) {
    $req .= 'Cookie: ' . substr($cookie_str, 0, -2) . $crlf;
  }
  if ($verb == 'POST' && !empty($postdata_str)) {
    $postdata_str = substr($postdata_str, 0, -1);
    $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf;
    $req .= 'Content-Length: ' . strlen($postdata_str) . $crlf . $crlf;
    $req .= $postdata_str;
  } else {
    $req .= $crlf;
  }
  if ($req_hdr) {
    $ret .= $req;
  }
  if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false) {
    return "Error $errno: $errstr\n";
  }
  stream_set_timeout($fp, 0, $timeout * 1000);
  fputs($fp, $req);
  while($line = fgets($fp)) {
    $ret .= $line;
  }
  fclose($fp);
  if (!$res_hdr) {
    $ret = substr($ret, strpos($ret, "\r\n\r\n") + 4);
  }
  return $ret;
}

// Создание глобальных переменных из таблиц БД
// name - имя переменной; tname - имя таблицы если отличается от имени переменной  
$__need_data = array();
function need_data($name, $tname = "") {
  global $__need_data;
  if (isset($__need_data[$name])) {
    return;
  }
  if ($tname == "") {
    $tname = $name;
  }
  $res = db::get_assoc_arrays("SELECT * FROM " . TBL_PREF . $tname);
  $GLOBALS[$name] = $res;
}

// Генерирует пароль указанной длины
function generate_pasw($size, $dig = true, $ab_b = true, $ab_s = true) {
  $abc = array();
  if ($dig) {
    $abc = array(
      '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
    );
  }
  if ($ab_b) {
    $abc = array_merge($abc, array(
      'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    ));
  }
  if ($ab_s) {
    $abc = array_merge($abc, array(
      'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
    ));
  }
  srand(time());
  shuffle($abc);
  if ($size > count($abc)) {
    $size = count($abc);
  }
  $ret = "";
  for($i = 0; $i < $size; $i++) {
    $ret .= $abc[$i];
  }
  return $ret;
}

// Заменяет перенос строки в тексте на тег <p>
function text_to_html($t, $s = "") {
  $blocks = explode("\n", $t);
  foreach($blocks as $k => $v) {
    $blocks[$k] = "<p style='" . $s . "'>" . $v . "</p>";
  }
  return implode("", $blocks);
}

// Сколько строк в тексте
function text_lines_count($t) {
  $blocks = explode("\n", $t);
  return count($blocks);
}

// Проверяет, является ли строка датой формата XX-XX-XXXX
function utils_is_id_date($t) {
  $dt = explode("-", $t);
  if (count($dt) != 3) {
    return false;
  }
  return true;
}

// конвертирует дату в формате DD-MM-YYYY в секунды
function utils_cvt_date2i($t, $current_time = false) {
  $dt = explode("-", $t);
  if (count($dt) != 3) {
    return 0;
  }
  if ($current_time) {
    return mktime(date('H'), date('i'), date('s'), $dt[1], $dt[0], $dt[2]);
  } else {
    return mktime(0, 0, 0, $dt[1], $dt[0], $dt[2]);
  }
}

// конвертирует секунды в часы минуты
function utils_cvt_i2time($v, &$hh, &$mm) {
  $hh = floor($v / 60);
  $mm = floor($v - 60 * $hh);
  if (strlen($hh) == 1) {
    $hh = "0" . $hh;
  }
  if (strlen($mm) == 1) {
    $mm = "0" . $mm;
  }
}

// Конвертирует секунды во время формата HH:MM
function utils_cvt_i2times($v) {
  utils_cvt_i2time($v, $h, $m);
  return $h . ":" . $m;
}

// Если возможно, конвертирует строку формата HH:MM во время в секундах
function utils_cvt_time2i($tm) {
  $a = explode(":", $tm);
  if (count($a) != 2) {
    return 0;
  }
  $hh = intval($a[0]);
  $mm = intval($a[1]);
  if ($hh < 0) {
    $hh = 0;
  }
  if ($mm < 0) {
    $mm = 0;
  }
  if ($hh > 23) {
    $hh = 23;
  }
  if ($mm > 59) {
    $mm = 59;
  }
  return 60 * $hh + $mm;
}

// Обрезает текст, если возможно, по разделяющим символам ., ?!, делает его не длинее l
function utils_crop_text($t, $l) {
  if (strlen($t) <= $l) {
    return $t;
  }
  $t = substr($t, 0, $l);
  $x = strrpos($t, ".") | strrpos($t, ",") | strrpos($t, " ") | strrpos($t, "!") | strrpos($t, "?");
  if ($x === false) {
    return $t . "...";
  }
  return substr($t, 0, $x) . "...";
}

function obj_dump($var, $return_as_string = false, $full_trace = true) {
  return ErrorLogger::obj_dump($var, $return_as_string, $full_trace);
}

function get_station_name($station_id) {
  try {
    $station = SubwayStation::find($station_id);
    return $station['name'];
  } catch(InvalidArgumentException $e) {
    return 'не определено';
  }
}

/**
 * @deprecated
 * @param $client_id
 * @return mixed
 */
function get_client_name($client_id) {
  $client = Client::find($client_id);
  return $client['fio'];
}

function format_date($date, $show_days_to_date = true, $show_hours = false) {
  if (intval($date) == 0) {
    return "не определено";
  }
  $days = '';
  if ($show_days_to_date) {
    $days = 1 + floor(($date - time()) / 86400);
    if ($days == 0) {
      $days = "";
    } else {
      $days = " (" . $days . ")";
    }
  }
  $format = "d-m-Y";
  if ($show_hours) {
    $format = "d-m-Y H:i:s";
  }
  return date($format, $date) . $days;
}

function get_status($status_id) {
  global $vis_statuses;
  return $vis_statuses[$status_id];
}

/**
 * @deprecated
 * @param $order_id
 * @return array
 */
function get_order_info($order_id) {
  return Order::find($order_id);
}

/**
 * @deprecated
 * @param $client_id
 * @return array
 */
function get_client_info($client_id) {
  return Client::find($client_id);
}

/**
 * @deprecated
 * @param $vuz_id
 * @return array
 */
function get_vuz_name($vuz_id) {
  if ($vuz_id == 0) {
    return array(
      'name' => 'не определен', 'sname' => '',
    );
  }
  return VUZ::find($vuz_id);
}

/**
 * @deprecated
 * @param $wordtype_id
 * @return array
 */
function get_worktype_name($wordtype_id) {
  try {
    $worktype = Worktypes::find($wordtype_id);
    return $worktype['name'];
  } catch(InvalidArgumentException $e) {
    return 'не указана';
  } catch(EntityNotFoundException $e) {
    return 'не указана';
  }
}

/**
 * @deprecated
 * @param $naprav_id
 * @return mixed
 */
function get_naprav_name($naprav_id) {
  try {
    $napravl = Napravl::find($naprav_id);
    return $napravl['name'];
  } catch(InvalidArgumentException $e) {
    return 'не указано';
  } catch(EntityNotFoundException $e) {
    return 'не указано';
  }
}

/**
 * @deprecated
 * @param $disc_id
 * @return mixed
 */
function get_discipline_name($disc_id) {
  try {
    $discipline = Discipline::find($disc_id);
    return $discipline['name'];
  } catch(InvalidArgumentException $e) {
    return 'не указана';
  } catch(EntityNotFoundException $e) {
    return 'не указана';
  }
}

/**
 * @deprecated
 * @param $payment_id
 * @return mixed
 */
function get_payment_name($payment_id) {
  try {
    $payment_method = PaymentMethod::find($payment_id);
    return $payment_method['name'];
  } catch(InvalidArgumentException $e) {
    return 'не указана';
  } catch(EntityNotFoundException $e) {
    return 'не указана';
  }
}

/**
 * @deprecated
 * @param $filial_id
 * @return mixed
 */
function get_filial_name($filial_id) {
  try {
    $filial = Filial::find($filial_id);
    return $filial['name'];
  } catch(InvalidArgumentException $e) {
    return 'не указана';
  } catch(EntityNotFoundException $e) {
    return 'не указана';
  }
}

function get_order_author_id($order_id) {
  try {
    $order = Order::find($order_id);
  } catch(Exception $e) {
    return 0;
  }
  return $order['author_id'];
}

function change_order_status($order_id, $new_status) {
  try {
    $order_status = OrderStatus::findOneBy(array(
      'internal_name' => $new_status,
    ));
  } catch(Exception $e) {
    return 'Не существует статуса с именем ' . $new_status;
  }

  Order::update($order_id, array(
    'status_id' => $order_status['id'],
  ));

  switch($new_status) {
    case 'RECEIVED_FILE_FROM_AUTHOR':
      Order::update($order_id, array(
        'time_auth_r' => time(),
      ));
      break;
    default:
      break;
  }

  return true;
}

/**
 * @deprecated
 * @param $status_id
 * @return mixed
 */
function get_status_name($status_id) {
  try {
    $status = OrderStatus::find($status_id);
  } catch(EntityNotFoundException $e) {
    $status['status_name'] = '';
  } catch(InvalidArgumentException $e) {
    $status['status_name'] = '';
  }

  return $status['status_name'];
}

/**
 * @deprecated
 * @param $status_id
 * @return mixed
 */
function get_status_iname($status_id) {
  try {
    $status = OrderStatus::find($status_id);
  } catch(EntityNotFoundException $e) {
    $status['internal_name'] = '';
  } catch(InvalidArgumentException $e) {
    $status['internal_name'] = '';
  }

  return $status['internal_name'];
}

/**
 * @deprecated
 * @param $internal_name
 * @return mixed
 */
function get_status_id_by_iname($internal_name) {
  $status = OrderStatus::findOneBy(array(
    'internal_name' => $internal_name,
  ));
  return $status['id'];
}

/**
 * @deprecated
 * @param bool $admin
 * @return array
 */
function get_modules($admin = false) {
  $where = 1;
  if ($admin) {
    $where = "internal_name != 'admin'";
  }

  return db::get_assoc("SELECT id, name FROM " . TABLE_MODULES . " WHERE " . $where . " ORDER BY id ASC");
}

/**
 * @deprecated
 * @param $parent_module_id
 * @return array
 */
function get_submodules($parent_module_id) {
  return db::get_assoc("SELECT id, name FROM " . TABLE_SUBMODULES . " WHERE module_id = " . db::input($parent_module_id) . " ORDER BY id ASC");
}

/**
 * @deprecated
 * @param $parent_module_id
 * @return array
 */
function get_commands($parent_module_id) {
  return db::get_assoc("SELECT id, name FROM " . TABLE_SUBMODULES_COMMANDS . " WHERE submodule_id = " . db::input($parent_module_id) . " ORDER BY `order` ASC");
}

/**
 * @deprecated
 * @param $parent_module_id
 * @return array
 */
function get_columns($parent_module_id) {
  return db::get_assoc("SELECT id, name FROM " . TABLE_SUBMODULES_COLUMNS . " WHERE submodule_id = " . db::input($parent_module_id) . " ORDER BY `order` ASC");
}

function yes_or_no($value) {
  if ($value) {
    return "да";
  } else {
    return "нет";
  }
}

function get_role_subsections($role_id, $parent_module_name) {
  global $db;
  $modules = array();
  if ($role_id == 0 || $role_id == 1) {
    $db->query("
      SELECT s.name, s.order, s.default
      FROM " . TBL_PREF . "submodules s
      JOIN " . TBL_PREF . "modules m ON m.id = s.module_id
      WHERE m.internal_name = '" . $parent_module_name . "'
      ORDER BY s.order ASC
    ");
  } else {
    $db->query("
      SELECT s.name, s.order, s.default
      FROM " . TBL_PREF . "submodules s
      JOIN " . TBL_PREF . "modules m ON m.id = s.module_id
      JOIN " . TBL_PREF . "roles_to_submodules rts ON s.id = rts.submodule_id
      WHERE rts.role_id = " . $role_id . "
        AND m.internal_name = '" . $parent_module_name . "'
      ORDER BY s.order ASC
    ");
  }
  if ($db->Error) {
    return $db->Error;
  }
  while($row = db::fetch_array($db->_res)) {
    $modules[] = $row;
  }
  return $modules;
}

/**
 * @deprecated
 * @param $module_name
 * @param $submodule_id
 * @param $role_id
 *
 * @return resource
 */
function get_role_columns($module_name, $submodule_id, $role_id) {
  return Roles::getColumns($module_name, $submodule_id, $role_id);
}

/**
 * @deprecated
 * @param $module_name
 * @param $submodule_id
 * @param $role_id
 * @param $command_name
 *
 * @return bool
 */
function user_can($module_name, $submodule_id, $role_id, $command_name) {
  return Roles::isActionAllowed($module_name, $submodule_id, $role_id, $command_name);
}

/**
 * @deprecated
 * @return array
 */
function get_orders_statuses() {
  return OrderStatus::findAll();
}

/**
 * @deprecated
 * @param $order_id
 * @return null
 */
function get_order_status($order_id) {
  return db::get_single_value("
    SELECT os.internal_name
    FROM " . TBL_PREF . "orders_status os
    JOIN " . TBL_PREF . "orders o ON o.status_id = os.id
    WHERE o.id = " . $order_id);
}

/**
 * @deprecated
 * @return array
 */
function get_users_groups() {
  return db::get_arrays("SELECT * FROM " . TBL_PREF . "roles");
}

/**
 * @deprecated
 * @param $group_id
 * @param null $filial_id
 * @return array
 */
function get_users_by_group($group_id, $filial_id = null) {
  return Employee::findBy(array(
    'group_id' => $group_id,
    'filial_id' => $filial_id,
  ), array(
    'fio' => 'ASC',
  ));
}

/**
 * @deprecated
 * @param $group_name
 * @param null $filial_id
 * @param bool $for_filter
 * @param bool $for_select
 * @return array
 */
function get_users_by_group_name($group_name, $filial_id = null, $for_filter = true, $for_select = false) {
  $role = Role::findOneBy(array(
    'name' => $group_name,
  ));

  if ($role) {
    $aUsers = get_users_by_group($role['id'], $filial_id);
    if ($for_filter) {
      $result = array();
      foreach($aUsers as $user) {
        $result[$user['id']] = $user;
      }
      return $result;
    } elseif ($for_select) {
      $result = array();
      foreach($aUsers as $user) {
        $result[$user['id']] = $user['fio'];
      }
      return $result;
    } else {
      return $aUsers;
    }
  } else {
    return array();
  }
}

function create_path($path, $base) {
  if (!is_dir($base)) {
    die($base . " is not a directory!");
  }
  $cur_path = $base;
  if (substr($cur_path, -1) == '/') {
    $cur_path = substr($cur_path, 0, -1);
  }
  if (substr($path, 0, strlen($base)) == $base) {
    // a path with base includes was passed. truncate it to path relative to base
    $path = substr($path, strlen($base));
  }
  $path_parts = explode('/', $path);
  foreach($path_parts as $dir_name) {
    if ($dir_name == '') {
      continue;
    }
    $cur_path .= '/' . $dir_name;
    if (!is_dir($cur_path)) {
      if (!@mkdir($cur_path, 0777)) {
        trigger_error($cur_path . " can't be created", E_USER_ERROR);
      }
    }
  }
}

/**
 * @deprecated
 * @param $user_id
 * @return bool
 */
function is_author($user_id) {
  if (db::num_rows(db::query("
    SELECT *
    FROM " . TBL_PREF . "data_users u
    JOIN " . TBL_PREF . "roles r ON u.group_id = r.id
    WHERE r.name = 'Автор' AND u.id = " . db::input($user_id) . "
  "))
  ) {
    return true;
  }
  return false;
}

/**
 * @deprecated
 * @param $user_id
 * @return bool
 */
function is_otdel_K($user_id) {
  if (db::num_rows(db::query("
    SELECT *
    FROM " . TBL_PREF . "data_users u
    JOIN " . TBL_PREF . "roles r ON u.group_id = r.id
    WHERE r.name = 'Отдел качества' AND u.id = " . db::input($user_id) . "
  "))
  ) {
    return true;
  }
  return false;
}

/**
 * @deprecated
 * @param $user_id
 * @param bool $admin
 *
 * @return bool
 */
function is_director($user_id, $admin = true) {
  $dop = '';
  if ($admin) {
    $dop = " OR r.name = 'Системный администратор'";
  }
  if (db::num_rows(db::query("
    SELECT *
    FROM " . TBL_PREF . "data_users u
    JOIN " . TBL_PREF . "roles r ON u.group_id = r.id
    WHERE (r.name = 'Руководитель'" . $dop . ") AND u.id = " . db::input($user_id) . "
  "))
  ) {
    return true;
  }
  return false;
}

function is_elder_manager($user_id) {
  if (db::num_rows(db::query("
    SELECT *
    FROM " . TBL_PREF . "data_users u
    JOIN " . TBL_PREF . "roles r ON u.group_id = r.id
    WHERE r.name = 'Старший менеджер'
      AND u.id = " . db::input($user_id) . "
  "))
  ) {
    return true;
  }
  return false;
}

/**
 * @deprecated
 * @param $user_id
 * @return bool
 */
function is_manager($user_id) {
  if (db::num_rows(db::query("
    SELECT *
    FROM " . TBL_PREF . "data_users u
    JOIN " . TBL_PREF . "roles r ON u.group_id = r.id
    WHERE (r.name = 'Старший менеджер' OR r.name = 'Менеджер' OR r.name = 'Младший менеджер') AND u.id = " . db::input($user_id) . "
  "))
  ) {
    return true;
  }
  return false;
}

function count_client_orders_qt($client_id) {
  return db::get_single_value("SELECT COUNT(*) FROM " . TBL_PREF . "orders WHERE klient_id = " . db::input($client_id));
}

/**
 * @deprecated
 * @param $role_name
 *
 * @return array
 */
function get_role_id_by_name($role_name) {
  $role = Role::findOneBy(array(
    'name' => $role_name,
  ));
  return $role['id'];
}

function get_file_ext($file_name) {
  $extension = pathinfo($file_name);
  return strtolower($extension['extension']);
}

function get_file_path($order_id, $file) {
  $extension = get_file_ext($file['name']);
  return DIR_FS_ORDER_FILES . $order_id . '/' . $file['id'] . '.' . $extension;
}

function get_file_size($v) {
  $base = log($v) / log(1024);
  $suffixes = array(
    'B', 'kB', 'MB', 'GB', 'TB'
  );
  if (floor($base) > 4 || floor($base) < 0) {
    return 'Невозможно определить размер';
  }
  return round(pow(1024, $base - floor($base)), 2) . $suffixes[floor($base)];
}

function send_real_email($receiver_email, $receiver_name, $subj, $body, $attachments = array(), $isHTML = false, $replyTo = array()) {
  include_once(DIR_FS_DOCUMENT_ROOT . "/frame/libs/libphpmailer.php");
  $m = new PHPMailer();
  $m->IsSMTP();
  $m->Host = MAIL_HOST;
  $m->Username = MAIL_USER;
  $m->Password = MAIL_PASW;
  $m->SMTPAuth = true;
  if (MAIL_HOST_SSL) {
    $m->SMTPSecure = "ssl";
    $m->Port = MAIL_HOST_SSL;
  }
  $m->CharSet = "UTF-8";


  $m->AddAddress($receiver_email, $receiver_name);
  $m->From = FIRM_EMAIL;
  $m->FromName = FIRM_NAME;
  $m->Subject = $subj;
  if (!empty($replyTo)) {
    $m->AddReplyTo($replyTo['email'], $replyTo['name']);
  }
  if ($isHTML) {
    $m->MsgHTML($body);
  } else {
    $m->Body = $body;
  }
  foreach($attachments as $file) {
    $m->addAttachment($file['path'], $file['name']);
  }
  $m->Send();
  if ($m->IsError()) {
    ErrorLogger::add('email', 'Email sending failed', $m->ErrorInfo);
  }
}

/**
 * @deprecated
 * @param $user_id
 * @return null
 */
function get_user_email($user_id) {
  return db::get_single_value("SELECT email FROM " . TBL_PREF . "data_users WHERE id = " . db::input($user_id));
}

function calculate_debt_to_company($client_cost, $author_cost, $filial_id) {
  return ($client_cost - $author_cost) * db::get_single_value("SELECT profit FROM " . TBL_PREF . "data_filials WHERE id = " . db::input($filial_id));
}

function get_config_by_id($id) {
  return db::get_single_row("SELECT * FROM " . TBL_PREF . "configs WHERE id = " . db::input($id) . "");
}

/**
 * @deprecated
 * @param $internal_name
 * @return array
 */
function get_config_by_iname($internal_name) {
  return db::get_single_row("SELECT * FROM " . TBL_PREF . "configs WHERE internal_name = '" . db::input($internal_name) . "'");
}

function get_config_value_by_iname($internal_name) {
  return db::get_single_value("SELECT value FROM " . TBL_PREF . "configs WHERE internal_name = '" . db::input($internal_name) . "'");
}

function get_order_files($order_id, $creator = null) {
  return db::get_arrays("
    SELECT *
    FROM " . TBL_PREF . "order_files
    WHERE order_id = " . db::input($order_id) . (is_null($creator) ? '' : ' AND creator_id = ' . db::input($creator)));
}

function generate_file_link($v, $row, $t) {
  $extension = pathinfo($row['name']);
  if (!empty($extension['extension'])) {
    $extension = strtolower($extension['extension']);
  } else {
    $extension = '';
  }
  $result = '';
  switch($extension) {
    case 'jpg':
    case 'jpeg':
    case 'gif':
    case 'png':
      $result .= "[<a href='?section=ord&subsection=2&p=4&order=" . $row["order_id"] . "&file=" . $row["id"] . "&action=download'>скачать</a>]/[<a href='?section=ord&subsection=2&p=4&order=" . $row["order_id"] . "&file=" . $row["id"] . "&action=view'>просмотреть</a>]";
      break;
    default:
      $result .= "[<a href='?section=ord&subsection=2&p=4&order=" . $row["order_id"] . "&file=" . $row["id"] . "&action=download'>скачать</a>]";
      break;
  }
  if (is_director($_SESSION["user"]["data"]["id"]) || (is_manager($row['creator_id']) && $row['creator_id'] == $_SESSION["user"]["data"]["id"])) {
    $result .= "/[<a href='?section=ord&subsection=2&p=4&order=" . $row["order_id"] . "&file=" . $row["id"] . "&action=delete'>удалить</a>]";
  }
  return $result;
}

//***********
//***********
//***********
//Функции Переписки
//***********
//***********
//***********
/* prior - 0=>"низкий", 1=>"нормальный", 2=>"высокий"
 *
 *
 *
 *
 */
function mls_Send($to, $from, $subj, $text, $prior, $srok, $parent = 0, $order = 0, $klient = 0, $visit = 0, $tender = 0) {
  return Message::create(array(
    "parent_id" => $parent,
    "order_id" => $order,
    "klient_id" => $klient,
    "visit_id" => $visit,
    "tender_id" => $tender,
    "created" => time(),
    "creator_id" => $from,
    "addr" => $to,
    "subject" => $subj,
    "text" => $text,
    "prior" => $prior,
    "uvedom" => 1,
    "readed" => 0,
    "needansv" => $srok,
    "basket" => 0,
  ));
}

/**
 * @deprecated
 * @param $id
 * @return array
 */
function mls_get($id) {
  return Message::find($id);
}

function mls_setreaded(&$message) {
  if (!$message["readed"]) {
    Message::update($message['id'], array(
      'readed' => 1,
    ));
    $message["readed"] = 1;
  }
}

function mls_setbasket(&$message, $delete) {
  if ($message["basket"] != $delete) {
    Message::update($message['id'], array(
      'basket' => $delete,
    ));
    $message["basket"] = $delete;
  }
}

/**
 * @deprecated
 * @param $v
 * @return string
 */
function mls_getAdrName($v) {
  $s = substr($v, 0, 1);
  $id = intval(substr($v, 1));
  if ($s == 'u') {
    return sotr_getFullName($id);
  }
  if ($s == 'k') {
    $client = Client::find($id);
    if ($client) {
      return "клн." . $client["fio"];
    } else {
      return "<i>неопределено</i>";
    }
  }
}

function mls_userMaySee($m, $uid) {
  return (($m["addr"] == "u" . $uid) || ($m["creator_id"] == "u" . $uid));
}

//***********
//***********
//***********
//конец Функции Переписки
//***********
//***********
//***********
//***********
//***********
//***********
//Функции Филивлов
//***********
//***********
//***********
function fils_get($id) {
  $filial = Filial::find($id);
  if ($filial["tm_special"]) {
    $filial["tm_special"] = @unserialize($filial["tm_special"]);
  } else {
    $filial["tm_special"] = array();
  }
  return $filial;
}

function fils_getworktime($fil, $day, &$start, &$end) {
  if (count($fil["tm_special"]) && isset($fil["tm_special"][$day])) {
    $start = $fil["tm_special"][$day]["s"];
    $end = $fil["tm_special"][$day]["e"];
    return $fil["tm_special"][$day]["w"];
  } else {
    $start = $fil["tm_open"];
    $end = $fil["tm_close"];
    return true;
  }
}

function show_percent($value) {
  return $value * 100;
}

//***********
//***********
//***********
//конец Функции Филивлов
//***********
//***********
//***********
//***********
//***********
//***********
//Функции Заказов
//***********
//***********
//***********

/**
 * @deprecated
 * @param $id
 * @return array
 */
function ord_get($id) {
  return Order::find($id);
}

/**
 * @deprecated
 * @param $cid
 * @return array
 */
function ord_getByClient($cid) {
  return Order::findBy(array(
    'klient_id' => intval($cid),
  ));
}

function ord_addFile($id, $user_id, $name, $size = '') {
  global $db;
  if ($size) {
    $names = array(
      "order_id", "creator_id", "created", "name", "size"
    );
    $values = array(
      $id, $user_id, mktime(), $name, $size
    );
    $db->Insert("order_files", $names, $values);
    return $db->InsertID;
  } else {
    return false;
  }
}

function ord_getFiles($id) {
  global $db;
  $ret = array();
  $db->Select("order_files", "*", "WHERE order_id=" . intval($id));
  if ($db->ResultCount) {
  }
  return $ret;
}

function get_debt_to_company($value, $row) {
  return number_format(calculate_debt_to_company($row['cost_kln'], $row['cost_auth'], $row['filial_id']), 2);
}

//***********
//***********
//***********
//конец Функции Заказов
//***********
//***********
//***********
//***********
//***********
//***********
//Функции Сотрудников
//***********
//***********
//***********
/**
 * @deprecated
 * @param $id
 * @return string
 */
function sotr_getFullName($id) {
  global $data_groups, $data_users;
  need_data('data_users');
  if (!$id || !isset($data_users[$id])) {
    return "Невозможно определить сотрудника";
  }
  return $data_groups[$data_users[$id]["group_id"]]["sname"] . " " . $data_users[$id]["fio"];
}

/**
 * @deprecated
 * @param $id
 * @return string
 */
function sotr_getFullNameWithLink($id) {
  return '<a href="?section=sotr&subsection=2&edit=' . $id . '">' . sotr_getFullName($id) . '</a>';
}

//***********
//***********
//***********
//конец Функции Сотрудников
//***********
//***********
//***********
//***********
//***********
//***********
//Функции клиентов
//***********
//***********
//***********

/**
 * @deprecated
 * @param $id
 * @return bool
 */
function kln_del($id) {
  return Client::delete($id);
}

/**
 * @deprecated
 * @param $id
 * @return array
 */
function kln_get($id) {
  return Client::find($id);
}

/**
 * @deprecated
 * @return array
 */
function kln_getlist() {
  if (is_director($_SESSION["user"]["data"]["id"])) {
    $clients = Client::findAll();
  } else {
    $clients = Client::findBy(array(
      'filial_id' => $_SESSION["user"]["data"]["filial_id"],
    ));
  }

  $ret = array();
  foreach($clients as $v) {
    $ret[$v["id"]] = $v["id"] . ". " . $v["fio"] . " (тел. " . $v["telnum"] . "; email: " . $v["email"] . ")";
  }
  return $ret;
}

/**
 * @deprecated
 * @return array
 */
function kln_getrawlist() {
  if ($_SESSION["user"]["data"]["group_id"] == 1 || $_SESSION["user"]["data"]["group_id"] == 0) {
    $sql = "SELECT * FROM " . TABLE_CLIENTS;
  } else {
    $sql = "SELECT * FROM " . TABLE_CLIENTS . " WHERE filial_id = " . db::input($_SESSION["user"]["data"]["filial_id"]);
  }
  return db::get_assoc_arrays($sql);
}

function kln_search_modal() {
  global $GUI, $kln_module_root, $kln_search_modal_form;
  $GUI->tmpls[] = $kln_module_root . "searchkln.tmpl.php";
  $frm = $GUI->ModalFormEx("Поиск клиента", 770, 400);
  $frm->Nosubmit = true;
  $GUI->Vars["kln_search_modal_form"] = $frm;
  $d = kln_getlist();
  $s = $frm->GridSelect(10, 50, 750, 270, array(
    array(
      "cap" => "Номер", "key" => "id", "width" => "40px; min-width: 40px;"
    ), array(
      "cap" => "Имя", "key" => "fio", "width" => "200px; min-width: 200px;"
    ), array(
      "cap" => "Почта", "key" => "email", "width" => "200px; min-width: 200px;"
    ), array(
      "cap" => "Телефон", "key" => "telnum", "width" => '150px; min-width: 150px;'
    ), array(
      "cap" => "Партнерский код", "key" => "referrer_code", "width" => '100px; min-width: 100px;'
    )
  ), kln_getrawlist());
  $frm->Label("№", 10, 5);
  $t1 = $frm->Text(10, 25, 40);
  $frm->Label("Имя", 55, 5);
  $t2 = $frm->Text(55, 25, 200);
  $frm->Label("Почта", 260, 5);
  $t3 = $frm->Text(260, 25, 200);
  $frm->Label("Телефон", 465, 5);
  $t4 = $frm->Text(465, 25, 150);

  $frm->Label("Партнерский код", 620, 5);
  $t5 = $frm->Text(620, 25, 110);

  $t1->AddJsEvent("keyup", $s->idname . "_sel_row = update_klient_search_filter('" . $s->idname . "', " . $s->idname . "_sel_row, '" . $t1->idname . "', '" . $t2->idname . "', '" . $t3->idname . "', '" . $t4->idname . "', '" . $t5->idname . "'); ");

  $t2->AddJsEvent("keyup", $s->idname . "_sel_row = update_klient_search_filter('" . $s->idname . "', " . $s->idname . "_sel_row, '" . $t1->idname . "', '" . $t2->idname . "', '" . $t3->idname . "', '" . $t4->idname . "', '" . $t5->idname . "'); ");

  $t3->AddJsEvent("keyup", $s->idname . "_sel_row = update_klient_search_filter('" . $s->idname . "', " . $s->idname . "_sel_row, '" . $t1->idname . "', '" . $t2->idname . "', '" . $t3->idname . "', '" . $t4->idname . "', '" . $t5->idname . "'); ");

  $t4->AddJsEvent("keyup", $s->idname . "_sel_row = update_klient_search_filter('" . $s->idname . "', " . $s->idname . "_sel_row, '" . $t1->idname . "', '" . $t2->idname . "', '" . $t3->idname . "', '" . $t4->idname . "', '" . $t5->idname . "'); ");

  $t5->AddJsEvent("keyup", $s->idname . "_sel_row = update_klient_search_filter('" . $s->idname . "', " . $s->idname . "_sel_row, '" . $t1->idname . "', '" . $t2->idname . "', '" . $t3->idname . "', '" . $t4->idname . "', '" . $t5->idname . "'); ");

  $b = $frm->Button("Выбрать", 200, 340, 80);
  $b->Event = 'var sr= ' . $s->idname . '_sel_row; if (sr != -1) {jQuery.modal.close(); var x = jQuery("#' . $s->idname . '_"+sr+"_0").text(); custom_klient_select_event(x);}';
  $b = $frm->Button("Отмена", 320, 340, 80);
  $b->Event = 'jQuery.modal.close();';
}

//***********
//***********
//***********
//Конец Функции клиентов
//***********
//***********
//***********
//***********
//***********
//***********
//Функции переписки
//***********
//***********
//***********
function _get_fmt_date($v) {
  if (intval($v) == 0) {
    return "не определено";
  }
  return date("d.m.Y", $v);
}

function _get_fmt_date_time($v) {
  if (intval($v) == 0) {
    return "не определено";
  }
  return date("d.m.Y H:i:s", $v);
}

function _get_ukname($v) {
  return mls_getAdrName($v);
}

function _set_row_color(&$Row) {
  if (!$Row["data"]["readed"]) {
    $Row["style"]["font-weight"] = "bold";
  }
}

function _get_prior_name($v, $k) {
  $out = "";
  $pr = array(
    0 => "низкий", 1 => "нормальный", 2 => "высокий"
  );
  $pr_c = array(
    0 => "<font color=blue>низкий</font>", 1 => "<font color=green>нормальный</font>", 2 => "<font color=red>высокий</font>"
  );
  if ($k["readed"]) {
    $out = $pr[$v];
  } else {
    $out = $pr_c[$v];
  }
  if ($k["needansv"]) {
    if (Message::findBy(array(
      'parent_id' => $k["id"],
    ))) {
      $out .= ", <font color=gray>ответ отправлен</font>";
    } else {
      $out .= ", <font color=red>ответ до " . date("d.m.y", $k["needansv"]) . "</font>";
    }
  }
  return $out;
}

function _get_message_num($v, $d) {
  $out = $v;
  // Если это в ответ, то показать номер письма
  if ($d["parent_id"]) {
    $out .= "<sup>(отв. на " . $d["parent_id"] . ")</sup>";
  }
  return $out;
}

function _get_readed($v) {
  if ($v) {
    return "";
  } else {
    return "не прочитано";
  }
}

//***********
//***********
//***********
//Конец Функции переписки
//***********
//***********
//***********
if (!function_exists('json_encode')) {
  function json_encode($data) {
    switch($type = gettype($data)) {
      case 'NULL':
        return 'null';
      case 'boolean':
        return ($data ? 'true' : 'false');
      case 'integer':
      case 'double':
      case 'float':
        return $data;
      case 'string':
        return '"' . addslashes($data) . '"';
      case 'object':
        $data = get_object_vars($data);
      case 'array':
        $output_index_count = 0;
        $output_indexed = array();
        $output_associative = array();
        foreach($data as $key => $value) {
          $output_indexed[] = json_encode($value);
          $output_associative[] = json_encode($key) . ':' . json_encode($value);
          if ($output_index_count !== null && $output_index_count++ !== $key) {
            $output_index_count = null;
          }
        }
        if ($output_index_count !== null) {
          return '[' . implode(',', $output_indexed) . ']';
        } else {
          return '{' . implode(',', $output_associative) . '}';
        }
      default:
        return ''; // Not supported
    }
  }
}

function validateEmail($email) {
  if (empty($email)) {
    return false;
  }

  return (bool)preg_match("/^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}$/", $email);
}

  /**
   * @param int $message_id Message id in TABLE_MESSAGES
   * @param string $receiver_email receiver's Email address
   * @param int $message_type тип сообщения, из набора EmailNotificationType::$NOTIFICATION_TYPES
   * @see EmailNotificationType
   * @return mixed
   */
function enqueue_message_to_email($message_id, $receiver_email, $notification_type) {
  assert( in_array($notification_type, EmailNotificationType::$NOTIFICATION_TYPES) );
  if (empty ($receiver_email)) {
    return false;
  }
  if(!EmailNotificationType::isPersistable($notification_type)) {
    return false;
  }
  return EmailNotification::create(array(
    'message_id'        => $message_id,
    'receiver_email'    => $receiver_email,
    'type'  => $notification_type,
  ));
}

  /**
   * @deprecated use Message::getReceiverEmailAndName() method instead
   * @see Message
   * @param $receiver_id
   * @return string|bool
   */
  function message_receiver_to_email($receiver_id) {
    if(strlen($receiver_id) <2 )
      return false;
    $receiver_type = substr($receiver_id, 0, 1);
    $id = substr($receiver_id, 1);
    $email = false;
    switch($receiver_type) {
      case 'u':
        need_data('data_users');
        global $data_users;
        if (!empty ($data_users[$id]['email'])) {
          $email =  $data_users[$id]['email'];
        }
        break;
      case 'k':
        $client = Client::find($id);
        if (!empty ($client['email']) )
          $email = $client['email'];
        break;
    }
    return $email;
  }

?>
