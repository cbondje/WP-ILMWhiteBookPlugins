<?php
class IGKHtmlBootStrapGridCell extends IGKHtmlItem
{
	/** @var IGKHtmlItem */
	private $m_vcontent;
	// private $m_box;
	private $m_i;
	function getHoverColor(){
		return $this["igk-cell-hover-color"];
	}
	public function setHoverColor($value){
		$this["igk-cell-hover-color"] = $value;
	}
	public function __construct(){
		parent::__construct("div");
		$this->m_vcontent = parent::add("div");
		$this->m_vcontent["class"] = "igk-grid-cell-content";

	}
	public function getCellNode(){
		return $this->m_vcontent;
	}

	// public function getBox(){return $this->m_box; }
	public function getContent(){
		return $this->m_vcontent->Content ;
	}
	public function setContent($value){
		$this->m_vcontent->Content  = $value;
	}
	public function ClearChilds(){
		return $this->m_vcontent->ClearChilds();
	}
	public function add($n, $b=null, $s=null){

		return $this->m_vcontent->add($n, $b, $s);
	}
	public function innerHTML(& $options = null)
	{
		if ($this->m_vcontent->HasChilds){
	//		igk_wln("child ");
			$p = igk_getv($this->m_vcontent->Childs , 0);
			if ($p && (get_class($p) == "HtmlBootStrapGrid")){
				//igk_wln("contains grid ". get_class($p) == "HtmlBootStrapGrid");
				//remove
				$this->m_vcontent ["class"] = "+igk-grid-cell-container";
			}
			else
				$this->m_vcontent ["class"] = "-igk-grid-cell-container";
		}
		return $this->m_vcontent->Render($options);
	}
}
