<?php

class CGUI_BasePanel {
	var $id;
	var $idname;
	var $Html = "";
	
	function __construct($uid="")
	{
		if ($uid!="")
			$this->id = "u".$uid;
		else
			$this->id = __get_new_upanel_id();
		
		$this->idname = "cgui_upanel_id".$this->id;
				
		page_ScriptNeed("gui_upanel.js", "gui");
	}
	
	function __destruct()
	{
		
	}
	
	function AddHTML($s)
	{
		$this->Html.= $s;
	}
	
}

class CGUI_ButtonsPanel extends CGUI_BasePanel {
	
	const VISIBLE_HIDDEN = 0;
	const BLOCK_NONE = 1;
	
	var $defOpen = false;
	var $mode = ENABLE_DISABLE;
	var $enabled = true;
	private $buttons = array();
			
	function __construct($uid, $en, $mode)
	{
		parent::__construct($uid);
		$this->enabled = $en;
		$this->mode = $mode;
		$this->idname = "cgui_bpanel_id".$this->id;
	}
			
	function GetHTML()
	{
		
		$s = "";
		if (!$this->enabled)
		{
			switch ($this->mode)
			{
				case VISIBLE_HIDDEN:
				default:
					$s = "style='visibility: hidden'";
					break;
				case BLOCK_NONE:
					$s = "style='display: none'";
					break;
					 
			}
		}
				
		$out = "";
		$out.= "<div id='".$this->idname."' class='cgui_bpanel_block' ".$s."><div class='cgui_bpanel_block_body'>";
		
		foreach ($this->buttons as $b)
		{
			if (isset($b['js']))
				$out.= "<button class='cgui_bpanel_button' onclick='cgui_bpanel_btn_pressed(\"". $this->idname ."\", function(){ return ".$b['js']." })'>".$b['caption']."</button>";
			else if (isset($b['href']))
				$out.= "<button class='cgui_bpanel_button' onclick='cgui_bpanel_btn_pressed(\"". $this->idname ."\", function(){ document.location.href = \"".$b['href']."\" })'>".$b['caption']."</button>";
		}
		
		$out.= "</div></div>";
		return $out;
	}
	
	function AddJsButton($capt, $js="")
	{
		$this->buttons[] = array(
		'caption' => $capt,
		'js' => $js,
		);
	}
	
	function AddUrlButton($capt, $url="")
	{
		$this->buttons[] = array(
		'caption' => $capt,
		'href' => (($url=="") ? "#" : $href),
		);
	}
	
}

class CGUI_UserPanel extends CGUI_BasePanel {
	var $defOpen = false;
	var $Caption;
	
	function __construct($uid="", $cap="")
	{
		parent::__construct($uid);
		$this->Caption = $cap;		
	}
	
	function GetHTML()
	{
		$out = "";
		$out.= "<div class='cgui_upanel_block'>" . "\n" .
		"<div class='cgui_upanel_block_hdr'><span id='".$this->idname."_lbl' class='";
		if ($this->defOpen) $out.= "icon_exp"; else $out.= "icon";
		$out.="' onclick='cgui_upanel_toggle(\"".$this->idname."\")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>" . "\n" . $this->Caption . "\n" .
		"</div><div id='".$this->idname."' class='cgui_upanel_block_body' style='display:";
		if ($this->defOpen) $out.= "block"; else $out.= "none";
		$out.= "'>" . "\n" . $this->Html . "\n" . "</div></div>" . "\n";
		return $out;
	}
}

?>
