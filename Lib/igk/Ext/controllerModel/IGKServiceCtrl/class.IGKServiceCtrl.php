<?php

use IGK\System\IO\File\WsdlFile;

use function igk_resources_gets as __;

define("IGK_SERVICE_BASE_URI", "services");

///<summary>represent a wsdl service controller type </summary>
abstract class IGKServiceCtrl extends IGKCtrlTypeBase
{
	private static $sm_services = [];
	public function getRootUri(){
		return 	igk_io_baseUri()."/".IGK_SERVICE_BASE_URI;
	}
	public function getServiceName(){
		return strtolower(igk_getv($this->Configs, "clServiceName"));
	}
	public function getServiceDescription(){
		return igk_getv($this->Configs, "clServiceDescription");
	}
	public function getServiceUri($d=null){
		return 	$this->getRootUri()."/".$this->getServiceName() . (($d)?"/".$d:null);
	}
	public function __construct(){
		parent::__construct();
	}
	protected function InitComplete(){
		parent::InitComplete();
		$this->register_service();
	}
	protected function pageFolderChanged(){
		// igk_wln("page folder changed ? ");
		
	}
	protected function register_service(){
		$c = "^/".IGK_SERVICE_BASE_URI."/".$this->getServiceName();
		$k = $this->getEnvParam("appkeys");
		if (!empty($k))
		{
			igk_sys_ac_unregister($k);
		}
		if ($c)
		{
			//register app contains
			$k = "".$c.IGK_REG_ACTION_METH;//"(/:function(/:params+)?)?";
			//igk_wln($k);
			igk_sys_ac_register($k, $this->getUri("evaluateUri"));
			$this->setEnvParam("appkeys", $k);
		}
		$t = & self::$sm_services; //$this->getEnvParam($key);
		if ($t ==null){
			$t =array();
			$c = "^/".IGK_SERVICE_BASE_URI."(/){0,1}$";//.IGK_REG_ACTION_METH;
			$f =  $this->getUri("baseEvaluateUri&m=global");
			igk_sys_ac_register($c, $f);
			self::$sm_services = & $t;

		}
		$t[] = $this;
	}

	public static function GetAdditionalConfigInfo()
	{
		return array("clServiceName"=>(object)array("clType"=>"text", "clRequire"=>1), "clServiceDescription", "clServiceDisableWSDLCache"=>(object)array("clType"=>"bool"));
	}
	public static function SetAdditionalConfigInfo(& $t)
	{
		$t["clServiceName"] = igk_getr("clServiceName");
		$t["clServiceDescription"] = igk_getr("clServiceDescription");
		$t["clServiceDisableWSDLCache"] = igk_getr("clServiceDisableWSDLCache");

		if (empty($t["clServiceName"])){//required field
			return false;
		}
		return 1;
	}

	public final function getServices(){
		return self::$sm_services;
	}
	public final function baseEvaluateUri(){
		$f = dirname(__FILE__)."/".IGK_VIEW_FOLDER."/default.phtml";
		$doc = igk_get_document("sys://base_service_doc");

		$doc->setParam("sys://designMode/off",1);

		$t =   igk_createNode("div");

		igk_doc_set_favicon($doc, dirname(__FILE__)."/Data/R/favicon.ico");

		$doc->addTempScript(dirname(__FILE__)."/Scripts/services.js");
		$t->clearChilds();
		$this->regSystemVars(null);
		//add extra parameter to view
		$this->regSystemVars(array("services"=>$this->getServices()));
		// $d  = $this->getSystemVars();
		//$t->resetParam();
		// $t->setParam("inf", "D");
		$bck = $this->TargetNode;
		$this->TargetNode = $t;


		$this->_include_file_on_context($f);
		$this->TargetNode = $bck;
		$doc->body->addBodyBox()->add($t);
		$doc->RenderAJX();

		// $d  = $this->getSystemVars();


		$u = igk_io_fullBaseRequestUri();
		igk_set_session_redirection($u);
		igk_exit();
	}
	public final function evaluateUri()
	{
		($inf = igk_sys_ac_getpatterninfo()) || die("uri evaluation setting action not resolved");
		$p = $inf->getQueryParams();
		$c = igk_getv($p, "function");
		$p = igk_getv($p, "params");
		header("content-type: text/html");
		igk_set_session_redirection($this->getServiceUri()); 
		$u = igk_io_request_uri();  
		if (preg_match('#\$metadata$#i', trim($u))){

			igk_set_header('404 not found');
			igk_exit();
		}
		// igk_ilog("Request  : ".$u);
		// igk_ilog($_SERVER);
		if ( isset($_SERVER["HTTP_SOAPACTION"]) || !strstr( igk_getv($_SERVER,"HTTP_USER_AGENT"), "MS Web Services Client Protocol" )){

			if (empty($c))
			{
				//render discovery information
				// igk_set_header('200', "Content-Type: text/xml");
				// $this->wsdl(null,0);
				
				//igk_ilog("render default doc.... \$f = ".$c);
				 ob_clean();
				 //handler server or render
				$this->renderDefaultDoc();
				igk_exit();
			}
			else{
				if (method_exists($this, $c))
				{
					ini_set('default_charset', null);
					//igk_set_header('200', "Content-Type: text/xml");
					if (is_array($p) == false)
						$p = array($p);
					call_user_func_array(array($this, $c), $p);
				}
				else{
					$this->renderError($c);
					igk_exit();
				}
			}
		}
		ob_clean();
		// igk_ilog("wsdl");
		igk_set_header('200', "Content-Type: application/xml");
		$this->wsdl();
		igk_exit();
	}
	public function renderError($c){
		$doc = igk_get_document("sys:://document/services");
		$doc->Title = "!Error - ".$this->getServiceName();
		$bbox = $doc->body->addBodyBox();
		$bbox->ClearChilds();
		$doc->RenderAJX();

	}

	protected function bindNodeClass($t, $fname, $css_def=null){
		$m = igk_getv(igk_get_env(IGK_ENV_INVOKE_ARGS), "m");
		if ($m == "global"){
			//for global states

			$this->_initCssStyle();
		}
		else
			parent::bindNodeClass($t, $fname, $css_def);

	}
	protected function _initCssStyle(){
		parent::_initCssStyle();
		if (igk_getv(igk_get_env(IGK_ENV_INVOKE_ARGS), "m")=="global"){
			// igk_css_bind_file($this, dirname(__FILE__)."/Styles/default.pcss");
			igk_ctrl_bind_css_file($this, dirname(__FILE__)."/Styles/default.pcss");
			// igk_css_reg_global_style_file(
			// 	dirname(__FILE__)."/Styles/default.pcss",
			// 	null,
			// 	$this);
		}
	}
	protected function getWsdlFile(){
		return igk_io_dir($this->getDataDir()."/service.wsdl");
	}
	///<summary>call this function to generate a wsdl file</summary>
	public function wsdl($a=null, $appxml=1){
		$b = $this->getWsdlFile();
		if ( ($a && igk_is_conf_connected())  || !file_exists($b)){
			$this->generate_wsdl($b);

			if (igk_getr("r")==1){
				igk_navto($this->getServiceUri());
			}
		}
		$s = IGKIO::ReadAllText($b);


		if ($appxml)
			header("Content-Type: application/xml");
		igk_wl($s);
		igk_exit();
	}

	public function cachewsl(){
		$this->Configs->clServiceDisableWSDLCache = !$this->Configs->clServiceDisableWSDLCache ;
		$this->storeConfigSettings();
		//igk_wln($this->Configs->clServiceDisableWSDLCache);
		igk_navto($this->getServiceUri());
	}


	///<summary></summary>
	private function generate_wsdl(){

		$b = $this->getWsdlFile();
		$n = $this->getServiceName();
		$g = new WsdlFile($n,
			igk_io_baseUri()."/".IGK_SERVICE_BASE_URI."/".$n,
			array(
				"nsprefix"=>"igkns",
				"nsuri"=>"http://www.igkdev.com"
			));
		$g->initService($n,array("doc"=>$this->getServiceDescription()));
		$fc = $this->getDataDir()."/.funclist.xml";
		if (file_exists($fc))
			@unlink($fc);
		$this->init_wsdl($g);
		$g->Save($b);
	}
	///<summary>init service environment</summary>
	public static function InitEnvironment($ctrl){
		if (!is_object($ctrl) || !igk_reflection_class_extends(get_class($ctrl), __CLASS__))
			return 0;
		$ctrl->generate_wsdl();
		return 1;
	}

	///<summary>get available function lists</summary>
	private function _getAvailableFuncs($new=false, $funcrequest=null){

		$tab =  igk_sys_getfunclist($this, $new, $funcrequest);
		$h = array();
		foreach($tab as $v){
			if ($this->IsExposedServiceFunction($v))
				$h[] = $v;
		}
		return $h;
	}
	public final function IsExposedServiceFunction($fn){ 
		$tab = igk_get_env("sys://services/".get_class($this)."/notexposed");
		if (isset($tab[strtolower($fn)])){
			return 0;
		}
		//igk_ilog("check if exposed ".$fn);
		return preg_match("/^(view|getname|getdb|clearwsdl_cache|getusedataschema)$/i", $fn) ? 0 : 1;
	}

	public function clearwsdl_cache(){
		$tab = array();
		$d = ini_get("soap.wsdl_cache_dir");

		$s = igk_io_getfiles($d, "/(.)+wsdl(.)+/i", 1);
		foreach($s as $f){
			@unlink($f);
		}
		// igk_wln(igk_get_session(IGK_REDIRECTION_SESS_PARAM));
		igk_nav_session();
		
	}
	protected function init_wsdl($wsdl){
		//initalize the wsdl function list
		$cl = get_class($this);
		$funclist = $this->_getAvailableFuncs();
		$wsdl->registerMethod($cl, $this->getServiceName(), $funclist);
	}
	protected function init_server(){

		//	if ($this->Configs->ServiceDisableWSDLCache){
		ini_set("soap.wsdl_cache_enabled",$this->Configs->clServiceDisableWSDLCache ? "0":"1");
		$header =igk_get_allheaders();
		//initialize the server.
		//override this method if you want to customize
		$b = $this->getWsdlFile();
		if (!file_exists($b)){
			$this->generate_wsdl();
		}
		$srvu = $this->getServiceUri();
		$srv = new SoapServer($b,array("uri"=>$srvu));
		$srv->setClass(get_class($this));
		$srv->handle();

		//igk_ilog(igk_env_count("init_server"));

		if (!isset($header ["SOAPACTION"]))
			return;


		$sc=ob_get_contents();
		if (!strstr($sc, 'SOAP-ENV:Envelope')){
			// igk_ilog($_SERVER);
			// igk_ilog($_SERVER);
			// igk_ilog($http_response_header);
			$r = igk_createXMLNode("response");
			$r->addXmlNode("error")->Content = "Failed to execute ".$_SERVER["HTTP_SOAPACTION"];
			$t = $r->addXmlNode("info");
			$t->addXmlNode("post_max_size")->Content = ini_get("post_max_size") ;
			$t->addXmlNode("post_length")->Content = igk_get_sizev(igk_getv($_SERVER, "CONTENT_LENGTH"));
			$r->RenderXML();

		}
		igk_exit();

	}

	protected function renderDefaultDoc(){
		// igk_ilog(__METHOD__);
		$this->init_server();
		$this->_viewDoc();;
		igk_exit();
	}


	///<summary>get de default string content</summary>
	public static function GetAdditionalDefaultViewContent(){//default view
		return  <<<EOF
<?php
\$f = \$this->getParentView("viewfuncs.phtml");
include(\$f);
?>
EOF;
	}

	private function _viewDoc(){
		//only one document for all services
		$d = igk_get_document("sys://document/services", true); //new IGKHtmlDoc($this->App, true);
		igk_set_env( "sys://designMode/off",10);
		$d->setParam("sys://designMode/off",1);


		$d->Title = R::ngets("title.app_2", $this->ServiceName, $this->App->Configs->website_title);
		$d->Favicon = new IGKHtmlRelativeUriValueAttribute(igk_io_baseRelativePath($this->getDataDir()."/R/Img/favicon.ico"));
		igk_html_rm($this->TargetNode);//->remove();
		// $d->Body->ClearChilds();
		$div = $d->Body->addBodyBox()->clearChilds();
		$this->setCurrentView("default", true, null, array("doc"=>$d));
		$div->add($this->TargetNode);
		$c = $d->Body->addNodeCallback("debug-z", function($t){
			return $t->addDiv()->setId("debug-z");//->Content = "test ";
		});

		// $ce = new globalListener();
		// igk_wln("register == ".	igk_notification_reg_event(null, $ce));
		// $c->Content = "DATA ...";
		//$c->Dispose();
		// igk_wln(array_keys($d->Body->getParam(IGK_NAMED_NODE_PARAM)));







		//$div->RenderAJX();
		$d->Body->addScriptContent("main-ss", "ns_igk.configure({nosymbol:1});");
		$d->setParam("no-script",0);
		$d->RenderAJX();
	}
	public final function getParentView($n){
		if(($s = realpath($n))==$n)
			return $n;

		$g =  IGKIO::GetDir(dirname(__FILE__)."/".IGK_VIEW_FOLDER. "/".$n);
		if (file_exists($g))
			return $g;
		return null;
	}
	public final function getParentArticle($n){
		if(($s = realpath($n))==$n)
			return $n;

		$g =  IGKIO::GetDir(dirname(__FILE__)."/".IGK_ARTICLES_FOLDER. "/".$n);
		if (file_exists($g))
			return $g;
		return null;
	}
	public function getExposedServiceFunction(){
		$funclist = $this->_getAvailableFuncs();
		return $funclist;
	}
	public static function controllerLoaded(){
		igk_wln_e(__FILE__.':'.__LINE__,
			"getConrollerLoaded"
		,"services"
		,igk_count(self::$sm_services));
	}

	private function __getMethodParameter($method){
		if (empty($method) || !method_exists($this, $method))
			return null;
		$rf = new ReflectionMethod($this, $method);
		return  $rf->getParameters();
	}
	////+-------------------------------------------------------
	/// SERVICE FUNC
	////+-------------------------------------------------------
	public function getDesc($method){

		$t = igk_createNode("div");
		// $t->addObData($method);
		$t->addDiv()->addArticle($this, $method.".desc");

		$c= $this->__getMethodParameter($method);

		// igk_ilog("etest ..... ".igk_count($c));
		if (igk_count($c)>0){
			$dv = $t->addDiv()->setClass("args")->setStyle("background-color:#efefef");
			$table = $dv->addTable();
			$r = $table->addTr();
			$r->addTh()->Content = __ ("Name");
			$r->addTh()->Content = __ ("Type");
			$r->addTh()->Content =__("Description");

			// $r = $table->addTr();
			// $f = ".json";
			// $t->addObData($rf);
			// //json_decode(igk_io_read_alltext
			$cf = $this->getArticlesDir()."/".$method.".json";
			//igk_ilog($cf);
			$store = 0;
			if (!file_exists($cf)){
				$store = 1;
				$jdata = igk_createObj();
			}else{
			$s = igk_io_read_allfile($cf);
			$jdata = igk_json_parse($s);
			}
			// if($method == "getProductCategoryHierachi"){
				// igk_wln(igk_json_parse($s));
				// $jdata = igk_json_array_parse($s);
			 // igk_wln($s);
			// igk_wln($jdata);
			
			// }

			$lg = R::GetCurrentLang();
			foreach($c as  $v){
				$nn= $v->name;
				$r = $table->addTr();
				$r->addTd()->Content = $nn;//addObData($v);
				$r->addTd()->Content = "";
				$v = igk_getv($jdata, $nn);
				$r->addTd()->Content = ($v) ? igk_getv($v, $lg ) : IGK_HTML_SPACE;
					if ($store){
						$jdata->$nn  =(object)array($lg=>"");
					}
			}
			if ($store){
				igk_io_save_file_as_utf8_wbom($cf, json_encode($jdata));
			}
		}
		return $t->Render();

	}


	public function getServiceViewTitle($m){
		$c = $this->__getMethodParameter($m);
		$ct = igk_count($c);//$this->__getMethodParameter($m);
		$n = igk_createNode("div");
		if (igk_is_conf_connected()){
			$n->addA("#")->setAttribute("onclick", "javascript: ns_igk.services.invoke('{$m}',{$ct}); return false;")->Content =$m;
		}else{
			$n->Content = $m;
		}
		return $n->Render();
	}

	public function getExtra($m){
		//$c = $this->__getMethodParameter($m);
		$n = igk_createNode("div");

		$f = $this->getArticlesDir()."/".$m.".json";
		if (file_exists($f)){
			$n->addJSAExtern("openFile", igk_io_to_uri($f))->setClass("igk-btn igk-btn-default igk-active")->Content = R::ngets("btn.Edit");
		}
		$s= $n->Render();
		return $s;
	}
	public function initRowView($m){
		$this->getExtra($m);
	}
}




igk_set_env("sys://controller/loaded", array("IGKServiceCtrl", "controllerLoaded"));


