<?php

final class IGKHtmlSliderZone extends IGKHtmlItem{
	function getIsRenderTagName(){return false; }
	public function __construct(){
		parent::__construct("igk-slider-zone");
	}
}
class IGKHtmlSliderItem extends IGKHtmlComponentNodeItem{

	/** @var IGKHtmlItem */
	private $m_content;
	private $m_script;
	private $m_orientation;


	public function getOrientation(){return $this->m_orientation; }
	public function setOrientation($v){ $this->m_orientation = $v; return $this; }

	public function __construct(){
		parent::__construct("div");
		$this["class"]="igk-slider";
		$this->m_orientation='horizontal'; //'vertical';
		$this->m_content = parent::add(new IGKHtmlSliderZone());
		$this->m_content["class"] = "igk-slider-c";
		$this->m_script = parent::addScript();
	}
	public function addPage($n){
		$dv = $this->m_content->addDiv();
		$dv["class"] = "igk-slider-page";
		$dv->add($n);
		return $dv;
	}
	public function ClearChilds(){
		$this->m_content->ClearChilds();
	}
	public function initDemo($t){
		$this->ClearChilds();
		$this->addPage(igk_createNode("div")->setContent("page1"));
		$this->addPage(igk_createNode("div")->setContent("page2"));
		$this->addPage(igk_createNode("div")->setContent("page3"));
	}
	public function AcceptRender($options=null){
		$this->m_script->Content = "igk.winui.slider.init({orientation:'{$this->m_orientation}'})";
		return true;
	}
}
