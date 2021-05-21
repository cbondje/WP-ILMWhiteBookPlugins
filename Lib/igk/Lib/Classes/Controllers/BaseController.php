<?php


namespace IGK\Controllers;

use Exception;
use IGKApp; 
use IGKFv;
use IGKHtmlSingleNodeViewer;
use IGKLoader;
use IIGKController;
use IIGKDataController;
use IIGKWebController;
use ReflectionClass;
use IGKIO;
use IGKPageView;
use IGKEvents;
use IGK\System\Http\Request;
use IGK\Resources\R as R;
use IGK\System\Configuration\ControllerConfigData;
use IGKAppModule;
use IGKControllerManagerObject;
use IGKDbColumnInfo;
use IGKEnvKeys;
use IGKResourceNotFoundException;
use IGKServerInfo;
use IGKString;

///<summary>Framework base Controller implementation</summary>
/**
* Framework base Controller implementation
*/
abstract class BaseController extends RootControllerBase implements IIGKController, IIGKWebController, IIGKDataController {
    const CHILDS_FLAG=5;
    const CURRENT_VIEW= IGK_CURRENT_CTRL_VIEW;
    const ENV_PARAM_USER_SETTINGS=0x200;
    const IGK_ENV_PARAM_LANGCHANGE_KEY="langchanged";
    const MAIN_VIEW=9;
    const PAGE_VIEW_FLAG=4;
    const PARAMS_FLAG=7;
    const REG_VIEW_CHILD=11;
    const SHOW_CHILD=10;
    const VIEWCHILDS_FLAG=6;
    const VISIBILITY_FLAG=2;
    const WEBPARENT_FLAG=1;
    private static $sm_sysController;
    /** @var array */
    static $sm_regComplete;

    

    ///<summary>.ctr BaseController</summary>
    /**
    * .ctr BaseController
    */
    function __construct(){
        if(IGKApp::$INITENV == 1){
            igk_wln("[IGK] Notice : construct controller on initializing environment not allowed. App::INITENV ".get_class($this));
            return;
        }
    }
    protected function getActionHandler($name, $params=null){
        if (($name!= IGK_DEFAULT_VIEW) && preg_match("/".IGK_DEFAULT_VIEW."$/",$name)){
            $name = rtrim(substr($name,0, -strlen(IGK_DEFAULT_VIEW)), "/");
        }
        $ns = $this->getEntryNameSpace();
        $c = [];
        $t = [];
        if (!empty($ns)){
            $c[] = $ns;
        }
        $c[] = "Actions\\".ucfirst($name)."Action";
        $t[] = implode("\\", $c);
         
        if ($name != IGK_DEFAULT_VIEW){
            $t[] = implode("\\",array_filter(array_merge([$ns], ["Actions\\".ucfirst(IGK_DEFAULT_VIEW)."Action"])));
        }  
        while($cl = array_shift($t)){
            if (class_exists($cl)){
                return $cl;
            }
        } 
        return null;
    }

    ///<summary>get the current controller entry - view behaviour</summary>
    /**
     * get the current controller entry - view behaviour
     */
    protected function getCtrl(){
        return $this;
    }
    ///<summary>get the data schema filename</summary>
    /**
     * get the data schema filename
     */
    public function getDataSchemaFile(){
        return $this->getDataDir()."/".IGK_SCHEMA_FILENAME;
    }
   
    // public static function Invoke($controller, $methodname, ...$args){
    //    return call_user_func_array([$controller, $methodname], $args);
    // }

     ///<summary> registered entry namespace . for auto load class </summary>
    /**
    *  registered entry namespace . for auto load class
    */
    protected function getEntryNameSpace(){
        $ns = dirname(igk_io_dir(get_class($this)));
        if ($ns != "."){
            return str_replace("/", "\\", $ns);
        }
        return null;
    } 
    ///delete entries in database
    ///@adapt : adapter
    ///@entries : array or object of entries to delete
    /**
    */
    private function __dbdelete($adapt, $entries){
        if($entries == null)
            return null;
        if(is_array($entries)){
            if(igk_count($entries) > 0){
                foreach($entries as  $v){
                    $this->__dbdelete($adapt, $v);
                }
            }
            return null;
        }
        return $adapt->delete($this->DataTableName, $entries);
    }
    ///<summary></summary>
    ///<param name="adapt"></param>
    ///<param name="entry"></param>
    /**
    * 
    * @param mixed $adapt
    * @param mixed $entry
    */
    private function __dbinsert($adapt, $entry){
        return $adapt->insert($this->getDataTableName(), $entry);
    }
    ///<summary></summary>
    ///<param name="adapt"></param>
    ///<param name="properties"></param>
    /**
    * 
    * @param mixed $adapt
    * @param mixed $properties
    */
    private function __dbselect_andwhere($adapt, $properties){
        return $adapt->selectAndWhere($this->getDataTableName(), $properties);
    }
    ///<summary></summary>
    ///<param name="adapt"></param>
    /**
    * 
    * @param mixed $adapt
    */
    private function __dbselectAll($adapt){
        return $adapt->selectAll($this->getDataTableName());
    }
    ///<summary></summary>
    ///<param name="adapt"></param>
    /**
    * 
    * @param mixed $adapt
    */
    private function __dbselectlastid($adapt){
        return $adapt->last_id();
    }
    ///<summary></summary>
    ///<param name="adapt"></param>
    ///<param name="entries" default="null"></param>
    /**
    * 
    * @param mixed $adapt
    * @param mixed $entries the default value is null
    */
    private function __dbupdate_entries($adapt, $entries=null){
        if($entries == null)
            return false;
        if(is_array($entries)){
            foreach($entries as $v){
                $this->__dbupdate_entries($adapt, $v);
            }
            return true;
        }
        return $adapt->update($this->DataTableName, $entries);
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    ///<param name="tablename"></param>
    ///<param name="etb"></param>
    ///<param name="db"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $tablename
    * @param mixed $etb
    * @param mixed $db
    */
    protected function __init_entries($ctrl, $tablename, $etb, $db){
        if($db->initSystableRequired($tablename)){
            $e=$this;
            $db->initSystablePushInitItem($tablename, function() use ($e, $ctrl, $tablename, $etb, $db){$e->__init_entries($ctrl, $tablename, $etb, $db);
            });
        }
        else{
            if(($etb != null) && array_key_exists($tablename, $etb)){
                foreach($etb[$tablename] as $e=>$ee){
                    if(!$db->insert($tablename, (object)$ee)){
                        igk_ilog("insert failed ".$db->getError());
                        return false;
                    }
                }
            }
        
            $ctrl->initDataEntry($db, $tablename);
        
        }
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function __loadCtrlConfig(){
        $t=igk_sys_getdefaultctrlconf();
        if(method_exists(get_class($this), "GetAdditionalConfigInfo")){
            $s=get_class($this);
            $c=call_user_func(array($s, "GetAdditionalConfigInfo"));
            if(is_array($c)){
                foreach($c as $k=>$v){
                    if(is_object($v)){
                        $t[$k]=null;
                    }
                    else if(is_string($v) && isset($t[$v])){
                        $t[$v]=null;
                    }
                }
            }
        }
        return (object)$t;
    }
    ///<summary>set up controller config</summary>
    ///<summary>to string</summary>
    /**
    * set up controller config
    * to string
    */
    function __toString(){
        return "IGKCONTROLLER::".get_class($this);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __wakeup(){
        parent::__wakeup();
        $this->_initialize();
    }
    ///<summary>load and init from configuration setting</summary>
    /**
    * load and init from configuration setting
    */
    protected function _conf_regToParent(){
        $h=null;
        $p=trim($this->getConfigs()->clParentCtrl);
        $p && ($h=igk_getctrl($p, false));
        if($h){
            if($this->WebParentCtrl != null){
                $this->WebParentCtrl->unregChildController($this);
            }
            if($h->CanAddChild){
                $h->regChildController($this);
            }
            else{
                $this->Configs->clParentCtrl=null;
            }
        }
    }
    ///<summary></summary>
    ///<param name="file"></param>
    /**
    * 
    * @param mixed $file
    */
    protected function _get_extra_args($file){
        $data=[];
        if(igk_is_included_view($file)){
            $tab=igk_get_env(IGKEnvKeys::CTRL_CONTEXT_SOURCE_VIEW_ARGS);
            $data["source_args"]=$tab[spl_object_hash($this)];
        }
        return $data;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function _include_constants(){
        if(($f=$this->getConstantFile()) && file_exists($f))
            include_once($f);
        if(($f=$this->getDbConstantFile()) && file_exists($f))
            include_once($f);
        unset($f);
    }
    ///<summary>copy this fonction to allow file inclusion on the current context controller</summayr>
    /**
    * copy this fonction to allow file inclusion on the current context controller
    */
    protected function _include_file_on_context($file){
        $this->_include_constants();
        igk_reset_globalvars();
        $fname=igk_io_getviewname($file, $this->getViewDir());
        $rname=igk_io_view_root_entry_uri($this, $fname);// ."/".$fname;

        extract($this->utilityViewArgs($fname, $file));
        extract($this->getSystemVars());  
        $this->setEnvParam("fulluri", $furi);
        $params=isset($params) ? $params: array();
 
        $query_options=$this->getEnvParam(IGK_VIEW_OPTIONS);
        $is_direntry=(count($params) == 0) && igk_str_endwith(explode('?', igk_io_request_uri())[0], '/');
        $this->bindNodeClass($t, $fname, strtolower((isset($css_def) ? " ".$css_def: null)));
       
        $doc->body["class"]="-custom-thumbnail";

        try {
            $viewargs=get_defined_vars();
            igk_set_env(IGKEnvKeys::CURRENT_CTRL, $this);
            igk_set_env(IGKEnvKeys::CTRL_CONTEXT_VIEW_ARGS, $viewargs); 
            
        

            extract($this->_get_extra_args($file));

            //+ | ----------------------------------------------------------------
            //+ | insert here a middle ware to auto handle the view before include 
            //+ | ----------------------------------------------------------------
            if ((igk_count($params)>0) && ($handler = $this->getActionHandler($fname, $params[0]))){                                
                $handler::Handle($this, $fname, $params);       
            } 
            ob_start();
            $bckdir = set_include_path(dirname($file).PATH_SEPARATOR.get_include_path());
            igk_environment()->viewfile = 1;
            include($file);
            igk_environment()->viewfile = 0;
            set_include_path($bckdir);
            $out=ob_get_contents();
            ob_end_clean();
            if(!empty($out)){
                $t->addSingleNodeViewer(IGK_HTML_NOTAG_ELEMENT)->Content=$out;
            }
        }
        catch(\Exception $ex){
            if (!($code = $ex->getCode())){
                $code = 500;
            }
            igk_set_header($code);
            igk_show_exception($ex);
            igk_exit();
        }
    }
    ///<summary>include view on contex</summary>
    /**
    * include view on contex
    */
    protected function _include_view_file($view, $args=null){
        $v_file=file_exists($view) ? $view: $this->getViewFile($view);
        if(file_exists($v_file) === true){
            $d=null;
            if($args !== null){
                $d=$this->getSystemVars();
                $this->regSystemVars(null);
                $this->regSystemVars($args);
            }
            $this->_include_file_on_context($v_file);
            if($d)
                $this->regSystemVars($d);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function _initCssStyle(){
        igk_ctrl_bind_css_file($this);
    }
    ///<summary></summary>
    /**
    * 
    */
    private function _initialize(){
        if(method_exists($this, "initialize")){
            $this->initialize();
        }
    }
    ///<summary> call init view file </summary>
    /**
    *  call init view file
    */
    protected function _initPage(){
        $f=$this->getViewFile("_init");
        if(file_exists($f) && igk_io_basenamewithoutext($f) == "_init"){
            include($f);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function _initScripts(){
        $f1=igk_html_uri(dirname($this->getDeclaredFileName()));
        $f2=IGK_LIB_DIR;
        if(($f1 != $f2) && !igk_io_is_subdir($f2, $f1) && $f1){
            $doc = igk_app()->getDoc();
            igk_js_load_script($doc, igk_io_dir($f1."/".IGK_SCRIPT_FOLDER));
        }
    }
    ///<summary>Initialize view setting - before renderging </summary>
    /**
    * Initialize view setting - before renderging
    */
    protected function _initView(){
        R::RegLangCtrl($this);
        $this->_initCssStyle();
        $this->ShowChildFlag=true;
    }
    ///<summary>view complete.</summary>
    /**
    * view complete.
    */
    protected function _onViewComplete(){
        if((($x=$this->getFlag(self::REG_VIEW_CHILD)) != null) && is_array($x)){
            foreach($x as $v){
                $m=$v->func;
                $v->ctrl->Invoke($m, $this);
            }
        }
        igk_invoke_session_event(IGKEvents::VIEWCOMPLETE, array($this, null));
    }
    ///<summary>reset the current view file request</summary>
    protected function _resolview($f, $params){
        $view_dir=$this->getViewDir();
        $dfile=dirname($f);
        $qfile=$dfile;
        $find=0; 
        while(!$find && ($qfile != $view_dir)){
            $qfile=dirname($qfile);
            if(file_exists($s=$qfile."/".IGK_DEFAULT_VIEW_FILE)){
                $find=$s;
                $ln=strlen($view_dir) + 1;
                $v=dirname(substr($s, $ln));
                $p=array_merge(explode("/", igk_html_uri(substr($dfile, $ln + strlen($v) + 1))), $params);
                $this->setFlag(self::CURRENT_VIEW, $v);
                $options=$this->getEnvParam(IGK_VIEW_OPTIONS);
                $this->regSystemVars(null, null);
                $this->regSystemVars($p, $options);
            }
        }
        return $find;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function _renderViewFile(){ 
  
        $ctrl = $this;
        include(IGK_LIB_DIR."/".IGK_INC_FOLDER."/.extract_view_args.pinc");
     
        $f="";
        $v=$this->getCurrentView() ?? igk_die("current view is null. ". get_class($this));
        $c=strtolower(igk_getr("c", null));
        if($c == strtolower($this->getName())){
            $v=igk_getr("v", $v);
        } 
        $meth_exits = method_exists($this, $meth=$v);
        if(($meth_exits && $this->IsFuncUriAvailable($meth)) || (isset($params) && method_exists($this, $meth=IGK_DEFAULT_VIEW))){
            try {
                $params=isset($params) ? $params: [];
                $out = call_user_func_array(array($this, $meth), $params);
            }
            catch(Exception $ex){
                igk_html_output(500);
                igk_wln_if(igk_environment()->is("development"), "error : ", $ex->getMessage());
                igk_exit();
            }
            return;
        }
       
        if(!$meth_exits && !file_exists($f=igk_io_dir($this->getViewFile($v)))){
            //
           
            $find = $this->_resolview($f, $params);
           
            if(!$find){
                if(igk_is_conf_connected() && IGKServerInfo::IsLocal()){
                    if(!igk_io_save_file_as_utf8($f, igk_get_defaultview_content($this), true)){
                        igk_ilog("can't create the file ".$f. " AT ".__LINE__);
                        igk_exit();
                    }
                }
                else{
                    $message=__("res.notfound_1", $f);
                    igk_html_output(404);
                    if(!igk_get_contents($this, 404, [$message, 404])){
                        if(!igk_sys_env_production()){
                            $m="[IGK] - can't get resource ".$f. " AT ".__LINE__. " ruri:".igk_io_request_uri();
                            $m .= igk_show_trace();
                            igk_wln_e("uri:".$v, $m);
                        }
                        throw new IGKResourceNotFoundException($message, $f);

                    }
                }
            }
            else{
                $f=$find;
            }
        }
		$vdir = $this->getViewDir();
        $tdir = igk_io_dir(implode("/", [$vdir, $v]));
        
        if((empty($f) && file_exists($f=igk_io_dir($this->getViewFile($v)))) || file_exists($f)){
            try {
			//+ bind view
                if ( empty(strstr($f, $tdir)) && ((dirname($f) == $vdir) || !is_dir($tdir)))
                {
                    if($v != IGK_DEFAULT_VIEW){  
                        if ( $params && ((count($params)>=1) && isset($params[0]) && ($params[0]!==$v))){
                            array_unshift($params, $v);
                            $this->regSystemVars(null, null);
                            $this->setEnvParam(IGK_VIEW_ARGS, $params);
                        }
                    } 
                } 
                $this->_include_file_on_context($f);
            }
            catch(Exception $ex){
                igk_html_output(404);
                igk_exit();
            }
        }
    }
    ///<summary></summary>
    ///<param name="targetNode" default="null"></param>
    /**
    * 
    * @param mixed $targetNode the default value is null
    */
    protected function _showChild($targetNode=null){
        $t=$targetNode == null ? $this->getTargetNode(): $targetNode;
        if($this->hasChild){
            foreach($this->getChilds() as  $v){
                $_ct=$v->getTargetNode();
                if($v->getIsVisible()){
                    igk_html_add($_ct, $t);
                    $v->View();
                }
                else{
                    igk_html_rm($_ct);
                }
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function _unregisterEvents(){}
    ///<summary></summary>
    ///<param name="t"></param>
    ///<param name="fname"></param>
    ///<param name="css_def" default="null"></param>
    /**
    * 
    * @param mixed $t
    * @param mixed $fname
    * @param mixed $css_def the default value is null
    */
    protected function bindNodeClass($t, $fname, $css_def=null){
        igk_ctrl_init_css($this, $t, igk_css_str2class_name($fname).($css_def ? " ".$css_def: ""));
    }
    ///<summary>call function in context of the class</summary>
    /**
    * call function in context of the class
    */
    public function call_incontext($funcname, $params){
        if(get_called_class() == get_class($this)){
            return call_user_func_array(array($this, $funcname), $params);
        }
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function CurrentQueryString(){
        return igk_server()->QUERY_STRING;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function CurrentUri(){
        return igk_io_request_uri();
    }
    ///<summary></summary>
    ///<param name="entries"></param>
    /**
    * 
    * @param mixed $entries
    */
    public function dbdelete($entries){
        return $this->dbinvokeDbFunction(array($this, "__dbdelete"), $entries);
    }
    ///<summary></summary>
    ///<param name="entry"></param>
    /**
    * 
    * @param mixed $entry
    */
    public function dbinsert($entry){
        $v=$this->dbinvokeDbFunction(array($this, "__dbinsert"), $entry);
        return $v;
    }
    ///<summary></summary>
    ///<param name="param"></param>
    /**
    * 
    * @param mixed $param
    */
    public function dbinvokeDbFunction($param){
        $adapt=igk_get_data_adapter($this, true);
        if($adapt == null)
            return null;
        $d=null;
        if($adapt->connect()){
            try {
                $p=array($adapt);
                if(func_num_args() > 1)
                    $p=array_merge($p, array_slice(func_get_args(), 1));
                $d=call_user_func_array($param, $p);
            }
            catch(Exception $ex){
                $d=null;
            }
            $adapt->close();
        }
        return $d;
    }
    ///<summary>select equal property</summary>
    ///<param name="equal"></param>
    /**
    * select equal property
    * @param mixed $equal
    */
    public function dbselect($equals){
        return $this->getDbEntries()->searchEqual($equals);
    }
    ///<summary></summary>
    ///<param name="whereTab"></param>
    ///<param name="options"></param>
    /**
    * 
    * @param mixed $whereTab
    * @param mixed $options
    */
    public function dbselectAndWhere($whereTab, $options){
        $v=$this->dbinvokeDbFunction(array($this, "__dbselect_andwhere"), $whereTab, $options);
        return $v;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function dbselectLastId(){
        $v=$this->dbinvokeDbFunction(array($this, "__dbselectlastid"));
        return $v;
    }
    ///<summary></summary>
    ///<param name="entries" default="null"></param>
    /**
    * 
    * @param mixed $entries the default value is null
    */
    public function dbupdate($entries=null){
        return $this->dbinvokeDbFunction(array($this, "__dbupdate_entries"), $entries);
    }
    ///<summary></summary>
    ///<param name="obj"></param>
    ///<param name="method"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $obj
    * @param mixed $method
    * @param mixed $args the default value is
    */
    protected static function Dispatch($obj, $method, $args=array()){
        static $cl=null;
        if($cl == null){
            if(method_exists($obj, "Dispatcher"))
                $cl=\Closure::fromCallable(array($obj, "Dispatcher"));
            else{
                $cl=\Closure::fromCallable(array($obj, $method));
            }
        }
        return igk_dispatch_call($cl, $obj, $method, $args);
    }
    ///<summary>dispatch to controller method</summary>
    ///<note>create an implementation of this method to dispatch to child method</note>
    /**
    * dispatch to controller method
    */
    protected static function Dispatcher($obj, $method){
        return call_user_func_array(array($obj, $method), array_slice(func_get_args(), 2));
    }
    ///<summary>dropControllerMessage </summary>
    /**
    * dropControllerMessage
    */
    public function dropController(){
        $this->_unregisterEvents();
        igk_html_rm($this->TargetNode);
        if($this->hasChilds){
            foreach($this->getChilds() as  $v){
                $v->setWebParentCtrl(null, true);
                $v->View();
            }
            $this->m_->unsetFlag(self::CHILDS_FLAG);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function dropDbFromSchemas(){
        $r=$this->loadDataAndNewEntriesFromSchemas();
        if(!$r){
            igk_die(__FUNCTION__." >> not data init for : ". get_class($this)." ".$this->getDeclaredFileName());
            return;
        }
        $tb=$r->Data;
        $etb=$r->Entries;
        $db=igk_get_data_adapter($this, true);
        if($db){
            if($db->connect()){
                $tables=array();
                foreach($tb as $k=>$v){
                    $tables[]=igk_db_get_table_name($k);
                }
                $db->DropTable($tables);
                $db->close();
            }
            else{
                igk_wln("connexion failed ");
            }
        }
        else{
            igk_log_write_i(__FUNCTION__, "no adapter found");
        }
        return $tb;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAllArticles(){
        return IGKIO::GetFiles($this->getArticlesDir(), "/\.(template|html|phtml)$/i", false);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAllArticlesByCurrentLang(){
        $dir=$this->getArticlesDir();
        $t=igk_io_getfiles($dir);
        $lang_search=null;
        $lang_search=R::GetCurrentLang();
        $out=array();
        if($t && count($t) > 0){
            sort($t);
            foreach($t as $k){
                $n=basename($k);
                if($lang_search && !IGKString::EndWith(strtolower($n), igk_get_article_ext($lang_search)))
                    continue;
                if($this->m_search_article && !strstr(strtolower($n), strtolower($this->m_search_article)))
                    continue;
                $out[]=$k;
            }
        }
        return $out;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getApp(){
        return igk_app();
    }
    ///<summary>getfull uri</summary>
    /**
    * getfull uri
    */
    public function getAppUri($function=null){
        $app=igk_app();
        if($app->SubDomainCtrl === $this){
            $g=$app->SubDomainCtrlInfo->clView;
            if(!empty($function) && (stripos($g, $function) === 0)){
                $function=substr($function, strlen($g));
            }
        }
        if($function)
            return igk_str_rm_last(igk_io_baseuri(), '/')."/".$function;
        return igk_io_baseuri();
    }
    ///<summary></summary>
    ///<param name="name"></param>
    /**
    * 
    * @param mixed $name
    */
    public function getArticle($name){
        return $this->getArticleInDir($name, $this->getArticlesDir());
    }
    ///<summary>get the article binding content</summary>
    /**
    * get the article binding content
    */
    public function getArticleBindingContent($name, $entries, $prebuild=true){
        if(is_object($entries) && ($entries->RowCount > 0)){
            $d=igk_createnode("div");
            igk_html_binddata($this, $d, $name, $entries);
            return $d->Render();
        }
        return IGK_STR_EMPTY;
    }
    ///<summary>get the article binding content with name. of the target controller</summary>
    /**
    * get the article binding content with name. of the target controller
    */
    public function getArticleBindingContentW($name, $targetCtrlName){
        return $this->getArticleBindingContent($name, igk_db_select_all(igk_getctrl($targetCtrlName)));
    }
    ///<summary>get article content</summary>
    ///<param name="name" > name of the article</param>
    ///<param name="evalExpression">demand for eval expression .default is true</param>
    ///<param name="row">row used info to eval expression</param>
    /**
    * get article content
    * @param mixed $name  name of the article
    * @param mixed $evalExpression demand for eval expression .default is true
    * @param mixed $row row used info to eval expression
    */
    public function getArticleContent($name, $evalExpression=true, $row=null){
        if(file_exists($f=$name) || file_exists($f=$this->getArticle($name))){
            $out=IGK_STR_EMPTY;
            $out=igk_io_read_allfile($f);
            if($evalExpression){
                $out = igk_html_treat_content($out, $this, $row)->render();
                
            }
            return $out;
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="fullname"></param>
    /**
    * 
    * @param mixed $fullname
    */
    public function getArticleFull($fullname){
        return igk_io_dir($this->getArticlesDir()."/".$fullname);
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="dir"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $dir
    */
    public function getArticleInDir($name, $dir){
        return igk_io_get_article($name, $dir);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getArticlesDir(){
        return igk_io_dir($this->getDeclaredDir()."/".IGK_ARTICLES_FOLDER);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBaseUri(){
        return $this->getEnvParam("fulluri") ?? $this->getAppUri($this->currentView);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBody(){
        return igk_app()->Doc->Body;
    }
    ///<summary>get base full request uri</summary>
    /**
    * get base full request uri
    */
    final function getBUri($function){
        return igk_io_baseuri().$this->getUri($function);
    }
    ///<summary>get or set if this items can't add child</summary>
    /**
    * get or set if this items can't add child
    */
    public function getCanAddChild(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getcanDelete(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCanEditConfig(){
        return (IGKControllerManagerObject::IsSystemController($this) == false);
    }
    ///<summary> override to init data entries</summary>
    /**
    *  override to init data entries
    */
    public function getCanEditDataBase(){
        return $this->UseDataSchema;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCanEditDataTableInfo(){
        return !$this->UseDataSchema;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCanInitDb(){
        if(defined('IGK_DB_GRANT_CAN_INIT') || igk_is_cmd())
            return true;
        return igk_is_conf_connected();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getcanModify(){
        return true;
    }
    ///<summary>in order to speed up initialisation - disabling Can register on init by default</summary>
    /**
    * in order to speed up initialisation - disabling Can register on init by default
    */
    protected function getCanRegisterOnInit(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getChilds(){
        return $this->getFlag(self::CHILDS_FLAG);
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function getConfigFile(){
        return igk_io_dir($this->getDataDir()."/".IGK_CTRL_CONF_FILE);
    }
    ///<summary>get controlleur current configuration</summary>
    /**
    * get controlleur current configuration
    */
    public function getConfigs(){
        $c=igk_get_env_init(igk_ctrl_env_param_key($this)."/configs", function(){
            $config=new ControllerConfigData($this);
            $config->initConfigSetting($this->__loadCtrlConfig());
            return $config;
        });
        return $c;
    }
    ///<summary>get the constant file </summary>
    /**
    * get the constant file
    */
    public function getConstantFile(){
        return $this->getDeclaredDir()."/.constants.php.inc";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getContentDir(){
        return igk_io_dir($this->getDeclaredDir().DIRECTORY_SEPARATOR.IGK_CONTENT_FOLDER);
    }
    ///<summary>get controller config options</summary>
    /**
    * get controller config options
    */
    public function getControllerConfigOptions(){
        igk_die_m(__METHOD__);
    }
    ///<summary></summary>
    ///<param name="path"></param>
    /**
    * 
    * @param mixed $path
    */
    public function getCtrlFile($path){
        if(igk_realpath($path) == $path)
            return $path;
        return igk_io_dir(dirname($this->getDeclaredFileName()).DIRECTORY_SEPARATOR.$path);
    }
    ///<summary>get the controller current document</summary>
    /**
    * get the controller current document
    */
    public function getCurrentDoc(){
        return igk_app()->Doc;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCurrentPage(){
        return $this->getApp()->CurrentPage;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCurrentPageFolder(){
        return $this->getApp()->CurrentPageFolder;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCurrentView(){
        return $this->getFlag(self::CURRENT_VIEW, IGK_DEFAULT_VIEW);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDataAdapterName(){
        return igk_sys_getconfig("default_dataadapter", IGK_MYSQL_DATAADAPTER);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDataDir(){
        return $this->getDeclaredDir()."/".IGK_DATA_FOLDER;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDataTableInfo(){
        if($this->getUseDataSchema()){
            $tb= igk_getv($this->loadDataFromSchemas(), "tables");
            return $tb;
        }
        if(file_exists($this->getDBConfigFile())){
            $e=igk_createnode("__loadData");
            $e->Load(IGKIO::ReadAllText($this->DBConfigFile));
            $t=array();
            foreach($e->getElementsByTagName(IGK_COLUMN_TAGNAME) as $k){
                $t[]=new IGKDbColumnInfo($k->Attributes->ToArray());
            }
            return $t;
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDataTableName(){
        if(file_exists($this->getDBConfigFile())){
            $e=igk_createnode("__loadData");
            $e->Load(IGKIO::ReadAllText($this->DBConfigFile));
            $t=igk_getv($e->getElementsByTagName(IGK_DATA_DEF_TAGNAME), 0);
            if($t){
                $s=$t["TableName"];
                if(!empty($s))
                    return $s;
            }
        }
        return igk_db_get_table_name("%prefix%".$this->getName(), $this);
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function getDBConfigFile(){
        return igk_io_dir($this->getDataDir()."/".IGK_CTRL_DBCONF_FILE);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDbConstantFile(){
        return $this->getDeclaredDir()."/.db.constants.php";
    }
    public function getSourceClassDir(){
        return $this->getDeclaredDir()."/Lib/Classes";
    }
    ///retrieve all controller db entries
    /**
    */
    public function getDbEntries(){
        return $this->dbinvokeDbFunction(array($this, "__dbselectAll"));
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDeclaredDir(){
        return dirname($this->getDeclaredFileName());
    }
    ///override this method to show the controller view.
    /**
    */
    public function getDeclaredFileName(){
        $h=new ReflectionClass($this);
        return $h->getFileName();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDisplayName(){
        return get_class($this);
    }
    ///<summary>get global document</summary>
    /**
    * get global document
    */
    public final function getDoc(){
        return $this->getEnvParam(IGK_CURRENT_DOC_PARAM_KEY) ?? igk_app()->Doc;
    }
   
    ///<summary>View Error</summary>
    ///<param name="ctrl"></param>
    ///<param name="code"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $code
    */
    public static function GetErrorView($ctrl, $code){
        if(!is_object($ctrl) || !is_subclass_of(get_class($ctrl), __CLASS__)){
            igk_die("controller not valid: ".get_class($ctrl). " # ". is_subclass_of(get_class($ctrl), __CLASS__));
        }
        return $ctrl->getErrorViewFile($code);
    }
    ///<summary></summary>
    ///<param name="code"></param>
    /**
    * 
    * @param mixed $code
    */
    protected function getErrorViewFile($code){
        $viewdir = $this->getViewDir();
        $f = $viewdir."/error/".$code.".phtml";
        if(!file_exists($f)){
            return $f;
        }
        return null;
    }


    ///<summary>get controller's exposed function list</summary>
    ///<return>exposed function list</return>
    /**
    * get controller's exposed function list
    */
    public function getExposedfunctions(){
        return array();
    }
    ///<summary></summary>
    ///<param name="f"></param>
    /**
    * 
    * @param mixed $f
    */
    protected final function getFile($f){
        return igk_io_dir($this->getDeclaredDir()."/".$f);
    }
    ///<summary>get the flag value</summary>
    /**
    * get the flag value
    */
    public function getFlag($code, $default=null){
        return $this->getM_()->getFlag($code, $default);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getFlagParams(){
        return $this->getFlag(self::PARAMS_FLAG);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function gethasChilds(){
        $g=$this->getFlag(self::CHILDS_FLAG);
        return $g && (is_array($g) && (count($g) > 0));
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getHeader(){
        return igk_app()->Doc->Header;
    }
    ///<summary>return the current inclusion directory</summary>
    /**
    * return the current inclusion directory
    */
    public function getIncDir(){
        return $this->getDeclaredDir()."/".IGK_INC_FOLDER;
    }
   
    ///<summary></summary>
    /**
    * 
    */
    public function getisAvailable(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsSystemController(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsVisible(){
        return $this->PageView->getIsVisible($this->CurrentPage);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getLoader(){
        $l=$this->getEnvParam("loader");
        if($l == null){
            $l=new IGKLoader($this, function (){
				return (object)["entryNS" =>$this->getEntryNamespace()];
			});
            $this->setEnvParam("loader", $l);
        }
        return $l;
    }
    ///<summary></summary>
    ///<return refout="true"></return>
    /**
    * 
    * @return *
    */
    protected function & getM_(){
        $classname=get_class($this);
        if(($r=IGKFv::Get($classname)) === null){
            $c= & igk_app()->session->getRegisteredControllerParams($classname);
            if($c !== null){
                $r=IGKFv::Create($classname, $c);
                return $r;
            }
            $tab=array();
            $r=IGKFv::Create($classname, $tab);
            //igk_app()->session->registerControllerParams($classname, $tab);
        }
        return $r;
    }
    ///<summary>get the main view </summary>
    /**
    * get the main view
    */
    public function getMainView(){
        return $this->getFlag(self::MAIN_VIEW, IGK_DEFAULT_VIEW);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getmsbox(){
        return igk_app()->getControllerManager()->msbox;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getName(){
        return strtolower(get_class($this));
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getPageView(){
        $g=$this->getFlag(self::PAGE_VIEW_FLAG) ?? (function(){
            $g=new IGKPageView();
            $g->register("default");
            return $g;
        })();
        return $g;
    }
    ///<summary>get store parameter</summary>
    ///<param name="register">register new value if non null</param>
    /**
    * get store parameter
    * @param mixed $registerregister new value if non null
    */
    public function & getParam($key, $default=null, $register=false){
        $m = $this->getM_();


        $c=& $m->getFlag($key, $default, $register);
        return $c;
        // $g=$this->getFlagParams();
        // $c=null;
        // if(isset($g[$key]))
            // $c=& $g[$key];
        // if($c === null){
            // if(is_callable($default)){
                // $c=$default();
                // if(is_callable($c))
                    // igk_die("/i\\ value not valid.  value return by a callable.", __METHOD__);
            // }
            // else{
                // $c=$default;
            // }
            // if($register && ($c !== null)){
                // $this->setParam($key, $c);
            // }
        // }
        // return $c;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getParamKeys(){
        return array_keys($this->getFlagParams());
    }
    ///<summary>get all controller's parameters</summary>
    /**
    * get all controller's parameters
    */
    public function getParams(){
        return $this->getFlagParams();
    }
    ///<summary>get style dir</summary>
    /**
    * get style dir
    */
    public function getPrimaryCssFile(){ 
        return igk_io_dir($this->getStylesDir()."/". igk_getv($this->getConfigs(), "PrimaryStyle", "default.pcss"));
    }
    ///<summary>hooks is temporary store callback for controller</summary>
    ///<summary>determine if this controller need to register to view mecanism</summary>
    /**
    * hooks is temporary store callback for controller
    * determine if this controller need to register to view mecanism
    */
    public function getRegisterToViewMecanism(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getResourcesDir(){
        return $this->getDataDir()."/".IGK_RES_FOLDER;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getScriptDir(){
        return $this->getDeclaredDir()."/".IGK_SCRIPT_FOLDER;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getShowChildFlag(){
        return $this->getFlag(self::SHOW_CHILD);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getStylesDir(){
        return $this->getDeclaredDir()."/".IGK_STYLE_FOLDER;
    }
    ///<summary>get system variables for this controller.</summary>
    /**
    * get system variables for this controller.
    */
    public function getSystemVars(){

        $ck=igk_ctrl_env_view_arg_key($this);
        $t=igk_get_env($ck);
        $c=$this->getEnvParam(IGK_VIEW_ARGS); 
        if($t !== null){
            return $t;
        }
        $t=array();
        $t["t"]=$this->getTargetNode();
        $t["ctrl"]=$this;
        if(isset($c["doc"])){
            $t["doc"]=$c["doc"];
        }
        else{
            $doc=$this->getEnvParam(IGK_CURRENT_DOC_PARAM_KEY);
            if(!$doc){
                $doc=$this->getApp()->Doc;
            }
            $t["doc"]=$doc;
        }
        if($viewctx=$this->getParam(IGK_CTRL_VIEW_CONTEXT_PARAM_KEY)){
            $t["viewcontext"]=$viewctx;
        }
        if(igk_count($_REQUEST) > 0)
            $t=array_merge($t, array("request"=>Request::getInstance()));
        $tab=$this->getApp()->getControllerManager()->getControllers();
        if(is_array($tab)){
            $t["igk_controllers"]=$tab;
        }
        if($this->getParam("func_get_args") != null){
            $t["func_get_args"]=$this->getParam("func_get_args");
        }
        if($c !== null){
             $t=array_merge($t, array("params"=>is_array($c) ? $c: array($c)));
        }
        igk_set_env($ck, $t);
        return $t;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function getTable($n){
        igk_die("not implements : ".__FUNCTION__);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTargetNode(){
        $b=$this->getEnvParam(IGK_CTRL_TG_NODE) ?? (function(){
            $g=$this->initTargetNode();
            $this->setEnvParam(IGK_CTRL_TG_NODE, $g);
            return $g;
        })();
        return $b;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTargetNodeId(){
        return $this->TargetNode["id"];
    }
    ///<summary></summary>
    ///<param name="function" default="null"></param>
    /**
    * 
    * @param mixed $function the default value is null
    */
    public function getUri($function=null){
        $out="?c=".strtolower($this->getName());
        if($function){
            $t=explode("&", $function);
            $t[0]="&f=".str_replace('_', '-', $t[0]);
            $out .= implode('&', $t);
        }
        return "./".$out;
    }
    ///<summary></summary>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $uri
    */
    public function getUril($uri){
        $out="?c=".strtolower($this->getName());
        if($uri)
            $out .= "&".$uri;
        return $out;
    }
    ///<summary></summary>
    ///<param name="page"></param>
    /**
    * 
    * @param mixed $page
    */
    public function getUriv($page){
        $out="?c=".strtolower($this->getName());
        if($page)
            $out .= "&v=".$page;
        return $out;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function getUseDataSchema(){
        return !self::IsSysController(get_class($this)) && igk_getv($this->getConfigs(), "clDataSchema");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getval(){
        return igk_app()->Validator;
    }
    ///<summary>call view layout without changing current view</summary>
    /**
    * call view layout without changing current view
    */
    public function getView($view=null, $forcecreation=false, $args=null, $options=null){
        extract($this->getSystemVars());
        $v=igk_io_dir($view != null ? $view: igk_getr("v", $view));
        $f=igk_realpath($v) === $v ? $v: $this->getViewFile($v);
        $this->regSystemVars(null);
        if(file_exists($f) || ($forcecreation && igk_io_save_file_as_utf8($f, IGK_STR_EMPTY))){
            $def=0;
            if(($args !== null) && !empty($args)){
                $def++;
            }
            if(($options != null) && !empty($options)){
                $def++;
            };
            if($def > 0)
                $this->regSystemVars($args, $options);
            $this->_initView();
            $this->_include_file_on_context($f);
            $this->regSystemVars(null);
        }
    }
    ///<summary></summary>
    ///<param name="view"></param>
    ///<param name="target"></param>
    ///<param name="forcecreation" default="false"></param>
    ///<param name="args" default="null"></param>
    /**
    * 
    * @param mixed $view
    * @param mixed $target
    * @param mixed $forcecreation the default value is false
    * @param mixed $args the default value is null
    */
    public function getViewContent($view, $target, $forcecreation=false, $args=null){
        $key="ctrl/backupnode";
        $g=$this->getParam($key);
        if($g){
            $this->TargetNode=$g;
        }
        $bck=$this->TargetNode;
        $this->setParam($key, $bck);
        $v_view=$this->CurrentView;
        $this->TargetNode=$target;
        $this->getView($view, $forcecreation, $args);
        $this->TargetNode=$bck;
        $this->resetCurrentView($v_view);
        $this->setParam($key, null);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getViewDir(){
        return igk_io_dir($this->getDeclaredDir()."/".IGK_VIEW_FOLDER);
    }
    ///<sample>editor[/package/function/arg1/args2]</sample>
    /**
    */
    public function getViewFile($view, $checkfile=1){
        $extension=IGK_DEFAULT_VIEW_EXT;
        if($e=igk_getv(array_slice(func_get_args(), 2), 0))
            $extension=$e;
        if(empty($view))
            $view=IGK_DEFAULT_VIEW;
        $f=igk_html_uri($this->getCtrlFile(IGK_VIEW_FOLDER."/".$view));
        $f=igk_str_rm_last($f, "/");
        $ext=$extension; 
        if(is_dir($f)){
				//window allow same file in folder
				if (file_exists($cf = $f."/".IGK_DEFAULT_VIEW_FILE)){
					$f = $cf;
				}else{
					$f = $f	.".".$extension;
                }
        }
        else{
            $ext=preg_match('/\.'.$ext. '$/i', $view) ? '': '.'.$ext;
            $f=$f.$ext;
            if(!empty($ext) && $checkfile){
                if(is_file($f)){
                    return $f;
                }
                else{
                    return dirname($f)."/".IGK_DEFAULT_VIEW.'.'.$extension;
                }
            }
        } 
        return $f;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getVisibility(){
        return $this->m_visibility;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getWebParentCtrl(){
        return $this->getM_()->getFlag(self::WEBPARENT_FLAG);
    }
    ///<summary> handle function</summary>
    ///<note>In ajx context (session ajx context or function name end with _ajx script will stop execution after function is called</note>
    /**
    *  handle function
    */
    protected final function handle_func($c, $param, $doc, $exit=1, $redirectUri=null){
        $h=0;
        if(method_exists($this, $c)){
            if($this->IsFuncUriAvailable($c)){
                $h=1;
                if($param == null)
                    $param=array();
                else if(is_array($param) == false)
                    $param=array($param);
                $this->_include_constants();
                $this->_initView();
                $this->register_autoload();
                $this->bindNodeClass($this->targetNode, $c);
                igk_hook("action_start", $this, $c);
                call_user_func_array(array($this, $c), $param);
                igk_hook("action_complete", $this, $c);
                if(igk_is_ajx_demand() || IGKString::EndWith($c, IGK_AJX_METHOD_SUFFIX)){
                    igk_exit();
                }
            }
            else{
                $msg=$c." function not available. ";
                if($exit && !$this->HandleError(5406)){
                    igk_sys_error(IGK_ERR_FUNCNOTAVAILABLE);
                }
                if($redirectUri){
                    igk_set_header(403);
                    igk_navto($redirectUri);
                }
                else{
                    $this->setEnvParam("header_status", 403);
                    $this->setEnvParam("header_msg", $msg);
                }
            }
            if($exit)
                igk_exit();
        }
        return $h;
    }
    ///<summary></summary>
    ///<param name="msg"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $msg
    * @param mixed $args
    */
    public function handleCmd($msg, $args){
        $f=igk_io_dir($this->getDeclaredDir()."/.msghandler.pinc");
        if(file_exists($f)){
            $fc=array();
            $ctrl = $this;
            include($f);
            $b=igk_getv($fc, $msg);
            if(is_callable($b)){
                $g=$args;
                if(func_num_args() > 2){
                    $g=array_merge(is_array($g) ? $g: array($g), array_slice(func_get_args(), 2));
                }
                $c=call_user_func_array($b, is_array($g) ? $g: array($g));
                return;
            }
        }
        else{
            $fc="handle_{$msg}";
            if(method_exists($this, $fc)){
                $c=call_user_func_array(array($this, $fc), is_array($args) ? $args: array($args));
                return;
            }
            else{
                igk_ilog(get_class($this)." no function define to handle command");
            }
        }
        igk_die("message {$msg} not implement");
    }
    ///<summary> override to init controller according to other created controller.</summary>
    /**
    *  override to init controller according to other created controller.
    */
    protected function InitComplete(){
        $this->_initPage();
        $this->_initScripts();
        if($this->getConfigs() != null){
            $this->_conf_regToParent();
        }
        else{
            igk_debug_wln("error ". $this->getName());
        }
        $p=$this->getPageView();
        if($p == null){
            igk_die("ERROR: no pageview defined for ".$this." your class probably don't call the base construct");
        }
        $p->registerPages();
        $this->registerHook();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initConfigMenu(){
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected static function initDb(){ 
        self::__callStatic(__FUNCTION__, []); 
    }
    // protected static function dropDb(){ 
    //     // self::__callStatic(__FUNCTION__, func_get_args());
    // }
    ///<summary>init database constant file</summary>
    /**
    * init database constant file
    */
    protected function initDbConstantFiles(){
        $f=$this->getDbConstantFile();
        $tb=$this->getDataTableInfo();
        

        $s="<?php".IGK_LF;
        $s .= "// Balafon : generated db constants file".IGK_LF;
        $s .= "// date: ".date("Y-m-d H:i:s").IGK_LF;
        // generate class constants definition
        $cl = igk_html_uri(get_class($this));
        $ns = dirname($cl);
        
        if (!empty($ns) && ($ns !=".")){
            $s .= "namespace ".str_replace("/","\\", $ns)."; ".IGK_LF;
        } 
		$s.= "abstract class ".basename($cl)."DbConstants{".IGK_LF;
		   if($tb != null){
			   ksort($tb);
               $prefix = igk_db_get_table_name("%prefix%", $this); 
			   foreach($tb as $k=>$v){
				   $n=strtoupper($k);
					$n=preg_replace_callback("/^%prefix%/i", function(){
						return IGK_DB_PREFIX_TABLE_NAME;
					}
					, $n);
                    if ($prefix){
                        $n = preg_replace("/^".$prefix."/i",  "TB_", $n);
                    }
                    if (empty($n)){ 
                        continue;
                    }
				   $s .= "\tconst ".$n." = \"".$k."\";".IGK_LF; 
			   }
		   }
		$s.="}".IGK_LF;

		igk_io_w2file($f, $s, true);
		include_once($f);		 
    }
    
     
    ///<summary> initialize db from data schemas </summary>
    /**
    *  initialize db from data schemas
    */
    protected function initDbFromSchemas(){
   
        $r=$this->loadDataAndNewEntriesFromSchemas();
        if(!$r)
            return; 
        $tb=$r->Data; 
        $db=igk_get_data_adapter($this, true);
        if($db){
            if($db->connect()){ 
				igk_db_init_dataschema($this, $r, $db); 
                $db->close();
            }
            else{
                igk_ilog("/!\\ connexion failed ");
            }
        }
        else{
            igk_log_write_i(__FUNCTION__, "no adapter found");
        }
        return $tb;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initMenu(){
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function initTargetNode(){
        $tagName=igk_sys_getconfig("app_default_controller_tag_name", "div");
        $div=igk_createnode($tagName);
        $div["id"]=igk_css_str2class_name(strtolower($this->Name));
        $div["igk-type"]="controller";
        return $div;
    }
    ///<summary></summary>
    ///<param name="form"></param>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $form
    * @param mixed $uri
    */
    public function initUriPost($form, $uri){
        $uri=$this->getUri($uri);
        $form->addInput("c", "hidden", strtolower($this->getName()));
        $tab=igk_getquery_args($uri);
        foreach($tab as $k=>$v){
            $form->addInput($k, "hidden", $v);
        }
    }
    ///<summary></summary>
    ///<param name="m"></param>
    /**
    * 
    * @param mixed $m
    */
    public function invokeInContext($m){
        include($this->getIncDir().'/common.phinc');
        if(function_exists($m)){
            $g=func_get_args();
            $g=array_slice($g, 1);
            call_user_func_array($m, igk_getv($g, 0));
        }
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    protected static function InvokeInitCompleteOn($ctrl){
        if($ctrl != null)
            $ctrl->InitComplete();
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function InvokeRegisterComplete(){
        if(self::$sm_regComplete){
            foreach(self::$sm_regComplete as  $v){
                $v->InitComplete();
            }
        }
        self::$sm_regComplete=null;
    }
    ///<summary>check that the controller can't be uses as entry controller</summary>
    ///<param name="ctrl">controller to check</param>
    /**
    * check that the controller can't be uses as entry controller
    * @param mixed $ctrl controller to check
    */
    public static function IsEntryController($ctrl){
        return (igk_app()->SubDomainCtrl === $ctrl) || (igk_get_defaultwebpagectrl() === $ctrl);
    }
    ///<summary>get if this function is available in output query context</summary>
    /**
    * get if this function is available in output query context
    */
    public function IsFunctionExposed($function){
        if(method_exists(get_class($this), $function))
            return true;
        return false;
    }
    ///<summary>check if this controller class is a system controller</summary>
    ///<param name="mixed">object|class name of a controller</summary>
    /**
    * check if this controller class is a system controller
    * @param mixed object|class name of a controller
    */
    public static function IsSysController($className){
        if(is_object($className)){
            $f=igk_html_uri($className->getDeclaredFileName());
            if(strstr($f, IGK_LIB_DIR)){
                return true;
            }
            return false;
        }
        return (igk_getv(self::$sm_sysController, $className) != null);
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function loadDataAndNewEntriesFromSchemas(){
        return igk_db_load_data_and_entries_schemas(igk_db_get_schema_filename($this), $this);
    }
    ///<summary>load data base from data schemas file</summary>
    /**
    * load data base from data schemas file
    */
    protected function loadDataFromSchemas(){
        return igk_db_load_data_schemas(igk_db_get_schema_filename($this), $this);
    }
    ///<summary>load data new entries from schemas file</summary>
    /**
    * load data new entries from schemas file
    */
    protected function loadDataNewEntriesFromSchemas(){
        return igk_db_load_data_entries_schemas(igk_db_get_schema_filename($this), $this);
    }
    ///<summary></summary>
    ///<param name="viewpath"></param>
    ///<param name="args" default="null"></param>
    /**
    * 
    * @param mixed $viewpath
    * @param mixed $args the default value is null
    */
    protected function loadview($viewpath, $args=null){
        $t=$this->targetNode;
        if($t)
            $t->addCtrlView($viewpath, $this, $args);
    }
    ///<summary> override this when page change on a spécific page</summary>
    /**
    *  override this when page change on a spécific page
    */
    protected function pageChanged(){}
    ///<summary> override to manage the view when page folder changed.</summary>
    /**
    *  override to manage the view when page folder changed.
    */
    protected function pageFolderChanged(){
        if(($this->getWebParentCtrl() == null) && !igk_own_view_ctrl($this) && $this->getIsVisible()){
            $this->View();
        }
    }
    ///<summary>register childs controllers</summary>
    /**
    * register childs controllers
    */
    public function regChildController($controller){
        if(!$this->CanAddChild)
            return;
        if($controller == null)
            return;
        if($controller->WebParentCtrl === $this)
            return;
        if($controller == $this)
            return;
        $n=strtolower($controller->getName());
        $g=$this->getFlag(self::CHILDS_FLAG);
        if($g && !isset($g[$n])){
            $g[$n]=$controller;
            $controller->setWebParentCtrl($this);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function registerHook(){
        igk_reg_hook(IGKEvents::HOOK_SCRIPTS, function(){
            $this->_initScripts();
        });
    }
    ///<summary>RegisterInitComplete . if Ctrl is not null add it to base controller list</summary>
    ///<param name="ctrl">if null return the count number of the registrated controller. else register the controller to iniList</param>
    /**
    * RegisterInitComplete . if Ctrl is not null add it to base controller list
    * @param mixed $ctrl if null return the count number of the registrated controller. else register the controller to iniList
    */
    public static function RegisterInitComplete($ctrl=null){
        if(self::$sm_regComplete === null)
            self::$sm_regComplete=array();
        if(($ctrl !== null) && ($ctrl->getCanRegisterOnInit())){
            self::$sm_regComplete[]=$ctrl;
        }
        return igk_count(self::$sm_regComplete);
    }
    ///<summary></summary>
    ///<param name="className"></param>
    /**
    * 
    * @param mixed $className
    */
    public static function RegSysController($className){
        if(self::$sm_sysController == null)
            self::$sm_sysController=array();
        if(class_exists($className)){
            self::$sm_sysController[$className]=$className;
        }
    }
    ///<summary>Register controller view $params var</summary>
    ///<param name="args">Mixed, single value or array . if single value it will be converted into an array of single array element</param>
    ///<param name="options">query options</param>
    ///<note>passing null will reset the system vars</note>
    /**
    * Register controller view $params var
    * @param mixed $args Mixed, single value or array . if single value it will be converted into an array of single array element
    * @param mixed $options query options
    */
    public function regSystemVars($args=null, $options=null){
   
        if($args === null){
            $this->setEnvParam(IGK_VIEW_ARGS, null);
            igk_set_env(igk_ctrl_env_view_arg_key($this), null);
        }
        else{ 

            $g=$this->getEnvParam(IGK_VIEW_ARGS);
            if(is_array($args)){
                if(is_array($g)){
                    $args=array_merge($g, $args);
                }
            }
            $this->setEnvParam(IGK_VIEW_ARGS,  $args);
        }
        if(is_string($options) && !empty($options)){
            $options=igk_get_query_options($options);
        }
        $this->setEnvParam(IGK_VIEW_OPTIONS, $options);
    }
    ///<summary>register a controller with a view callback function</summary>
    /**
    * register a controller with a view callback function
    */
    public function regView($ctrl, $callback){
        if($ctrl === $this)
            return;
        $_c=$this->getFlag(self::REG_VIEW_CHILD);
        if($_c === null){
            $_c=array();
        }
        $_c[$ctrl->Name]=(object)array("ctrl"=>$ctrl, "func"=>$callback);
        $this->setFlag(self::REG_VIEW_CHILD, $_c);
    }
    ///used to reload a view in ajx context
    /**
    */
    public function reload_view_ajx(){
        if(igk_is_ajx_demand() == 1){
            $this->View();
            $c=igk_createnode("script");
            $c->Content="if(IGK) window.igk.ajx.post( '".$this->getUri("view_ajx")."',null,  new window.igk.ajx.responseNode('".$this->getTargetNodeId()."').response )";
            $m=new IGKHtmlSingleNodeViewer($c);
            $m->RenderAJX();
        }
    }
    ///<summary>reset the value of the current view</summary>
    /**
    * reset the value of the current view
    */
    protected function resetCurrentView($view=null){
        $this->setFlag(self::CURRENT_VIEW, $view);
    }
    ///<summary>reset the parameters</summary>
    /**
    * reset the parameters
    */
    protected function resetParam(){
        $this->m_->unsetFlag(self::PARAMS_FLAG);
    }
    ///<summary>used to run crons script on server</summary>
    /**
    * used to run crons script on server
    */
    public static function RunCrons($ctrl){
        $f=igk_io_getfiles($ctrl->getDeclaredDir()."/Crons", "/\.phtml$/");
        foreach($f as $v){
            $ctrl->_include_file_on_context($v);
        }
    }
    ///<summary>set the current view</summary>
    ///<param name="options">extra option to pass to view</param>
    /**
    * set the current view
    * @param mixed $options extra option to pass to view
    */
    public function setCurrentView($view, $reload=true, $targetNode=null, $args=null, $options=null){
        // igk_trace();
        // igk_wln("change view: ".$view);
        $cview=$this->getCurrentView();
        if($cview != $view){
            $this->setFlag(self::CURRENT_VIEW, $view);
        }
        if($reload){
            $t=$this->getTargetNode();
            $bck=$targetNode && ($targetNode !== $t) ? $t: null;
            if($bck)
                $this->TargetNode=$targetNode;
            $this->regSystemVars($args, $options);
            $this->View();
            if($bck)
                $this->TargetNode=$bck;
        }
    }
    
    ///<summary>set the flag</summary>
    /**
    * set the flag
    */
    public function setFlag($code, $value){
        $this->getM_()->setFlag($code, $value);
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    protected function setMainView($v){
        $this->setFlag(self::MAIN_VIEW, $v);
    }
    ///<summary>set the controller parameters</summary>
    /**
    * set the controller parameters
    */
    public function setParam($key, $value){

        $m=$this->getM_();
        $m->setFlag($key, $value);

        return $this;
        // $p=$this->getFlagParams();
        // if($p == null){
            // $p=array();
        // }
        // $p[$key]=$value;
        // $this->updateFlagParams($p);
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setShowChildFlag($v){
        $this->setFlag(self::SHOW_CHILD, $v);
    }
    ///<summary></summary>
    ///<param name="node"></param>
    /**
    * 
    * @param mixed $node
    */
    protected function setTargetNode($node){
        $this->setEnvParam(IGK_CTRL_TG_NODE, $node);
    }
    ///<summary>this method will be call by system manager to configure requirement need by a specific controller</summary>
    /**
    * this method will be call by system manager to configure requirement need by a specific controller
    */
    protected function SetupCtrl($param){
        if($this->getDeclaredFileName() == __FILE__)
            return;
        if(IGK_LIB_DIR != $this->getDeclaredDir()){
            $f=$this->getDeclaredDir()."/.requirement.phtml";
            if(!isset($param["Requirements"])){
                $param["Requirements"]=array();
            }
            if(!isset($param["Requirements"][$f])){
                $libFile=__FILE__;
                $bDir=igk_realpath(IGK_APP_DIR);
                $ads=igk_getv($param, "AddRequires");
                igk_io_save_file_as_utf8($f, <<<EOF
<?php
//file: .requirement.phtml
//Description: This files is generated by balafon framework and its contains may not be edited directly.
//it purpose is to be included in direct access stand alone script
@require_once("{$libFile}");
define('IGK_APP_DIR', igk_realpath("{$bDir}");
if (!IGKSysCache::LoadCacheLibFiles())
{
	igk_exit();
}
//
//addition controller requirement
//
{$ads}
?>
EOF
                , true);
                $param["Requirements"][$f]=1;
            }
        }
        $f=$this->getDeclaredDir()."/.setup.phtml";
        if(file_exists($f)){
            include($f);
        }
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $args
    */
    public function setViewArgs($name, $args){
        $args=$this->getEnvParam(IGK_VIEW_ARGS);
        if(!$args){
            $args=array();
        }
        $args[$name]=$args;
        $this->setEnvParam(IGK_VIEW_ARGS, array_merge("base", $args));
    }
    ///<summary></summary>
    ///<param name="visibility"></param>
    /**
    * 
    * @param mixed $visibility
    */
    protected function setVisibility($visibility){
        $this->m_visibility=$visibility;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    ///<param name="store" default="false"></param>
    /**
    * 
    * @param mixed $value
    * @param mixed $store the default value is false
    */
    public function setWebParentCtrl($value, $store=false){
        $g=$this->m_->getFlag(self::WEBPARENT_FLAG);
        if($g != $value){
            if($g != null)
                $g->unregChildController($this);
            $g=$value;
            $this->Configs->clParentCtrl=$value ? $value->getName(): null;
            if($store)
                $this->storeConfigSettings();
            $this->m_->updateFlag(self::WEBPARENT_FLAG, $g);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function storeConfigSettings(){
        $d=igk_createxmlnode("config");
        $c=$this->getConfigs();
        foreach($c as $k=>$v){
            igk_conf_store_value($d, $k, $v);
        }
        $s=$d->Render();
        if(igk_io_save_file_as_utf8($this->ConfigFile, $s)){
            return true;
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="controller"></param>
    /**
    * 
    * @param mixed $controller
    */
    public function unregChildController($controller){
        if($controller == null)
            return;
        if($controller->WebParentCtrl === $this)
            return;
        if($controller == $this)
            return;
        $n=strtolower($controller->getName());
        $g=$this->getFlag(self::CHILDS_FLAG);
        if($g && isset($g[$n])){
            $p=$g[$n];
            $p->setWebParentCtrl(null, true);
            unset($g[$n]);
            $this->m_->updateFlag(self::CHILDS_FLAG, $g);
        }
        else{
            igk_debug_wln("unreg controller not removed");
        }
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    public static function UnRegisterInitComplete($ctrl){
        $t=array();
        foreach(self::$sm_regComplete as $v){
            if($v === $ctrl)
                continue;
            $t[]=$v;
        }
        self::$sm_regComplete=$t;
    }
    ///<summary>remove controller registered view</summary>
    /**
    * remove controller registered view
    */
    public function unregView($ctrl){
        if($ctrl != null){
            $_c=$this->getFlag(self::REG_VIEW_CHILD);
            if($_c){
                unset($_c[$ctrl->Name]);
            }
        }
    }
    ///<summary></summary>
    ///<param name="key"></param>
    /**
    * 
    * @param mixed $key
    */
    public function unsetParam($key){
        $this->m_->unsetFlag($key);
        ///TODO: Unset param

    }
    ///<summary>update the current data base</summary>
    /**
    * update the current data base
    */
    public function updateDb(){
        $s=igk_is_conf_connected() || igk_user()->auth($this->Name.":".__FUNCTION__);
        if(!$s){
            igk_ilog("not authorize to updateDb of " + $this->getName());
            igk_navto($this->getAppUri());
        }
        igk_db_update_ctrl_db($this);
        $uri=$this->getAppUri();
        igk_navto($uri);
    }
    ///<summary></summary>
    ///<param name="g"></param>
    /**
    * 
    * @param mixed $g
    */
    public function updateFlagParams($g){
        $this->m_->updateFlag(self::PARAMS_FLAG, $g);
    }
    ///<summary>utility view args</summary>
    /**
    * utility view args
    */
    protected function utilityViewArgs($fname, $file=null){
        $furi=$this->getAppUri($fname);
        $dir=dirname($file);
        $this->setCurrentView($fname, false);
        $cview=$this->getCurrentView();
        $entryuri=igk_io_view_entry_uri($this, $fname);
        return get_defined_vars();
    }
    ///<summary>override this method to show the controller view.</summary>
    /**
    * override this method to show the controller view.
    */
    public function View(){ 
        $t=igk_getv($this->getSystemVars(), "t"); 
        
        if($t){
            $this->ShowChildFlag=true;
            $this->_initView();
            $this->_renderViewFile();
        }
        else{
            igk_ilog("/!\\ TargetNode is null ".get_class($this));
        }
    }
    ///ask for a view in ajx context
    /**
    */
    public function view_ajx(){
        $v=igk_getr("v", "default");
        $this->CurrentView=$v;
        $this->TargetNode->RenderAJX();
    }
    ///<summary>render the target node in ajx context</summary>
    /**
    * render the target node in ajx context
    */
    public function ViewAJX(){
        $t=$this->getTargetNode();
        if($t != null){
            $t->RenderAJX();
        }
        igk_exit();
    }
    ///<summary> view file in context view of the controller</summary>
    /**
    *  view file in context view of the controller
    */
    public static function ViewInContext($ctrl, $file, $params=null){
        if(igk_realpath($file) || file_exists($file=$ctrl->getViewFile($file))){
            $bck=$ctrl->getSystemVars();
            if($params){
                $ctrl->regSystemVars($params);
                $key=igk_ctrl_env_view_arg_key($ctrl);
                $tparams=array_merge($bck, $params);
                igk_set_env($key, $tparams);
            }
            $ctrl->_include_file_on_context($file);
            $ctrl->regSystemVars($bck);
        }
    }
}


