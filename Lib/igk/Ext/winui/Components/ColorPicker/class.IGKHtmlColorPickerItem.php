<?php
//igk.winui.colorpicker
final class IGKHtmlColorPickerItem extends IGKHtmlItem
{
	private $m_script;
	private $r;
	private $g;
	private $b;
	public function getWebValue(){
		$v_r = IGKNumber::ToBase($this->r, 16, 2);
		$v_g = IGKNumber::ToBase($this->g, 16, 2);
		$v_b = IGKNumber::ToBase($this->b, 16, 2);

		return "#".$v_r.$v_g.$v_b;
	}
	public function __construct(){
		parent::__construct("div");
		$this->setClass("igk-clpicker");

		$this->addDiv()
			->addTrackBar()->setId("clr");
		$this->addDiv()
			->addTrackBar()->setId("clg");
		$this->addDiv()
			->addTrackBar()->setId("clb");

		$frm = $this->addDiv()->addForm();
		$frm->addInput("clValue", "hidden",$this->getWebValue());
		//$this->addDiv()->Content = $this->getWebValue();


		$this->addScript()->Content =<<<EOF
ns_igk.readyinvoke('igk.winui.components.colorpicker.init');
EOF;

		include(dirname(__FILE__)."/Styles/default.pcss");
	}

	public function initDemo($t){
		$this["demo"] = "1";
		$this->addDiv()->Content = "for demo";

	}
}

final class IGKHtmlCircleColorPickerItem extends IGKHtmlItem
{
	private $m_script;
	private $m_ctrl;
	private $r;
	private $g;
	private $b;
	public function getWebValue(){
		$v_r = IGKNumber::ToBase($this->r, 16, 2);
		$v_g = IGKNumber::ToBase($this->g, 16, 2);
		$v_b = IGKNumber::ToBase($this->b, 16, 2);

		return "#".$v_r.$v_g.$v_b;
	}
	public function __construct(){
		$this->m_ctrl = igk_getctrl("igkcolorpickercomponentcontroller");
		parent::__construct("div");
		$this->setClass("igk-circ-clpicker");
		$this->initView();

	}
	public function initView(){
		$this->ClearChilds();
		$d = $this->addDiv()->setClass("dispib");
		$c = $d->addDiv();
		$uri = $this->m_ctrl->getDataDir()."/R/Img/bg-circ.png";
		$uri = IGKResourceUriResolver::getInstance()->resolve($uri); 
		$c->addImg()->setAttribute("src", $uri);

		$c->addDiv()->setClass("posab loc_l loc_t loc_r fith igk-circ-pan")->setStyle("border :1px solid #eee");

		$d->addDiv()->setClass("dispb alignc igk-circ-v")->Content = "&nbsp;";
		$d->addDiv()->setClass("dispb")->addTrackbar();
		$i = $d->addDiv()->setClass("dispb")->addInput("clvalue", "text");
		$i["class"]= "igk-form-control";

$this->addScript()->Content = <<<EOF
ns_igk.readyinvoke('igk.winui.components.circleColorPicker.init');
EOF;
	}

	public function initDemo($t){
		$this["demo"] = "1";
		$this->addDiv()->setClass("demo")->Content = "for demo";
	}
}



final class IGKColorPickerComponentController extends IGKNonVisibleControllerBase
{
	public function getcanModify(){return false;}
	public function getcanDelete(){return false;}
	public function InitComplete(){
		parent::InitComplete();
	}

}