<?php

use IGK\Controllers\ControllerExtension;
use IGK\Resources\R;
use IGK\System\Console\Logger;

use function igk_resources_gets as __;


///<summary>Represente class: IGKApplicationController</summary>
/**
* Represente IGKApplicationController class
*/
abstract class IGKApplicationController extends IGKPageControllerBase implements IIGKUriActionRegistrableController {
    const IGK_CTRL_APPS_KEY=IGKSession::BASE_SESS_PARAM + 0xA00;
    const IGK_CTRL_APP_INIT=self::IGK_CTRL_APPS_KEY + 1;
    const IGK_CTRL_APP_TEMPLATE=self::IGK_CTRL_APPS_KEY + 2;
    private static $INIT;
    static $sm_apps;
    ///<summary></summary>
    ///<param name="view" default="'default'"></param>
    ///<param name="doc" default="null"></param>
    ///<param name="render" default="true"></param>
    /**
    * 
    * @param mixed $view the default value is 'default'
    * @param mixed $doc the default value is null
    * @param mixed $render the default value is true
    */
    protected function __viewDoc($view='default', $doc=null, $render=true){

        $d= $doc ?? $this->getAppDocument(true);
        if(($d === igk_app()->Doc))
            igk_die("/!\\ app document match the global document. That is not allowed");




	    $d->Title= __("title.app_2", igk_getv($this->Configs, IGK_CTRL_CNF_TITLE), igk_app()->Configs->website_title);
        $this->setEnvParam(IGK_CURRENT_DOC_PARAM_KEY, $d);
        $bbox=$d->Body->addBodyBox();
        igk_doc_set_favicon($d, $this->getResourcesDir()."/Img/favicon.ico");
        $this->setCurrentView($view, true);
        $bbox->add($this->TargetNode);

        if($render){
            igk_render_doc($d, 0, $this);
        }
    }
    ///<summary></summary>
    ///<param name="news" default="false"></param>
    ///<param name="funcrequest" default="null"></param>
    /**
    * 
    * @param mixed $news the default value is false
    * @param mixed $funcrequest the default value is null
    */
    private function _getfunclist($news=false, $funcrequest=null){
        return igk_sys_getfunclist($this, $news, $funcrequest);
    }
    ///<summary> override this method to handle shortcut evaluationUri according to function and param</summary>
    ///<return> true if handled otherwise false</return>
    /**
    *  override this method to handle shortcut evaluationUri according to function and param
    */
    protected function _handle_uri_param($fc, $param, $options=null){
        return false;
    }
    ///<summary>override to create the application db utility intance </summary>
    /**
    * override to create the application db utility intance
    */
    protected function _createDbUtility(){
        return new IGKDbUtility($this);
    }
    ///<summary></summary>
    /**
    * 
    */
    // public function about(){
    //     $doc= $this->getAppDocument(); //createNewDoc();
    //     $doc->Title=R::ngets("title.about_1", $doc->Title);
    //     $f=$this->getViewFile("about");
    //     if(file_exists($f)){
	// 		$this->loader->view($f, func_get_args());
	// 		$doc->body->addBodyBox()->add($this->getTargetNode());
    //     }
    //     else{
    //         $bbox=$doc->body->addBodyBox();
    //         $bbox->ClearChilds();
    //         $t=$bbox;
    //         $t->addContainer()->addCol()->addDiv()->setClass("igk-fsl-4")->Content=R::ngets("title.about");
    //         $ct=$t->addDiv()->addContainer();
    //         $ct->addCol()->addDiv()->Content="Version : ".igk_getv($this->Configs, "Version", "1.0");
    //         $ct->addCol()->addDiv()->Content="Author : ".IGK_AUTHOR;
    //         $ct->addCol()->addDiv()->Content="CONTACT : ".IGK_AUTHOR_CONTACT;
    //         $dv=$ct->addWebMasterNode()->addCol()->addDiv();
    //         $dv->Content="Location : ".$this->getDeclaredFileName();
    //     }
    //     $doc->RenderAJX();
    //     // $doc->RemoveChilds();
    //     // $doc->Dispose();
    //     // unset($doc);
	// 	igk_exit();
    // }
    ///<summary></summary>
    /**
    * 
    */
    public function administration(){
        $doc= $this->getAppDocument();
        $div=$doc->body->clearChilds()->addDiv();
        $div["class"]="igk-notify igk-notify-warning";
        $div["style"]="display:block; position:absolute; top:50%; min-height:96px; margin-top:-48px;";
        $div->Content="No administration page";
        $div=$doc->body->addDiv();
        $div["style"]="font-size: 3em; ";
        $div->addA($this->getAppUri(""))->setClass("glyphicons no-decoration")->Content="&#xe021;";
        $doc->RenderAJX();
    }
    ///<summary></summary>
    ///<param name="func"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $func
    * @param mixed $args
    */
    protected function bind_func($func, $args){
        if($func){
            $cl=get_class($this);
            if(method_exists($cl, $func)){
                call_user_func_array(array($this, $func), $args);
                return true;
            }
        }
        return false;
    }
    ///<summary>check before controller add</summary>
    /**
    * check before controller add
    */
    public static function CheckBeforeAddControllerInfo($request){
        $title=igk_getv($request, IGK_CTRL_CNF_TITLE);
        $appname=strtolower(igk_getv($request, IGK_CTRL_CNF_APPNAME));
        $c=self::GetApps();
        if(isset($c->apps[$appname]) && igk_is_class_incomplete($c->apps[$appname])){
            unset($c->apps[$appname]);
        }
        if(empty($title) || empty($appname) || !preg_match(IGK_IS_FQN_NS_REGEX, $appname) || isset($c->apps[$appname])){
            return false;
        }
        return true;
    }
    ///<summary></summary>
    ///<param name="funcname"></param>
    /**
    * 
    * @param mixed $funcname
    */
    protected final function checkFunc($funcname){
        if(igk_is_conf_connected() || $this->UserAllowedTo($funcname))
            return true;
        igk_notifyctrl()->addWarning(R::ngets("warning.usernotallowed_1", $funcname));
        igk_navto($this->getAppUri(""));
        igk_exit();
        return false;
    }
    // ///<summary>check init and init user to this apps </summary>
    // /**
    // * check init and init user to this apps
    // */
    // public function checkUser($nav=true, $uri=null){
    //     $r=true;
    //     $u= igk_app()->Session->User;
    //     $ku=$this->getUser();   

    //     if($ku === null){
    //         if($u !== null){
    //             $this->setUser($this->initUserFromSysUser($u));
    //         }
    //         else
    //             $r=false;
    //     }
    //     if($nav && !$r){
    //         $ruri=igk_io_base_request_uri();
    //         $s="";
    //         if($ruri)
    //             $s="q=".base64_encode($ruri);
    //         $u=($uri == null ? $this->getAppUri(""): $uri);
    //         if($s)
    //             $u .= ((strpos($u, "?") === false) ? "?": "&").$s;
    //         igk_navto($u);
    //     }
    //     return $r;
    // }
    ///<summary></summary>
    ///<param name="node" default="null"></param>
    /**
    * 
    * @param mixed $node the default value is null
    */
    public function conffunctions($node=null){
        if(!igk_is_conf_connected()){
            igk_navto($this->getAppUri());
            igk_exit();
        }
        if($node == null){
            $doc= $this->getAppDocument();
            $doc->Title="Configure Functions - [".$this->App->Configs->website_domain."]";
            $bbox=$doc->body->addBodyBox();
            $bbox->addIGKAppHeaderBar($this);
            $bbox->addMenuBar();
            $this->conffunctions($bbox);
            $doc->RenderAJX();
            return;
        }
        $d=$node->addDiv();
        $tab=$d->addTable();
        foreach(igk_sys_getall_funclist($this) as  $v){
            $tr=$tab->add("tr");
            $tr->add("td")->addSpace();
            $tr->add("td")->Content=$v->clName;
            $i=$tr->add("td")->addInput("meth[]", "checkbox");
            $i["value"]=$v->clName;
            if($v->clAvailable){
                $i["checked"]="checked";
            }
        }
    }
    ///<summary></summary>
    ///<param name="clear" default="false"></param>
    /**
    * 
    * @param mixed $clear the default value is false
    */
    public function createNewDoc($clear=false){
        $doc=$this->getParam("app/document");
        if($doc == null){
            $doc= IGKHtmlDoc::CreateDocument();
            $this->setParam("app/document", $doc);
        }
        $doc->Title=$this->AppTitle;
        if($clear)
            $doc->body->ClearChilds();
        else
            $doc->body->addBodyBox()->ClearChilds();

        return $doc;
    }
    ///<summary></summary>
    /**
    * 
    */
    public final function dbinitentries(){
        $s=igk_is_conf_connected() || $this->IsUserAllowedTo(igk_ctrl_auth_key($this, __FUNCTION__));
        if(!$s){
            igk_notifyctrl()->addErrorr("err.operation.notallowed");
            igk_navto($this->getAppUri()); 
        } 
        if($this->getUseDataSchema()){
            $r=$this->loadDataAndNewEntriesFromSchemas();
            $tb=$r->Data;
            $etb=$r->Entries;
            $ee=igk_createNode();
            $db=igk_get_data_adapter($this, true);
            if($db){
                if($db->connect()){
                    foreach($tb as $k=>$v){
                        $n=igk_db_get_table_name($k);
                        $data=igk_getv($etb, $k);
                        if(igk_count($data) == 0)
                            continue;
                        if($db->tableExists($n)){
                            foreach($data as $vv){
                                igk_db_insert_if_not_exists($db, $n, $vv);
                                if($db->getHasError()){
                                    $dv=$ee->addDiv();
                                    $dv->addNode("div")->Content="Code : ".$db->getErrorCode();
                                    $dv->addNode("div")->setClass("error_msg")->Content=$db->getError();
                                }
                            }
                        }
                        else{
                            $r=$db->createTable($n, igk_getv($v, 'ColumnInfo'), $data, igk_getv($v, 'Description'), $db->DbName);
                        }
                    }
                    $db->close();
                }
            }
        }
        if($ee->HasChilds){
            igk_notifyctrl()->addError($ee->Render());
        }
        else{
            igk_notification_push_event(igk_get_event_key($this, "dbchanged"), $this);
            $this->logout();
        } 
        igk_navto($this->getAppUri()); 
    }
    ///<summary>drop application table from system config</summary>
    /**
    * drop application table from system config
    */
    protected static function dropDb($navigate=true, $force=false){
       
        if (!($c = igk_getctrl(static::class,false))){
            return;
        }
        $s=$force || igk_is_conf_connected() || $c->IsUserAllowedTo($c->Name.":".__FUNCTION__);
        
        if($s){
            $args = func_get_args();
            $db = [ControllerExtension::class, __FUNCTION__];            
            return $db($c, ...$args);
        } 
		igk_hook("sys://drop_app_database", [$c]);
        if(igk_app_is_uri_demand($c, __FUNCTION__)){
            igk_navto($c->getAppUri());
        }
    }
    ///<summary>use to handle redirection uri</summary>
    /**
    * use to handle redirection uri
    */
    public final function evaluateUri(){
        $inf=igk_sys_ac_getpatterninfo();
        if($inf === null){
            return;
		}
        $this->handle_redirection_uri($inf);
        igk_exit();
    }
    ///<summary>List Exposed Functions</summary>
    /**
    * List Exposed Functions
    */
    public function functions($n=false){
        if(!igk_server_is_local() && !igk_is_conf_connected()){
            igk_notifyctrl()->addWarningr("warn.noaccessto_1", __FUNCTION__);
            igk_navto($this->getAppUri());
            igk_exit();
        }
        $doc = $this->getAppDocument();
        $doc->Title=R::ngets("title.app_2", "Functions ".igk_getv($this->Configs, IGK_CTRL_CNF_TITLE), $this->App->Configs->website_title);
        $d= $bodybox=$doc->body->addBodyBox();
        $d->ClearChilds();
        $m=$d->addDiv()->addDiv()->addContainer();
        $r=$m->addRow();
        $cl=get_class($this);
        $ref=new ReflectionClass($cl);
        $sf=$this->getDeclaredFileName();
        $r->addDiv()->setClass("fc_h")->setStyle("font-size:1.4em")->Content="File : ".igk_io_basepath($sf);
        $m=$d->addDiv()->addDiv()->addContainer();
        $r=$m->addRow();
        $func=$this->_getfunclist($n);
        usort($func, function($a, $b){
            return strcmp(strtolower($a), strtolower($b));
        });
        foreach($func as $k){
            $b=$r->addCol("igk-col-12-2 igk-sm-list-item")->setStyle("padding-top:8px; padding-bottom:8px")->addDiv();
            $b->addA($this->getAppUri($k))->setContent($k);
        }
        $bodybox->setStyle("position:relative; color: #eee; margin-bottom:300px;padding-bottom:0px; overflow-y:auto; color:indigo;");
        $bodybox->addDiv()->setClass("posfix loc_b loc_r loc_l dispb footer-box igk-fixfitw")->setId("fbar")->setAttribute("igk-js-fix-loc-scroll-width", "1")->setStyle("min-height:80px; z-index: 10; width: auto;");
        $bodybox->addDiv()->setClass("no-visibity dispb")->setAttribute("igk-js-fix-height", "#fbar");
        $b=$bodybox->addActionBar();
        $u=$this->getAppUri("functions/1");
        $b->addButton("btn.init")->setAttribute("value", "init function list")->setAttribute("onclick", "javascript: ns_igk.form.posturi('".$u."'); return false;");
        $doc->RenderAJX();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function get_data_schemas(){
        $u=$this->App->Session->User;
        if(!igk_is_conf_connected() && !$this->IsUserAllowedTo("system/:".__FUNCTION__)){
            igk_wln("user not allowed to");
            igk_exit();
        }
        $f=$this->getDataSchemaFile(); // Dir()."/".IGK_SCHEMA_FILENAME;
        if(file_exists($f)){
            $s=IGKHtmlReader::LoadFile($f);
            $s->RenderXML();
        }
        else{
            $d=IGKHtmlItem::CreateWebNode(IGK_SCHEMA_TAGNAME);
            $d->RenderXML();
        }
        igk_exit();
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetAdditionalConfigInfo(){
        return array(
            IGK_CTRL_CNF_TITLE=>igk_createAdditionalConfigInfo(array("clRequire"=>1)),
            IGK_CTRL_CNF_APPNAME=>igk_createAdditionalConfigInfo(array("clRequire"=>1)),
            IGK_CTRL_CNF_BASEURIPATTERN=>igk_createAdditionalConfigInfo(array("clRequire"=>1)),
            IGK_CTRL_CNF_TABLEPREFIX=>igk_createAdditionalConfigInfo(array("clRequire"=>1, "clDefaultValue"=>"tbigk_")),
            IGK_CTRL_CNF_APPNOTACTIVE=>(object)array("clType"=>"bool", "clDefaultValue"=>"0")
        );
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetAdditionalDefaultViewContent(){
        return <<<EOF
<?php
\$t->ClearChilds();
\$t->addDiv()->addSectionTitle(4)->Content = R::ngets("Title.App_1", \$this->AppTitle);
\$t->inflate(igk_io_dir(\$dir."/".\$fname));
EOF;
}
    ///<summary></summary>
    /**
    * 
    */
    protected function getAllowViewDirectAccess(){
        return 0;
    }
    ///<summary> get a application document. getDoc return the global document </summary>
    /**
    *  get a application document. getDoc return the global document
    */
    protected function getAppDocument($newdoc=false){
        return igk_get_document($this, $newdoc);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAppImgUri(){
        return igk_html_resolv_img_uri($this->getDataDir().IGK_APP_LOGO);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAppName(){
        return igk_getv($this->Configs, IGK_CTRL_CNF_APPNAME);
    }
    ///<summary>get if this application is not active</summary>
    /**
    * get if this application is not active
    */
    public function getAppNotActive(){
        return igk_getv($this->Configs, IGK_CTRL_CNF_APPNOTACTIVE);
    }
    ///<summary></summary>
    ///<return refout="true"></return>
    /**
    * 
    * @return *
    */
    public static function & GetApps(){
        if(IGKApplicationController::$sm_apps === null){
            // igk_wln_e("application get Apps : call");
            // $m=igk_app()->Session->getParam(__METHOD__);
            $m = igk_environment()->get(self::IGK_CTRL_APPS_KEY);
            if($m === null){
                $m=(object)array('_'=>array());
                // igk_app()->Session->setParam(self::IGK_CTRL_APPS_KEY, $m);
                igk_environment()->set(self::IGK_CTRL_APPS_KEY, $m);
            }
            IGKApplicationController::$sm_apps=& $m;
        }
        return IGKApplicationController::$sm_apps;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAppTitle(){
        return igk_getv($this->Configs, IGK_CTRL_CNF_TITLE);
    }
    ///<summary>return application uri</summary>
    /**
    * return application uri
    */
    public function getAppUri($function=null){
        $app=igk_app();
		if (!empty($function)){
			$function = igk_str_rm_start($function, "/");
        }
        if ($function == IGK_DEFAULT_VIEW){
            $function = "";
        }

        // igk_wln_e(__METHOD__,
        //     [
        //         "getClass: "=>get_class($this),
        //         "objectid: "=>spl_object_id($this),
        //         "subobjectid: "=>spl_object_id($app->SubDomainCtrl),
        //         "SubDomainCtrl?"=>$app->SubDomainCtrl !== null,
        //         "SubDomainCtrl class"=>get_class($app->SubDomainCtrl),
        //         "isEntry : "=> self::IsEntryController($this),
        //         "check ? "=>igk_app()->SubDomainCtrl === $this
        //     ]
        // );

        if(self::IsEntryController($this)){
            if($app->SubDomainCtrl === $this){
                $g=$app->SubDomainCtrlInfo->clView;
                if(!empty($function) && (stripos($g, $function) === 0)){
                    $function=substr($function, strlen($g));
                }
            }
        }
        else{
            $s="";
            if($this->getEnvParam(self::IGK_ENV_PARAM_LANGCHANGE_KEY)){
                $s .= R::GetCurrentLang()."/";
            }
            $rt=$this->getRootPattern();
            if($rt || $s){
                $function=$s.$rt.(!empty($function) ? "/".$function: '');
            }
        }
        if($function)
            return igk_str_rm_last(igk_io_baseuri(), '/')."/".$function;
        return igk_io_baseuri();
    }
    ///<summary>getApp Uri by transforming to balafon requirement</summary>
    /**
    * getApp Uri by transforming to balafon requirement
    */
    public final function getAppUris($function=null){
        return $this->getAppUri(igk_str_view_uri($function));
    }
    ///<summary>get authorisation key</summary>
    /**
    * get authorisation key
    */
    public function getAuthKey($k=null){
        return igk_ctrl_auth_key($this, $k);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBasicUriPattern(){
        return igk_getv($this->Configs, IGK_CTRL_CNF_BASEURIPATTERN);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getcanAddChild(){
        return false;
    }
    ///<summary>get the application current document</summary>
    /**
    * get the application current document
    */
    public function getCurrentDoc(){
        return $this->AppDocument;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDataTablePrefix(){
        return igk_getv($this->Configs, IGK_CTRL_CNF_TABLEPREFIX);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDb(){
        if(!$db=$this->getEnvParam("dbu")){
            ($db=$this->_createDbUtility()) || igk_die("failed to create db utility");
            $this->setEnvParam("dbu", $db);
        }
        return $db;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDomainUriAction(){
        return "^(/(?P<lang>".R::GetSupportLangRegex()."))?".IGK_REG_ACTION_METH."(;:options)?";
    }
    ///<summary>get exposed functions list</summary>
    /**
    * get exposed functions list
    */
    public function getExposed(){
        static $exposed=null;
        if($exposed === null){
            $exposed=array("about"=>1, "logout"=>1);
        }
        return $exposed;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsVisible(){
        $v=$this->RegisterToViewMecanism;
        $c=$v || (!$this->getAppNotActive() && $this->IsActive());
        return $c;
    }
    ///<summary> application by default not allowed global action</summary>
    public function getNoGlobalAction(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getRegInvokeUri(){
        return $this->getUri(IGK_EVALUATE_URI_FUNC);
    }
    ///<summary>can be invoked in platform's view mecanism</summary>
    /**
    * can be invoked in platform's view mecanism
    */
    public function getRegisterToViewMecanism(){
        $f=igk_app()->Session->PageFolder;
        return ($this === igk_get_defaultwebpagectrl()) && ($f == IGK_HOME_PAGEFOLDER);
    }
    ///<summary>get sub application app uri </summary>
    /**
    * get sub application app uri
    */
    public function getRegUriAction(){
        $primary=$this->getBasicUriPattern();
        if(empty($primary))
            return null;
        $s="".$primary.IGK_REG_ACTION_METH;
        if((strlen($s) > 0) && $s[0]="^"){
            $s="^(/:lang)?".substr($s, 1)."(;:options)?";
        }
        return $s;
    }
    ///<summary>get base uri pattern configured</summary>
    /**
    * get base uri pattern configured
    */
    protected function getRootPattern(){
        $t=array();
        $broot=$this->getBasicUriPattern();
        $s=preg_match_all("/(\^\/)?(?P<name>(([^\/]+)(\/([^\/]+)\/?)?))/i", $broot, $t);
        if($s > 0){
            $o=$t["name"][0];
            return $o;
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSystemVars(){
        $doc=$this->getEnvParam(IGK_CURRENT_DOC_PARAM_KEY);
        if($doc === null){
            if(igk_sys_is_subdomain() && ($this->App->SubDomainCtrl === $this)){
                $doc=$this->AppDocument;
            }
            else{
                $doc=igk_app()->Doc;
            }
            $this->setEnvParam(IGK_CURRENT_DOC_PARAM_KEY, $doc);
        }
        return parent::getSystemVars();
    }
    ///<summary> base application uri handle</summary>
    /**
    *  base application uri handle
    */
    public function handle_redirection_uri($u, $forcehandle=1){
        igk_sys_handle_uri();
        extract(array(
            "page"=>0,
            "k"=>0,
            "pattern"=>0,
            "p"=>0,
            "c"=>0,
            "param"=>0,
            "viewdefault"=>0,
            "query_options"=>0
        ));
        $lang=null;
        
        if(is_string($u)){
            $page=explode("?", $u);
            $k=$this->getDomainUriAction();
            $pattern=igk_pattern_matcher_get_pattern($k);
            $p=igk_pattern_get_matches($pattern, $page[0], array_merge(["lang"], igk_str_get_pattern_keys($k)));
            extract(igk_pattern_view_extract($this, $p, 1)); 
            igk_ctrl_change_lang($this, $p);
        }
        else{
            unset($u->ctrl);
            $page=explode("?", $u->uri);
            $pattern=$u->pattern;
            $p=$u->getQueryParams();
            $viewdefault=1;
            extract(igk_pattern_view_extract($this, $p, 1)); 
            igk_ctrl_change_lang($this, $p);
        }
        //passing ctrl to view for sitepam
        $ctrl = $this;
        include(IGK_LIB_DIR."/Inc/igk_sitemap.pinc");
        $tn=$this->TargetNode;
        
        
        if($this->_handle_uri_param($c, $param, $query_options)){
            igk_exit();
        }
        
        $this->regSystemVars(null);
        if(empty($param))
        $param=array();
        if(empty($c) && ($this->RegisterToViewMecanism || $viewdefault)){
            $this->renderDefaultDoc(igk_conf_get($this->Configs, "/default/document", 'default'));
            igk_exit();
        }
        $doc=$this->AppDocument;
        $this->setEnvParam(IGK_CURRENT_DOC_PARAM_KEY, $doc);
        $fnc="";
        $handle=0;  
        //igk_wln_e(__FILE__.":".__LINE__, $u, $c, $param, $query_options, $this->getViewFile($c));
        if(!($handle=$this->handle_func($c, $param, $doc, 0, null)) && (file_exists($fnc=$this->getViewFile($c)) && preg_match(IGK_VIEW_FILE_END_REGEX, $fnc))){
     
            $actionctrl=igk_getctrl(IGK_SYSACTION_CTRL, true);
            $m=$actionctrl->matche($page[0]);
            $ck=$this->getEnvParam("appkeys");
            if($m !== null){
                if($m->action === $ck){
                    if(igk_sys_is_subdomain() && ( ($loc = igk_get_defaultwebpagectrl()) === $this)){
                        $m="Misconfiguration. Subsequent call of domain controller is not allowed. ".igk_io_request_uri().
						"<br />".$this->getName().
						"<br />";
                        throw new IGKUriActionException($m, $u, 0x1a001);
                    }
                }
                else{
                    $actionctrl->invokeUriPattern($m);
                    igk_exit();
                }
            } 
            
            igk_view_dispatch_args($this, $c, $fnc, $param);          
            $this->setCurrentView($c, true, null, $param, $query_options);
            $ctx=$this->getEnvParam(IGK_CTRL_VIEW_CONTEXT_PARAM_KEY);
            if(igk_is_ajx_demand()){
                igk_ajx_replace_node($tn, "#".$tn["id"]); 
            }
            else{
                if($ctx == "docview"){
                    igk_app()->Doc->RenderAJX();
                }
                else{
                    $doc->body->addBodyBox()->clearChilds()->add($tn);
                    $doc->RenderAJX();
                }
            }
            $this->resetCurrentView(null);
            igk_exit();
        }
        if(!empty($s=$this->_output)){
            $tn->addSingleNodeViewer(IGK_HTML_NOTAG_ELEMENT)->Content=$s;
            $this->_output=null;
        }
        if($handle){
            if($forcehandle){
                $doc->body->addBodyBox()->clearChilds()->add($tn);
                $doc->RenderAJX();
                igk_exit();
            }
            return 1;
        }
        // igk_wln("action not handled");
        $actionctrl=igk_getctrl(IGK_SYSACTION_CTRL, true);
        $ck=$this->getEnvParam("appkeys");
        $m=$actionctrl->matche($page[0]);
        // igk_trace();
        if (igk_env_count(__METHOD__)>10){
            igk_wln_e("infinie loop detected on request ",
                __FILE__.':'.__LINE__);
        }
        if((!empty($fnc) && file_exists($fnc)) && $m){
            if(igk_sys_is_subdomain() && ($m->action === $ck)){
                igk_set_header(500);
                $m="Misconfiguration. Subsequent call of domain controller is not allowed. ".igk_io_request_uri();
                throw new IGKUriActionException($m, $u, 0x1a001);
            }
            igk_app()->Session->RedirectionContext=1;
           //  igk_wln_e("invoke uri pattern ");
            // $actionctrl->invokeUriPattern($m);

        }
        else{

		    igk_sys_show_error_doc(igk_getr('__c', 404), $this, ["page"=>$page, "fnc"=>$fnc]);
            igk_exit();
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="code"></param>
    /**
    * 
    * @param mixed $code the default value is 0
    */
    protected function HandleError($code=0){
        return 0;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function InitComplete(){
        parent::InitComplete();
        $n=str_replace("\\", ".", $this->AppName);
        $c=self::GetApps();
        if(empty($n)){
            $n=str_replace("\\", ".", $this->Name);
        }  
        if(preg_match(IGK_IS_FQN_NS_REGEX, $n) && !isset($c->_[$n])){
            $c->_[$n]=$this->getName();
        }
        else{
            igk_assert_die(!igk_get_env("sys://reloadingCtrl"), "Application identifier is not valid or already register. #".$n);
        }
        $this->register_action();
        if(!isset(self::$INIT)){
            igk_notification_reg_event(IGK_EVENT_DROP_CTRL, "igk_app_ctrl_dropped_callback");
            self::$INIT=true;
        }
        IGKOwnViewCtrl::RegViewCtrl($this, 0);
    }
   
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    public static function InitEnvironment($ctrl){
        IGKIO::CreateDir($ctrl->getDataDir());
        IGKIO::CreateDir($ctrl->getResourcesDir());
        $s=IGKGD::Create(256, 128);
		$n = $ctrl->getName();
        igk_io_w2file($ctrl->getDataDir().IGK_APP_LOGO, $s->RenderText(), true);
        igk_io_w2file($ctrl->getDataDir().IGK_APP_LOGO.".gkds", <<<EOF
<gkds>
  <Project>
    <SurfaceType>IconSurface</SurfaceType>
  </Project>
  <Documents>
    <LayerDocument PixelOffset="None" BackgroundTransparent="True" Width="256" Height="128" Id="{$n}">
      <Layer Id="layer_{$n}">
      </Layer>
    </LayerDocument>
  </Documents>
</gkds>
EOF
        , true);
        return true;
    }
    ///<summary>check that if the controller handle base uri</summary>
    /**
    * check that if the controller handle base uri
    */
    public function is_handle_uri($uri=null){
        if(igk_const('IGK_REDIRECTION') == 1){
            if(preg_match("#^/!@#", igk_io_request_uri()))
                return false;
        }
        return $this->RegisterToViewMecanism;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function IsActive(){
        $inf=igk_sys_ac_getpatterninfo();
        return (($inf != null) && preg_match(igk_sys_ac_getpattern($this->getBasicUriPattern()), igk_io_rootBaseRequestUri()));
    }
    ///<summary></summary>
    ///<param name="k"></param>
    /**
    * 
    * @param mixed $k
    */
    public function isAuthKeys($k){
        if(preg_match("/^(".$this->getAuthKey().")/", $k))
            return true;
        return false;
    }
    ///<summary>get if function is available</summary>
    /**
    * get if function is available
    */
    protected function IsFuncUriAvailable(& $func){
        
        $c = new ReflectionMethod($this, $func);
        if (!$c->isPublic()){
            return false;
        }
        if(igk_is_conf_connected() || isset($this->Exposed[$func]))
            return true;
        $lst=$this->_getfunclist(false, $func);
        if(igk_array_value_exist($lst, $func))
            return true;
        return false;
    }
   
    ///<summary></summary>
    /**
    * 
    */
    public function load_data(){
        $doc= $this->getAppDocument();
        $d=$doc->Body->add("div");
        $frm=$d->addForm();
        $frm["action"]=$this->getAppUri("load_data_files");
        $frm["method"]="POST";
        $i=$frm->addInput("clFileName", "file");
        $i["class"]="dispn";
        $i["multiple"]="false";
        $i["accept"]="text/xml";
        $i["onchange"]="this.form.submit(); return false;";
        $frm->addInput("clRuri", "hidden", $this->getAppUri(""));
        $frm->addScript()->Content=<<<EOF
(function(){var f = \$ns_igk.getParentScriptByTagName('form'); f.clFileName.click();})();
EOF;
        $doc->RenderAJX();
        igk_exit();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function load_data_files(){
        if(isset($_FILES["clFileName"])){
            $f=$this->getDataSchemaFile();
            $dom=IGKHtmlItem::CreateWebNode("dummy");
            $dom->Load(IGKIO::ReadAllText($_FILES["clFileName"]["tmp_name"]));
            $d=$this->getAppDocument();
            $div=$d->Body->add("div");
            if(igk_count($dom->getElementsByTagName(IGK_SCHEMA_TAGNAME)) == 1){
                igk_io_move_uploaded_file($_FILES["clFileName"]["tmp_name"], $f);
                $div->add("div", array("class"=>"igk-title"))->Content=R::ngets("Title.GoodJOB");
                $div->add("div", array("class"=>"igk-notify igk-notify-success"))->Content=R::ngets("msg.fileuploaded");
            }
            else{
                $div->add("div", array("class"=>"igk-title"))->Content=R::ngets("Title.Error");
                $div->add("div", array("class"=>"igk-notify igk-notify-danger"))->Content=R::ngets("error.msg.filenotvalid");
            }
            $d->RenderAJX();
            unset($d);
            unset($dom);
        }
        else{
            igk_navtobase("/");
        }
        igk_exit();
    }
    ///<summary></summary>
    /**
    * 
    */
    protected final function register_action(){
        $k=$this->getEnvParam("appkeys");
        if(!empty($k)){
            igk_sys_ac_unregister($k);
        }
        $k=$this->getRegUriAction();
        if(!empty($k)){
            igk_sys_ac_register($k, $this->getRegInvokeUri());
            $this->setEnvParam("appkeys", $k);
        }
    }
    ///<summary></summary>
    ///<param name="view" default="'default'"></param>
    ///<param name="doc" default="null"></param>
    ///<param name="render" default="true"></param>
    /**
    * 
    * @param mixed $view the default value is 'default'
    * @param mixed $doc the default value is null
    * @param mixed $render the default value is true
    */
    protected function renderDefaultDoc($view='default', $doc=null, $render=true){
         $this->__viewDoc($view, $doc, $render);
    }
    ///<summary></summary>
    ///<param name="c"></param>
    /**
    * 
    * @param mixed $c
    */
    protected function renderError($c){
		igk_dev_wln_e(__FILE__.".".__LINE__, "RenderError document");
        $f=igk_io_baseDir("Pages/error_404.html");
        if(file_exists($f)){
            include($f);
        }
        else{
            $d= $this->getAppDocument();
            $d->Title= R::ngets("title.app_2", igk_getv($this->Configs, IGK_CTRL_CNF_TITLE), $this->App->Configs->website_title);
            $div=$d->Body->add("div");
            $div->add("div", array("class"=>"igk-title"))->Content=R::ngets("Title.Error");
            $div->add("div", array("class"=>"igk-notify igk-notify-danger"))->Content="No function $c found";
            $d->RenderAJX();
			igk_exit();
        }
    }
     
    ///<summary> save data schema</summary>
    /**
    *  save data schema
    */
    public function save_data_schemas($exit=1){
        $this->checkFunc(__FUNCTION__);
        $dom=IGKHtmlItem::CreateWebNode(IGK_SCHEMA_TAGNAME);
        $dom["ControllerName"]=$this->Name;
        $dom["Platform"]=IGK_PLATEFORM_NAME;
        $dom["PlatformVersion"]=IGK_WEBFRAMEWORK;
        $e=IGKHtmlItem::CreateWebNode("Entries");
        $d= igk_getv($this->loadDataFromSchemas(),"tables");
        if($d){
            $tabs=array();
            foreach($d as $k=>$v){
                $b=$dom->add(IGKDbSchemas::DATA_DEFINITION);
                $b["TableName"]=$k;
                $b["Description"]=$v["Description"];
                $tabs[]=$k;
                foreach($v["ColumnInfo"] as $cinfo){
                    $col=$b->add(IGK_COLUMN_TAGNAME);
                    $tb=(array)$cinfo;
                    $col->setAttributes($cinfo);
                }
            }
            $db=igk_get_data_adapter($this);
            $r=null;
            if($db){
                $db->connect();
                foreach($tabs as $tabname){
                    try {
                        $r=$db->selectAll($tabname);
                        if($r->RowCount > 0){
                            $s=$e->add($tabname);
                            foreach($r->Rows as $c=>$cc){
                                $irow=$s->addXMLNode(IGK_ROW_TAGNAME);
                                $irow->setAttributes($cc);
                            }
                        }
                    }
                    catch(Exception $ex){}
                }
                $db->close();
            }
        }
        if($e->HasChilds){
            $dom->add($e);
        }
        if($exit)
            header("Content-Type: application/xml");
        $dom->RenderAJX();
        if($exit)
            igk_exit();
    }
    ///<summary></summary>
    ///<param name="t" ref="true"></param>
    /**
    * 
    * @param  * $t
    */
    public static function SetAdditionalConfigInfo(& $t){
        $t[IGK_CTRL_CNF_BASEURIPATTERN]=igk_getr(IGK_CTRL_CNF_BASEURIPATTERN);
        $t[IGK_CTRL_CNF_TITLE]=igk_getr(IGK_CTRL_CNF_TITLE);
        $t[IGK_CTRL_CNF_APPNAME]=strtolower(igk_getr(IGK_CTRL_CNF_APPNAME));
        $t[IGK_CTRL_CNF_APPNOTACTIVE]=igk_getr(IGK_CTRL_CNF_APPNOTACTIVE);
        $t[IGK_CTRL_CNF_TABLEPREFIX]=igk_getr(IGK_CTRL_CNF_TABLEPREFIX);
    }
    ///<summary></summary>
    ///<param name="doc"></param>
    /**
    * 
    * @param mixed $doc
    */
    protected function setDefaultFavicon($doc){		
		throw new Exception(__("Not implement : use igk_doc_set_favicon function "));
        // $d=$this->getResourcesDir()."/Img/favicon.ico";
        // igk_doc_set_favicon($doc, $d);
    }
    ///<summary></summary>
    ///<param name="param"></param>
    /**
    * 
    * @param mixed $param
    */
    public function SetupCtrl($param){
        parent::SetUpCtrl($param);
        $t=strtolower(str_replace(' ', '_', $this->Name));
        if(empty($t))
            throw new Exception(__("Can't setup controller: {0}", get_class($this) ));
        $c=array($t, $t."_administrator");
        $table = igk_db_get_table_name(IGK_TB_GROUPS);
        foreach($c as $k){
            $e=igk_db_table_select_where($table, array(IGK_FD_NAME=>$k), $this);
            if($e && !$e->Success){
                igk_db_insert($this, $table, array(IGK_FD_NAME=>$k));
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function storeConfigSettings(){
        if ($ret = parent::storeConfigSettings()){
            $this->register_action();
        }
        return $ret;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function sync_from_user_data(){
        igk_wln(__FUNCTION__." Not implements");
    }
    ///<summary> synchronize the current user data to target server</summary>
    /**
    *  synchronize the current user data to target server
    */
    public function sync_user_data($login=null){
        if(($login == null) && ($this->User != null))
            $login=$this->User->clLogin;
        $c=igk_get_user_bylogin($login);
        $d=igk_createNode("response");
        if($c == null){
            $d->addXmlNode("error")->Content="LoginNotFound";
            $d->addXmlNode("msg")->Content="login . not present on our database";
            igk_wln($d);
            igk_exit();
        }
        ob_start();
        $this->save_data_schemas(0);
        $s=ob_get_contents();
        ob_end_clean();
        igk_wl($s);
        igk_exit();
    }
    ///<summary>shortcut to global view function</summary>
    /**
    * shortcut to global view function
    */
    public function v($view='default', $forceview=false){
        $this->setCurrentView($view, $forceview);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function View(){
        $v_context="app";
        if($this->RegisterToViewMecanism && !$this->IsActive()){
            $v_context="docview";
            $doc=$this->getEnvParam(IGK_CURRENT_DOC_PARAM_KEY) ?? igk_app()->Doc;
            if($doc !== null){
                igk_html_add($this->TargetNode, $doc->body->addBodyBox()->ClearChilds());
            }
            else{
                igk_ilog(implode(",", ["Session probably destroyed. Document is null",
                "session time out ". ini_get('session.gc_maxlifetime')]));
                igk_die("/!\\ Session kill");
            }
        }
        $this->setEnvParam(IGK_CTRL_VIEW_CONTEXT_PARAM_KEY, $v_context);
        try {
            parent::View();
        }
        catch(\Exception $ex){
            ob_clean();
            igk_wln("ERROR : ".$ex->getMessage());
            igk_exit();
        }
        $this->setEnvParam(IGK_CTRL_VIEW_CONTEXT_PARAM_KEY, null);
    }
}