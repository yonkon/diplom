<?php

use Components\Classes\db;

define("CGUI_PAGER_FLAG_RR", 1);
define("CGUI_PAGER_FLAG_R", 2);
define("CGUI_PAGER_FLAG_F", 4);
define("CGUI_PAGER_FLAG_FF", 8);
define("CGUI_PAGER_FLAG_SEL", 16);
define("CGUI_PAGER_FLAG_ALL", 31);

define("CGUI_TABLE_FMT_SIZE", 1);
define("CGUI_TABLE_FMT_DATE", 2);
define("CGUI_TABLE_FMT_DATETIME", 3);

function _tbl_fmt_size($v) {
  $base = log($v) / log(1024);
  $suffixes = array('B', 'kB', 'MB', 'GB', 'TB');

  if (floor($base) > 4 || floor($base) < 0) {
    return 'Невозможно определить размер';
  }
  return round(pow(1024, $base - floor($base)), 2) . $suffixes[floor($base)];
}

function _tbl_fmt_date($v) {
  return date("d.m.Y", $v);
}

function _tbl_fmt_datetime($v) {
  return date("d.m.Y H:i:s", $v);
}

$__cgui_teblecellinfo_counter = 0;

class CGUI_TableCellInfo {
  var $text = "";
  var $idname;
  var $icon = '[?]';

  function __construct() {
    global $__cgui_teblecellinfo_counter;

    $this->idname = "cgui_table_cellinfo_" . $__cgui_teblecellinfo_counter;
    $__cgui_teblecellinfo_counter++;

    page_scriptNeed("gui_table.js", "gui");
  }

  function Text($val) {
    $this->text = $val;
  }

  function GetHTML($src) {
    $out = "";
    $out .= "<div class='cgui_tablecellinfo' id='" . $this->idname . "' onmouseover='cgui_tablecellinfo_show(\"" . $this->idname . "\")' onmouseout='cgui_tablecellinfo_hide(\"" . $this->idname . "\")'>" . "\n" . $this->text . "\n" . "</div>" . "\n" . $src . "\n" . "<span id='" . $this->idname . "_link' style='color:gray; font-size:8pt; margin-left: 2px; cursor:pointer' onmouseover='cgui_tablecellinfo_show(\"" . $this->idname . "\")' onmouseout='cgui_tablecellinfo_hide(\"" . $this->idname . "\")'>" . $this->icon . "</span>" . "\n";
    return $out;
  }
}

class  CGUI_TablePager {
  var $onPage = 0;
  var $curPage = 0;
  var $curCount = 0;
  var $counts;
  var $name;
  var $_tbl;
  var $allcount = 0;
  var $size = 0;
  var $flags = 0;

  function __construct($name, $flags, $size, $counts) {
    $this->name = $name;
    $this->counts = $counts;
    $this->size = $size;
    $this->flags = $flags;

    if (isset($_SESSION["cgui_table_pager"][$this->name])) {
      $this->curPage = intval($_SESSION["cgui_table_pager"][$this->name]["curpage"]);
      if ($this->curPage < 0) {
        $this->curPage = 0;
      }
      $this->curCount = intval($_SESSION["cgui_table_pager"][$this->name]["curcount"]);
    }

    if (isset($_REQUEST[$this->name . "_cnt"])) {
      $this->curCount = intval($_REQUEST[$this->name . "_cnt"]);
    }

    if (isset($_REQUEST[$this->name . "_pg"])) {
      $this->curPage = intval($_REQUEST[$this->name . "_pg"] - 1);
      if ($this->curPage < 0) {
        $this->curPage = 0;
      }
    }

    if (isset($this->counts[$this->curCount])) {
      $this->onPage = $this->counts[$this->curCount];
    }
  }

  function __destruct() {
    $_SESSION["cgui_table_pager"][$this->name]["curpage"] = $this->curPage;
    $_SESSION["cgui_table_pager"][$this->name]["curcount"] = $this->curCount;
  }

  function GetLimitStr() {
    $out = "";
    if (!$this->onPage) {
      return $out;
    }
    $pg_cnt = ceil($this->allcount / $this->onPage);
    if ($pg_cnt == 0) {
      return $out;
    }
    if ($this->curPage > ($pg_cnt - 1)) {
      $this->curPage = $pg_cnt - 1;
    }
    if ($this->onPage) {
      $out = " LIMIT " . ($this->onPage * $this->curPage) . "," . $this->onPage;
    }
    return $out;
  }

  function GetHTML_Count() {
    $out = "<div class='cgui_pager_counter'>Строк на странице: <select onchange='document.location.href=\"?" . $this->_tbl->_gui->Url() . $this->name . "_cnt=\"+this.value;'>" . "\n";
    foreach ($this->counts as $k => $v) {
      if ($v == 0) {
        $v = "все";
      }
      $out .= "<option value='" . $k . "'";
      if ($k == $this->curCount) {
        $out .= " selected";
      }
      $out .= ">" . $v . "</option>" . "\n";
    }
    $out .= "</select></div>" . "\n";
    return $out;
  }

  function GetHTML_Pages() {
    if (!$this->onPage) {
      return "";
    }
    $pg_cnt = ceil($this->allcount / $this->onPage);
    if ($pg_cnt < 2) {
      return "";
    }

    if ($this->curPage > ($pg_cnt - 1)) {
      $this->curPage = $pg_cnt - 1;
    }

    $out = "<div class='cgui_tablepager'><table cellpadding=0 cellspacing=1><tr><td>Страницы: </td>" . "\n";

    if ($this->flags & CGUI_PAGER_FLAG_RR) {
      $out .= "<td width='25' class='navbtn' onmouseover='this.className=\"navbtn_sel\"' onmouseout='this.className=\"navbtn\"' onclick='document.location.href=\"?" . $this->_tbl->_gui->Url() . $this->name . "_pg=1\"'><<</td>" . "\n";
    }

    if ($this->flags & CGUI_PAGER_FLAG_R) {
      $out .= "<td class='navbtn'  onmouseover='this.className=\"navbtn_sel\"' onmouseout='this.className=\"navbtn\"' onclick='document.location.href=\"?" . $this->_tbl->_gui->Url() . $this->name . "_pg=" . max(1, $this->curPage) . "\"'><</td>" . "\n";
    }

    /////

    $n1 = 1;
    $n = $pg_cnt;
    if ($pg_cnt > $this->size) {
      $n = $this->size;
      $n1 = max(1, $this->curPage + 1 - $this->size / 2);
      $n = min($pg_cnt, $this->size + $n1 - 1);
    }

    for ($i = $n1; $i <= $n; $i++) {
      $c = "";
      if ($i == ($this->curPage + 1)) {
        $c = " sel";
      }
      $out .= "<td class='navpg" . $c . "' onmouseover='this.className=\"navpg_sel\"' onmouseout='this.className=\"navpg" . $c . "\"' onclick='document.location.href=\"?" . $this->_tbl->_gui->Url() . $this->name . "_pg=" . $i . "\"'>" . $i . "</td>" . "\n";
    }
    /////

    if ($this->flags & CGUI_PAGER_FLAG_F) {
      $out .= "<td class='navbtn' onmouseover='this.className=\"navbtn_sel\"' onmouseout='this.className=\"navbtn\"' onclick='document.location.href=\"?" . $this->_tbl->_gui->Url() . $this->name . "_pg=" . min($pg_cnt, $this->curPage + 2) . "\"'>></td>" . "\n";
    }

    if ($this->flags & CGUI_PAGER_FLAG_FF) {
      $out .= "<td class='navbtn' onmouseover='this.className=\"navbtn_sel\"' onmouseout='this.className=\"navbtn\"' onclick='document.location.href=\"?" . $this->_tbl->_gui->Url() . $this->name . "_pg=" . ($pg_cnt) . "\"'>>></td>" . "\n";
    }

    if ($this->flags & CGUI_PAGER_FLAG_SEL) {
      $out .= "<td><select onchange='document.location.href=\"?" . $this->_tbl->_gui->Url() . $this->name . "_pg=\"+this.value;'>";
      for ($i = 1; $i <= $pg_cnt; $i++) {
        $out .= "<option ";
        if ($i == ($this->curPage + 1)) {
          $out .= "selected ";
        }
        $out .= "value='" . $i . "'>" . $i . "</option>" . "\n";
      }
      $out .= "</select></td>" . "\n";
    }

    return $out . "</tr></table></div>" . "\n";
  }
}

class CGUI_TableColumn {
  var $Caption = "caption";
  var $Key = "";
  var $DoSort = false;
  var $Process = "";
  var $Custom = array();
  var $ExtData = NULL;
  var $NoWrap = false;
  var $Align = "";
  var $Vars = array();
  var $Format = 0;
  var $instantEdit = false;
  var $hidden = false;
  var $id = '';
  var $index = 0;
  var $menu = null;
}

class CGUI_TableMenu {
  private $commands = array();
  var $Vars = array();
  
  function __construct()
  {
  	page_scriptNeed("gui_table.js", "gui");
  }
  	
  function AddCommand($name, $cmd) {
    $this->commands[] = array("name" => $name, "sp" => 0, "cmd" => $cmd);
  }
  
  function AddJsCommand($name, $cmd) {
    $this->commands[] = array("name" => $name, "sp" => 0, "js" => $cmd);
  }

  function AddSplitter() {
    $this->commands[] = array("sp" => 1);
  }

  function GetHTML($val="") 
  {
    $out = "<div class='cgui_table_rowmenu'>" . $val . "<img src='gui/skin/arr_exp.png'>" . "\n";
    $out .= "<div class='cgui_table_rowmenu_menu'>" . "\n";
    foreach ($this->commands as $v)
    {
      if ($v["sp"])
      {
        $out .= "<div class='cgui_table_rowmenu_menusplit'></div>" . "\n";
      }
      else
      {
      	$onclick = "";
		
      	if (isset($v["cmd"]))
		{
			$c = $v["cmd"];
	        foreach ($this->Vars as $kv => $vv)
	        {
	          $c = str_replace("%" . $kv . "%", $vv, $c);
	        }
	        $onclick = "document.location.href=\"" . $c . "\"";
		}
		else if (isset($v["js"]))
		{
			$onclick = $v["js"];
		}			
		
		$out .= "<div class='cgui_table_rowmenu_menuitem' onclick='".$onclick."'>" . $v["name"] . "</div>" . "\n";
				
      }
    }
    $out .= "</div></div>" . "\n";
    return $out;
  }
}

class CGUI_Table {
  var $id;
  var $_gui;

  var $started = false;
  var $finished = false;
  var $sort_link = "";
  var $cur_sort_up = false;
  var $cur_sort = 0;

  var $Name = "";
  var $Columns = array();
  var $HtmlS = "";
  var $HtmlB = "";
  var $HtmlE = "";
  var $ClassName = "cgui_table";
  var $Settings = array();
  var $Width = false;

  var $RowSelect = true;
  var $RowEvent = "";
  var $RowEvent2 = "";
  var $RowSelectCol = "#ddf";

  var $isort = false;
  var $Rows = array();

  var $Highlite = false;

  var $mysql_source = false;
  var $mysql_source_alias = '';
  var $mysql_filter = "";
  var $mysql_flds = "*";

  var $pager = false;
  var $pages_str = "";

  var $sort_checked = false;

  var $before_start_event = "";

  var $OnRowStart = "";

  var $rowmenu = false;
  var $useColors = false;

	var $order_rules = null;

  function __construct($uid = "", $defaults = array()) {

    if ($uid != "") {
      $this->id = "u" . $uid;
    } else {
      $this->id = __get_new_table_id();
    }

    $this->Name = "cgui_table_id_" . $this->id;
    $this->sort_link = "sort_" . $this->Name;

    if (isset($_SESSION[$this->Name . "_ses_settings"])) {
      $this->Settings = $_SESSION[$this->Name . "_ses_settings"];
      $this->cur_sort = $this->Settings["cur_sort"];
      $this->cur_sort_up = $this->Settings["cur_sort_up"];
    } else {
      if (isset($defaults["cur_sort_up"])) {
        $this->cur_sort_up = $defaults["cur_sort_up"];
      }
    }
  }

  function __destruct() {
    // save all cols
    $this->Settings["cur_sort"] = $this->cur_sort;
    $this->Settings["cur_sort_up"] = $this->cur_sort_up;
    $_SESSION[$this->Name . "_ses_settings"] = $this->Settings;
  }

  function DataMYSQL($tbl_name, $flds = "*", $mysql_source_alias = '') {
    if ($tbl_name) {
      $this->mysql_source = $tbl_name;
      $this->InlineSort(false);
      $this->mysql_flds = $flds;
    } else {
      $this->mysql_source = false;
    }
    if (!empty ($mysql_source_alias)) {
      $this->mysql_source_alias = $mysql_source_alias.'.';
    }
  }

  function FilterMYSQL($f) {
    $this->mysql_filter = $f;
  }
  
  function OrderingMYSQL($o)
  {
  	if (strlen($o))
  		$this->order_rules = $o;
	else
		$this->order_rules = null;
  }

  function Pager($flags, $size, $counts) {
    $this->pager = new CGUI_TablePager("cgui_table_pager_" . $this->id, $flags, $size, $counts);
    $this->pager->_tbl = $this;
  }

  function InlineSort($v) {
    $this->isort = $v;
    if ($v) {
      $this->mysql_source = false;
    }
  }

  function _inlinesort() {
    $sort_arr = array();

    $k = $this->Columns[$this->cur_sort]->Key;
    foreach ($this->Rows as $r) {
      if (!isset($r["data"][$k])) {
        $sort_arr = $this->Rows;
        break;
      }
      $sort_arr[] = $r["data"][$k];
    }

    if ($this->cur_sort_up) {
      array_multisort($sort_arr, SORT_DESC, $this->Rows);
    } else {
      array_multisort($sort_arr, $this->Rows);
    }
  }

  function NewColumn() {
    $col = new CGUI_TableColumn();
	$col->index = count($this->Columns); 
    $this->Columns[] = $col;
    return $col;
  }

  function _get_sort_block($nm, $srt, $col_id) {
    if ($srt) {
      if ($this->cur_sort == $col_id) {
        if ($this->cur_sort_up) {
          return $nm . "<a href='?" . $this->_gui->Url() . $this->sort_link . "=" . $col_id . "' style='text-decoration:none'><font style='color:red; font-size:10pt'>&#9650;</font></a>";
        } else {
          return $nm . "<a href='?" . $this->_gui->Url() . $this->sort_link . "_up=" . $col_id . "' style='text-decoration:none'><font style='color:red; font-size:10pt'>&#9660;</font></a>";
        }
      } else {
        return $nm . "<a href='?" . $this->_gui->Url() . $this->sort_link . "_up=" . $col_id . "' style='text-decoration:none'><font  style='color:gray; font-size:10pt'>&#8661;</font></a>";
      }
    } else {
      return $nm;
    }
  }

  function GetCurSortKey() {
    if (!isset($this->Columns[$this->cur_sort])) {
      return "";
    }
    $c = $this->Columns[$this->cur_sort];
    if (count($c->Custom)) {
      $c = $c->Custom[$this->Settings["cust"][$this->cur_sort]];
    }
    if (!$c->DoSort) {
      return "";
    }

    if ($this->cur_sort_up) {
      return $c->Key . " DESC";
    } else {
      return $c->Key;
    }
  }

  function _check_sort_links() {
    if ($this->sort_checked) {
      return;
    }
    $this->sort_checked = true;
    // Check for sort
    if (isset($_REQUEST[$this->sort_link])) {
      $v = intval($_REQUEST[$this->sort_link]);
      $this->cur_sort = $v;
      $this->cur_sort_up = false;
    }

    if (isset($_REQUEST[$this->sort_link . "_up"])) {
      $v = intval($_REQUEST[$this->sort_link . "_up"]);
      $this->cur_sort = $v;
      $this->cur_sort_up = true;
    }
  }

  function StartTable() {
    if ($this->started) {
      return;
    }
    $this->started = true;

    if ($this->before_start_event) {
      eval($this->before_start_event . "($" . "this);");
    }

    $this->_check_sort_links();

    $this->HtmlS = "";

    if ($this->pager) {
      $this->pages_str = "<table width='100%'><tr><td align='left'>" . $this->pager->GetHTML_Pages() . "</td><td align='right'>" . $this->pager->GetHTML_Count() . "</td></tr></table>";
      $this->HtmlS .= $this->pages_str;
    }

    $this->HtmlS .= "<div style='width:100%; overflow-x:auto'><table";
    if ($this->Width != "") {
      $this->HtmlS .= " width='" . $this->Width . "'";
    }
    $this->HtmlS .= " cellpadding=4 cellspacing=1 class='" . $this->ClassName . "'><tr class='header' style='text-align:center'>";
    foreach ($this->Columns as $k => $v) {
      if (count($v->Custom)) 
      {

        if (!isset($this->Settings["cust"][$k])) {
          $this->Settings["cust"][$k] = 0;
        }

        //check for custom
        $nm = "table_" . $this->Name . "_custom_col_" . $k;
        if (isset($_REQUEST[$nm])) {
          // change
          $this->Settings["cust"][$k] = intval($_REQUEST[$nm]);
        }

        $this->HtmlS .= "<td nowrap><form method='post' style='margin:0; padding:0'><select name='" . $nm . "' onchange='submit();'></form>";
        $v_sel = false;

        foreach ($v->Custom as $k1 => $v1) {
          $this->HtmlS .= "<option value='" . $k1 . "'";
          if ($k1 == $this->Settings["cust"][$k]) {
            $this->HtmlS .= " selected";
            $v_sel = $v1;
          }
          $this->HtmlS .= ">" . $v1->Caption . "</option>";
        }
        $this->HtmlS .= "</select>" . $this->_get_sort_block("", $v_sel->DoSort, $k) . "</td>";
      }
      else
      {
      	$m = "";
      	if ($v->menu)
		{
			$m = $v->menu->GetHTML();
		}
		
        $this->HtmlS .= '<td nowrap' . ($v->hidden ? ' class="hide"' : '') . '>' . $m . $this->_get_sort_block($v->Caption, $v->DoSort, $k) . "</td>";
      }
    }
    $this->HtmlS .= "</tr>";
  }

  function makeRowStyle($Row) {
    if (!isset($Row["style"])) {
      return "";
    }
    $s = array();
    foreach ($Row["style"] as $k => $v) {
      $s[] = $k . ":" . $v;
    }
    return implode(";", $s);
  }

  function MakeHTML() {

    $this->_check_sort_links();

    if ($this->isort) {
      $this->_inlinesort();
    }

    $where = "";
    if ($this->mysql_filter != "") {
      $where = " WHERE " . $this->mysql_filter;
    }

    if ($this->mysql_source) 
    {
      $this->Rows = array();
      $limit = "";
      if ($this->pager) 
      {
        $this->pager->allcount = intval(db::get_single_value("SELECT COUNT(" . $this->mysql_source_alias ."id) AS cnt FROM " . TBL_PREF . $this->mysql_source . $where));
        $limit = $this->pager->GetLimitStr();
      }

		$ord_str = "ORDER BY "; 

		if (!is_null($this->order_rules))
			$ord_str .= $this->order_rules . ",";
		
		 $ord_str .= (!$this->GetCurSortKey() ? $this->mysql_source_alias."id" : $this->mysql_source_alias.$this->GetCurSortKey());
		
				
		foreach (db::get_arrays("SELECT " . $this->mysql_flds . " FROM " . TBL_PREF . $this->mysql_source . $where . " " . $ord_str . $limit) as $r) 
		{
			$this->AddRow($r, $this->mysql_source_alias."id");
		}
	  
    }
    else
    {
      if ($this->pager)
	  {
        $this->pager->allcount = count($this->Rows);
        if ($this->pager->GetLimitStr() != '')
        {
          $tmp = $this->Rows;
          $this->Rows = array();
          for ($i = 0; $i < $this->pager->onPage; $i++)
          {
            if (isset($tmp[$this->pager->curPage * $this->pager->onPage + $i]))
            {
              $this->Rows[$i] = $tmp[$this->pager->curPage * $this->pager->onPage + $i];
            }
          }
        }
      }
    }

    $this->StartTable();

    $this->HtmlB = "";
    foreach ($this->Rows as $kr => $r) {

      $rdata = $r["data"];
      $r["style"]["cursor"] = "arrow";
      $r["style"]["background-color"] = "";

      if ($this->OnRowStart) {
        eval("$" . "s = " . $this->OnRowStart . "($" . "r);");
      }

      if (is_array($this->Highlite)) {
        if ($rdata[$this->Highlite[0]] == $this->Highlite[1]) {
          $r["style"]["background-color"] = "yellow";
        }
      }

      if (!empty($_SESSION['user']['data']['conf_ord_colors']) && $this->useColors) {
        $currentColors = unserialize($_SESSION['user']['data']['conf_ord_colors']);
        $r["style"]["background-color"] = isset($currentColors[$rdata['id']]) ? $currentColors[$rdata['id']] : '#FFFFFF';
      } else {
        $currentColors = array();
      }
      $st = $this->makeRowStyle($r);

      $this->HtmlB .= "<tr style='" . $st . "'  data-color='" . (isset($currentColors[$rdata['id']]) ? $currentColors[$rdata['id']] : '#FFFFFF') . "' data-row-id='" . $rdata['id'] . "'";

      if ($this->RowSelect) {
        $this->HtmlB .= " onmouseover='jQuery(this).css(\"background-color\", \"" . $this->RowSelectCol . "\");' onmouseout='jQuery(this).css(\"background-color\", \"" . $r["style"]["background-color"] . "\");'";
        if ($this->RowEvent != "") {
          $this->HtmlB .= " onclick='" . $this->RowEvent . "(" . $r["data"][$r["ek"]] . ");'";
        } else if ($this->RowEvent2 != "") {
          if(strpos($this->RowEvent2, "%var%") != false) {
            $s = str_replace("%var%", $r["data"][$r["ek"]], $this->RowEvent2);
          } else {
            $attr_index = strpos($this->RowEvent2, "%var.");
            $s = str_replace("%var.", '', $this->RowEvent2);
            $attr_ends = strpos($s, '%', $attr_index);
            $attr_name = substr($s, $attr_index, $attr_ends-$attr_index);
            $s = str_replace($attr_name.'%', $r["data"][$attr_name], $s);
          }
          $this->HtmlB .= " onclick='" . $s . "'";
        }
      }

      $this->HtmlB .= ">" . "\n";
      foreach ($this->Columns as $k => $v) {
        $s = "";

        if (count($v->Custom)) {
          $v = $v->Custom[$this->Settings["cust"][$k]];
        }

        if (($v->Key != "") && (isset($rdata[$v->Key]))) {
          if (is_array($v->ExtData)) {
            $s = $v->ExtData[$rdata[$v->Key]];
          } else {
            $s = $rdata[$v->Key];
          }
        }

        if ($v->Process != "") {
          $this->Rows[$kr]['info'][$k] = '';
          eval("$" . "s=" . $v->Process . "($" . "s, $" . "rdata, $" . "this, $" . "this->Rows[$" . "kr]['info'][$" . "k]);");
        } elseif ($v->Format) {
          switch ($v->Format) {
            case CGUI_TABLE_FMT_SIZE:
              $s = _tbl_fmt_size($s);
              break;
            case CGUI_TABLE_FMT_DATE:
              $s = _tbl_fmt_date($s);
              break;
            case CGUI_TABLE_FMT_DATETIME:
              $s = _tbl_fmt_datetime($s);
              break;
          }
        }

        if (!empty($this->Rows[$kr]["info"][$k])) {
          $s = $this->Rows[$kr]["info"][$k]->GetHTML($s);
        }

        $this->HtmlB .= "<td";
        if ($v->NoWrap) {
          $this->HtmlB .= " nowrap";
        }
        if ($v->Align) {
          $this->HtmlB .= " style='text-align: " . $v->Align . "'";
        }
        if ($v->hidden) {
          $this->HtmlB .= ' class="hide"';
        }
        if ($v->id) {
          $this->HtmlB .= ' id="' . $v->id . '"';
        }
        $this->HtmlB .= ">";
        if ($v->instantEdit) {
          $this->HtmlB .= '<span class="instantEditOldValue">' . $s . '</span><span class="instantEdit" data-title="' . $v->Caption . '" data-field="' . $v->Key . '" data-value="' . $rdata[$v->Key] . '"></span>' . "\n";
        } else {
          $this->HtmlB .= $s . "\n";
        }

        $this->HtmlB .= "</td>" . "\n";
      }
      $this->HtmlB .= "</tr>" . "\n";
    }

    $this->EndTable();
  }

  function AddRow($data, $evnt_key = 0) {
    $this->Rows[] = array("data" => $data, "ek" => $evnt_key, "info" => array());
  }

  function PrintTable() {
    $this->MakeHTML();
    print $this->HtmlS . $this->HtmlB . $this->HtmlE;
  }

  function EndTable() {
    if ($this->finished) {
      return;
    }
    $this->finished = true;

    $this->HtmlE = "</table></div>";
    if ($this->pager) {
      $this->HtmlE .= $this->pages_str;
    }
  }

  function CreateRowMenu() {
    $this->rowmenu = new CGUI_TableMenu();
    return $this->rowmenu;
  }
  
  function DefaultSortBy($col, $up=true)
  {
  	if (!isset($this->Settings["cur_sort"]))
	{
  		$this->cur_sort = $col->index;
		$this->cur_sort_up = $up;
	}
  }
}

?>
