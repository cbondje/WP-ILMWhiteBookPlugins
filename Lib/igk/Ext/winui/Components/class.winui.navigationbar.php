<?php

/*
* file: class.winui.navigationbar.php
* description : control that can't make navigation page control
* requirement : Scripts/winui/igk.winui.navigationbar.js
**/

$s  = igk_io_dir(IGK_LIB_DIR."/Scripts/winui/igk.winui.navigationbar.js");

// if (file_exists($s) == false)
// {
	// igk_wln("base dir :  ".IGK_LIB_DIR);
	// igk_wln( "current working dir : ". getcwd());
	// igk_wln("files not found : ".$s);

	// return;
// }
if (defined("IGK_WINUI_NAVIGATIONBAR"))
	return;

define("IGK_WINUI_NAVIGATIONBAR",1);


class IGKWinUINavigationBar extends  IGKWinUIControl
{
	private $m_target; //target that will host navigation bar
	private $m_scripts ;
	private $m_pages;
	private $m_ciblingCtrl;

	public function getTarget(){return $this->m_target;}
	public function setTarget($target) { $this->m_target  = $target;}

	public function getCiblingCtrl(){return $this->m_ciblingCtrl;}
	public function setCiblingCtrl($value){ $this->m_ciblingCtrl = $value; }

	public function __construct()
	{
		parent::__construct("div");
		$this["class"] ="IGKWinUI";
		$this->m_scripts =  IGKHtmlItem::CreateWebNode("script");
		$this->m_pages = array();


	}

	protected function innerHTML(& $xmloptions=null)
	{
		$o =  parent::innerHtml($xmloptions);
		if($this->m_ciblingCtrl){
		$ctrl = strtolower($this->m_ciblingCtrl->Name);
		$this->m_scripts->Content = <<<EOF
(function(node, parent){igk.ready(function(){ igk.winui.navigationbar.init(node, parent,  {duration:1000, interval:20, "orientation":"vertical"},false, false);});})(igk.getParentScript(), document.getElementById('{$ctrl}'));
EOF;
		$o .= $this->m_scripts->Render();
		}
		return $o;
	}

	///<summary>add item buttuon</summary>
	///@@ $page: the page name
	///@@ $target : the controller target name
	public function addPage($page, $target)
	{
		$t = $this->add("a", array(
		   "href"=>"#".strtolower($page),
		   "igk-navigation-target"=>$target));

		$t->Content = R::ngets("btn.".strtolower($page));
		$this->m_pages[] = $t;
	}
	public function init()
	{

$p_content = $this->TargetNode->addDiv(array("class"=>"kms-page-content" ));

$v_d =  IGKHtmlItem::CreateWebNode("div");
$v_d["style"] = "overflow:hidden";

igk_html_article($this , "default.phtml", $v_d);
$d = $v_d->getElementsByTagName("li");
$globalmenu = array();
if ($d)
{
	foreach($d as $k)
	{
	   $s =  $k->innerHTML;
	   $uri = $k["uri"];
	   $k->ClearChilds();
	   //$k->Add("a", array("href"=>$this->getUri("navigate&n=".base64_encode($s))))->Content = $s;
	   $ctrl_name = strtolower("kms_".$s."ctrl");
	   if ( ($uri==null) &&  ( ($ctrl = igk_getctrl($ctrl_name, false)) !=null) && ($ctrl->webParentCtrl!=null) && ($ctrl->webParentCtrl->Name == "kms_defaultctrl"))
	   {
			$k->Add("a", array(
		   "href"=>"#".strtolower($s),
		   "igk-navigation-target"=>$ctrl_name))->Content=  R::ngets("btn.".strtolower($s));
		   $globalmenu[]  = $s;
	   }
	   else{
			if ($uri==null){
				$k->Add("a", array("href"=>$this->getUri("navigate&n=".$s), "class"=>"nofocus"))->Content = R::ngets("btn.".$s);
			}
			else{
				$k->Add("a", array("href"=>$uri, "target"=>"_blank", "class"=>"nofocus" ))->Content = R::ngets("btn.".$s);
			}
	   }
	}
	$v_d->addScript()->Content = <<<EOF
(function(node, parent){igk.ready(function(){ igk.winui.navigationBar.init(node, parent,  {duration:1000, interval:20, "orientation":"vertical"},false, false);});})(igk.getParentScript(), document.getElementById('kms_defaultctrl'));
EOF;
}
	}
}


?>