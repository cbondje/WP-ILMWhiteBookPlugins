<?php

///<summary>bootraps tool tip item</summary>
final class IGKHtmlBootstrapToolTipItem extends IGKHtmlItem
{
	public function setDataPlacement($v){//direction
		$this["data-placement"] = $v;
		return $this;
	}

	public function getDataPlacement($v){//direction
		return $this["data-placement"];
	}
	public function __construct(){
		parent::__construct("a");
		$this["class"] = "";
		$this["href"] = "#";
		$this["data-toggle"] = "tooltip";
	}

	public function initDemo($t)
	{
		$this->ClearChilds();
		$this["title"] = "Demonstration";
		$this->Content = "pass over this ";
		$this->setDataPlacement("right");
		$this->addScript()->Content = <<<EOF
\$igk(igk.getParentScript()).reg_event("mouseover", function(){\$(this).tooltip('show');});
EOF;
	}
}

final class IGKHtmlBootstrapPopoverItem extends IGKHtmlItem
{

	public function setTitle($v){//direction
		$this["data-original-title"] = $v;
		return $this;
	}

	public function getTitle($v){//direction
		return $this["data-original-title"];
	}
	public function setMessage($v){//direction
		$this["data-content"] = $v;
		return $this;
	}

	public function getMessage($v){//direction
		return $this["data-content"];
	}
	public function __construct(){
		parent::__construct("a");
		$this["class"] = "";
		$this["href"] = "#";
		$this["data-toggle"] = "popover";
	}

	public function initDemo($t)
	{
		$this->ClearChilds();
		//$this["title"] ="demonstration";
		$this->Content = "pass over this";
		$this["data-original-title"]  = "the title";
		$this["data-content"]  = "this is a content to show. ... ";
		$this->addScript()->Content = <<<EOF
\$igk(igk.getParentScript()).reg_event("mouseover", function(){
	\$(this).popover('show');
}).reg_event('mouseout', function(){
	\$(this).popover('hide');
});
EOF;
	}
}

final class IGKHtmlBootstrapPopOverHtmlItemCtrl extends IGKNonVisibleControllerBase
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
?>