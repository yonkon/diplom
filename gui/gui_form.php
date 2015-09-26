<?php

class CGUI_Validator {
  function Validate($val) {
  }

  function GetJSName() {
  }

  function GetErrName() {
  }
}

class CGUI_VALIDATOR_NOEMPTY extends CGUI_Validator {
  function Validate($val) {
    return $val != "";
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_noempty";
  }

  function GetErrName() {
    return "Поле не должно быть пустым";
  }
}

class CGUI_VALIDATOR_MAXLEN extends CGUI_Validator {
  var $len = 0;

  function __construct($len) {
    $this->len = $len;
  }

  function Validate($val) {
    return (mb_strlen($val, 'utf-8') <= $this->len);
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_none";
  }

  function GetErrName() {
    return "Длина строки должна быть не более " . $this->len . " симв.";
  }
}

class CGUI_VALIDATOR_MINLEN extends CGUI_Validator {
  var $len = 0;

  function __construct($len) {
    $this->len = $len;
  }

  function Validate($val) {
    return (strlen($val) >= $this->len);
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_none";
  }

  function GetErrName() {
    return "Длина строки должна быть не менее " . $this->len . " симв.";
  }
}

class CGUI_VALIDATOR_AZaz09 extends CGUI_Validator {
  function Validate($val) {
    return !preg_match("[^a-z0-9]", $val);
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_AZaz09";
  }

  function GetErrName() {
    return "Разрешенные символы: A..Z, a..z, 0..9";
  }
}

class CGUI_VALIDATOR_DDMMYYYY extends CGUI_Validator {
  function Validate($val) {
    if ($val == "") {
      return true;
    }
    $d = explode("-", $val);
    if (count($d) != 3) {
      return false;
    }
    $day = intval($d[0]);
    $month = intval($d[1]);
    $year = intval($d[2]);
    if (($day < 1) || ($day > 31)) {
      return false;
    }
    if (($month < 1) || ($month > 12)) {
      return false;
    }
    return true;
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_none";
  }

  function GetErrName() {
    return "Формат даты: ДД-ММ-ГГГГ";
  }
}

class CGUI_VALIDATOR_09 extends CGUI_Validator {
  function Validate($val) {
    return !preg_match("[^0-9]", $val);
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_09";
  }

  function GetErrName() {
    return "Разрешенные символы: 0..9";
  }
}

class CGUI_VALIDATOR_EMAIL extends CGUI_Validator {
  function Validate($val) {
    $val = trim($val);
    if ($val != "") {
      if (preg_match("/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/i", $val)) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_email";
  }

  function GetErrName() {
    return "Некорректный адрес почты";
  }
}

class CGUI_VALIDATOR_TELNUM extends CGUI_Validator {
  function Validate($val) {
    if ($val !== "") {
      preg_replace("[^0-9]", "", $val);
      return strlen($val) >= 7;
    } else {
      return false;
    }
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_telnum";
  }

  function GetErrName() {
    return "Некорректный номер телефона";
  }
}

class CGUI_VALIDATOR_NOZERO extends CGUI_Validator {
  function Validate($val) {
    return ($val != 0);
  }

  function GetJSName() {
    page_scriptNeed("gui_validators.js", "gui");
    return "cgui_validator_nozero";
  }

  function GetErrName() {
    return "Значение не выбрано";
  }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
class CGUI_Label {
  var $caption;

  var $left = 0;

  var $top = 0;

  var $display;

  function __construct($cap, $x = 0, $y = 0) {
    $this->caption = $cap;
    if ($x) {
      $this->left = $x;
    }
    if ($y) {
      $this->top = $y;
    }
  }

  function GetHTML() {
    return "<div class='cgui_form_label' style='margin-left: " . $this->left . "px; margin-top: " . $this->top . "px;" . (!empty($this->display) ? ' display: ' . $this->display . ';' : '') . "'>" . "\n" . $this->caption . "\n" . "</div>" . "\n";
  }
}

class CGUI_VLine {
  var $left = 0;

  var $top = 0;

  var $width = 0;

  function __construct($x = 0, $y = 0, $w = 0) {
    if ($x) {
      $this->left = $x;
    }
    if ($y) {
      $this->top = $y;
    }
    if ($w) {
      $this->width = $w;
    }
  }

  function GetHTML() {
    return "<div class='cgui_form_vline' style='margin-left: " . $this->left . "px; margin-top: " . $this->top . "px; width: " . $this->width . "px'></div>" . "\n";
  }
}

class CGUI_EmptyDiv {
  var $left = 0;

  var $top = 0;

  var $width = 0;

  var $height = 0;

  var $css = "";

  var $Id;

  function __construct($x = 0, $y = 0, $w = 0, $h = 0) {
    if ($x) {
      $this->left = $x;
    }
    if ($y) {
      $this->top = $y;
    }
    if ($w) {
      $this->width = $w;
    }
    if ($h) {
      $this->height = $h;
    }
  }

  function GetHTML() {
    return "<div id='" . $this->Id . "' style='position: absolute; overflow:hidden; margin-left: " . $this->left . "px; margin-top: " . $this->top . "px; width: " . $this->width . "px; height: " . $this->height . "px;" . $this->css . "'></div>" . "\n";
  }
}

$__cgui_form_button_cnt = 0;
class CGUI_Button {
  var $caption;

  var $fid;

  var $width = 0;

  var $top = 0;

  var $left = 0;

  var $submit = false;

  var $Event = "";

  var $id = 0;

  function __construct($cap, $x = 0, $y = 0, $w = 0, $s = false) {
    global $__cgui_form_button_cnt;
    $this->caption = $cap;
    if ($x) {
      $this->left = $x;
    }
    if ($y) {
      $this->top = $y;
    }
    if ($w) {
      $this->width = $w;
    }
    $this->submit = $s;
    $this->id = $__cgui_form_button_cnt;
    $__cgui_form_button_cnt++;
  }

  function GetHTML() {
    $s = "";
    if ($this->width) {
      $s = "width: " . $this->width . "px; ";
    }
    $clk = "return false";
    if ($this->Event) {
      $clk = $this->Event;
    }
    if ($this->submit) {
      $clk = "jQuery(\"#cgui_form_id_" . $this->fid . "\").submit();";
    }
    $fnm = "cgui_btn_click_" . $this->id;
    page_AddScriptText("function " . $fnm . "(){ " . $clk . " }");
    $out = "<button class='cgui_form_button' onclick='" . $fnm . "(); return false;' style='" . $s . "margin-left: " . $this->left . "px; margin-top: " . $this->top . "px'>" . "\n" . $this->caption . "\n" . "</button>" . "\n";
    return $out;
  }
}

//////////////////////////////////////////////////////////////////////////////////////
class CGUI_DataField {
  var $id;

  var $fid;

  var $name;

  var $idname;

  var $vls = array();

  var $err_fnd = false;

  var $err_name = "";

  var $width = 60;

  var $top = 0;

  var $left = 0;

  var $Value;

  var $defval;

  var $jsevents = array();

  var $Disabled = false;

  var $linkName = "";

  var $display;

  var $class = '';

  function AddValidator($vl) {
    $this->vls[] = $vl;
  }

  function AddJsEvent($name, $code) {
    $this->jsevents[] = array("name" => $name, "code" => $code);
  }

  function Validate() {
    if ($this->Disabled) {
      return true;
    }
    $err = false;
    foreach($this->vls as $vl) {
      $err = !$vl->Validate($this->Value);
      if ($err) {
        $this->err_fnd = true;
        $this->err_name = $vl->GetErrName();
      }
    }
    return !$err;
  }

  function GetFromReq() {
    return @$_REQUEST["FORMS_DATA"][$this->fid][$this->id];
  }

  function ExistInReq() {
    return isset($_REQUEST["FORMS_DATA"][$this->fid][$this->id]);
  }

  function GetHTML() {
    $out = "";
    if (count($this->jsevents) || $this->Disabled) {
      $out .= '<script>jQuery(function() {' . "\n";
      if ($this->Disabled) {
        $out .= "jQuery('#" . $this->idname . "').attr('disabled', 'disabled');" . "\n";
      }
      foreach($this->jsevents as $je) {
        $out .= " jQuery('#" . $this->idname . "').bind('" . $je["name"] . "',function(){ " . $je["code"] . " })" . "\n";
      }
      $out .= "});</script>" . "\n";
    }
    if ((!$this->Disabled) && (count($this->vls))) {
      $out .= "<div class='cgui_field_errbox' style='position:absolute' id='" . $this->idname . "_err'></div>" . "\n";
    }
    if ($this->err_fnd) {
      $out .= "<div class='cgui_field_errbox' onclick='this.style.display=\"none\"' style='display: block; margin-left: " . ($this->width + 10) . "px'>" . "\n" . $this->err_name . "\n" . "</div>" . "\n";
    }
    if (!$this->Disabled) {
      foreach($this->vls as $k => $vl) {
        $out .= "<script type='text/javascript'>" . "\n" . "cgui_vforms['" . $this->fid . "'].push(['cgui_form_" . $this->fid . "_field_" . $this->id . "', " . $vl->GetJSName() . ", '" . $vl->GetErrName() . "']);" . "\n" . "</script>" . "\n";
      }
    }
    return $out . "\n";
  }
}

class CGUI_Text extends CGUI_DataField {
  var $pwd = false;

  function __construct($x, $y, $w, $v) {
    $this->left = $x;
    $this->top = $y;
    $this->width = $w;
    $this->defval = $v;
  }

  function GetHTML() {
    $out = "<div class='cgui_form_text'  style='width: " . $this->width . "px; margin-left:" . $this->left . "px; margin-top: " . $this->top . "px;'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<input type='";
    if ($this->pwd) {
      $out .= "password";
    } else {
      $out .= "text";
    }
    $out .= "'";
    if ($this->class) {
      $out .= " class='" . $this->class . "'";
    }
    $out .= "name='" . $this->name . "' id='" . $this->idname . "' value='" . $this->Value . "' style='position: absolute; width:" . $this->width . "px'>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_DatePic extends CGUI_DataField {
  function __construct($x, $y, $w, $v) {
    $this->left = $x;
    $this->top = $y;
    $this->width = $w;
    if ($v) {
      $vv = explode("-", $v);
      if (count($vv) == 3) {
        $v = date("d-m-Y", strtotime($v));
      } else {
        $v = date("d-m-Y", intval($v));
      }
    }
    $this->defval = $v;
    page_ScriptNeed("calendar.js");
  }

  function GetHTML() {
    $out = "<div class='cgui_form_text'  style='width: " . $this->width . "px; margin-left:" . $this->left . "px; margin-top: " . $this->top . "px;'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<input type='text' name='" . $this->name . "' id='" . $this->idname . "' value='" . $this->Value . "' style='position: absolute; width:" . $this->width . "px' " . "onclick='event.cancelBubble=true; this.select(); lcs(this, 0)'; onfocus='this.select(); lcs(this, 0);'>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_TimePic extends CGUI_DataField {
  var $min_time = 0;

  var $max_time = 0;

  var $min_step = 15;

  var $setTimeEventCode = "";

  function __construct($x, $y, $w, $v) {
    $this->left = $x;
    $this->top = $y;
    $this->width = $w;
    $a = explode(":", $v);
    $t = 0;
    if (count($a) == 2) {
      $t = utils_cvt_time2i($v);
    } else {
      $t = intval($v);
    }
    $this->defval = $t;
    $this->max_time = (23 * 60 + 59);
    page_ScriptNeed("timepicker.js");
  }

  function _getValue($v) {
    return utils_cvt_time2i($v);
  }

  function Validate() {
    $v = parent::Validate();
    if (!$v) {
      return false;
    }
    if ($this->Value < $this->min_time) {
      $this->err_fnd = true;
      $this->err_name = "Время меньше положенного";
      return false;
    }
    if ($this->Value > $this->max_time) {
      $this->err_fnd = true;
      $this->err_name = "Время больше положенного";
      return false;
    }
    return true;
  }

  function SetTimeEvent($code) {
    $this->setTimeEventCode = $code;
  }

  function GetHTML() {
    $out = "<div class='cgui_form_text'  style='width: " . $this->width . "px; margin-left:" . $this->left . "px; margin-top: " . $this->top . "px;'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<input type='text' name='" . $this->name . "' id='" . $this->idname . "' value='" . utils_cvt_i2times($this->Value) . "' style='position: absolute; width:" . $this->width . "px'>" . "\n";
    $out .= "<div id='" . $this->idname . "_timepicker' class='timepicker_box'></div></div>" . "\n";
    $out .= "<script>" . "\n" . "jQuery(function(){ timepicker_init('" . $this->idname . "', " . $this->min_time . ", " . $this->max_time . ", " . $this->min_step . "); });" . "\n" . "</script>" . "\n";
    $out .= "<script>" . "\n" . "function timepicker_se_" . $this->idname . "(val){" . $this->setTimeEventCode . "}" . "\n" . "</script>" . "\n";
    return $out;
  }
}

class CGUI_Tracker extends CGUI_DataField {
  var $MinVal = 0;

  var $MaxVal = 100;

  function __construct($x, $y, $w, $v) {
    $this->left = $x;
    $this->top = $y;
    $this->width = $w;
    $this->defval = $v;
    page_ScriptNeed("gui_tracker.js", "gui");
  }

  function GetHTML() {
    $out = "<div class='cgui_form_text'  style='width: " . $this->width . "px; margin-left:" . $this->left . "px; margin-top: " . $this->top . "px;'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<input type='text' name='" . $this->name . "' id='" . $this->idname . "' value='" . $this->Value . "' style='position: absolute; width:" . $this->width . "px' " . "onclick='event.cancelBubble=true; this.select(); tracker(this, " . $this->MinVal . ", " . $this->MaxVal . ")'; onfocus='this.select(); tracker(this, " . $this->MinVal . ", " . $this->MaxVal . ");'>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_TextArea extends CGUI_DataField {
  var $height;

  function __construct($x, $y, $w, $h, $v) {
    $this->left = $x;
    $this->top = $y;
    $this->width = $w;
    $this->height = $h;
    $this->defval = $v;
  }

  function GetHTML() {
    $out = "<div class='cgui_form_text'  style='width: " . $this->width . "px; height: " . $this->height . "px; margin-left:" . $this->left . "px; margin-top: " . $this->top . "px;'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<textarea name='" . $this->name . "' id='" . $this->idname . "' style='position: absolute; width:" . $this->width . "px; height:" . $this->height . "px'>" . $this->Value . "</textarea>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_TextArea2 extends CGUI_DataField {
  var $height;

  function __construct($x, $y, $w, $h, $v) {
    $this->left = $x;
    $this->top = $y;
    $this->width = $w;
    $this->height = $h;
    $this->defval = $v;
    page_scriptNeed("redactor.js", "js/redactor");
    page_styleNeed("redactor.css", "js/redactor/css/");
  }

  function GetHTML() {
    page_AddScriptText('$' . '(document).ready( function(){$' . '(\'#' . $this->idname . '\').redactor();});');
    $out = "<div class='cgui_form_text'  style='width: " . $this->width . "px; height: " . $this->height . "px; margin-left:" . $this->left . "px; margin-top: " . $this->top . "px;'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<textarea name='" . $this->name . "' id='" . $this->idname . "' style='position: absolute; width:" . $this->width . "px; height:" . $this->height . "px'>" . $this->Value . "</textarea>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_Checkbox extends CGUI_DataField {
  function __construct($x, $y, $c = false, $v = null, $label = null, $position = 'absolute') {
    $this->left = $x;
    $this->top = $y;
    $this->defval = $v;
    $this->checked = $c;
    $this->label = $label;
    $this->position = $position;
  }

  function GetHTML() {
    $out = "<div class='cgui_form_checkbox'  style='margin-left:" . $this->left . "px; margin-top: " . $this->top . "px;'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    if (!empty($this->label)) {
      $out .= '<label for="' . $this->idname . '">' . $this->label . '</label>' . "\n";
    }
    $out .= "<input type='checkbox' ";
    if ($this->checked) {
      $out .= 'checked="checked" ';
    }
    $out .= "name='" . $this->name . "' id='" . $this->idname . "' value='" . $this->defval . "' style='position: " . $this->position . ";'>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_Hidden extends CGUI_DataField {
  function __construct($v) {
    $this->defval = $v;
  }

  function GetHTML() {
    return "<input type='hidden' name='" . $this->name . "' id='" . $this->idname . "' value='" . $this->Value . "' class='" . $this->class . "'>" . "\n";
  }
}

class CGUI_FileField extends CGUI_DataField {
  function __construct($x, $y, $w) {
    $this->width = $w;
    $this->left = $x;
    $this->top = $y;
  }

  function GetHTML() {
    $out = "<div class='cgui_form_text'  style='margin-left:" . $this->left . "px; margin-top: " . $this->top . "px; width:" . $this->width . "px'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<input type='file' name='" . $this->name . "' id='" . $this->idname . "' style='position: absolute; width:" . $this->width . "px'>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_SelectField extends CGUI_DataField {
  var $data = array();

  var $RowSize = 1;

  var $ContEdit = false;

  var $Multiple = 0;

  function __construct($x, $y, $w, $data, $dkey = "", $v = "") {
    $this->width = $w;
    $this->left = $x;
    $this->top = $y;
    $this->defval = $v;
    if ($dkey == "") {
      $this->data = $data;
    } else {
      foreach($data as $k => $v) {
        $this->data[$k] = isset($v[$dkey]) ? $v[$dkey] : "-выберите-";
      }
    }
  }

  function GetHTML() {
    $out = "<div class='cgui_form_text' style='margin-left:" . $this->left . "px; margin-top: " . $this->top . "px; width:" . $this->width . "px;" . (!empty($this->display) ? ' display: ' . $this->display . ';' : '') . "'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<select name='" . $this->name . ($this->Multiple ? '[]' : '') . "' id='" . $this->idname . "' ";
    if ($this->Multiple) {
      $out .= "multiple";
    }
    $out .= " style='position: absolute; width:" . $this->width . "px' size='" . $this->RowSize . "'>" . "\n";
    if (is_array($this->data) && count($this->data) > 0) {
      foreach($this->data as $k => $v) {
        $out .= "<option ";
        if ($k == $this->Value || (is_array($this->Value) && in_array($k, $this->Value))) {
          $out .= "selected ";
        }
        $out .= "value='" . $k . "'>" . $v . "</option>" . "\n";
      }
    }
    $out .= "</select>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

class CGUI_GridSelect extends CGUI_DataField {
  var $height;

  var $cols = array();

  var $rows = array();

  function __construct($x, $y, $w, $h, $cols, $rows = array()) {
    $this->width = $w;
    $this->left = $x;
    $this->top = $y;
    $this->height = $h;
    $this->cols = $cols;
    $this->rows = $rows;
  }

  function GetHTML() {
    page_AddScriptText("var " . $this->idname . "_sel_row = -1;");
    $out = "";
    $out .= "<div class='cgui_form_text'  style='margin-left:" . $this->left . "px; margin-top: " . $this->top . "px; width:" . $this->width . "px'>" . "\n";
    $out .= parent::GetHTML() . "\n";
    $out .= "<input type='hidden' name='" . $this->name . "' id='" . $this->idname . "'>" . "\n";
    $out .= "<div class='cgui_form_gridbox' style='width: " . $this->width . "px; height: " . $this->height . "px'><table id='" . $this->idname . "_table' cellpadding=2 cellspacing=0>" . "\n";
    $ir = 0;
    foreach($this->rows as $r) {
      $out .= "<tr id='" . $this->idname . "_" . $ir . "' onmouseover='jQuery(this).addClass(\"ovr\");' onmouseout='jQuery(this).removeClass(\"ovr\");' onclick='cgui_form_gridsel_click(\"" . $this->idname . "\", " . $ir . ", " . $this->idname . "_sel_row); " . $this->idname . "_sel_row = " . $ir . "'>" . "\n";
      $ic = 0;
      foreach($this->cols as $c) {
        $w = "";
        if (@$c["width"]) {
          $w = "width: " . $c["width"];
        }
        $out .= "<td style='" . $w . "' id='" . $this->idname . "_" . $ir . "_" . $ic . "'>" . $r[$c["key"]] . "</td>" . "\n";
        $ic++;
      }
      $out .= "</tr>" . "\n";
      $ir++;
    }
    $out .= "</table></div>" . "\n";
    $out .= "</div>" . "\n";
    return $out;
  }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
class CGUI_Form {
  var $id;

  var $idname;

  var $caption;

  var $width;

  var $height;

  var $elms = array();

  var $elms_data = array();

  var $OnExecute;

  var $_gui;

  var $multipart = false;

  var $flags = 0;

  var $Nosubmit = false;

  function __construct($cap, $w = 0, $h = 0, $f = 0) {
    $this->caption = $cap;
    if (!$w) {
      $this->width = 300;
    } else {
      $this->width = $w;
    }
    if (!$h) {
      $this->height = 200;
    } else {
      $this->height = $h;
    }
    $this->id = __get_new_form_id();
    $this->idname = "cgui_form_" . $this->id;
    $this->flags = $f;
    if (CGUI_FORM_FLAG_MODAL & $f) {
      page_scriptNeed("jquery.simplemodal.min.js");
    }
  }

  function Rename($name) {
    $this->id = $name;
    $this->idname = "cgui_form_" . $this->id;
  }

  function _addNewDataFiled($f) {
    $id = count($this->elms_data);
    $f->name = "FORMS_DATA[" . $this->id . "][" . $id . "]";
    $f->id = $id;
    $f->fid = $this->id;
    $f->idname = "cgui_form_" . $this->id . "_field_" . $id;
    $this->elms[] = $f;
    $this->elms_data[] = & $f;
  }

  function ExistInReq() {
    return isset($_REQUEST["FORMS_DATA"][$this->id]);
  }

  function GetValue($i) {
    return $this->elms_data[$i]->Value;
  }

  function GetValueI($i) {
    return intval($this->elms_data[$i]->Value);
  }

  function GetValueH($i) {
    return htmlspecialchars($this->elms_data[$i]->Value);
  }

  function GetNmValue($s) {
    foreach($this->elms_data as $k => $v) {
      if ($v->linkName == $s) {
        return $v->Value;
      }
    }
    return false;
  }

  function GetNmValueI($s) {
    return intval($this->GetNmValue($s));
  }

  function GetNmValueH($s) {
    return htmlspecialchars($this->GetNmValue($s));
  }

  function GetAllNmValues() {
    $ret = array();
    foreach($this->elms_data as $v) {
      if ($v->linkName != "") {
        $ret[$v->linkName] = $v->Value;
      }
    }
    return $ret;
  }

  function Execute() {
    if ($this->Nosubmit) {
      return;
    }
    if (!isset($_REQUEST["FORMS_DATA"][$this->id]) && !isset($_FILES["FORMS_DATA"]["name"][$this->id])) {
      foreach($this->elms as $elm) {
        if (get_parent_class($elm) == "CGUI_DataField") {
          $elm->Value = $elm->defval;
        }
      }
      return;
    }
    $val_ok = true;
    foreach($this->elms as $elm) {
      if (get_parent_class($elm) == "CGUI_DataField") {
        if (get_class($elm) == "CGUI_FileField") {
          if (!$_FILES["FORMS_DATA"]["error"][$this->id][$elm->id] && is_uploaded_file($_FILES["FORMS_DATA"]["tmp_name"][$this->id][$elm->id])) {
            $elm->Value["tmp_name"] = $_FILES["FORMS_DATA"]["tmp_name"][$this->id][$elm->id];
            $elm->Value["name"] = $_FILES["FORMS_DATA"]["name"][$this->id][$elm->id];
            $elm->Value["size"] = $_FILES["FORMS_DATA"]["size"][$this->id][$elm->id];
            $elm->Value["type"] = $_FILES["FORMS_DATA"]["type"][$this->id][$elm->id];
          } else {
            $val_ok = false;
          }
        } else {
          if (is_callable(array(get_class($elm), "_getValue"))) {
            $elm->Value = $elm->_getValue(@$_REQUEST["FORMS_DATA"][$this->id][$elm->id]);
          } else {
            $elm->Value = @$_REQUEST["FORMS_DATA"][$this->id][$elm->id];
          }
          $val_ok &= $elm->Validate();
        }
      }
    }
    if ($val_ok) {
      eval($this->OnExecute . "($" . "this, 0);");
    } else {
      eval($this->OnExecute . "($" . "this, 1);");
    }
  }

  function Button($capt, $x = 0, $y = 0, $w = 0, $s = false) {
    $b = new CGUI_Button($capt, $x, $y, $w, $s);
    $b->fid = $this->id;
    $this->elms[] = $b;
    return $b;
  }

  function Label($capt, $x, $y) {
    $l = new CGUI_Label($capt, $x, $y);
    $this->elms[] = $l;
    return $l;
  }

  function VLine($x, $y, $w) {
    $l = new CGUI_VLine($x, $y, $w);
    $this->elms[] = $l;
    return $l;
  }

  function EmptyDiv($x, $y, $w, $h) {
    $l = new CGUI_EmptyDiv($x, $y, $w, $h);
    $this->elms[] = $l;
    return $l;
  }

  function Text($x, $y, $w, $v = "") {
    $t = new CGUI_Text($x, $y, $w, $v);
    $this->_addNewDataFiled($t);
    return $t;
  }

  function Tracker($x, $y, $w, $v = "") {
    $t = new CGUI_Tracker($x, $y, $w, $v);
    $this->_addNewDataFiled($t);
    return $t;
  }

  function DatePic($x, $y, $w, $v = "") {
    $t = new CGUI_DatePic($x, $y, $w, $v);
    $this->_addNewDataFiled($t);
    return $t;
  }

  function TimePic($x, $y, $w, $v = "") {
    $t = new CGUI_TimePic($x, $y, $w, $v);
    $this->_addNewDataFiled($t);
    return $t;
  }

  function Pasw($x, $y, $w) {
    $t = new CGUI_Text($x, $y, $w, "");
    $t->pwd = true;
    $this->_addNewDataFiled($t);
    return $t;
  }

  function TextArea($x, $y, $w, $h, $v = "") {
    $t = new CGUI_TextArea($x, $y, $w, $h, $v);
    $this->_addNewDataFiled($t);
    return $t;
  }

  function TextArea2($x, $y, $w, $h, $v = "") {
    $t = new CGUI_TextArea2($x, $y, $w, $h, $v);
    $this->_addNewDataFiled($t);
    return $t;
  }

  function Checkbox($x, $y, $checked = false, $def_v = null, $label = null) {
    $c = new CGUI_Checkbox($x, $y, $checked, $def_v, $label);
    $this->_addNewDataFiled($c);
    return $c;
  }

  function Hidden($v) {
    $h = new CGUI_Hidden($v);
    $this->_addNewDataFiled($h);
    return $h;
  }

  function File($x, $y, $w) {
    $this->multipart = true;
    $f = new CGUI_FileField($x, $y, $w);
    $this->_addNewDataFiled($f);
    return $f;
  }

  function Select($x, $y, $w, $data, $dkey = "", $v = "") {
    $s = new CGUI_SelectField($x, $y, $w, $data, $dkey, $v);
    $this->_addNewDataFiled($s);
    return $s;
  }

  function GridSelect($x, $y, $w, $h, $cols, $rows = array()) {
    $s = new CGUI_GridSelect($x, $y, $w, $h, $cols, $rows);
    $this->_addNewDataFiled($s);
    return $s;
  }

  function GetHTML() {
    $valid = "";
    $out = "";
    foreach($this->elms as $elm) {
      if ((strtolower(get_parent_class($elm)) == "cgui_datafield") && count($elm->vls)) {
        $valid = "onsubmit='return cgui_form_validator(this)'";
        $out .= "<script type='text/javascript'>cgui_vforms['" . $this->id . "'] = [];</script>";
        break;
      }
    }
    if ($this->Nosubmit) {
      if ($valid) {
        $valid = "onsubmit='cgui_form_validator(this); return false;'";
      } else {
        $valid = "onsubmit='return false;'";
      }
    }
    $et = "";
    if ($this->multipart) {
      $et = "enctype='multipart/form-data'";
    }
    $s = "";
    if ($this->flags & CGUI_FORM_FLAG_MODAL) {
      $s = "display:none";
    }
    $out .= "<div id='" . $this->idname . "' class='cgui_form_box' style='width:" . $this->width . "px; height: " . $this->height . "px; " . $s . "'>" . "\n" . "<div class='cgui_form_capt'>" . "\n";
    if ($this->flags & CGUI_FORM_FLAG_MODAL) {
      $out .= "<div class='cgui_form_closebtn' style='margin-left:" . ($this->width - 30) . "px' onclick='jQuery.modal.close();' onmouseover='jQuery(this).addClass(\"cgui_form_closebtn_over\")' onmouseout='jQuery(this).removeClass(\"cgui_form_closebtn_over\")'></div>" . "\n";
    }
    $out .= $this->caption . "\n" . "</div>" . "\n" . "<form class='cgui_form' method='post' id='cgui_form_id_" . $this->id . "' name='cgui_form_id_" . $this->id . "' " . $valid . " " . $et . ">" . "\n" . "<input type='hidden' name='nn' value='" . $this->id . "'>" . "\n";
    foreach($this->elms as $elm) {
      $out .= $elm->GetHTML() . "\n";
    }
    $out .= "</form></div>" . "\n";
    return $out;
  }
}

?>