<?php
namespace IGK\Resources;

use IGK\Controllers\BaseController;
use IGKObject;
use IGKAppContext;
use IGK\Resources\IGKLangKey;
use IGKControllerManagerObject;
use IGK\Resources\IGKLangResDictionary;
use IGKHtmlReader;

use function igk_resources_gets as __;


///<summary>IGKResource Class. represent resource / lang / resource management </summary>
/**
* IGKResource Class. represent resource / lang / resource management
*/
final class R extends IGKObject {
    const DEFAULT_LANG="fr";
    private $m_langChangedDate;
    private $m_langFiles;
    private $m_langctrl;
    private $m_langloaded;
    static $sm_instance;
    static $sm_keyVAR;
    var $KeysAdded;
    var $LangChangedEvent;
    var $PageLangChangedEvent;
    var $langRes;
    ///<summary></summary>
    /**
    * 
    */
    private function __construct(){}
    ///<summary>set the langage key</summary>
    /**
    * set the langage key
    */
    public static function AddLang($key, $value){
        if(!empty($key))
            self::getInstance()->langRes[strtolower(trim($key))]=$value;
    }
    ///<summary></summary>
    ///<param name="lang" default="fr"></param>
    /**
    * 
    * @param mixed $lang the default value is "fr"
    */
    public static function ChangeLang($lang="fr"){
        $app=igk_app();
        $v=self::getInstance();
        if(igk_get_env($key="flag:".__FUNCTION__))
            return;
        igk_set_env($key, 1);
        $v_lang=$app->session->lang;
        $v_langc=igk_getctrl(IGK_CSVLANGUAGE_CTRL);
        if($lang && !$v_langc->support($lang)){
            $lang=igk_app()->Configs->default_lang;
        }
        $r=false;
        if(($v_lang != $lang) && $v_langc->support($lang)){
            $app->session->lang=$lang;
            $v->m_langloaded=false;
            self::LoadLang();
            $v->onPageLangChangedEvent();
            $r=true;
        }
        igk_set_env($key, null);
        return $r;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function ClearLang(){
        $v=self::getInstance();
        $v->langRes=array();
        self::SaveLang(null);
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetCurrentLang(){
        $lg=igk_app()->session->lang ?? self::GetSupportLang();
        if(empty($lg)){
            igk_die("language is empty :::".self::GetSupportLang());
        }
        return $lg;
    }
    ///<summary> get the current language file</summary>
    /**
    *  get the current language file
    */
    public function GetCurrentLangPath($folder=null){
        if($folder == null)
            $folder=IGK_LIB_DIR."/Default/Lang";
        $lg=self::GetCurrentLang();
        return $folder."/".IGK_LANG_FILE_PREFIX.$lg.IGK_LANG_FILE_EXTENSION;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetDefaultLang(){
        $app=igk_app();
        if($lg=$app->Configs->default_lang){
            return $lg;
        }
        return self::DEFAULT_LANG;
    }
    ///<summary>get resource image uri</summary>
    /**
    * get resource image uri
    */
    public static function GetImgResUri($name){
        $v=igk_getctrl(IGK_PIC_RES_CTRL);
        if($v){
            return $v->getImgUri($name, true);
        }
        return IGK_STR_EMPTY;
    }
    ///<summary></summary>
    ///<param name="name"></param>
    /**
    * 
    * @param mixed $name
    */
    public static function GetImgUri($name){
        $v=igk_getctrl(IGK_PIC_RES_CTRL);
        if($v){
            return $v->getImgUri($name);
        }
        return IGK_STR_EMPTY;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function getInstance(){
        if(self::$sm_instance === null){
            $b=0;
            self::$sm_instance=new R();
            self::LoadLang();
        }
        return self::$sm_instance;
    }
    ///<summary></summary>
    ///<param name="key"></param>
    /**
    * 
    * @param mixed $key
    */
    public static function GetKeyValue($key){
        $v=self::getInstance();
        return igk_getv($v->langRes, $key);
    }
    ///<summary> get Language key</summary>
    /**
    *  get Language key
    */
    public static function GetLang($keys){
        return new IGKLangExpression($keys);
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetLangInfo(){
        return self::getInstance()->langRes;
    }
    ///<summary>get string expression </summary>
    /**
    * get string expression
    */
    public static function gets($key){
        if(igk_current_context() == IGKAppContext::initializing)
            return $key;
        $i=self::getInstance();
        if(is_array($key)){
            igk_trace();
            igk_wln_e(__METHOD__." Keys is array");
        }
        $t=strtolower($key);
        if(isset($i->langRes[$t])){
            $s=$i->langRes[$t];
            $match=array();
            $c=0;
            $c=preg_match_all("/\[(?P<value>[a-zA-Z0-9_\.]+)\]/i", $s, $match);
            if($c > 0){
                for($i=0; $i < $c; $i++){
                    $ckey=$match["value"][$i];
                    if(strtolower($ckey) == $t)
                        continue;
                    $s=str_replace($match[0][$i], __($ckey), $s);
                }
            }
            $key=$s;
        }
        else{
            if(!empty($key)){
                $i->langRes[$key]=$key;
                $i->OnKeyAdded($key);
            }
        }
        if(func_num_args() > 1){
            return call_user_func_array('igk_str_format', array_merge(array($key), array_slice(func_get_args(), 1)));
        }
        return $key;
    }
    ///<summary>prepare support lang</summary>
    /**
    * prepare support lang
    */
    private static function GetSupportLang(){
        $lang=IGKUserAgent::GetDefaultLang();
        if(!self::ChangeLang($lang)){
            igk_app()->session->lang=$lang;
        }
        return $lang;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetSupportLangRegex(){
        return igk_getctrl(IGK_CSVLANGUAGE_CTRL)->getLangRegex() ?? igk_app()->Configs->default_lang;
    }
    ///<summary></summary>
    /**
    * 
    */
    private function langscript(){
        $f=igk_io_basepath("Lib/Scripts/lang/".R::GetCurrentLang().".xml");
        if(empty($f))
            return;
        $s=<<<EOF
var lang = igk.R.getLang();
var h = igk_getdir(igk_get_script_src());
if (h){
var dir = "{$f}";

igk.file.getcontents(dir, function(data){
	var d = igk.createNode("dum");
	var ts = d.setHtml(data).getElementsByTagName("string");
	for(var i = 0;i < ts.length; i++){
		 keys[ts[i].getAttribute("name")] = ts[i].innerHTML;
	}
}, true);

}
EOF;
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    ///<param name="files" default="null"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $files the default value is null
    */
    private static function LoadCtrlLang($ctrl, $files=null){


        
        if (!($ctrl instanceof BaseController)){
            return;
        }
        $v= $_instance = self::getInstance();
        $gdir=$ctrl->configDir()."/Lang";
        $f=null;
        $tfile=array();
        if($files === null){
            $f=igk_io_dir($v->GetCurrentLangPath($gdir));
            $tfile[]=$f;
        }
        else{
            foreach($files as $c){
                $sfile=igk_io_dir($gdir."/".$c);
                if((!file_exists($sfile)) && !file_exists($sfile=self::GetCurrentLang().IGK_LANG_FILE_EXTENSION)){
                    continue;
                }
                $tfile []=$sfile;
            }
        }
        while($f=array_shift($tfile)){
            if(file_exists($f) && !isset($_instance->m_langFiles[$f])){
                $l=& $v->langRes;
                include($f);
                $v->langRes=$l;
                $_instance->m_langFiles[$f]=1;
            }
            else{
                $lang=igk_io_dir($gdir."/".R::GetCurrentLang().".xml");
                if(file_exists($lang)){
                    R::LoadLangFileXml($lang);
                    $_instance->m_langFiles[$f]=1;
                }
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function LoadLang(){
        $v=self::getInstance();
        if($v->m_langloaded){
            igk_ilog(" lang already loaded ", __METHOD__);
            return;
        }
        $v->langRes=new IGKLangResDictionary();
        $v->m_langloaded=false;
        $l=& $v->langRes;
        $f=$v->GetCurrentLangPath();
        if(file_exists($f)){
            include($f);
        }
        else{
            if(!igk_sys_env_production()){
                igk_ilog("Language file not found. -- [{$f}] -- ", __METHOD__);
            }
        }
        if($v->m_langctrl){
            foreach($v->m_langctrl as $k=>$vc){
                self::LoadCtrlLang($vc);
            }
        }
        $v->m_langloaded=true;
    }
    ///<summary></summary>
    ///<param name="file"></param>
    /**
    * 
    * @param mixed $file
    */
    public static function LoadLangFiles($file){
        if(file_exists($file)){
            $v=self::getInstance();
            $l=$v->langRes;
            include($file);
            $v->langRes=$l;
        }
    }
    ///<summary></summary>
    ///<param name="file"></param>
    ///<param name="override" default="true"></param>
    /**
    * 
    * @param mixed $file
    * @param mixed $override the default value is true
    */
    public static function LoadLangFileXml($file, $override=true){
        if(!file_exists($file))
            return;
        $t=IGKHtmlReader::LoadFile($file);
        $h=igk_getv($t->getElementsByTagName("resources"), 0);
        if($h){
            $g=self::getInstance();
            $tab=$h->getElementsByTagName("string");
            foreach($tab as  $v){
                $n=strtolower($v["name"]);
                $r=trim($v->innerHTML);
                if(!empty($n) && ($override || !isset($g->langRes[$n]))){
                    $g->langRes[$n]=$r;
                }
            }
        }
    }
    ///<summary>get new key string value from controller</summary>
    /**
    * get new key string value from controller
    */
    public static function ncgets($ctrl, $key){
        if(empty($key) || ($ctrl == null))
            return null;
        return self::ngets(strtolower($ctrl->Name.".".$key));
    }
    ///<summary>get new language expression</summary>
    /**
    * get new language expression
    */
    public static function ngets($key){
        if($key == null)
            return null;
        if(!is_string($key)){
            return $key;
        }
        if(self::$sm_keyVAR == null)
            self::$sm_keyVAR=array();
        $T=strtolower($key);
        $default=self::gets($T);
        $args=array();
        if(func_num_args() > 1){
            $t=func_get_args();
            for($i=1; $i < count($t); $i++){
                if(($i == 1) && is_array($t[$i])){
                    $args=array_merge($args, $t[$i]);
                }
                else
                    $args[]=$t[$i];
            }
            return new IGKLangKey($key, $default, $args);
        }
        else{
            if(isset(self::$sm_keyVAR[$T])){
                return self::$sm_keyVAR[$T];
            }
            else{
                self::$sm_keyVAR[$T]=new IGKLangKey($key, $default, $args);
                return self::$sm_keyVAR[$T];
            }
        }
    }
    ///<summary></summary>
    ///<param name="key"></param>
    /**
    * 
    * @param mixed $key
    */
    protected function OnKeyAdded($key){
        igk_hook("LangKeyAdded", func_get_args());
    }
    ///<summary></summary>
    ///<param name="key"></param>
    /**
    * 
    * @param mixed $key
    */
    protected function OnLangChangedEvent($key){
        igk_die(__METHOD__." Obselete");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function onPageLangChangedEvent(){
        if($this->PageLangChangedEvent != null){
            $this->PageLangChangedEvent->Call($this, null);
        }
    }
    ///<summary>register a language controller</summary>
    /**
    * register a language controller
    */
    public static function RegLangCtrl($ctrl){
        $_instance=self::getInstance();
        $v=self::getInstance();
        if($_instance->m_langctrl == null)
            $_instance->m_langctrl=array();
        if($_instance->m_langFiles == null)
            $_instance->m_langFiles=array();
        if($ctrl && !IGKControllerManagerObject::IsSystemController($ctrl) && !isset($_instance->sm_langctrl[$ctrl->Name])){
            $_instance->m_langctrl[$ctrl->Name]=$ctrl;
            if($v->m_langloaded){
                self::LoadCtrlLang($ctrl);
            }
        }
    }
    ///<summary></summary>
    ///<param name="name"></param>
    /**
    * 
    * @param mixed $name
    */
    public static function RemoveKey($name){
        $name=strtolower($name);
        $v=self::getInstance();
        if(isset($v->langRes[$name])){
            unset($v->langRes[$name]);
            return true;
        }
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function ResetLang(){
        $v=self::getInstance();
        $v->langRes=array();
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function SaveLang(){
        $instance=self::getInstance();
        $out="<?php \n//Balafon Generated language file ".IGK_LF;
        $tab=$instance->langRes;
        if($tab == null){
            self::LoadLang();
            $tab=& $instance->langRes;
            igk_ilog("reload lang resources");
        }
        $ktab=$tab->sortKeys();
        if($ktab){
            foreach($ktab as $k){
                $k=trim($k);
                $v=$tab[$k];
                $v=str_replace("\'", "'", str_replace("\"", "&quot;", $v));
                if(!empty($k)){
                    $out .= "\$l[\"".strtolower($k)."\"]=\"".$v."\";".IGK_LF;
                }
            }
        }
        $file=$instance->GetCurrentLangPath();
        if(igk_io_w2file($file, $out, true)){
            igk_sys_regchange("LangChanged", $v->m_langChangedDate);
            $instance->OnLangChangedEvent(null);
            return true;
        }
        else{
            igk_notify_error("can't save lang file");
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="filename" default="null"></param>
    /**
    * 
    * @param mixed $filename the default value is null
    */
    public static function SaveLangXml($filename=null){
        $q=self::getInstance();
        $out=igk_createnode("resources");
        $tab=$q->langRes;
        $ktab=array_keys($tab);
        igk_usort($ktab, "igk_key_sort");
        foreach($ktab as $k){
            $v=$tab[$k];
            $v=str_replace("\'", "'", str_replace("\"", "&quot;", $v));
            $v=str_replace("<", "&lt;", $v);
            $v=str_replace(">", "&gt;", $v);
            $v=preg_replace_callback("/([&](.){0,1})/i", function($m){
                if(trim($m[0]) == "&"){
                    return "&amp;";
                }
                return $m[0];
            }
            , $v);
            if(!empty($k))
                $out->add("string")->setAttribute("name", $k)->Content=$v;
        }
        $file=$filename == null ? igk_io_basedir(IGK_RES_FOLDER."/Lang/".self::GetCurrentLang().".xml"): $filename;
        if(igk_io_save_file_as_utf8($file, $out->Render(), true)){
            self::LoadLang();
            igk_sys_regchange("LangChanged", $q->m_langChangedDate);
            $q->OnLangChangedEvent(null);
            return true;
        }
        else{
            igk_notify_error("can't save lang file");
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    public static function UnRegLangCtrl($ctrl){
        $_instance=self::getInstance();
        if($_instance->m_langctrl == null)
            $_instance->m_langctrl=array();
        if(isset($_instance->m_langctrl[$ctrl->Name]))
            unset($_instance->m_langctrl[$ctrl->Name]);
    }
}