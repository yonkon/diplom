<?php

namespace Components\Classes;

use Components\Exceptions\DatabaseErrorException;

class db {
  private static $_link;

  /**
   * @param string $server
   * @param string $username
   * @param string $password
   * @param string $database
   *
   * @return resource
   * @throws \Components\Exceptions\DatabaseErrorException
   */
  public static function connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE) {

    self::$_link = @mysql_connect($server, $username, $password, true);

    if (self::$_link) {
      self::query("SET NAMES '" . DB_CHARSET . "'");
      self::select_db($database);
      return self::$_link;
    }
    throw new DatabaseErrorException;
  }

  private static function select_db($database) {
    return mysql_select_db($database, self::$_link);
  }

  public static function close() {
    return mysql_close(self::$_link);
  }

  public static function start() {
    $trans_lvl = self::$_link . '_trans_lvl';
    global $$trans_lvl;
    self::query('START TRANSACTION');
    $$trans_lvl++;
  }

  public static function commit() {
    $trans_lvl = self::$_link . '_trans_lvl';
    global $$trans_lvl;
    self::query('COMMIT');
    $$trans_lvl--;
  }

  public static function rollback() {
    $trans_lvl = self::$_link . '_trans_lvl';
    global $$trans_lvl;
    self::query('ROLLBACK');
    $$trans_lvl--;
  }

  public static function error($query, $errno, $error, $return = true) {
    $error_string = self::trace();
    $error_string .= '<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[SCRIPT STOPPED]</font></small><br><br></b></font>';
      if(!$return) {
          echo $error_string;
      }
    $trans_lvl = self::$_link . '_trans_lvl';
    global $$trans_lvl;
    while ($$trans_lvl) {
      self::rollback();
    }
      if(!$return) {
          die;
      }
      return $error_string;
  }

  public static function prep($sql) {
    $args = func_get_args();
    array_shift($args);
    if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
      $args = $args[0];
    }
    self::prep_callback($args, true);
    return preg_replace_callback('/(%d|%s|%%|%f|%b)/', '_db_prep_callback', $sql);
  }

  private static function prep_callback($match, $init = false) {
    static $args = null;
    if ($init) {
      $args = $match;
      return;
    }
    switch ($match[1]) {
      case '%d':
      case '%s':
      case '%b': // binary data
        return self::input(array_shift($args));
      case '%%':
        return '%';
      case '%f':
        return (float)array_shift($args);
    }
  }


  public static function query($query) {
    $result = mysql_query($query, self::$_link) or self::error($query, mysql_errno(self::$_link), mysql_error(self::$_link));

    return $result;
  }

  private static function perform($table, $data, $action = 'insert', $parameters = '') {
    if (substr($table, 0, strlen(TBL_PREF)) != TBL_PREF) {
      $table = TBL_PREF . $table;
    }
    $fields = array();
    $values = array();
    $fv_seq = array();

    if (is_array($data)) {
      foreach ($data as $field => $value) {
        $spec = substr($field, 0, 1);
        $field = "`" . ($spec == '@' ? substr($field, 1) : $field) . "`";
        $fields[] = $field;
        switch ((string)$value) {
          case 'now()':
            $value = 'NOW()';
            break;
          case 'null':
            $value = 'NULL';
            break;
          default:
            if ($spec != '@') {
              $value = "'" . self::input($value) . "'";
            }
            break;
        }
        $values[] = $value;
        $fv_seq[] = $field . ' = ' . $value;
      }
    }
    switch ($action) {
      case 'insert':
      case 'insert ignore':
        $query = strtoupper($action) . ' INTO `' . $table . '` (' . join(', ', $fields) . ') VALUES (' . join(', ', $values) . ')';
        break;

      case 'update':
        $query = 'UPDATE `' . $table . '` SET ' . join(', ', $fv_seq) . ($parameters != '' ? ' WHERE ' . $parameters : '');
        if (is_string($parameters) && $parameters == '') {
          self::error($query, 0, "Dangerous query! No condition for WHERE");
        }
        break;

      case 'replace':
        $query = 'REPLACE `' . $table . '` SET ' . join(', ', $fv_seq);
        break;

      case 'delete':
        $query = 'DELETE FROM `' . $table . '`' . ($parameters != '' ? ' WHERE ' . $parameters : '');
        if (is_string($parameters) && $parameters == '') {
          self::error($query, 0, "Dangerous query! No condition for WHERE");
        }
        break;
    }
    return self::query($query);
  }

  public static function update($table, $data, $parameters = '') {
    return self::perform($table, $data, 'update', $parameters);
  }

  public static function insert($table, $data) {
    return self::perform($table, $data, 'insert', '');
  }

  public static function insert_ignore($table, $data) {
    return self::perform($table, $data, 'insert ignore', '');
  }

  public static function replace($table, $data) {
    return self::perform($table, $data, 'replace', '');
  }

  public static function delete($table, $parameters = '') {
    return self::perform($table, null, 'delete', $parameters);
  }

  public static function fetch_array($query) {
      if($query == false) {
          return false;
      }
    return mysql_fetch_array($query, MYSQL_ASSOC);
  }

  public static function fetch_row($query) {
    return mysql_fetch_row($query);
  }

  public static function result($result, $row, $field = '') {
    return mysql_result($result, $row, $field);
  }

  public static function num_rows($query) {
    return mysql_num_rows($query);
  }

  public static function data_seek($query, $row_number) {
    return mysql_data_seek($query, $row_number);
  }

  public static function insert_id() {
    return mysql_insert_id(self::$_link);
  }

  public static function free_result($query) {
    return mysql_free_result($query);
  }

  public static function fetch_fields($query) {
    return mysql_fetch_field($query);
  }

  public static function affected_rows() {
    return mysql_affected_rows(self::$_link);
  }

  public static function output($string) {
    return stripslashes($string);
  }

  public static function input($string) {
    return addslashes($string);
  }

  public static function input_like($string) {
    return str_replace(array('\\', '%', "'", '"', '_'), array('\\\\', '\\%', "\\'", '\\"', '\\_'), $string);
  }

  public static function prepare_input($string) {
    if (is_string($string)) {
      return trim(stripslashes($string));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = self::prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

  public static function get_single_value($sql) {
    $res = self::query($sql);
    if ($res && $row = self::fetch_row($res)) {
      return $row[0];
    }
    return null;
  }

  public static function get_arrays($sql) {
    $result = array();
    $res = self::query($sql);
    while ($row = self::fetch_array($res)) {
      $result[] = $row;
    }
    return $result;
  }

  public static function get_assoc_arrays($sql) {
    $result = array();
    $res = self::query($sql);
    while ($row = self::fetch_array($res)) {
      reset($row);
      $result[current($row)] = $row;
    }
    return $result;
  }

  public static function get_single_row($sql) {
    $res = self::query($sql);
    return self::fetch_array($res);
  }

  public static function get_select($sql, $sName, $empty = '', $selected = '', $params = '') {
    $result = '<select name="' . $sName . '" ' . $params;
    $result .= '>';
    if ($empty) {
      $result .= '<option value="0">' . $empty . '</option>';
    }
    $res = self::query($sql);
    while ($row = mysql_fetch_row($res)) {
      if ($row[0] == $selected) {
        $result .= '<option value="' . $row[0] . '" selected="selected">' . $row[1] . '</option>';
      } else {
        $result .= '<option value="' . $row[0] . '">' . $row[1] . '</option>';
      }
    }
    $result .= '</select>';
    return $result;
  }

  /*
   * returns associative array of kind key => value where key is first column in query and value - second one
   * all other columns won't be returned!
   */
  public static function get_assoc($sql) {
    $res = self::query($sql);
    $a = array();
    while ($row = self::fetch_row($res)) {
      $a[$row[0]] = $row[1];
    }
    return $a;
  }

  public static function get_single_values_string($sql, $sep = ', ') {
    $buf = array();
    $res = self::query($sql);
    while ($row = self::fetch_row($res)) {
      $buf[] = $row[0];
    }
    return join($sep, $buf);
  }

  public static function get_single_values_array($sql) {
    $result = array();
    $res = self::query($sql);
    while ($row = self::fetch_row($res)) {
      $result[] = $row[0];
    }
    return $result;
  }

  private static function trace() {
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

  public static function tempTable($src, $dst) {
    self::query("CREATE TEMPORARY TABLE " . TBL_PREF . $dst . " LIKE " . $src);
    self::query("INSERT INTO " . TBL_PREF . $dst . " SELECT * FROM " . $src);
  }

  public static function truncate($tbl_name) {
    self::query("TRUNCATE TABLE " . $tbl_name);
  }

//  var $tables = array();
//  var $link = false;
//  var $basename = "";
//  var $base = false;
//  var $_res = false;
//  var $Result = array();
//  var $ResultCount = 0;
//  var $Error = "";
//  var $InsertID = 0;
//
//  function __construct($host, $user, $pwd, $dbname = "", ) {
//    global self::$_link;
//
//    $this->link = self::$_link = mysql_connect($host, $user, $pwd);
//    if (!$this->link) {
//      $this->Error = "cant connect";
//      return;
//    }
//    if (strlen($dbname)) {
//      $this->SelectDB($dbname);
//    }
//  }
//
//  function Clear() {
//    $this->Result = array();
//    $this->ResultCount = 0;
//  }
//
//  function SelectDB($nm) {
//    if (!$this->link) {
//      $this->Error = "no connection";
//      return;
//    }
//    $this->base = false;
//    $this->base = mysql_select_db($nm, $this->link);
//
//    if ($this->base) {
//      $this->basename = $nm;
//
//      if (isset($_SESSION["mysqldb_" . $nm . "_struct"])) {
//        $this->tables = $_SESSION["mysqldb_" . $nm . "_struct"];
//      } else {
//        $res = mysql_query("SHOW TABLES FROM " . $this->basename, $this->link);
//        while ($row = mysql_fetch_row($res)) {
//          $this->tables[$row[0]] = array();
//          $res1 = mysql_query("SHOW COLUMNS FROM " . $row[0], $this->link);
//          while ($row1 = mysql_fetch_row($res1)) {
//            $t = 0;
//            if (strpos(strtolower($row1[1]), "text") !== false) {
//              $t = 1;
//            }
//            if (strpos(strtolower($row1[1]), "varchar") !== false) {
//              $t = 1;
//            }
//            if (strpos(strtolower($row1[1]), "blob") !== false) {
//              $t = 2;
//            }
//            $this->tables[$row[0]][$row1[0]] = $t;
//          }
//          mysql_free_result($res1);
//        }
//        mysql_free_result($res);
//
//        $_SESSION["mysqldb_" . $nm . "_struct"] = $this->tables;
//      }
//    } else {
//      $this->basename = "";
//      $this->Error = "cant select base";
//    }
//  }
//
//  function query($q) {
//    $this->_res = false;
//    if (!$this->link) {
//      $this->Error = "no connection";
//      return;
//    }
//
//    $this->_res = mysql_query($q);
//    $this->Error = mysql_error();
//    //print "<div>".$q."</div>";
//  }
//
//  function Delete($tbl_name, $dop) {
//    $this->Clear();
//
//    if (!$this->base) {
//      $this->Error = "no database";
//      return;
//    }
//    if (!isset($this->tables[TBL_PREF . $tbl_name])) {
//      $this->Error = "table '$tbl_name' was not found";
//      return;
//    }
//    $this->query("DELETE FROM " . TBL_PREF . $tbl_name . " " . $dop);
//  }
//
//  function Truncate($tbl_name) {
//    $this->Clear();
//
//    if (!$this->base) {
//      $this->Error = "no database";
//      return;
//    }
//    if (!isset($this->tables[TBL_PREF . $tbl_name])) {
//      $this->Error = "table '$tbl_name' was not found";
//      return;
//    }
//    $this->query("TRUNCATE TABLE " . TBL_PREF . $tbl_name);
//  }
//
//  function Select($tbl_name, $fields, $dop = "") {
//    $this->Clear();
//
//    if (!$this->base) {
//      $this->Error = "no database";
//      return;
//    }
//    if (!isset($this->tables[TBL_PREF . $tbl_name])) {
//      $this->Error = "table '$tbl_name' was not found";
//      return;
//    }
//
//    $this->Result = array();
//    $this->query("SELECT " . $fields . " FROM " . TBL_PREF . $tbl_name . " " . $dop);
//    if ($this->_res) {
//      while ($row = mysql_fetch_assoc($this->_res)) {
//        $this->Result[] = $row;
//      }
//      mysql_free_result($this->_res);
//    }
//    $this->ResultCount = count($this->Result);
//  }
//
//  function Update($tbl_name, $fld_nm, $fld_vl, $dop) {
//    $this->Clear();
//
//    if (!$this->base) {
//      $this->Error = "no database";
//      return;
//    }
//    if (!isset($this->tables[TBL_PREF . $tbl_name])) {
//      $this->Error = "table '$tbl_name' was not found";
//      return;
//    }
//
//    $nm = array();
//    $vl = array();
//
//    if (is_array($fld_nm)) {
//      $nm = $fld_nm;
//    } else {
//      $nm[] = $fld_nm;
//    }
//
//    if (is_array($fld_vl)) {
//      $vl = $fld_vl;
//    } else {
//      $vl[] = $fld_vl;
//    }
//
//    if (count($vl) != count($nm)) {
//      $this->Error = "count vals != count params";
//      return;
//    }
//    ;
//
//    $q = "";
//    foreach ($nm as $k => $v) {
//
//      if (!isset($this->tables[TBL_PREF . $tbl_name][$v])) {
//        $this->Error = "column " . $v . " not found";
//        return;
//      }
//
//      if ($this->tables[TBL_PREF . $tbl_name][$v]) {
//        $val = "'" . addslashes($vl[$k]) . "'";
//      } else {
//        // dig
//        $val = $vl[$k];
//      }
//
//      if ($q) {
//        $q .= ",";
//      }
//      $q .= "`" . $v . "`=" . $val;
//    }
//
//    $this->query("UPDATE " . TBL_PREF . $tbl_name . " SET " . $q . " " . $dop);
//  }
//
//  function Insert($tbl_name, $fld_nm, $fld_vl) {
//    $this->Clear();
//
//    if (!$this->base) {
//      $this->Error = "no database";
//      return;
//    }
//    if (!isset($this->tables[TBL_PREF . $tbl_name])) {
//      $this->Error = "table '$tbl_name' was not found";
//      return;
//    }
//
//    $nm = array();
//    $vl = array();
//
//    if (is_array($fld_nm)) {
//      $nm = $fld_nm;
//    } else {
//      $nm[] = $fld_nm;
//    }
//
//    if (is_array($fld_vl)) {
//      $vl = $fld_vl;
//    } else {
//      $vl[] = $fld_vl;
//    }
//
//    if (count($vl) != count($nm)) {
//      $this->Error = "count vals != count params";
//      return;
//    }
//
//    foreach ($nm as $k => $v) {
//
//      if (!isset($this->tables[TBL_PREF . $tbl_name][$v])) {
//        $this->Error = "column " . $v . " not found";
//        return;
//      }
//
//      if ($this->tables[TBL_PREF . $tbl_name][$v]) {
//        //text
//        $vl[$k] = "'" . str_replace("'", '"', $vl[$k]) . "'";
//      }
//    }
//
//    $this->query("LOCK TABLES " . TBL_PREF . $tbl_name . " WRITE");
//    $this->query("INSERT INTO " . TBL_PREF . $tbl_name . " (`" . implode("` , `", $nm) . "`) VALUES (" . implode(",", $vl) . ")");
//    $this->InsertID = mysql_insert_id($this->link);
//    $this->query("UNLOCK TABLES");
//  }
//
//  function TmpTable($src, $dst) {
//    if (isset($this->tables[TBL_PREF . $src])) {
//      $this->Error = "";
//      $this->query("CREATE TEMPORARY TABLE " . TBL_PREF . $dst . " LIKE " . TBL_PREF . $src);
//      $this->query("INSERT INTO " . TBL_PREF . $dst . " SELECT * FROM " . TBL_PREF . $src);
//      // add info to tables
//      $this->tables[TBL_PREF . $dst] = $this->tables[TBL_PREF . $src];
//    } else {
//      $this->Error = "table " . $src . " not exists";
//    }
//  }
//
//  function db_get_select($sql, $sName, $empty = '', $selected = '', $params = '') {
//    $result = '<select name="' . $sName . '" ' . $params;
//    $result .= '>';
//    if ($empty) {
//      $result .= '<option value="0">' . $empty . '</option>';
//    }
//    $this->query($sql);
//    if ($this->_res) {
//      while ($row = mysql_fetch_row($this->_res)) {
//        if ($row[0] == $selected) {
//          $result .= '<option value="' . $row[0] . '" selected="selected">' . $row[1] . '</option>';
//        } else {
//          $result .= '<option value="' . $row[0] . '">' . $row[1] . '</option>';
//        }
//      }
//    }
//    $result .= '</select>';
//    return $result;
//  }
//
//  function db_num_rows($self::query) {
//    return mysql_num_rows($self::query);
//  }
//
//  function db_fetch_row($self::query) {
//    return mysql_fetch_row($self::query);
//  }
//
//  function &db_get_single_values_array($sql) {
//    $result = array();
//    $this->query($sql);
//    if ($this->_res) {
//      while ($row = $this->db_fetch_row($this->_res)) {
//        $result[] = $row[0];
//      }
//    }
//    return $result;
//  }
//
//  function db_fetch_array($self::query) {
//    return mysql_fetch_array($self::query, MYSQL_ASSOC);
//  }
//
//  function db_get_single_value($sql) {
//    $this->query($sql);
//    if ($this->Error) {
//      return $this->Error;
//    }
//    if ($this->_res) {
//      if ($row = $this->db_fetch_row($this->_res)) {
//        return $row[0];
//      }
//    }
//    return null;
//  }
}