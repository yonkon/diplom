<?php
header('Access-Control-Allow-Origin: *');
require_once('includes/application_top.php');
//include_once "mysql.class.php";
//include_once "config/config.php";
//include_once "utils.php";
//include_once "default_data.php";
//include_once "gui/gui.php";
include_once DIR_FS_DOCUMENT_ROOT.DIRECTORY_SEPARATOR."diplom.api.class.php";

if (!function_exists('json_encode')) {
  function json_encode($data) {
    switch ($type = gettype($data)) {
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
        foreach ($data as $key => $value) {
          $output_indexed[] = json_encode($value);
          $output_associative[] = json_encode($key) . ':' . json_encode($value);
          if ($output_index_count !== NULL && $output_index_count++ !== $key) {
            $output_index_count = NULL;
          }
        }
        if ($output_index_count !== NULL) {
          return '[' . implode(',', $output_indexed) . ']';
        } else {
          return '{' . implode(',', $output_associative) . '}';
        }
      default:
        return ''; // Not supported
    }
  }
}

if (!function_exists('json_decode')) {
  function json_decode($json) {
    $comment = false;
    $out     = '$x=';
    for ($i=0; $i<strlen($json); $i++) {
      if (!$comment) {
        if (($json[$i] == '{') || ($json[$i] == '[')) {
          $out .= 'array(';
        }
        elseif (($json[$i] == '}') || ($json[$i] == ']')) {
          $out .= ')';
        }
        elseif ($json[$i] == ':') {
          $out .= '=>';
        }
        elseif ($json[$i] == ',') {
          $out .= ',';
        }
        elseif ($json[$i] == '"') {
          $out .= '"';
        }
        /*elseif (!preg_match('/\s/', $json[$i])) {
          return null;
        }*/
      }
      else $out .= $json[$i] == '$' ? '\$' : $json[$i];
      if ($json[$i] == '"' && $json[($i-1)] != '\\') $comment = !$comment;
    }
    eval($out. ';');
    return $x;
  }
}

if (!empty($_REQUEST['action'])) {
  $params = array();
  if (!empty($_REQUEST['params']) )
    if (is_array($_REQUEST['params'])) {
    $params = $_REQUEST['params'];
  } else {
      $params = json_decode($_REQUEST['params'], true);
    }
  echo json_encode(diplom::$_REQUEST['action']($params));
//  echo json_encode(diplom::$_REQUEST['action'](json_decode($_REQUEST['params'], true)));
}




