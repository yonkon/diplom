<?php

$cgui_all_forms_cnt = 0;
$cgui_all_tables_cnt = 0;
$cgui_all_upanels_cnt = 0;

define("CGUI_FORM_FLAG_MODAL", 1);

define("CGUI_DEBUG_MODE", 0);

include_once("gui_table.php");
include_once("gui_upanel.php");
include_once("gui_form.php");
include_once("gui_filters.php");

function __get_new_form_id()
{
    global $cgui_all_forms_cnt;
    return $cgui_all_forms_cnt++;
}

function __get_new_table_id()
{
    global $cgui_all_tables_cnt;
    return $cgui_all_tables_cnt++;
}

function __get_new_upanel_id()
{
    global $cgui_all_upanels_cnt;
    return $cgui_all_upanels_cnt++;
}


class CGUI_MainMenuItem
{
    var $id = "";
    var $name = "";
    var $section = "";
    var $cname = "punkt";
    var $def = false;
    var $subitems = array();
    var $selected = false;
    var $caption = "Undefined";

    function AddItem($id, $name, $sec, $def = false)
    {
        $i = new CGUI_MainMenuItem();
        $i->id = $id;
        $i->name = $name;
        $i->section = $sec;
        $i->def = $def;
        $this->subitems[] = $i;
        return $i;
    }

    function Update()
    {
        if (isset($_REQUEST["subsection"])) {

            $s = $_REQUEST["subsection"];
            $this->selected = false;

            foreach ($this->subitems as $i) {
                if ($i->section == $s) {
                    $this->selected = $i;
                    $i->cname = "punkt_sel";
                    break;
                }
            }

            if (!$this->selected) {
                $this->selected = new CGUI_MainMenuItem();
                $this->selected->section = $s;
            }
        } else {

            foreach ($this->subitems as $i) {
                if ($i->def) {
                    $this->selected = $i;
                    $i->cname = "punkt_sel";
                    break;
                }
            }
        }
    }
}

class CGUI_MainMenu
{
    var $items = array();
    var $updated = false;
    var $selected = false;

    function __construct()
    {
    }

    function __destruct()
    {
    }

    function Update()
    {
        if ($this->updated) {
            return;
        }
        $this->updated = true;

        if (isset($_REQUEST["section"])) {

            $s = $_REQUEST["section"];

            $allowed_section = false;
            foreach ($this->items as $i) {
                if ($i->section == $s) {
                    $allowed_section = true;
                    break;
                }
            }

            if ($allowed_section) {
                foreach ($this->items as $i) {
                    if ($i->section == $s) {
                        $this->selected = $i;
                        $i->cname = "punkt_sel";
                        break;
                    }
                }
            } else {
                foreach ($this->items as $i) {
                    if ($i->def) {
                        $this->selected = $i;
                        $i->cname = "punkt_sel";
                        break;
                    }
                }
            }
        } else {
            foreach ($this->items as $i) {
                if ($i->def) {
                    $this->selected = $i;
                    $i->cname = "punkt_sel";
                    break;
                }
            }
        }

        if ($this->selected) {
            $this->selected->Update();
        }
    }

    function GetHTML()
    {
        $this->Update();
        $out = "<table class='cgui_mainmenu_box'><tr>" . "\n";
        foreach ($this->items as $v) {
            $a = "index.php?section=" . $v->section;
            $out .= "<td id='cgui_mainmenu_section_" . $v->section . "' nowrap class='" . $v->cname . "' onmouseover='this.className=\"punkt_ovr\"'" . " onmouseout='this.className=\"" . $v->cname . "\"' onclick='document.location.href=\"" . $a . "\"'><a href='" . $a . "'>" . $v->name . "</a></td>" . "\n";
        }

        $out .= "<td style='width:100%'>&nbsp;</td>" . "\n";
        $out .= '<td style="white-space: nowrap; padding-right: 20px;">' . sotr_getFullName($_SESSION['user']['data']['id']) . '</td>' . "\n";
        $out .= "<td class='punkt' onmouseover='this.className=\"punkt_ovr\"' onmouseout='this.className=\"punkt\"' onclick='document.location.href=\"?logout\"'>" . "<a href='?logout'>Выход</a></td></tr></table>" . "\n";

        if ($this->selected && count($this->selected->subitems)) {
            $out .= "<table class='cgui_mainmenusub_box'><tr>";

            foreach ($this->selected->subitems as $v) {
                $a = "index.php?section=" . $this->selected->section . "&subsection=" . $v->section;
                $out .= "<td nowrap class='" . $v->cname . "' onmouseover='this.className=\"punkt_ovr\"' onmouseout='this.className=\"" . $v->cname . "\"' onclick='document.location.href=\"" . $a . "\"'><a href='" . $a . "'>" . $v->name . "</a></td>" . "\n";
            }

            $out .= "<td style='width:100%;'>&nbsp;</td></tr></table>" . "\n";
        }

        return $out;
    }

    function AddItem($id, $name, $sec, $def = false)
    {
        $item = new CGUI_MainMenuItem();
        $item->name = $name;
        $item->section = $sec;
        $item->def = $def;
        $item->id = $id;
        $this->items[] = $item;
        return $item;
    }
}


class CGUI_Informer
{
    var $text = "";
    var $col = "black";
    var $bcol = "white";
    var $showed = false;

    function __construct()
    {
        page_scriptNeed("gui_informer.js", "gui");
        if (isset($_SESSION["cgui_informer"])) {
            $this->text = $_SESSION["cgui_informer"]["text"];
            $this->col = $_SESSION["cgui_informer"]["col"];
            $this->bcol = $_SESSION["cgui_informer"]["bcol"];
            unset($_SESSION["cgui_informer"]);
        }
    }

    function __destruct()
    {
        if (!$this->showed) {
            $_SESSION["cgui_informer"]["text"] = $this->text;
            $_SESSION["cgui_informer"]["col"] = $this->col;
            $_SESSION["cgui_informer"]["bcol"] = $this->bcol;
        }
    }

    function GetHTML()
    {
        if ($this->showed) {
            return "";
        }
        $this->showed = true;

        if ($this->text == "") {
            return "";
        }
        $out = "<div id='cgui_informer' class='cgui_informer' style='background-color: " . $this->bcol . "; color: " . $this->col . "'>" . $this->text . "</div>" . "\n";
        return $out;
    }

    function AddMsg($text, $col, $bcol)
    {
        $this->text = $text;
        $this->col = $col;
        $this->bcol = $bcol;
    }

    function OK($text)
    {
        $this->AddMsg($text, "white", "green");
    }

    function ERR($text)
    {
        $this->AddMsg($text, "white", "red");
    }
}


class CGUI_CmdMenu
{
    var $items = array();

    function GetHTML()
    {

        if (!count($this->items)) {
            return;
        }

        $h = max(130, count($this->items) * 32 + 2);

        page_scriptNeed("gui_cmdmenu.js", "gui");

        $out = "<div id = 'cgui_commandmenu' class='cgui_commandmenu' style='height:" . $h . "px'><div class='cgui_commandmenu_cont'><br>" . "\n";

        foreach ($this->items as $i) {
            $out .= "<div class='item' onclick='document.location.href=\"" . $i["cmd"] . "\"'>" . $i["name"] . "</div>" . "\n";
        }

        $out .= "</div><div class='cgui_commandmenu_bar'><br>К<br>О<br>М<br>А<br>Н<br>Д<br>Ы</div></div>" . "\n";
        return $out;
    }

    function AddItem($name, $cmd)
    {
        $this->items[] = array("name" => $name, "cmd" => $cmd);
    }
}

class CGUI
{
    var $forms = array();
    var $tables = array();
    var $panels = array();
    var $filterscol = array();
    var $mmenu;
    var $informer;
    var $tmpls = array();
    var $Vars = array();
    var $url = "";
    var $cmdmenu;
    private $dbgmsg = array();

    function __construct()
    {
        page_styleNeed("style.css", "gui/skin");
        page_scriptNeed("1.7.2.jquery.js");
        page_scriptNeed("gui.js", "gui");
        $this->mmenu = new CGUI_MainMenu();
        $this->informer = new CGUI_Informer();
        $this->cmdmenu = new CGUI_CmdMenu();
    }

    function Url($exclude = array())
    {
        if ($this->url == "") {
            if ($this->mmenu && $this->mmenu->selected) {
                $this->url = "section=" . $this->mmenu->selected->section . "&";
                $exclude[] = 'section';
                if ($this->mmenu->selected->selected) {
                    $this->url .= "subsection=" . $this->mmenu->selected->selected->section . "&";
                    $exclude[] = 'subsection';
                    foreach ($_GET as $key => $value) {
                        if (in_array($key, $exclude)) {
                            continue;
                        }
                        if (stripos($key, 'sort_cgui_') !== false) {
                            continue;
                        }
                        $this->url .= $key . "=" . $value . "&";
                    }
                }
            }
        }
        return $this->url;
    }

    function HTML()
    {
        print "<div id='cgui_area' class='cgui_area'>" . "\n";
        foreach ($this->filterscol as $elm) {
            $elm->Execute();
        }
        foreach ($this->forms as $elm) {
            $elm->Execute();
        }
        if (count($this->tmpls)) {
            $i = 0;
            do {
                if (file_exists($this->tmpls[$i])) {
                    include($this->tmpls[$i]);
                }
                $i++;
            } while ($i < count($this->tmpls));
        }
        if ($this->informer) {
            print $this->informer->GetHTML() . "\n";
        }
        if (CGUI_DEBUG_MODE) {
            print "<div style='overflow:auto; width: 200px; height: 400px; position:fixed; border: 2px solid gray; background-color:white; font-size:6pt; right:0; bottom:0'>";
            foreach ($this->dbgmsg as $m) {
                switch ($m["m"]) {
                    case 1:
                        $c = "red";
                        break;
                    case 2:
                        $c = "gray";
                        break;
                    default:
                        $c = "black";
                }
                print "<div style='margin-left:2px; margin-top:1px; color: " . $c . "'>" . $m["t"] . "</div>";
            }
            print"</div>";
        }
        print "</div>" . "\n";
    }

    function Form($cap, $w = 0, $h = 0, $flags = 0)
    {
        $f = new CGUI_Form($cap, $w, $h, $flags);
        $f->_gui = $this;
        $this->forms[] = $f;
        return $f;
    }

    function ModalForm($cap, $w = 0, $h = 0, $flags = 0)
    {
        $f = $this->ModalFormEx($cap, $w, $h, $flags);
        $this->forms[] = $f;
        return $f;
    }

    function ModalFormEx($cap, $w = 0, $h = 0, $flags = 0)
    {
        $f = new CGUI_Form($cap, $w, $h, $flags | CGUI_FORM_FLAG_MODAL);
        page_scriptNeed("jquery.simplemodal.min.js");
        $f->_gui = $this;
        return $f;
    }

    function Table($uid = "", $defaults = array())
    {
        $t = new CGUI_Table($uid, $defaults);
        $t->_gui = $this;
        $this->tables[] = $t;
        return $t;
    }

    function UPanel($uid = "")
    {
        $p = new CGUI_UserPanel($uid);
        $p->_gui = $this;
        $this->panels[] = $p;
        return $p;
    }

    function BtnsPanel($uid = "", $enable = false, $mode = CGUI_ButtonsPanel::VISIBLE_HIDDEN)
    {
        $p = new CGUI_ButtonsPanel($uid, $enable, $mode);
        $p->_gui = $this;
        $this->panels[] = $p;
        return $p;
    }

    function FltrCol($nm, $cfg)
    {
        $ret = new CGUI_FilterCollection($this, $nm, $cfg);
        $this->filterscol[] = $ret;
        return $ret;
    }

    function OK($s)
    {
        if ($this->informer) {
            $this->informer->OK($s);
        }
    }

    function ERR($s)
    {
        if ($this->informer) {
            $this->informer->ERR($s);
        }
    }

    function DBG($s)
    {
        $this->dbgmsg[] = array("t" => $s, "m" => 0);
    }

    function DBGe($s)
    {
        $this->dbgmsg[] = array("t" => $s, "m" => 1);
    }

    function DBGv($s)
    {
        $this->dbgmsg[] = array("t" => $s, "m" => 2);
    }

    function getIcon($url, $nm, $cap)
    {
        return "<a href='" . $url . "' title='" . $cap . "'><img width='16' height='16' alt='" . $cap . "' src='/gui/skin/i" . $nm . ".png'></a>";
    }
}

$GUI = new CGUI(); ?>