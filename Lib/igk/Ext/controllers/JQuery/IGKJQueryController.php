<?php

use IGK\Resources\R;

use function igk_resources_gets as __;

/*
boot strap structure controller
*/
final class IGKJQueryController extends IGKConfigCtrlBase
{
	public function getName(){
		return "jquery";
	}
	public function getIsSystemController()
	{
		return true;
	}
	public function getIsEnabled(){
		return igk_sys_getconfig("JQuery.Enabled", false);
	}
	public function getCDNUri(){
		return igk_sys_getconfig("JQuery.CDNUri", null);
	}
	protected function InitComplete(){
		parent::InitComplete();
		$this->_loadInfo();
	}

	public function onHandleSessionEvent($msg){
		switch($msg){
			case IGK_ENV_NEW_DOC_CREATED:
				$args = array_slice(func_get_args(), 1);
				$this->docCreated($args[0], $args[1]);
			break;
		}
	}

	private function docCreated($n, $a)
	{
		if ($a !=null)
			$this->__bindJquery($a);
	}
	private function _loadInfo(){
		if ($doc = igk_app()->doc)
			$this->__bindJquery($doc);//this->App->Doc);
	}
	public function __bindJquery($doc){

		$f = "";
		$f = $this->getCDNUri();
		if (!$f){
			$f =  igk_io_baseDir("/Lib/jquery/jquery.min.js");
			if (!file_exists($f))
				{
				$f=null;
				}
		}

		if ($this->IsEnabled)
		{

			// $f = $this->getCDNUri();
			// if ($f){
				// $doc->Body->appendScript($f, false,  -100);
				// return;
			// }else{
				// $f =  igk_io_baseDir("/Lib/jquery/jquery.min.js");
				// if (file_exists($f))
				// {
					$doc->Body->appendScript($f, false,  -100);
					// return;
				// }

			// }
		}else
			$doc->Body->removeScript($f);

	}

	public function getConfigPage()
	{
		return "jquery";
	}
	public function View(){
		if (!$this->getIsVisible())
		{
			igk_html_rm($this->TargetNode);
			return;
		}
		$c = $this->TargetNode;
		igk_html_add($c, $this->ConfigNode);
		$c->ClearChilds();
		$box = $c->addPanelBox();

		igk_html_add_title($box, "title.ConfigJQuery");

		if (file_exists($f = $this->getArticle("./help/jquery.conf"))){

			$box->addHSep();
			igk_html_article($this, $f, $box->addDiv(), null, null, true);
			$box->addHSep();
		}else{
			$box->addDiv()->setStyle("margin-bottom:1em");
		}


		$div = $box->addDiv();
		$frm = $div->addForm();
		$frm["method"]="POST";
		$frm["action"]=$this->getUri("update_jquery_setting");

		$d = $frm->addDiv();
		$d["class"]="form-group";
		//enable boot strap
		$ct = $d->addSLabelInput("clEnableJQuery",
		"checkbox" , "1");
		$ct->input["onchange"] = igk_js_post_form_uri(
		$this->getUri("update_jquery_setting_ajx"),
		"function(xhr){ if (this.isReady()){ }}"
		);
		$ct->input["checked"] =  (igk_parsebool($this->IsEnabled)=="true")?"true": null;

		$d->addHSep();
		$dv = $frm->addDiv();

		$dv->addFields(
		// igk_html_build_form($dv->add("ul"), 
		array(
			"clCDNUri"=>array("label_text"=>"CDN", "attribs"=>array(
				"placeholder"=>__("cdn uri access"),
				'value'=>$this->getCDNUri()
				))
		));

		$frm->addInput("update", "submit", R::ngets("btn.update"))->setClass("igk-btn igk-btn-default")->setStyle("font-size:1em; width:auto; line-height:1em;");

		$frm->addToken();
		$s = $d->addDiv();

	}
	public function update_jquery_setting()
	{
		$this->update_jquery_setting_ajx();
		igk_navto($this->getUri("showConfig"));

	}
	public function update_jquery_setting_ajx()
	{
		$this->App->Configs->SetConfig("JQuery.Enabled",igk_getr("clEnableJQuery", false));
		$this->App->Configs->SetConfig("JQuery.CDNUri",igk_getr("clCDNUri", false));
		$this->_loadInfo();
		igk_save_config();
		$this->View();
	}
} 