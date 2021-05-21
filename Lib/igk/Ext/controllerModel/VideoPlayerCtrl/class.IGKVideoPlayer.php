<?php
final class IGKHtmlObjectNode extends IGKHtmlItem
{
	public function __construct()
	{
		parent::__construct("object");
	}
}
class IGKHtmlVideoSourceNode extends IGKHtmlItem
{
	public 	function getSrc(){return $this["src"];}
	public 	function setSrc($value){$this["src"] = $value;}

	public 	function getType(){return $this["type"];}
	public 	function setType($value){$this["type"] = $value;}

	public function __construct($src, $type)
	{
		parent::__construct("source");
		$this["src"] = $src;
		$this["type"] = $type;
	}
	public function ClearChilds(){
		//no clear childs
	}
}
final class IGKHtmlVideoNode extends IGKHtmlItem
{
	private $m_ObjectNode;
	private $m_sources;
	public function addSource($src, $type)
	{
		$h = new IGKHtmlVideoSourceNode($src, $type);

		$this->m_sources[] = $h;
		$this->add($h);
		if (igk_count($this->m_sources) == 1)
			$this["src"] = $src;
		else{
			$this["src"] = null;
		}
		return $h;
	}
	public  function clearSource()
	{
		$this->m_sources = array();
	}
	public function getAllowControl(){
		return ($this["controls"] != null);
	}
	public function setAllowControl($value){
		if ($value == null)
		{
			$this["controls"]= null;
		}
		else{
			$this->activate("controls");// = new IGKHtmlNoValueAttribute();
		}
	}
	public function __construct()
	{
		parent::__construct("video");
		$this->m_sources = array();
		$this->m_ObjectNode = new IGKHtmlObjectNode();
		//auto control or not
		$this["controls"]= null;
		$this["height"] = "300px";
		$this["width"] = "400px";

	}

	public function ClearChilds(){
		//no clear childs
		parent::ClearChilds();
	}
	public function innerHTML(& $xmlOptions= null)
	{
		$o  = parent::innerHTML($xmloptions);
		$this->_buildObject();
		$o .= $this->m_ObjectNode->Render();
		return $o;
	}
	private function _buildObject()
	{
		$t = $this->m_ObjectNode;
		$t->ClearChilds();
		$t["data"] = igk_count( $this->m_sources)>0 ? $this->m_sources[0]->Src : null;
		$t["width"] = $this["width"];
		$t["height"] = $this["height"];

		$r = $t->add("embed");
		$r["src"] =  igk_count( $this->m_sources)>0 ? $this->m_sources[0]->Src : null;
		$r["width"] = $this["width"];
		$r["height"] = $this["height"];
		$r->add("data", array());
		$t->addDiv()->Content = R::ngets("msg.cantrendervideo");
		$this->m_ObjectNode = $t;

	}

}
abstract class IGKVideoPlayerCtrl extends IGKCtrlTypeBase
{
	private $m_vidNode;

	public static function GetAdditionalConfigInfo()
	{
		return array(
		"clPrimaryMovie"=>new IGKAdditionCtrlInfo("text", ""),
		"clPrimaryWidth"=>new IGKAdditionCtrlInfo("text", "400px"),
		"clPrimaryHeight"=>new IGKAdditionCtrlInfo("text", "300px")
		);
	}

	public function __construct(){//vid player construct
		parent::__construct();
	}
	protected function initTargetNode()
	{
		$t  = parent::initTargetNode();

		$n = new IGKHtmlVideoNode();
		$t->add($n);
		$this->m_vidNode = $n;
		return $t;
	}

	protected function setupCtrlConfigonfigSettings()
	{
		parent::setupCtrlConfigonfigSettings();
		//init basics source
		$this->_initBasicsSource();
	}
	private function _initBasicsSource()
	{
		if (igk_getv($this->Configs,"clPrimaryMovie" ))
		{
			$this->setSource($this->Configs->clPrimaryMovie,"video/mp4");
		}
		if (igk_getv($this->Configs,"clPrimaryWidth" ))
		{
			$this->m_vidNode["width"] = $this->Configs->clPrimaryWidth;
		}
		if (igk_getv($this->Configs,"clPrimaryHeight" ))
		{
			$this->m_vidNode["height"] = $this->Configs->clPrimarHeight;
		}
	}

	public function vidplayer_editsource_ajx()
	{

	}
	public function getControllerConfigOptions()
	{
		/** @var IGKHtmlItem $t*/
		$t = parent::getControllerConfigOptions();
		IGKHtmlUtils::AddImgLnk($t->add("li"), igk_js_post_frame($this->getUri("vidplayer_editsource_ajx")), "videos");
		return $t;
	}
	public function setSource($src=null, $type="video/mp4")
	{
		$this->m_vidNode->clearSource();
		$this->addSource($src, $type);

	}
	public function addSource($src=null, $type="video/mp4")
	{
		$src = $src == null? igk_getr("src") : $src;
		$type = $type == null? igk_getr("type") : $type;
		$this->m_vidNode->addSource(igk_io_baseuri()."/R/Videos/".$src, $type);
	}
	public function noControl(){
		$this->m_vidNode->setAllowControl(false);
	}
	public function allowControl(){
		$this->m_vidNode->setAllowControl(true);
	}
	public function  View(){
		//no view. rendering
		if ($this->IsVisible == false)
			igk_html_rm($this->TargetNode);
	}
	protected function _showViewFile(){
		//not visible by default
	}
	public function getCanAddChild(){return false;}
	protected function _showChild($targetnode=null){
		//no childs
	}
}
?>