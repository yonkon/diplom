<?php

use Components\Classes\db;

// Работа с наборами доступна, если можно сохранять в базе.
define("CGUI_FILTERS_DEBUG", 1);
/// Переменная в фильтре - массив типа
class CGUI_FilterAbstract {
  var $name = "default";

  var $id;

  var $value = 0;

  var $keyid;

  var $updated = false;

  var $collection;

  var $hidden = false;

  var $defvalue = 0;

  var $enabled = 1;

  var $alias = '';

  function Filtering() {
  }

  function GetHTML($readonly = false) {
  }

  function Configure($data) {
    if (isset($data[0])) {
      $this->value = $data[0];
    }
  }

  function getFilterString($alias) {
    return '';
  }
}

///////////////////////////
class CGUI_FilterSelect extends CGUI_FilterAbstract {
  private $items;

  public $multisel = false;

  public $multisel_size = 3;

  public $multisel_on = false;

  private $multivals = array();

  function SetSelectData($data, $key = "") {
    $frst = true;
    $this->items = array();
    foreach($data as $k => $d) {
      if ($key) {
        $v = $d[$key];
      } else {
        $v = $d;
      }
      $this->items[$k] = $v;
      if ($frst) {
        $frst = false;
        $this->value = $k;
        $this->defvalue = $k;
      }
    }
  }

  function Filtering() {
    if ($this->keyid) {
      if (is_array($this->value)) {
        $q = "";
        foreach($this->value as $v) {
          if ($q) {
            $q .= "AND";
          }
          $q .= "(" . $this->keyid . " != " . intval($v) . ")";
        }
      } else {
        $q = $this->keyid . " != " . intval($this->value);
      }
      db::delete($this->collection->DstTable, $q);
    }
  }

  function GetHTML($readonly = false) {
    $idname = $this->collection->idname;
    $out = "";
    if ($readonly) {
      foreach($this->items as $k => $i) {
        if (is_array($this->value) && in_array($k, $this->value)) {
          if (!empty($out)) {
            $out .= ' + ';
          }
          $out .= "<b>" . $i . "</b>" . "\n";
        } elseif (!is_array($this->value) && $k == $this->value) {
          $out .= "<b>" . $i . "</b>" . "\n";
          break;
        }
      }
    } else {
      if ($this->multisel_on) {
        $out .= "<select multiple size='" . $this->multisel_size . "' name='" . $idname . "_fltr_" . $this->id . "_val[0][]' onchange='cgui_filters_submit_form(\"" . $this->collection->idname . "\")'>" . "\n";
      } else {
        $out .= "<select name='" . $idname . "_fltr_" . $this->id . "_val[0]' onchange='cgui_filters_submit_form(\"" . $this->collection->idname . "\")'>" . "\n";
      }
      foreach($this->items as $k => $i) {
        $out .= "<option ";
        if (($this->multisel_on && in_array($k, $this->value)) || (!$this->multisel_on && ($this->value == $k))) {
          $out .= "selected ";
        }
        $out .= "value='" . $k . "'>" . $i . "</option>" . "\n";
      }
      $out .= "</select>";
      if ($this->multisel) {
        $out .= "<input ";
        if ($this->multisel_on) {
          $out .= "checked ";
        }
        $out .= "type='checkbox' name='" . $idname . "_fltr_" . $this->id . "_val[1]' onchange='cgui_filters_submit_form(\"" . $this->collection->idname . "\")'>Мультивыбор " . "\n";
      }
    }
    return $out;
  }

  function getFilterString($alias) {
    $alias .= '.';
    if (is_array($this->value) && count($this->value)) {
      $q = "(";
      $temp = '';
      foreach($this->value as $v) {
        if ($temp) {
          $temp .= " OR ";
        }
        $temp .= $alias . $this->keyid . " = " . intval($v);
      }
      $q .= $temp . ")";
    } else {
      $q = $alias . $this->keyid . " = " . intval($this->value);
    }
    return $q;
  }

  function Configure($data) {
    if (isset($data[0])) {
      $this->value = $data[0];
    }
    if (isset($data[1])) {
      $this->multisel_on = $data[1];
    }
    if ($this->multisel_on && !is_array($this->value)) {
      $this->value = array($this->value);
    } elseif (!$this->multisel_on && is_array($this->value)) {
      $this->value = $this->value[0];
    }
  }
}

///////////////////////////
class CGUI_FilterDate extends CGUI_FilterAbstract {
  private $date1;

  private $date2;

  public $use_mysql_timestamp = false;

  function __construct() {
    $this->date1 = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
    $this->date2 = 0;
  }

  function Filtering() {
    if ($this->keyid && $this->date1) {
      if (!empty($this->alias)) {
        $field = '`' . $this->alias . '`.`' . $this->keyid . '`';
      } else {
        $field = '`' . $this->keyid . '`';
      }
      $date1 = $this->date1;
      if ($this->date2) {
        $date2 = $this->date2 + (24 * 60 * 60) - 1;
        if ($this->use_mysql_timestamp) {
          $date2 = date('Y-m-d H:i:s', $date2);
          $date1 = date('Y-m-d H:i:s', $date1);
        }
        $q = "(" . $field . " < '" . $date1 . "') OR (" . $field . " > '" . $date2 . "')";
      } else {
        if ($this->use_mysql_timestamp) {
          $date1 = date('Y-m-d H:i:s', $date1);
        }
        $q = $field . " < '" . $date1 . "'";
      }
      db::delete($this->collection->DstTable, $q);
    }
  }

  function getFilterString($alias, $key = null, $use_mysql_timestamp = false) {
    $alias .= '.';
    if (is_null($key)) {
      $key = $this->keyid;
    }
    if ($key && $this->date1) {
      $date1 = $this->date1;
      if ($this->date2) {
        $date2 = $this->date2 + (24 * 60 * 60) - 1;
        if ($this->use_mysql_timestamp || $use_mysql_timestamp) {
          $date2 = date('Y-m-d H:i:s', $date2);
          $date1 = date('Y-m-d H:i:s', $date1);
        }
        $q = "(" . $alias . $key . " > '" . $date1 . "' AND " . $alias . $key . " < '" . $date2 . "')";
      } else {
        if ($this->use_mysql_timestamp || $use_mysql_timestamp) {
          $date1 = date('Y-m-d H:i:s', $date1);
        }
        $q = $alias . $key . " > '" . $date1 . "'";
      }
    } else {
      $q = 1;
    }
    return $q;
  }

  function GetHTML($readonly = false) {
    page_scriptNeed("calendar.js");
    $idname = $this->collection->idname;
    $out = "с <input style='width: 80px' type='text' name='" . $idname . "_fltr_" . $this->id . "_val[0]' value='";
    if ($this->date1) {
      $out .= date("d-m-Y", $this->date1);
    }
    $out .= "' onclick='event.cancelBubble=true; this.select(); lcs(this, 0)' onfocus='this.select(); lcs(this, 0);'> " . "\n" . "по <input style='width: 80px' type='text' name='" . $idname . "_fltr_" . $this->id . "_val[1]' value='";
    if ($this->date2) {
      $out .= date("d-m-Y", $this->date2);
    }
    $out .= "' onclick='event.cancelBubble=true; this.select(); lcs(this, 0)' onfocus='this.select(); lcs(this, 0);'>" . "\n";

    if (!$readonly) {
        $out .= "<input type='button' value='Применить' onclick='cgui_filters_submit_form(\"" . $this->collection->idname . "\")'>" . "\n";
    }
    return $out;
  }

  function Configure($data) {
    if (!isset($data[0]) || !isset($data[1])) {
      return;
    }
    if (CGUI_FILTERS_DEBUG) {
      $this->collection->_gui->DBG("> FilterDate '" . $this->name . "' > Configure");
    }
    if (utils_is_id_date($data[0])) {
      $d1 = utils_cvt_date2i($data[0]);
    } else {
      $d1 = intval($data[0]);
    }
    if (utils_is_id_date($data[1])) {
      $d2 = utils_cvt_date2i($data[1]);
    } else {
      $d2 = intval($data[1]);
    }
    if (!$d1) {
      $d2 = 0;
    } else if ($d2 && ($d2 < $d1)) {
      $d2 = $d1;
    }
    $this->date1 = $d1;
    $this->date2 = $d2;
  }

  public function getDataFrom() {
    return $this->date1;
  }

  public function getDataTo() {
    return $this->date2;
  }
}

///////////////////////////
class CGUI_FilterInteger extends CGUI_FilterAbstract {
  function __construct() {
    $this->value = 0;
    $this->hidden = true;
  }

  function Filtering() {
    if ($this->keyid) {
      db::delete($this->collection->DstTable, $this->keyid . " != " . $this->value);
    }
  }

  function SetSettings($data) {
    $this->value = @$data["val"];
  }

  function GetHTML($readonly = false) {
    return "<b>" . intval($this->value) . "</b>" . "\n";
  }
}

class CGUI_FilterIntegerRange extends CGUI_FilterAbstract {
  private $value_from;

  private $value_to;

  function __construct() {
    $this->value_from = 0;
    $this->value_to = 0;
    $this->hidden = false;
  }

  function Filtering() {
    if ($this->keyid) {
      if (empty($this->value_from) && empty($this->value_to)) {
        return false;
      }
      if (is_numeric($this->value_from) && $this->value_from >= 0) {
        if (is_numeric($this->value_to) && $this->value_to >= $this->value_from) {
          $where = $this->keyid . " NOT BETWEEN " . $this->value_from . " AND " . $this->value_to;
        } else {
          $where = $this->keyid . " < " . $this->value_from;
        }
        db::delete($this->collection->DstTable, $where);
      }
    }
  }

  function Configure($data) {
    if (!isset($data[0]) || !isset($data[1])) {
      return;
    }
    $this->value_from = $data[0];
    $this->value_to = $data[1];
  }

  function GetHTML($readonly = false) {
    $idname = $this->collection->idname;
    $out = "с <input style='width: 80px' type='text' name='" . $idname . "_fltr_" . $this->id . "_val[0]' value='";
    if ($this->value_from) {
      $out .= $this->value_from;
    } else {
      $out .= 0;
    }
    $out .= "' />";
    $out .= "по <input style='width: 80px' type='text' name='" . $idname . "_fltr_" . $this->id . "_val[1]' value='";
    if ($this->value_to) {
      $out .= $this->value_to;
    } else {
      $out .= 0;
    }
    $out .= "' />";
    $out .= "<input type='button' value='Применить' onclick='cgui_filters_submit_form(\"" . $idname . "\")'>" . "\n";
    return $out;
  }
}

class CGUI_FilterFirstBiggerSecond extends CGUI_FilterAbstract {
  public $firstField;

  public $secondField;

  function __construct() {
    $this->firstField = '';
    $this->secondField = '';
    $this->hidden = true;
  }

  function Filtering() {
    if ($this->keyid) {
      if (empty($this->firstField) && empty($this->secondField)) {
        return false;
      }
      if (empty($this->firstField)) {
        $this->firstField = $this->keyid;
      }
      $where = $this->firstField . ' < ' . $this->secondField . ' OR ' . $this->firstField . ' = ' . $this->secondField;
      db::delete($this->collection->DstTable, $where);
    }
  }
}

///////////////////////////
class CGUI_FilterCurDate extends CGUI_FilterAbstract {
  function __construct() {
    $this->value = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
  }

  function Filtering() {
    if ($this->keyid && $this->value) {
      db::delete($this->collection->DstTable, $this->keyid . " != " . $this->value);
    }
  }
}

///////////////////////////
class CGUI_FilterLastDate extends CGUI_FilterAbstract {
  function __construct() {
    $this->value = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
  }

  function Filtering() {
    if ($this->keyid && $this->value) {
      db::delete($this->collection->DstTable, $this->keyid . " >= " . $this->value);
    }
  }
}

///////////////////////////
class CGUI_FilterCurMonth extends CGUI_FilterAbstract {
  var $v1;

  var $v2;

  function __construct() {
    $this->v1 = mktime(0, 0, 0, date("m"), 1, date("Y"));
    $this->v2 = mktime(0, 0, 0, date("m"), date("t"), date("Y"));
  }

  function Filtering() {
    if ($this->keyid) {
      db::delete($this->collection->DstTable, "(" . $this->keyid . " < " . $this->v1 . ") OR (" . $this->keyid . " > " . $this->v2 . ")");
    }
  }
}

class CGUI_FilterLikeText extends CGUI_FilterAbstract {
	var $text = null;
	
	function __construct(){}
	
	function Filtering()
	{
		if (!is_null($this->text))
			db::delete($this->collection->DstTable, "NOT (" . $this->keyid . " LIKE '" . mysql_escape_string($this->text) . "')" );
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
class CGUI_FilterColUsedFilter {
  public $filter;

  public $set;

  public $fix_pos = true; // Пожет быть удален из набора
  public $fix_state = true; // Может быть выключен или выбрано другое значение
  public $readonly = false; // Может быть выключен или выбрано другое значение
  public $enabled = true;

  public $conf = array();

  function GetHTML() {
    $out = "<tr><td>" . "\n";
    //on/off
    if ($this->fix_state) {
      $out .= "<input type='checkbox' checked disabled>" . "\n";
    } else {
      $out .= "<input type='checkbox'";
      if ($this->enabled) {
        $out .= " checked ";
      }
      $out .= "onchange='cgui_filters_change_state(" . $this->filter->id . ", \"" . $this->set->collection->uri . "\", \"" . $this->set->collection->idname . "_chng_fltr\")'>" . "\n";
    }
    $out .= "</td><td>" . "\n";
    // name
    $out .= $this->filter->name;
    $out .= "</td><td>" . "\n";
    //filter html
    $this->filter->Configure($this->conf);
    $out .= $this->filter->GetHTML($this->readonly);
    $out .= "</td><td>" . "\n";
    //delete
    if (!$this->fix_pos) {
      $out .= "<a class='cgui_filters_remove_link' style='margin-left:10px' href='#' onclick='cgui_filters_remove_fltr(" . $this->filter->id . ", \"" . $this->set->collection->uri . "\", \"" . $this->set->collection->idname . "_rem_fltr\"); return false;'>убрать</a>" . "\n";
    }
    $out .= "</td></tr>" . "\n";
    return $out;
  }

  function SaveBaseConf() {
    $m = array();
    $m["en"] = $this->enabled;
    $m["fid"] = $this->filter->id;
    $m["conf"] = $this->conf;
    $m["fixpos"] = $this->fix_pos;
    $m["fixst"] = $this->fix_state;
    return $m;
  }

  function LoadBaseConf($data) {
    if (isset($data["en"])) {
      $this->enabled = $data["en"];
    }
    if (isset($data["fixpos"])) {
      $this->fix_pos = $data["fixpos"];
    }
    if (isset($data["fixst"])) {
      $this->fix_state = $data["fixst"];
    }
    if (isset($data["conf"])) {
      if (CGUI_FILTERS_DEBUG) {
        $this->set->collection->_gui->DBG("> LOAD CONFIG FROM BASE to '" . $this->set->name . " - " . $this->filter->name . "'");
      }
      $this->SetConf($data["conf"]);
    }
  }

  function SetConf($conf) {
    $this->conf = $conf;
  }

  function Filtering() {
    $this->filter->Configure($this->conf);
    $this->filter->Filtering();
  }

  function Update() {
    if (CGUI_FILTERS_DEBUG) {
      $this->set->collection->_gui->DBG("> FilterColUsedFilter > Update");
    }
    $arrname = $this->set->collection->idname . "_fltr_" . $this->filter->id . "_val";
    if (isset($_REQUEST[$arrname]) && is_array($_REQUEST[$arrname])) {
      $this->filter->Configure($_REQUEST[$arrname]);
      $this->conf = $_REQUEST[$arrname];
      return true;
    }
    return false;
  }
}

////////////////////////////////////////////////////////////////////////////////////////////////
class CGUI_FilterCollectionSet {
  public $name = "";

  public $id;

  public $collection;

  public $used_filters = array();

  function __construct($name) {
    $this->name = $name;
  }

  function FilterInSet($id) {
    foreach($this->used_filters as $uf) {
      if ($uf->filter->id == $id) {
        return true;
      }
    }
    return false;
  }

  function getFilter($id) {
    foreach($this->used_filters as $uf) {
      if ($uf->filter->id == $id) {
        return $uf;
      }
    }
    return false;
  }

  function ChangeStateFilter($id) {
    if ($this->FilterInSet($id)) {
      foreach($this->used_filters as $f) {
        if ($f->filter->id == $id) {
          $f->enabled = !$f->enabled;
          return;
        }
      }
    }
  }

  function LoadBaseConf($data) {
    if (!is_array($data)) {
      return;
    }
    if (isset($data["name"])) {
      $this->name = $data["name"];
    }
    if (isset($data["filters"])) {
      foreach($data["filters"] as $f) {
        $this->UseFilterBase($f);
      }
    }
  }

  function SaveBaseConf() {
    if (CGUI_FILTERS_DEBUG) {
      $this->collection->_gui->DBG("> FilterColletionSet '" . $this->name . "'> SaveBaseConf");
    }
    $m = array();
    $m["name"] = $this->name;
    $m["filters"] = array();
    foreach($this->used_filters as $f) {
      $m["filters"][] = $f->SaveBaseConf();
    }
    return $m;
  }

  function Filtering() {
    foreach($this->used_filters as $f) {
      if ($f->enabled) {
        $f->Filtering();
      }
    }
  }

  function UseFilter($id, $maydel = true, $maydis = true, $readonly = false) {
    if ($this->collection->GetFilter($id)) {
      if ($this->FilterInSet($id)) {
        return $this->getFilter($id);
      }
      $f = new CGUI_FilterColUsedFilter();
      $f->set = $this;
      $f->filter = $this->collection->GetFilter($id);
      $f->fix_pos = $maydel;
      $f->fix_state = $maydis;
      $f->readonly = $readonly;
      $this->used_filters[] = $f;
      return $f;
    }
    return false;
  }

  function UnuseFilter($id) {
    foreach($this->used_filters as $k => $f) {
      if ($f->filter->id == $id) {
        unset($this->used_filters[$k]);
        return;
      }
    }
  }

  function UseFilterBase($data) {
    // data[en,fid,conf]
    if (isset($data["fid"])) {
      $f = $this->UseFilter($data["fid"], false, false);
      if ($f) {
        $f->LoadBaseConf($data);
      }
    }
  }

  function GetHTML() {
    $out = "";
    $out .= "<div style='color:gray'>Фильтры:</div>" . "\n" . "<form id='" . $this->collection->idname . "_form' method='post' action='" . $this->collection->uri . "'><table>" . "\n";
    foreach($this->used_filters as $f) {
      $out .= $f->GetHTML() . "\n";
    }
    $out .= "</table></form>" . "\n";
    return $out;
  }

  function UpdateFltr() {
    $upd = false;
    foreach($this->used_filters as $f) {
      $upd |= $f->Update();
    }
    return $upd;
  }
}

////////////////////////////////////////////////////////////////////////////////////////////////
class CGUI_FilterCollection {
  // private
  private $ses_ident;

  private $cfg_table = "";

  private $cfg_field = "";

  private $req = array(); // массив параметр-значение из строки запроса
  private $filters = array(); // массив классов фильтров
  private $curSetType = 0; // 0-выкл 1-стандарт 2-пользователь 3-временный
  private $curSet; // текущий набор
  private $userSets = array(); // наборы пользователя
  private $stdSets = array(); // наборы стандартные
  private $set_enable = false;

  //public
  public $_gui;

  public $idname;

  public $uri = ""; // адрес для формирования ссылок
  public $SrcTable = "";

  public $DstTable = "";

  public $OpenPanel = false;
  public $can_clear = true;
  public $show_sets = true;

  function __construct($gui, $uname, $savecfg = "") {
    $this->_gui = $gui;
    $this->ses_ident = "cgui_filcol_" . $uname;
    $this->idname = "cgui_filcol_" . $uname;
    // Сюда добавляем фильтры если наборы не используются
    $this->curSet = new CGUI_FilterCollectionSet("");
    $this->curSet->collection = $this;
    if (strpos($savecfg, ":") > 0) {
      $a = explode(":", $savecfg);
      $this->cfg_table = $a[0];
      $this->cfg_field = $a[1];
    }
    //parse URI
    $x = strpos($_SERVER["REQUEST_URI"], "?");
    if ($x === false) {
      $this->uri = $_SERVER["REQUEST_URI"];
      $this->uri .= "?section=" . $this->_gui->mmenu->selected->section;
      $this->uri .= "&subsection=" . $this->_gui->mmenu->selected->selected->section;
    } else {
      $this->uri = substr($_SERVER["REQUEST_URI"], 0, $x + 1);
      $s = substr($_SERVER["REQUEST_URI"], $x + 1);
      $m = explode("&", $s);
      foreach($m as $v) {
        $m1 = explode("=", $v);
        $this->req[$m1[0]] = $m1[1];
        if ($m1[0] == "section") {
          $this->uri .= "section=" . $m1[1];
        }
        if ($m1[0] == "subsection") {
          $this->uri .= "&subsection=" . $m1[1];
        }
      }
    }
    page_scriptNeed("gui_filters.js", "gui");
  }

  function GetFilter($id) {
    if (isset($this->filters[$id])) {
      return $this->filters[$id];
    } else {
      return false;
    }
  }

  // Добавить фильтр
  function AddFilter($class) {
    $f = new $class;
    $f->id = count($this->filters);
    $f->collection = $this;
    $this->filters[] = $f;
    return $f;
  }

  // Создать временный набор
  function MakeTmpSet() {
    $this->curSetType = 3;
    $this->curSet = new CGUI_FilterCollectionSet("Временный набор");
    $this->curSet->collection = $this;
    $this->curSet->id = 0;
    $this->OpenPanel = true;
    return $this->curSet;
  }

  // Создать набор с фиксированными фильтрами
  function MakeStdSet($name) {
    $this->set_enable = true;
    $set = new CGUI_FilterCollectionSet($name);
    $set->collection = $this;
    $set->id = count($this->stdSets);
    $this->stdSets[] = $set;
    return $set;
  }

  // Создать набор пользователя - фильтры грузим из базы
  function MakeUserSets($count) {
    $this->set_enable = true;
    for($i = 0; $i < $count; $i++) {
      $set = new CGUI_FilterCollectionSet("Набор " . ($i + 1));
      $set->collection = $this;
      $set->id = count($this->userSets);
      $this->userSets[] = $set;
    }
  }

  function Execute() {
  }

  // Применение фильтров текущего набора
  function Filtering() {
    if (CGUI_FILTERS_DEBUG) {
      $this->_gui->DBG("> filtering");
    }
    if (!is_null($this->SrcTable)) {
      // create tmp table
      db::tempTable($this->SrcTable, $this->DstTable);
    }
    if ($this->curSet) {
      $this->curSet->Filtering();
    }
  }

  function html_sets() {
    $out = "Набор: <select onchange='cgui_filters_change_group(this, \"" . $this->uri . "\", \"" . $this->idname . "_sel_grp\");'>" . "\n";

    if ($this->can_clear) {
      $out .= "<option value='-1'>Очистить набор</option>" . "\n";
    }
    //standart
    if (count($this->stdSets)) {
      $out .= "<option disabled style='background-color:silver'>Предустановленные</option>" . "\n";
      foreach($this->stdSets as $v) {
        $out .= "<option ";
        if ($this->curSetType == 1 && $this->curSet->id == $v->id) {
          $out .= "selected='selected'";
        }
        $out .= "value='std" . $v->id . "'>" . $v->name . "</option>" . "\n";
      }
    }
    //userdef
    if (count($this->userSets)) {
      $out .= "<option disabled style='background-color:silver'>Настраиваемые</option>" . "\n";
      foreach($this->userSets as $v) {
        $out .= "<option ";
        if (($this->curSetType == 2) && ($this->curSet->id == $v->id)) {
          $out .= "selected ";
        }
        $out .= "value='usr" . $v->id . "'>" . $v->name . "</option>" . "\n";
      }
    }
    $out .= "</select>";
    //rename
    if ($this->curSetType == 2) {
      $out .= " <a href='#' class='cgui_filters_remove_link' onclick='cgui_filters_rename_grp(this, \"" . $this->uri . "\", \"" . $this->idname . "_grp_name\", \"" . $this->curSet->name . "\"); return false;'>переименовать</a>" . "\n";
    }
    return $out;
  }

  function html_fltrs() {
    $out = "Добавить фильтр: <select onchange='cgui_filters_addfilter(this.value, \"" . $this->uri . "\", \"" . $this->idname . "_add_fltr\")'><option value='-1'></option>" . "\n";
    foreach($this->filters as $f) {
      if (!$this->curSet->FilterInSet($f->id)) {
        if (!$f->hidden) {
          $out .= "<option value='" . $f->id . "'>" . $f->name . "</value>" . "\n";
        }
      }
    }
    $out .= "</select>" . "\n";
    return $out;
  }

  function GetHTML() {
    // Общий контейнер
    $out = "<div class='cgui_filters_block'><div>" . "\n";
    // Выбор наборов если $curSetType != 3
    $s = "";
    if ($this->curSetType == 3) {
      // Временный набор - в него можно добавлять свои фильтры
      $s = "Временный набор: <a class='cgui_filters_link_reset' href='#' onclick='document.location.href=\"" . $this->uri . "&" . $this->idname . "_sel_grp=-1\"; return false;'>сбросить</a>" . "\n";
    } else {
      // Показать список наборов
      if ($this->set_enable && $this->show_sets) {
        $s = $this->html_sets();
      }
    }
    $out .= $s . "\n";
    // Добавление фильтра в набор - если не 0 и не 1
    if (in_array($this->curSetType, array(2, 3))) {
      if ($s) {
        $out .= " | ";
      }
      $out .= $this->html_fltrs() . "\n";
    }
    $out .= "</div><div style='margin-top: 5px; margin-left: 5px'>" . "\n";
    // Если выбран набор, показываем его фильтры
    if ($this->curSet) {
      $out .= $this->curSet->GetHTML();
    }
    $out .= "</div></div>" . "\n";
    return $out;
  }

  function _select_group($v) {
    if ($v == "-1") {
      //reset
      $this->curSetType = 0;
      $this->curSet = false;
      return true;
    } elseif (strpos($v, "std") === 0) {
      // стандартные
      if (!$this->set_enable) {
        return false;
      }
      $id = intval(substr($v, 3));
      if (isset($this->stdSets[$id])) {
        $this->curSetType = 1;
        $this->curSet = $this->stdSets[$id];
        return true;
      }
      return false;
    } elseif (strpos($v, "usr") === 0) {
      // Пользовательские
      if (!$this->set_enable) {
        return false;
      }
      $id = intval(substr($v, 3));
      if (isset($this->userSets[$id])) {
        $this->curSetType = 2;
        $this->curSet = $this->userSets[$id];
        return true;
      }
    }
    return false;
  }

  function SaveToBase() {
    if ($this->cfg_table && $this->cfg_field) {
      $sv = array();
      //save usersets
      $sv["usersets"] = $sv['stdsets'] = array();
      foreach($this->userSets as $k => $v) {
        $sv["usersets"][$v->id] = $v->SaveBaseConf();
      }
      foreach($this->stdSets as $k => $v) {
        $sv["stdsets"][$v->id] = $v->SaveBaseConf();
      }
      if ($this->cfg_table == 'clients') {
        $id = $_SESSION["frame"]["client"]["id"];
      } else {
        $id = $_SESSION["user"]["data"]["id"];
      }
      db::update($this->cfg_table, array($this->cfg_field => serialize($sv)), "id = " . $id);
    }
  }

  function LoadFromBase() {
    if (CGUI_FILTERS_DEBUG) {
      $this->_gui->DBG("> FilterColletion > LoadFromBase");
    }
    if ($this->cfg_table && $this->cfg_field) {
      if ($this->cfg_table == 'clients') {
        $id = $_SESSION["frame"]["client"]["id"];
      } else {
        $id = $_SESSION["user"]["data"]["id"];
      }

      $fields = db::get_single_row("SELECT " . $this->cfg_field . " FROM " . TBL_PREF . $this->cfg_table . " WHERE id = " . $id);

      if (count($fields) && strlen($fields[$this->cfg_field])) {
        $sv = unserialize($fields[$this->cfg_field]);
        //load usersets
        foreach($this->userSets as $k => $v) {
          if (isset($sv["usersets"][$v->id])) {
            $v->LoadBaseConf($sv["usersets"][$v->id]);
          }
        }
        foreach($this->stdSets as $k => $v) {
          if (isset($sv["stdsets"][$v->id])) {
            $v->LoadBaseConf($sv["stdsets"][$v->id]);
          }
        }
      }
    }
  }

  function LoadFromSes() {
    if (!isset($_SESSION[$this->ses_ident])) {
      return;
    }
    $s = $_SESSION[$this->ses_ident];
    if ($this->curSetType == 3) {
      // Задано использовать временный набор
    } elseif (isset($_SESSION[$this->ses_ident]["tempset"])) {
      // Помним что надо использовать временный набор
      $this->MakeTmpSet();
      $this->curSet->LoadBaseConf($_SESSION[$this->ses_ident]["tempset"]);
    } else {
      // Смотрим какой набор нужно показать
      if (isset($s["selset"])) {
        $this->_select_group($s["selset"]);
      }
      if (!$this->set_enable && in_array($this->curSetType, array(1, 2))) {
        $this->curSetType = 0;
      }
      if ($this->curSetType) {
        $this->OpenPanel = true;
      }
    }
  }

  function Requests() {
    if (CGUI_FILTERS_DEBUG) {
      $this->_gui->DBG("> Requests");
    }
    $update_db = false;
    $grp_selected = false;
    $isreq = false;
    $this->LoadFromBase(); // наборы и их фильтры с уставками
    $this->LoadFromSes(); // выбор текущего набора
    foreach($this->req as $k => $v) {
      // Выбор набора
      if (!strcmp($k, $this->idname . "_sel_grp")) {
        $this->OpenPanel = true;
        if ($this->_select_group($v)) {
          $grp_selected = $v;
          $isreq = true;
        }
      } // _sel_grp
      // Переименовать набор (только пользовательский)
      if (!strcmp($k, $this->idname . "_grp_name")) {
        // Изменить название
        if ($this->curSetType == 2) {
          // Меняем название класса
          $this->curSet->name = urldecode($v);
          // Надо сохранить в БД
          $update_db = true;
          $isreq = true;
        }
      }
      // Добавить фильтр, если он не добавлен еще
      if (!strcmp($k, $this->idname . "_add_fltr")) {
        $id = intval($v);
        if (isset($this->filters[$id]) && !$this->filters[$id]->hidden && !$this->curSet->FilterInSet($id)) {
          if ($this->curSetType == 2) {
            $update_db = true;
            $this->curSet->UseFilter($id, false, false);
            $isreq = true;
          }
          if ($this->curSetType == 3) {
            // Добавляет ко временному набору - не надо писать в базу ничего
            $f = $this->curSet->UseFilter($id, false, false);
          }
        }
      }
      // Убрать фильтр
      if (!strcmp($k, $this->idname . "_rem_fltr")) {
        $id = intval($v);
        if (isset($this->filters[$id]) && $this->curSet->FilterInSet($id)) {
          if ($this->curSetType == 2) {
            $update_db = true;
            $this->curSet->UnuseFilter($id);
            $isreq = true;
          }
          if ($this->curSetType == 3) {
            $this->curSet->UnuseFilter($id);
          }
        }
      }
      // Вкл выкл фильтр
      if (!strcmp($k, $this->idname . "_chng_fltr")) {
        $this->curSet->ChangeStateFilter(intval($v));
        $update_db = true;
        $isreq = true;
      }
      //
    }
    //foreach
    // Обработка формы фильтров - если не было других запросов
    if (!$isreq && $this->curSet) {
      if ($this->curSet->UpdateFltr()) {
        $update_db = true;
      }
    }
    if ($grp_selected) {
      //save so sess
      $_SESSION[$this->ses_ident]["selset"] = $grp_selected;
    }
    if ($this->curSetType == 3) {
      // Запомним что был показан временный набор - сохраним туда фильтры
      $_SESSION[$this->ses_ident]["tempset"] = $this->curSet->SaveBaseConf();
    } else {
      unset($_SESSION[$this->ses_ident]["tempset"]);
    }
    if ($update_db) {
      $this->SaveToBase();
    }
  }
}

/*
 * 			if (isset($s["setsconf"][$this->curSetType."-".$this->curSet->id]))
				$this->curSet->LoadSesConf($s["setsconf"][$this->curSetType."-".$this->curSet->id]);
 * */
?>