<?php


final class IGKHtmlAccordeonCookiePanel extends IGKObject{
	var $m_pindex;
	var $m_o;

	public function __construct($o, $index){
		$this->m_o = $o;
		$this->m_pindex = $index;

	}
	public function getCookieId(){
	$m = $this->m_o->getCookieId();
	return  $m ? $m."#".$this->m_pindex : null; }
}
////<summary>represent an accordeon html item</summary>
final class IGKHtmlAccordeonItem extends IGKHtmlItem
implements IIGKHtmlCookieItem
{
	private $m_CookieId;
	private $m_panCount;
	private $m_script;
	public function getCookieId(){return $this->m_CookieId; }
	public function setCookieId($v){ $this->m_CookieId = $v; return $this;}

	public function __construct(){
		parent::__construct("div");
		// igk-panel-group panel-group
		$this["class"] = "igk-accordeon";
		$this->setAttribute("igk-js-toggle-cookies", new IGKValueListener($this, "CookieId"));
		$this->m_script = igk_createNode("balafonJS");
		$this->m_script->Content = "igk.winui.accordeon.init();";
	}
	protected function __getRenderingChildren($o=null){
		$s = parent::__getRenderingChildren($o);
		if ($this->m_script)
			$s[] = $this->m_script;
		return $s;
	}
	public function initDemo($t){

		// igk_die("kljb");


		$t->addCode()->Content = <<<EOF

	//add accordeon
	\$a = igk_createNode('accordeon');
	\$a->addPanel("title 1", "content for panel1", true);
	\$a->addPanel("title 2", "content for panel2", false);
	\$a->addPanel("title 3", "content for panel3", false);

EOF;


	}
	public function addPanel($title, $content, $active=false)
	{
		$d = $this->addDiv();
		$d->setClass("igk-panel");
		$h = $d->addDiv()
		->setClass("igk-panel-heading")		;
		$m = $h->addDiv()//A("#")
		//->setAttribute("igk-js-toggle","{parent:'^.igk-panel', target:'.igk-c', data:'igk-collapse'}")
		->setAttribute("igk-js-toggle-cookies", new IGKValueListener(new IGKHtmlAccordeonCookiePanel($this, $this->m_panCount), "CookieId"));
		$m->Content = $title;

		//$active
		$d->addDiv()
		->setClass( (!$active ? "igk-collapse" : ""). " igk-c in")
		->setAttribute("class", "igk-trans-all")
		->Content = $content;
		$this->m_panCount ++;
		return $d;
	}
}

final class IGKAccordeonHtmlItemCtrl extends IGKNonVisibleControllerBase
{
	public function getcanModify(){return false;}
	public function getcanDelete(){return false;}
	public function InitComplete(){
		parent::InitComplete();
		$f =dirname(__FILE__)."/Styles/default.pcss";
        if (file_exists($f))
		    include_once($f);
	}
}