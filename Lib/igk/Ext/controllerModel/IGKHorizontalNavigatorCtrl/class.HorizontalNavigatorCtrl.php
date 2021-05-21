<?php
/*
file: class.HorizontalNavigatorCtrl
Description: control that will host every article and navigate thru them by configuration

*/
//controller code class declaration
//file is a part of the controller tab list
abstract class HorizontalNavigatorCtrl extends IGKCtrlTypeBase {
	public function getName(){return get_class($this);}

	protected function InitComplete(){
		parent::InitComplete();
		//please enter your controller declaration complete here
		igk_js_load_script($this->App->Doc, dirname(__FILE__)."/".IGK_SCRIPT_FOLDER);
	}
	//@@@ init target node
	protected function initTargetNode(){
		$node =  parent::initTargetNode();
		return $node;
	}
	public function getcanAddChild(){
		return false;
	}
	public static function GetAdditionalDefaultViewContent(){
		return null;
	}

	public function storeDBConfigsSetting()
	{
		parent::storeDBConfigsSetting();

	}
	public static function GetAdditionalConfigInfo()
	{
		return array(
		"clShowBullet"=> new IGKAdditionCtrlInfo("bool", true),
		"clanim_NAV_ANIMFREQUENCY"=> new IGKAdditionCtrlInfo("text", 20),
		"clanim_NAV_ANIMDURATION"=> new IGKAdditionCtrlInfo("text", 1000),
		"clanim_NAV_AUTOANIMATE"=> new IGKAdditionCtrlInfo("bool", true),
		"clanim_NAV_AUTOPERIOD"=> new IGKAdditionCtrlInfo("text", 10000),
		"clanim_NAV_ANIMTYPE"=> new IGKAdditionCtrlInfo("select",
		array("translation"=>"translation", "rotation"=>"rotation", "fade"=>"fade"),
		"translation")
		);
	}
	public static function SetAdditionalConfigInfo(& $t)
	{
		$t["clShowBullet"] = igk_getr("clShowBullet");
		$t["clanim_NAV_ANIMFREQUENCY"] = igk_getr("clanim_NAV_ANIMFREQUENCY");
		$t["clanim_NAV_ANIMDURATION"] = igk_getr("clanim_NAV_ANIMDURATION");
		$t["clanim_NAV_AUTOANIMATE"] = igk_getr("clanim_NAV_AUTOANIMATE");
		$t["clanim_NAV_ANIMTYPE"] = igk_getr("clanim_NAV_ANIMTYPE");
		$t["clanim_NAV_AUTOPERIOD"] = igk_getr("clanim_NAV_AUTOPERIOD");
	}
	//----------------------------------------
	//Please Enter your code declaration here
	//----------------------------------------
	//@@@ parent view control
	public function View(){
		$this->TargetNode->ClearChilds();
		$c = new IGKJS_horizontalPane($this->TargetNode);
		$this->buildPage($c);
		$c->ShowBullet = true;
		$c->AnimInterval  = igk_getv($this->Configs, "clanim_NAV_ANIMFREQUENCY", 20);//igk_get_uvar(strtoupper($this->Name."_NAV_ANIMFREQUENCY"), 20, true,"rate time in (ms > 0)");
		$c->AnimDuration  = igk_getv($this->Configs, "clanim_NAV_ANIMDURATION", 1000);//igk_get_uvar(strtoupper($this->Name."_NAV_ANIMDURATION"), 1000, true, "time in (ms > 0)");
		$c->IsAutoAnimate = igk_getv($this->Configs, "clanim_NAV_AUTOANIMATE", 1);//igk_get_uvar(strtoupper($this->Name."_NAV_AUTOANIMATE"), 1, true, "0 or 1");
		$c->AnimPeriod    = igk_getv($this->Configs, "clanim_NAV_AUTOPERIOD", 10000);//igk_get_uvar(strtoupper($this->Name."_NAV_AUTOPERIOD"), 10000, true, "");
		$c->AnimType      = igk_getv($this->Configs, "clanim_NAV_ANIMTYPE", "translation");//igk_get_uvar(strtoupper($this->Name."_NAV_ANIMTYPE"), "translation", true, "translation,fade,rotation");
		$c->flush();
		$this->_incViewfile("default");
		$this->_onViewComplete();
	}

	protected function buildPage($pane){//build page
		$t = $this->getAllArticles();
		if (is_array($t))
		{
			sort($t);
			foreach($t as $v)
			{
				igk_html_article($this, basename($v), $pane->addPage());
			}
		}
	}
}

?>