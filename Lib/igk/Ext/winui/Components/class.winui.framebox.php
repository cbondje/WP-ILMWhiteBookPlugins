<?php
class IGKWinUI_framebox extends IGKHtmlItem
{
	private $m_script;
	private $m_nodes;
	var $closeUri;

	public function __construct()
	{
		parent::__construct("div");
		$this["class"] = "posab fitw fith loc_t loc_l overflow_none ztop";

	}

	public function render($options =null)
	{
		$out ="";
		$out .="<div ";
		$out .= $this->getAttributeString();
		$out .= ">";
		$out .= $this->innerHTML($options);
		$this->m_script =  IGKHtmlItem::CreateWebNode("script");
		$this->m_script->Content = "igk.winui.framebox.initSingle(igk.getParentScript(), ".(($this->closeUri)?"'". $this->closeUri. "'":'null'). ");";
		$out .= $this->m_script->Render($options);
		$out .= "</div>";
		return $out;
	}

}
?>