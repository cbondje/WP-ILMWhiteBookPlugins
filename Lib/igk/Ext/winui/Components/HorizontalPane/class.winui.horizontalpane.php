<?php

function igk_hpane_get_dir_uri($f){
	return igk_html_uri(igk_io_baseRelativePath(dirname($f)));
}
///<summary>horizontal pane panel used to add animation pane on a target node</summary>
final class IGKJS_HorizontalPane extends IGKObject
{

	/** @var IGKHtmlItem */
	private $m_target;
	/** @var IGKHtmlItem */
	private $m_script;
	/** @var IGKHtmlItem */
	private $m_pageNode;
	/** @var IGKHtmlItem */
	private $m_bulletZone;
	private $m_AnimDuration;
	private $m_AnimInterval;
	private $m_IsAutoAnimate;
	private $m_AnimType; //translation or fade
	private $m_AnimPeriod;

	private $m_rootNode;

	const DEFAULTANIMTYPE = IGKHTMLHorizontalAnimType::rotation;


	public function getpageNode(){return $this->m_pageNode; }
	public function getTarget(){return $this->m_target;}
	public function getScript(){return $this->m_script; }
	public function getbulletZone(){return $this->m_bulletZone; }
	public function getAnimInterval(){return $this->m_AnimInterval; }
	public function getAnimDuration(){return $this->m_AnimDuration; }
	public function getAnimPeriod(){return $this->m_AnimPeriod;}
	public function getIsAutoAnimate(){return $this->m_IsAutoAnimate; }
	public function getAnimType() { return $this->m_AnimType;}
	public function setAnimType($value){
			$this->m_AnimType = $value;
	}
	public function setAnimInterval($value){ $this->m_AnimInterval = $value; }
	public function setAnimDuration($value){ $this->m_AnimDuration = $value; }
	public function setAnimPeriod($value){$this->m_AnimPeriod =$value;}
	public function setIsAutoAnimate($value){ $this->m_IsAutoAnimate = $value; }

	public function __construct($target)
	{
		$this->m_AnimDuration = 500;
		$this->m_AnimInterval = 20;
		$this->m_AnimPeriod = 5000;
		$this->m_target = $target;
		$this->m_IsAutoAnimate = true;
		$this->m_AnimType = self::DEFAULTANIMTYPE;//IGKHTMLHorizontalAnimType::r "translation";/// fade, rotation
		//fit parent

		$this->m_pageNode = $target->addDiv();
		$this->m_pageNode["igk-control-type"]="igk-pane";
		$this->m_pageNode->setClass("igk-pane rotation");
		//global register theme to document directry if this object is created
		igk_app()->Doc->Theme->addFile(igk_getctrl(IGK_SYS_CTRL), dirname(__FILE__)."/Styles/default.pcss");

	}
	public function addPage($attribute=null)
	{
		$p = new IGKJS_HorizontalPage();
		if ($attribute)
			$p->AppendAttributes($attribute);
		$this->m_pageNode->add($p);
		return $p;
	}
	public function Clear(){
		$this->m_pageNode->ClearChilds();
	}
	public function flush()
	{
		if ($this->m_bulletZone == null)
		{
			$this->m_bulletZone  =  $this->m_target->addDiv();
			$this->m_bulletZone["igk-control-type"]="hpane-bz";
			$this->m_bulletZone["class"]="hpane-bz";
		}

		if ($this->m_script == null)
			$this->m_script = $this->m_target->addScript();
		$b = igk_parsebool($this->m_IsAutoAnimate);

		// igk_wln("animtype ".$this->m_AnimType);
		
		$this->m_script->Content = <<<EOF
(function(q){ ns_igk.ready(function(){igk.winui.horizontalScrollPane.init(q, {autoanimate: {$b}, animtype: '{$this->m_AnimType}', period: {$this->m_AnimPeriod}},  {duration:{$this->m_AnimDuration}, interval: {$this->m_AnimInterval}, orientation:'horizontal'}); }); })(ns_igk.getParentScript());
EOF;


	}

}


final class IGKJS_HorizontalPage extends IGKHtmlItem
{
	private $m_options;
	private $m_file; //sorce file

	public function __construct(){
		parent::__construct("div");
		$this["class"] = "igk-pane-page";
		$this["igk-control-type"]="igk-pane-page";
	}
	public function setFile($file){
		$this->m_file = $file;
	}
	public function innerHTML(& $o = null){
		$s = parent::innerHTML($o);
		return $s;
	}
	public function __toString(){
		return __CLASS__;
	}
}

//horizontal pane item
interface IIGKHTMLHorizontalPaneListener{
	function getAddHorizontalPageUri();
	function buildPages($horizontalPane);
	function clearPagesUri();
	function getOptionsUri();

}

///<summary>define enumeraion</summary>
final class IGKHTMLHorizontalAnimType
{
	const none="none";
	const translation="translation";
	const rotation="rotation";
	const fade="fade";
}
//private component
final class IGKHTMLHorizontalPaneManager extends IGKHtmlComponentNodeItem
{
	private $m_owner;
	private $m_paneAdd; //pane add btn info
	private $m_paneClear;//pane clear btn btn info
	private $m_paneOptions;//pane options btn info

	public function dropFile(){	//delete all files
		 $q = base64_decode(igk_getr('q'));
		// igk_wln($t);
		$tab = array();
		parse_str($q, $tab);
		$_REQUEST = $tab;

		$f = base64_decode(igk_getr("n"));

		if (file_exists($f)){
			$dirname = dirname($f);

			//remove all file that match the pattern
			$l = igk_io_basenamewithoutext($f);
			$s = IGKIO::GetFiles($dirname, "#(".$l.")#", false);
			foreach($s as  $v){
				unlink($v);
			}
		//.unlink($f);
		}
		igk_resetr();
		$this->_view_pane();
		igk_exit();
	}
	public function addHPaneOption($typename){
		$d = $this->addDiv();
		$d->setClass("igk-pane-option igk-trans-all-200ms");
		$d->add("span")->setClass("igk-hbox")->Content = $typename;
		$d->pane = $this->m_owner;
		// igk_wln($this->m_owner );
		// igk_exit();
		$m_script = $d->addScript();
		$no = (object)array();
		$no->item = $d;
		$no->script = $m_script ;
		return $no;
	}
	public function __construct($owner)
	{
		parent::__construct("div");
		$this->setClass("igk-pane-manager");//->setStyle("border:1px solid black;");
		$this->m_owner=$owner;
		$this->m_pandeAdd = $this->addHPaneOption("add");

		$f = igk_create_node_callback(array($this->m_owner, "isVisible"), array('option'));

		// igk_wln("create ".$owner->Pane);
	 // igk_exit();

		$this->m_pandeAdd->item->setCallback("getIsVisible", $f);
		$this->m_pandeAdd->item->setStyle("[res:add_32x32]; cursor:cell;");

		$this->m_paneClear = $this->addHPaneOption("clearall");
		$this->m_paneClear->item->setStyle("[res:del_32x32]; cursor: pointer;");
		$this->m_paneClear->item->setCallback("getIsVisible", $f);

		$this->m_paneOptions = $this->addHPaneOption("options");
		$this->m_paneOptions->item->setStyle("[res:option_32x32]; cursor: pointer;");

		$this->addDiv()->setClass("igk-cleartab")->Content = " ";

		$this->setCallback("getIsVisible", $f);
	}

	private function _view_pane(){
		$pane = $this->m_owner;
		$pane->clearPages();
		$pane->configure();
		$pane->flush();
		$pane->RenderAJX();
	}
	public function addUri(){
		$tab = igk_get_allheaders();
		$pane = $this->m_owner;
		if (igk_getr('ie', 0) == 1)
		{
			$pane->RenderAJX();
			igk_exit();
		}


		if (igk_getv($tab, "IGK_UPLOADFILE", false) ==false)
		{
			$s = IGKHtmlItem::CreateWebNode("script");
$s->Content = <<<EOF
ns_igk.winui.notify.showError("Can't upload file IGK_UPLOADFILE not founds");
EOF;
$s->RenderAJX();
$pane->RenderAJX();
			igk_exit();
		}
		$type = igk_getv($tab, "IGK_UP_FILE_TYPE","text/html");
		$fname = igk_getv($tab, "IGK_FILE_NAME", "file.data");
		$bfname = igk_io_basenamewithoutext($fname);
		//$s = file_get_contents("php://input");
		$dir = $pane->Folder;

		// igk_ilog("uploading ... ".$fname . " type : ".$type. " to ".$dir);

		switch(strtolower($type ))
		{
			case "image/jpeg":
			case "image/jpg":
			case "image/png":

				$f = date("Ymd").igk_new_id().".".igk_io_path_ext($fname);
				$s  =  $dir."/".$f;
				igk_io_store_uploaded_file($s);
				//igk_io_save_file_as_utf8_wbom($dir."/".$fname, $s, true);
				//igk_io_save_file_as_utf8_wbom($storelocation, $s, true);

				$div=  IGKHtmlItem::CreateWebNode("div");
				$div["class"]="fitw fith";
				$img =  $div->addXmlNode("igk:img");
				//$img =  $div->addXmlNode("img");
				$cfname=addslashes (basename($s));

				$img["src"] = "[func:igk_hpane_get_dir_uri(\$row->file).'/{$cfname}']";
				//igk_html_inlinedata($type, igk_io_read_allfile($s));//"data:{$type}, base64, ".base64_encode($s);// "[func:igk_hpane_geturi(\$row->file).'/{$cfname}']";
				$img->setClass("igk-pane-img");
				$div->addDiv()->setClass("igk-pane-view");
				//store template
				igk_io_save_file_as_utf8_wbom($dir."/".basename($s).".".IGK_DEFAULT_VIEW_EXT, $div->Render(null) , true);
				break;
			default:
				igk_io_save_file_as_utf8_wbom($dir."/".$fname.".".IGK_DEFAULT_VIEW_EXT, $s, true);
			break;
		}
		$this->_view_pane();
		igk_exit();
	}
	public function optionsUri(){
		$pane = $this->m_owner;
		if ($pane){
			igk_wl($pane->getOptionsXML($this->getController()->getUri("set_nav_options", $this)));
		}
		igk_exit();
	}

	public function update_nav_options()
	{
		$pane = $this->m_owner;
		if (!$pane)
		{
			return;
		}
		$pane->Pane->AnimPeriod = igk_getr('AnimPeriod', 500);
		$pane->Pane->AnimInterval = igk_getr('AnimInterval', 20);
		$pane->Pane->AnimPeriod = igk_getr('AnimPeriod', 1000);
		igk_frame_close("nav_options_frame");
		$pane->storeDBConfigsSetting();
		$pane->flush();
		$pane->RenderAJX();
		igk_exit();
	}
	public function set_nav_options(){
		$pane = $this->m_owner;
		if (!$pane)
		{
			return;
		}
		$def = false;
		$p = igk_getr("menu");
		switch($p){
			case "option":
				$frame = igk_html_frame($this, "nav_options_frame");
				$frame->Title = R::ngets("title.options");
				$d = $frame->BoxContent ;
				$d->ClearChilds();
				$frm = $d->addForm();
				$frm["action"] = $this->getController()->getUri("update_nav_options", $this);
				$frm["igk-js-ajx-form"] = "1";
				$pane->EditPaneOptions($frm);
				$frame->RenderAJX();
			break;
			case "setanimtype":
			$p = igk_getr("n");
			$pane->Pane->AnimType = $p;
			$def= true;
			break;
		}

		//store config
		//------------
		if ($def){
		$pane->storeDBConfigsSetting();
		$pane->flush();
		$pane->RenderAJX();
		}
		igk_exit();
	}

	public function clearUri(){

		$pane = $this->m_owner;
		$dir = $pane->Folder;
		if (!is_dir($dir))
		{
			igk_notify_error("folder not a dir ".$dir);
			return;
		}
		foreach(igk_io_getfiles($dir, "/(.)*/i") as  $v){
			if (is_file($v))
				unlink($v);
		}
		$pane->clearPages();
		$pane->configure();
		$pane->flush();
		$pane->RenderAJX();
		igk_exit();
	}
	public function getUriSetting(){
		$p = $this->m_owner->getPageViewListener();
		$obj = (object)array("addUri"=>null,"clearUri"=>null, "optsUri"=>null);
			if ($p)
			{
				$obj->addUri = $p->getAddHorizontalPageUri();
				$obj->optsUri = $p->getOptionsUri();
				$obj->clearUri = $p->clearPagesUri();
			}
			else{
				$obj->addUri = $this->getController()->getUri("addUri", $this);
				$obj->optsUri = $this->getController()->getUri("optionsUri", $this);
				$obj->clearUri = $this->getController()->getUri("clearUri", $this);;
			}
			return $obj;
	}

	public function AcceptRender($options =null){

		//igk_wln($this->m_owner->isVisible($this, 'option'));
		//igk_exit();
		if (!$this->IsVisible)
			return false;
		$obj = $this->getUriSetting();

		$this->m_pandeAdd->script->Content = <<<EOF
ns_igk.readyinvoke('igk.winui.horizontalScrollPane.initdrag','{$obj->addUri}');
EOF;

		if (isset($obj->clearUri ))
		$this->m_paneClear->script->Content = <<<EOF
ns_igk.readyinvoke('igk.winui.horizontalScrollPane.initclearpage','{$obj->clearUri}');
EOF;

		if (isset($obj->optsUri ))
		$this->m_paneOptions->script->Content = <<<EOF
ns_igk.readyinvoke('igk.winui.horizontalScrollPane.initoptions','{$obj->optsUri }');
EOF;
 	$this->setClass("-empty");
	//igk_wln("Has page ".$this->m_owner->getHasPage());
	//igk_exit();
	if (!$this->m_owner->getHasPage())
		$this->setClass("+empty");

		return true;
	}
	// public function render($options =null){
			// check if web master view



		// $s = parent::Render($options);
		// return $s;
	// }
}

///<summary>external html item. Add to View</summary>
final class IGKHTMLHorizontalPaneItem extends IGKHtmlItem
{
	private $m_pane;
	private $m_pagelistener;
	private $m_manager;
	private $m_infobox;
	private $m_infoboxScript;

	private $m_pattern;
	private $m_folder;
	private $m_ConfigFileName;
	private $m_ctrl;

	public function getFolder(){return $this->m_folder; }
	public function setFolder($v){ $this->m_folder = IGKIO::GetDir($v); }

	public function getConfigFileName(){ return $this->m_ConfigFileName;}
	public function setConfigFileName($v){ $this->m_ConfigFileName = $v;}

	public function getPattern(){return $this->m_pattern; }
	public function setPattern($v){$this->m_pattern = $v; }

	public function getPane(){return $this->m_pane; }
	public function __toString(){
		return __CLASS__;
	}

	public function getHasPage(){
		return $this->m_pane->pageNode->HasChilds;
	}
	protected function loadingComplete(){
		$this->configure();
		$this->flush();
	}
	///<summary>call it first</summary>
	public function setCtrl($ctrl, $folder=null){
		$this->clearPages();
		$this->Folder = $folder==null? $ctrl->getDataDir()."/R/barner" : $folder;
		$this->configure();
		$this->flush();
		$this->m_ctrl = $ctrl;
		return $this;
	}
	private function loadConfigSetting(){
		$f = $this->Folder."/".$this->ConfigFileName;

		if (!file_exists($f))
			return;
		$div =  IGKHtmlReader::LoadFile($f);
		$d = igk_getv($div->getElementsByTagName("config"), 0);
		if ($d)
		{
			foreach($d->Childs as $k)
			{

				if ($k->Type == "HtmlText")
				continue;
				$r = $k->TagName;
				$this->Pane->$r = trim($k->innerHTML);
			}
		}
	}

	public function storeDBConfigsSetting()
	{
		$f = $this->Folder."/".$this->ConfigFileName;
		$d = IGKHtmlItem::CreateWebNode("config");
		//store

		$d->add("AnimType")->Content = $this->m_pane->AnimType;
		$d->add("AnimInterval")->Content = $this->m_pane->AnimInterval;
		$d->add("AnimPeriod")->Content = $this->m_pane->AnimPeriod;
		$d->add("AnimDuration")->Content = $this->m_pane->AnimDuration;
		$d->SaveToFile($f);
	}
	public function getOptionsXML($uri){
		$d = IGKHtmlItem::CreateWebNode("div");
		$d->add("li")->setAttributes(array("uri"=>$uri."&menu=option", "ajx"=>1, "complete"=>"ns_igk.winui.horizontalScrollPane.append_to_body_from(this)"))->Content = "options";
		$d->addXmlNode("sep");
		foreach(igk_get_class_constants('IGKHTMLHorizontalAnimType') as  $v)
		{
			$d->add("li")->setAttributeç($this->m_pane->AnimType==$v, "class", "+igk-checked")
			->setAttributes(array("uri"=>$uri."&menu=setanimtype&n=".$v, "ajx"=>1))
			->Content = $v;
		}
		return $d->getinnerHTML();
	}
	public function EditPaneOptions($target){
		if ($target==null)
			return;
	$pane= $this->m_pane;

		$s = <<<EOF
<igk:labelInput igk:id='AnimDuration' igk:value='{$pane->AnimDuration}' />
<igk:labelInput igk:id='AnimInterval' igk:value='{$pane->AnimInterval}'/>
<igk:labelInput igk:id='AnimPeriod' igk:value='{$pane->AnimPeriod}'/>
<igk:HSep />
<input type="submit" class="igk-btn igk-btn-default" value="[lang:btn.update]" / >
EOF;
$target->Load( igk_html_databinding_treatresponse($s,null, null,null));

	}
	public function __construct(){
		parent::__construct("div");
		$this["class"]="igk-hpane-container ";
		$this->m_pane = new IGKJS_HorizontalPane($this);
		$this->m_pattern = "/\.phtml$/i";
		$this->m_manager = new IGKHTMLHorizontalPaneManager($this);
		$this->m_infobox = $this->addDiv()->setClass("igk-pane-infobox");


		$this->m_pane->pageNode["igk-component-id"] = $this->m_manager["igk-component-id"];
		$this->add($this->m_manager);
		$this->m_infobox->setCallback("getIsVisible", igk_create_node_callback(array($this, "isVisible"), array('infobox')));
		$this->m_infobox->addDiv()->setClass("igk-pane-infobox-c disptable fitw fith")
		->addDiv()->setClass("disptabc alignc alignm")
		->Content = R::ngets("msg.nopageaddedtopane");
		$this->m_infoboxScript = $this->m_infobox->addScript();
		$this->m_ConfigFileName = "config.xml";
	}
	///mage all visibility
	public function isVisible($n, $t){

		$u = igk_app()->Session->User;
		switch($t){
			case "infobox":
				return !$this->HasPage && (IGKApp::getInstance()->IsSupportViewMode(IGKViewMode::WEBMASTER) || ($u && $u->auth("sys://designpage")));

			case "option":
				return (igk_app()->IsSupportVIewMode(IGKViewMode::WEBMASTER) || ($u && $u->auth('sys://designpage')) )&& ($this->Folder != null);

		}
		return false;
	}	
	public function loadData($data){
		// igk_ilog("load data");
		// $data->RenderAJX();
		// igk_wln($data->getElementsByTagName("page"));
		
		foreach($data->getElementsByTagName("page") as $e){
			$file = $e["file"];
			//igk_ilog("file .... ".$file);
			$p = $this->addPage();
			igk_html_bind_target(null, $p, $e->innerHtml(), (object)array("file"=>$file));
			$p->setFile($file);

			 // header("Content-Type: text/plain");
			 // echo $e->innerHtml();
			 // echo $p->Render();
			 // igk_exit();
			if (IGKApp::getInstance()->IsSupportViewMode(IGKViewMode::WEBMASTER)){
			$c = igk_html_article_options(null, $p, $file);
			if ($c){
				$uri = $this->m_manager->getController()->getUri("dropFile&n=".base64_encode($file), $this->m_manager);
				$c->dropFileUri =
				"javascript: ns_igk.readyinvoke('igk.winui.horizontalScrollPane.dropfile_ajx', this, '{$uri}');";
			}
			}
		}
	}
	public function AcceptRender($options = null){
		if (!$this->IsVisible)
			return false;

		$p = $this->getPageViewListener();
		$uri = null;
		//$u = igk_app()->Session->User;
		$u = igk_app()->Session->User;

		$v = IGKApp::getInstance()->IsSupportViewMode(IGKViewMode::WEBMASTER) || ($u && $u->auth("sys://designpage"));
		if ($v)
		{
			if ($p){
				$uri = $p->getAddHorizontalPageUri();
		}
		else{
			$uri = $this->m_manager->getController()->getUri("addUri", $this->m_manager);
		}
		$this->m_infoboxScript->Content = <<<EOF
ns_igk.readyinvoke('igk.winui.horizontalScrollPane.initdrag','{$uri}');
EOF;
	$this->m_infoboxScript->setIsVisible(true);
}
else {
	$this->m_infoboxScript->Content = null;
	$this->m_infoboxScript->setIsVisible(false);
}
return true;
	}
	// public function render($options =null)
	// {

		// return parent::Render($options);
	// }
	public function setPageViewListener($listener)
	{
		if (($listener == null) || !igk_reflection_class_implement($listener, 'IIGKHTMLHorizontalPaneListener'))
			igk_die("listener is not a valid value ");
		$this->m_pagelistener = $listener;
	}
	public function getPageViewListener()
	{
		return $this->m_pagelistener ;
	}
	public function flush(){
		$this->m_pane->flush();
	}
	public function addPage($attributes=null){
		return $this->m_pane->addPage($attributes);
	}
	public function clearPages(){
		$this->m_pane->Clear();
	}
	public function configure(
		$AnimDuration = 500,
		$AnimInterval = 20,
		$AnimPeriod = 25000,
		$IsAutoAnimate = true,
		$AnimType = IGKJS_HorizontalPane::DEFAULTANIMTYPE // "rotation" //"translation"/// fade, rotation
		){
			// igk_ilog("configure page ");
	$this->m_pane->AnimDuration = $AnimDuration;
	$this->m_pane->AnimInterval = $AnimInterval;
	$this->m_pane->AnimPeriod = $AnimPeriod;
	$this->m_pane->IsAutoAnimate = $IsAutoAnimate;
	$this->m_pane->AnimType = $AnimType;

		if ($this->m_pagelistener !=null)
		{
			$this->m_pagelistener->buildPages($this);
		}
		else{

			$data = igk_createXmlNode("horizontal-pane-data");// IGKHtmlItem::CreateWebNode("horizontal-pane-data");
			$dir = $this->Folder;
			$p = $this->Pattern;
			if (IGKIO::CreateDir($dir)){
				IGKIO::WriteToFileAsUtf8WBOM($dir."/.htaccess", "allow from all", false);
				//igk_ilog("loading ....");
				foreach(igk_io_getfiles($dir, $p, false) as  $v)
				{
					// igk_ilog("loading ....file : ".$v);
					$v_p = $data->add("page");
					$v_p["file"] = $v;
					//$v_p->LoadFile($v);
					$v_p->LoadExpression(igk_io_read_allfile($v));
				}
				$this->loadData($data);
				$this->loadConfigSetting();
				// igk_ilog($data->Render());
			}
		}
	}
}

?>