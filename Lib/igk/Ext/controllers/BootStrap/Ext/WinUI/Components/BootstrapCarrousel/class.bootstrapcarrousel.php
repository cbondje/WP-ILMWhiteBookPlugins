<?php
final class IGKHtmlBootstrapCarouselItem extends IGKHtmlItem
{
	public function __construct(){
		parent::__construct("div");
		$this["class"] = "igk-carrousel carousel";
		$this["data-ride"] = "carousel";
	}
	public function initDemo($t){
		$this->ClearChilds();
		$this->setId("car");
		$ol = $this->add("ol");
		$ol["class"] = "carousel-indicator";
		$this->addItem($ol,"#car" , "0", true);
		$this->addItem($ol,"#car" , 1, false);
		$this->addItem($ol,"#car" , 2, false);
		$this->addItem($ol,"#car" , 3, false);

		$b = $this->addDiv()->setClass("carousel-inner");
		$b->setStyle("height:200px; width: 400px;");
		$this->addSlide($b, "page1", true);
		$this->addSlide($b, "page2");
		$this->addSlide($b,"page3");
		$this->addSlide($b,"page4");

		$this->addButton("#car");
	}
	public function addSlide($d, $content, $active=false)
	{
		$h  = $d->addDiv()->setClass("item");
		$h->Content = $content;
		if ($active)
		{
			$h->setClass("active");
		}

		return $h;
	}
	public function addItem($ol, $target, $slide, $active=false){
		$li =  $ol->add("li");
		$li["data-target"] = $target;
		$li["data-slide-to"] = $slide;
		if ($active){
			$li->setClass("+active");
		}
		return $li;
	}
	public function addButton($target){
		$this->addA($target)->setClass("left ")
		->setAttribute("data-slide", "prev")
		->add("span")
		->setClass("glyphicon glyphicon-chevron-left");
		//->Content = "prev";
		$this->addA($target)->setClass("right ")
		->setAttribute("data-slide", "next")
		->add("span")
		->setClass("glyphicon glyphicon-chevron-right");//->Content = "next";
	}
}

final class IGKCarrouselHtmlItemCtrl extends IGKNonVisibleControllerBase
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