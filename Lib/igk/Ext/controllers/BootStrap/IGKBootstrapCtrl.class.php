<?php

use IGK\Resources\R;

use function igk_resources_gets as __;
/*
boot strap structure controller
*/
final class IGKBootstrapCtrl extends IGKConfigCtrlBase
{
	public function getName(){
		return __CLASS__;
	}
	public function getIsSystemController()
	{
		return true;
	}
	public function getIsEnabled(){
		return igk_sys_getconfig("BootStrap.Enabled", false);
	}
	public function getIsJsEnabled(){
			return igk_sys_getconfig("BootStrap.JsEnabled", false);
	}

	public function getCDNBundleJs(){
		return igk_sys_getconfig("bootstrap.cdn.bundle.js", null);
	}
	public function getCDNCss(){
		return igk_sys_getconfig("bootstrap.cdn.css", null);
	}
	public function getCDNJs(){
		return igk_sys_getconfig("bootstrap.cdn.js", null);
	}
	protected function InitComplete(){
		parent::InitComplete();
		$this->_loadInfo();
		//register for new document creation
		//$this->App->addNewDocumentCreatedEvent($this, "docCreated");

		// igk_reg_session_event(IGK_ENV_NEW_DOC_CREATED, array($this, "docCreated"));
		igk_notification_reg_event("bootstrap://propertieschanged", array($this, "View"));
		// igk_admin_reg_menu("bootstrap");
	}

	public function onHandleSessionEvent($msg){
		switch($msg){
			case IGK_ENV_NEW_DOC_CREATED:
				$args = array_slice(func_get_args(), 1);
				$this->docCreated($args[0], $args[1]);
			break;
		}
	}
	protected function getGlobalHelpArticle(){
		return "./help/help.global.bootstrap";

	}

	public function getBootStrapFile(){
		return igk_io_baseDir("Lib/bootstrap/css/bootstrap.min.css");
	}
	public function docCreated($n, $a)
	{
		if ($a !=null)
			$this->__bindBootstrap($a);
	}
	private function __bindBootstrap($doc)
	{
		$tab = array(
		$this->getCDNCss(),
		$this->getCDNJs(),
		$this->getCDNBundleJS());

		$f = $tab[0] ?? $this->getBootStrapFile();
		// $js= $tab[0]
		$dir = igk_io_baseDir("Lib/bootstrap/");
		if (!empty($f)){
			if ($this->IsEnabled)	{
				if (is_dir($dir)){
				igk_css_reg_font_package("glyphicons" , igk_io_basePath($dir."fonts/glyphicons-halflings-regular.ttf"),null,"TrueTypeFont");
				igk_css_reg_font_package("glyphicons" , igk_io_basePath($dir."fonts/glyphicons-halflings-regular.woff"),null, "WOFF");
				}
				$doc->addStyle($f);
			}
			else {
				$doc->removeStyle($f);
				igk_css_unreg_font_package("glyphicons");
			}
		}


		$d = $tab[1] ?? igk_io_baseDir("Lib/bootstrap/js/bootstrap.min.js");
		if (!empty($d)){
			if ($this->IsJsEnabled)
			{
				$doc->Body->appendScript($d, false, 100);
			}
			else {
				$doc->Body->removeScript($d);
			}
		}
	}
	private function _loadInfo(){
		// igk_ilog("Bind loading info to documents : ". igk_count(igk_get_documents()));
		$v_docs = igk_get_documents();
		if ($v_docs!=null){
			foreach($v_docs as $v){
					$this->__bindBootstrap($v);
			}
		}
		if($doc = igk_app()->doc)
			$this->__bindBootstrap($doc);
	}

	public function getConfigPage(){
		return "bootstrap";
	}


	public function View(){
		if (!$this->getIsVisible())
		{
			igk_html_rm($this->TargetNode);
			return;
		}
		$c = $this->TargetNode;
		$c->ClearChilds();
		igk_html_add($c, $this->ConfigNode);

		$box = $c->addPanelBox();

		igk_html_add_title($box, "title.ConfigBootStrap");
		$f = $this->getArticle("./help/help.bootstrap");
		if (file_exists($f)){
			$box->addHSep();
			igk_html_article($this, "./help/help.bootstrap", $box->addDiv(), null, null, true);
			$box->addHSep();
		}


		$div = $box->addDiv();
		$frm = $div->addForm();
		$frm["method"]="POST";
		$frm["action"]=$this->getUri("update_bootstrap_setting");

		$d = $frm->addDiv();
		$d->addScript()->Content = <<<EOF
(function(){
	igk.system.createNS("igk.lib.bootstrap", {
		update:function(xhr){
			if (this.isReady()){
				// this.replaceBody(xhr.responseText);
			}
		}
	});
})();
EOF;
		$v_usetting =  igk_js_post_form_uri($this->getUri("update_bootstrap_setting_ajx"),"ns_igk.lib.bootstrap.update");
		$d["class"]="form-group";
		//enable boot strap
		$ct = $d->addSLabelInput("clEnableBootStrap",
		 "checkbox" , "1");
		$ct->input["onchange"] = $v_usetting;
		$ct->input["checked"] =  (igk_parsebool($this->IsEnabled)=="true")?"true": null;


		//enabble boot js
		$d = $frm->addDiv();
		$d["class"]="form-group";
		$ct = $d->addSLabelInput("clEnableJSBootStrap",
		 "checkbox" , "1");
		$ct->input["onchange"] = $v_usetting;
		$ct->input["checked"] =  (igk_parsebool($this->IsJsEnabled)=="true")?"true": null;




		$d->addHSep();

		$d = $frm->addDiv();
		$box = $d->addPanelBox();
		igk_html_title($box->addDiv(), R::ngets( "title.CDNSettings" ) );

		$box->add("ul")->addFields(
		//igk_html_build_form($box->add("ul"), 
		array(
			"bootstrap.cdn.css"=>array("attribs"=>array("class"=>"igk-form-control form-control", "value"=>$this->getCDNCss())),
			"bootstrap.cdn.js"=>array("attribs"=>array("class"=>"igk-form-control form-control", "value"=>$this->getCDNJs())),
			"bootstrap.cdn.bundle.js"=>array("attribs"=>array("class"=>"igk-form-control form-control", "value"=>$this->getCDNBundleJs()))
		));

		$box->addInput("update", "submit", R::ngets("btn.update"))->setClass("igk-btn igk-btn-default")->setStyle("font-size:1em; width:auto; line-height:1em;");


		$f = igk_io_getfiles(igk_io_currentRelativePath("Lib/bootstrap"), "/\.(css|js)$/i");
		if (igk_count($f)>0 ){
			$s = $d->addDiv()->addPanelBox();
			$ul = $s->add("ul");
			$s->addSectionTitle(4)->Content = "Files";
			foreach($f as  $v){
				$ul->add("li")->Content = IGKIO::GetDir($v);
			}
		}
		else{
			$p = $d->addPanel();
			$p->Content = R::ngets("msg.bootstrap.nofilefound");
			$frm = $p->addForm();
			$a = $frm->add("a");
			$a["href"] = "https://getbootstrap.com";
			$a["target"] = "__blank";
			$a->Content = __("Get Bootstrap");
		}
	}
	public function update_bootstrap_setting()
	{
		$this->update_bootstrap_setting_ajx();
	}
	public function update_bootstrap_setting_ajx(){
		$app = igk_app();
		$k = igk_getr("clEnableBootStrap", false);

		// igk_wln($_REQUEST);
		// igk_exit();
		$app->Configs->SetConfig("BootStrap.Enabled", $k);
		$app->Configs->SetConfig("BootStrap.JsEnabled", igk_getr("clEnableJSBootStrap", false));
		$app->Configs->SetConfig("bootstrap.cdn.css", igk_getr("bootstrap_cdn_css", false));
		$app->Configs->SetConfig("bootstrap.cdn.js", igk_getr("bootstrap_cdn_js", false));
		$app->Configs->SetConfig("bootstrap.cdn.bundle.js", igk_getr("bootstrap_cdn_bundle_js", false));

		igk_save_config();
		$this->_loadInfo();
		$this->View();
		$b =  IGKHtmlItem::CreateWebNode("bootstrap-response");
		$b->add("status-bootstrap")->Content = igk_parsebool($this->IsEnabled);
		$b->add("status-js-bootstrap")->Content = igk_parsebool($this->IsJSEnabled);
		//$b->RenderAJX();
		igk_notification_push_event("bootstrap://propertieschanged", $this);

		$val = array();
		$val[0]='false';
		$val[1]='true';

		$sc = igk_createNode("script");
		$uri = igk_io_fullpath2fulluri($this->getBootStrapFile());
		$sc->Content = <<<EOF
		igk.ready(
function(){ var f = 0; var r = 0; var i = igk.css.selectStyle({href:/bootstrap\.min\.css\$/}, function(q){ q.disabled= !{$val[$this->IsEnabled]}; f = 1; }); if (!f){
	igk.css.appendStyle("{$uri}");
}}
);
EOF;
		$app->Doc->body->add(new IGKHtmlSingleNodeViewer($sc));
		if (igk_is_ajx_demand()){
			igk_render_doc();
			igk_exit();
		}
	}


}

