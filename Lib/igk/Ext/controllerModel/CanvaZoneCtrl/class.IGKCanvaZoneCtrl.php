<?php


class IGKCanvaZoneNode extends IGKHtmlItem
{
	private $m_ctrl;

	public function __construct($ctrl){
		parent::__construct("canvas");
		$this->m_ctrl = $ctrl;
		$this["width"] = "320px";
		$this ["height"] = "500px;";
	}
	public function innerHTML(& $xmlOption=null)
	{
		$o = parent::innerHTML($xmloption );
		$script =  IGKHtmlItem::CreateWebNode("script");
		$script->Content = "window.igk.winui.canva.initctrl('".$this->m_ctrl->getUri("getCanvaRendering")."');";
		$o .= $script->Render();
		return $o;
	}
}

igk_bind_attribute("class","IGKCanvaZoneCtrl", new IGKControllerTypeAttribute());
/*
represent a canva zone controller type
*/
abstract class IGKCanvaZoneCtrl extends IGKCtrlTypeBase
{
	private $m_canva;
	public function __construct(){
		parent::__construct();
	}
	protected function InitComplete(){
		parent::InitComplete();
		igk_js_load_script($this->App->Doc, dirname(__FILE__)."/".IGK_SCRIPT_FOLDER);
	}
	public function getCanAddChild(){
		return false;
	}
	protected function initTargetNode(){
		$n = parent::initTargetNode();
		$this->m_canva = new IGKCanvaZoneNode($this);
		$this->m_canva->setId($this->Name."_canva");
		$this->m_canva["class"] = strtolower($this->Name."_canva");
		$n->add($this->m_canva);
		return $n;
	}
	public function View(){
		if (!$this->IsVisible)
		{
			igk_html_rm($this->TargetNode);
		}
	}
	public function getCanvaRendering(){
		//override this method to render on canvas
		//exit for rectangle
		//default canvas width : 300, height:150 . to change used canva.width and canva.height properties. value is an integer.
		igk_wl(IGKIO::ReadAllText(dirname(__FILE__)."/".IGK_DATA_FOLDER."/context.iwcjs"));
		igk_exit();
	}
} 