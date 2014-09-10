<?php
  /*******************************************************************************
   * Software: ERROR LOGGER                                                       *
   * Version:  1.1                                                                *
   * Date:     2013-06-05                                                         *
   * Author:   Wild THING   --- Ukraine                                           *
   * License:  Public                                                             *
   *                                                                              *
   *******************************************************************************/

namespace Components\Classes;

define('EMAIL_ERROR_LOG_RECIPIENTS', 'yonkon.ru@gmail.com;maxfroll@gmail.com');

class ErrorLogger {
  static $pattern_parts;
  static $patterns;
  static $hostname;

  public static function dumpCommented($var, $full_trace = true) {
    echo "<!-- \n" . self::obj_dump($var, true, $full_trace) . "-->";
  }

  public static function obj_dump($var, $return_as_string = false, $full_trace = false) {
    if (function_exists('debug_backtrace')) {
      $Tmp1 = debug_backtrace();
    } else {
      $Tmp1 = array(
        'file' => 'UNKNOWN FILE',
        'line' => 'UNKNOWN LINE',
      );
    }
    $var_value = "";
    $output = "<FIELDSET STYLE=\"font:normal 12px helvetica,arial; margin:10px;\"><LEGEND STYLE=\"font:bold 14px helvetica,arial\">Dump - " . $Tmp1[0]['file'] . " : " . $Tmp1[0]['line'] . "</LEGEND><PRE>\n";
    if ($return_as_string) {
      $var_value .= "\nDump - " . $Tmp1[0]['file'] . " : " . $Tmp1[0]['line'] . "\n";
    }
    if ($full_trace) {
      if ($return_as_string && $full_trace) {
        $var_value .= "\n" . self::trace_to_str($Tmp1) . "\n";
      } else {
        $output .= "<LEGEND STYLE=\"font:bold 14px helvetica,arial\">" . self::trace_to_str($Tmp1) . "</LEGEND>";
      }
    }
    if (is_bool($var)) {
      $var_value .= '(bool) ' . ($var ? 'true' : 'false');
    } elseif (is_null($var)) {
      $var_value .= 'null';
//    } elseif (is_array($var)) {
//      $var_value .= self::obj_dump($var, true);
    } else {
      $var_value .= htmlspecialchars(print_r($var, true));
    }
    $output .= $var_value . "</PRE></FIELDSET>\n\n";

    if ($return_as_string) {
      return $var_value;
    }
    echo $output;
  }

  public static function trace_to_str($tmp = '') {
    if (!$tmp) {
      $tmp = debug_backtrace();
    }
    $strLog = "Stack tracing:\n";
    for ($i = 1; $i < count($tmp); $i++) {
      $arr_error = $tmp[$i];
      $file = isset($arr_error['file']) ? $arr_error['file'] : 'Can`t detect file';
      $line = isset($arr_error['line']) ? $arr_error['line'] : 'Can`t detect line';
      $function = $arr_error['function'];
      $strLog .= "$file, function $function, line $line\n";
    }
    $strLog .= "----------------------------------------\n";

    return $strLog;
  }

  public static function trace() {
    $tmp = debug_backtrace();
    $trace_string = "Trace path:<br/>";
    for ($i = 2; $i < count($tmp); $i++) {
      $arr_error = $tmp[$i];
      $file = '`Can`t detect file`';
      if (isset($arr_error['file'])) {
        $file = $arr_error['file'];
      }
      $line = '`Can`t detect line`';
      if (isset($arr_error['line'])) {
        $line = $arr_error['line'];
      }
      $function = $arr_error['function'];
      $trace_string .= "<b>$file</b>, function <b>$function</b>, line <b>$line</b><br/>";
    }
    return $trace_string;
  }

  public static function all_error_handler($errno, $errstr, $errfile, $errline) {
    if (!error_reporting()) {
      return;
    } // if Not show error

    $aErrTypeToText = array(
      E_ERROR => 'ERROR',
      E_WARNING => 'WARNING',
      E_PARSE => 'PARSE',
      E_NOTICE => 'NOTICE',
      E_CORE_ERROR => 'CORE_ERROR',
      E_CORE_WARNING => 'CORE_WARNING',
      E_COMPILE_ERROR => 'COMPILE_ERROR',
      E_COMPILE_WARNING => 'COMPILE_WARNING',
      E_USER_ERROR => 'USER_ERROR',
      E_USER_WARNING => 'USER_WARNING',
      E_USER_NOTICE => 'USER_NOTICE',
    );

    $allowedErrType = array(
      E_ERROR,
      E_WARNING,
      E_PARSE,
//    E_NOTICE,
      E_CORE_ERROR,
      E_CORE_WARNING,
      E_COMPILE_ERROR,
      E_COMPILE_WARNING,
      E_USER_ERROR,
      E_USER_WARNING,
      E_USER_NOTICE,
    );

    if (PHP_VERSION >= 5.0) {
      $aErrTypeToText[E_STRICT] = 'STRICT';
//    $allowedErrType[] = E_STRICT;
    }
    if (PHP_VERSION >= 5.2) {
      $aErrTypeToText[E_RECOVERABLE_ERROR] = 'RECOVERABLE_ERROR ';
      $allowedErrType[] = E_RECOVERABLE_ERROR;
    }
    if (PHP_VERSION >= 5.3) {
      $aErrTypeToText[E_DEPRECATED] = 'DEPRECATED';
      $aErrTypeToText[E_USER_DEPRECATED] = 'USER_DEPRECATED';
      $allowedErrType[] = E_DEPRECATED;
      $allowedErrType[] = E_USER_DEPRECATED;
    }

    $error_string = '';
    $error_string .= "<b>" . $aErrTypeToText[$errno] . "</b>: $errstr in line $errline of file $errfile<br/>\n";
    $error_string .= self::trace();
    switch ($errno) {
      case E_STRICT: // ignore this type of error
      case E_NOTICE:
      case E_USER_NOTICE:
      default:
//        echo $error_string;
    }

    if (in_array($errno, $allowedErrType)) {
      ErrorLogger::send_email('Error report', $error_string, '', 'ERS_ERROR_TYPE_ERROR');
    }
//    die;
    if ($errno == E_ERROR || $errno == E_USER_ERROR) {
      die();
    }
  }

  public static function exception_handler($exception) {
    if (isset($exception->xdebug_message)) {
      $error_string = '<pre>' . $exception->xdebug_message . '</pre>';
    } else {
      $error_string = "<b>Uncaught exception:</b> " . $exception->getMessage() . "<br/>Trace path:<br/>";
      foreach ($exception->getTrace() as $arr_error) {
        $file = '`Can`t detect file`';
        if (isset($arr_error['file'])) {
          $file = $arr_error['file'];
        }
        $line = '`Can`t detect line`';
        if (isset($arr_error['line'])) {
          $line = $arr_error['line'];
        }
        $class = '`Can`t detect class`';
        if (isset($arr_error['class'])) {
          $class = $arr_error['class'];
        }
        $function = '`Can`t detect function`';
        if (isset($arr_error['function'])) {
          $function = $arr_error['function'];
        }
        $error_string .= "<b>" . $file . "</b>, method <b>" . $function . "</b> of class <b>" . $class . "</b>, line <b>" . $line . "</b><br/>";
      }
    }

    echo $error_string;
    ErrorLogger::send_email('Error report', $error_string, '', 'ERS_ERROR_TYPE_ERROR');
    exit;
  }

  static function send_email($title = 'Error handle', $data = '', $email = '', $error_type = 'ERS_ERROR_TYPE_INFO') {
    $aPatternParts = self::getPatternParts($title, $data, $error_type);
    self::add('send_email_error_log', $title, $data, $error_type);
    $aEmailList = array();
    if ($email != '') {
      $aEmailList = array($email);
    } else {
      if (defined('EMAIL_ERROR_LOG_RECIPIENTS')) {
        $aEmailList = explode(";", EMAIL_ERROR_LOG_RECIPIENTS);
      }
    }
    foreach ($aEmailList as $sEmail) {
      if (isset($aPatternParts['log']['DATA_DUMP']) && $aPatternParts['log']['DATA_DUMP'] != '' && $sEmail != '') {
        error_log(nl2br($aPatternParts['log']['DATE'] . " " . $aPatternParts['log']['TITLE'] . " on " . self::$hostname . $aPatternParts['log']['REQUEST_URI'] . "\n" . $aPatternParts['log']['HTTP_USER_AGENT'] . "\n" . $aPatternParts['log']['HTTP_REFERER'] . $aPatternParts['log']['SESSION_ID'] . "':" . "\n" . $aPatternParts['log']['DATA_DUMP'] . "\n"), 1, $sEmail, 'Content-type: text/html; charset=utf-8' . "\r\n");
      }
    }
  }

  static function create_path($path, $base) {
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
    foreach ($path_parts as $dir_name) {
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

  static function parsePattern($aParts, $sErrorType = 'default_pattern', $sOwnPattern = '') {
    self::$pattern_parts = array('%DATE%', '%TITLE%', '%SESSION_ID%', '%HTTP_REFERER%', '%REQUEST_URI%', '%DATA_DUMP%');
    self::$patterns['default_pattern'] = <<<TEXT_PATTERN
%DATE% %TITLE% %SESSION_ID% %HTTP_REFERER% %REQUEST_URI% %DATA_DUMP%
TEXT_PATTERN;

    if ($sOwnPattern != '') {
      $sPattern = $sOwnPattern;
    } elseif (isset(self::$patterns[$sErrorType])) {
      $sPattern = self::$patterns[$sErrorType];
    } else {
      $sPattern = self::$patterns['default_pattern'];
    }
    
    foreach ($aParts as $sPart => $sValue) {
      $sPattern = str_replace('%' . strtoupper(trim($sPart, '%')) . '%', $sValue, $sPattern);
    }

    //clean default parts
    foreach (self::$pattern_parts as $sPart) {
      $sPattern = str_replace($sPart, '', $sPattern);
    }

    return $sPattern;
  }

  static function getPatternParts($title, $data, $error_type) {
    $aPatternParts = array();

    $date = date("Y-m-d H:i:s");
    $aPatternParts['log']['DATE'] = '[' . $date . ']';
    $aPatternParts['ers']['DATE'] = '"' . $date . '"';
    $aPatternParts['log']['TITLE'] = $title;
    $aPatternParts['ers']['TITLE'] = '"' . $title . '"';

    //add session id
    $session_id = session_id();
    $aPatternParts['ers']['SESSION_ID'] = $aPatternParts['log']['SESSION_ID'] = '';
    if ($session_id != '') {
      $aPatternParts['ers']['SESSION_ID'] = '"' . $session_id . '"';
      $aPatternParts['log']['SESSION_ID'] = "; SESSION_ID = $session_id";
    }

    //add referer
    $referer = @$_SERVER['HTTP_REFERER'];
    $aPatternParts['ers']['HTTP_REFERER'] = $aPatternParts['log']['HTTP_REFERER'] = '';
    if ($referer != '') {
      $aPatternParts['ers']['HTTP_REFERER'] = '"' . $referer . '"';
      $aPatternParts['log']['HTTP_REFERER'] = "; HTTP_REFERER = $referer";
    }

    //add request uri
    $request_uri = @$_SERVER['REQUEST_URI'];
    $aPatternParts['ers']['REQUEST_URI'] = $aPatternParts['log']['REQUEST_URI'] = '';
    if ($request_uri != '') {
      $aPatternParts['ers']['REQUEST_URI'] = '"' . $request_uri . '"';
      $aPatternParts['log']['REQUEST_URI'] = "\n" . "REQUEST_URI = $request_uri";
    }

    //add user agent
    $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    $aPatternParts['ers']['HTTP_USER_AGENT'] = $aPatternParts['log']['HTTP_USER_AGENT'] = '';
    if ($request_uri != '') {
      $aPatternParts['ers']['HTTP_USER_AGENT'] = '"' . $user_agent . '"';
      $aPatternParts['log']['HTTP_USER_AGENT'] = "\n" . "HTTP_USER_AGENT = $user_agent";
    }

    //add description
    $aPatternParts['ers']['DATA_DUMP'] = $aPatternParts['log']['DATA_DUMP'] = '';
    if ($data != '') {
      $aPatternParts['ers']['DATA_DUMP'] = '"' . base64_encode(print_r($data, 1)) . '"';
      $aPatternParts['log']['DATA_DUMP'] = "\n" . print_r($data, 1);
    }

    //add error type
    $aPatternParts['ers']['ERROR_TYPE'] = '"' . $error_type . '"';

    return $aPatternParts;
  }

  static function add($file_name = 'default', $title = '', $data = '', $error_type = 'ERS_ERROR_TYPE_INFO') {
    /* * possible error types:
     * ERS_ERROR_TYPE_WARNING, ERS_ERROR_TYPE_ERROR, ERS_ERROR_TYPE_DEBUG, ERS_ERROR_TYPE_INFO, ERS_ERROR_TYPE_UNDEFINED
     * */

    $aPatternParts = self::getPatternParts($title, $data, $error_type);

    $log_string = "\n" . self::parsePattern($aPatternParts['log'], $error_type) . "\n";


    $file = DIR_FS_LOGFILES . $file_name;
    error_log($log_string, 3, $file . '.log');
    @chmod($file . '.log', 0666);

    if (defined('ENABLE_ERS') && ENABLE_ERS) {
      $ers_log_string = $aPatternParts['ers']['DATE'] . ';' . $aPatternParts['ers']['TITLE'] . ';' . $aPatternParts['ers']['SESSION_ID'] . ';' . $aPatternParts['ers']['HTTP_REFERER'] . ';' . $aPatternParts['ers']['REQUEST_URI'] . ';' . $aPatternParts['ers']['DATA_DUMP'] . ';' . $aPatternParts['ers']['ERROR_TYPE'] . "\n";
      error_log($ers_log_string, 3, $file . '.csv');
      @chmod($file . '.csv', 0666);
    }
  }
}