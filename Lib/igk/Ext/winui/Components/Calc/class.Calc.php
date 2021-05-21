<?php
final class IGKHtmlCalcItem extends IGKHtmlItem
{
	private $m_mode;
	private $m_value;

	public function getMode(){return $this->m_mode; }
	public function setMode($v){$this->m_mode = $v; return $this; }


	public function getValue(){return $this->m_value; }
	public function setValue($v){$this->m_value = $v; return $this; }


	public function __construct(){
		parent::__construct("div");
		$this["class"]="igk-calc";

	}
	public function initView(){
		$this->ClearChilds();
		//model de vuew
		$frm = $this->addForm();
		$dv = $frm->addDiv();
		//$dv->addLabel("clValue")->Content = R::ngets("lb.verser");
		$i = $dv->addInput("clValue", "text", new IGKValueListener($this, "Value"))->setAttribute("default-v",new IGKValueListener($this, "Value"));
		$i["class"] = "+alignr";
		$frm->addDiv()->add("span")->Content = "0";
	}
}

final class IGKHtmlCalcItemCtrl extends IGKNonVisibleControllerBase
{
	public function InitComplete(){
		parent::InitComplete();
        $f =dirname(__FILE__)."/Styles/default.pcss";
        if (file_exists($f))
		    include_once($f);

	}
}
?>