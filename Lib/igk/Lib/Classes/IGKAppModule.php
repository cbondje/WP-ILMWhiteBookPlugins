<?php
use IGK\Controllers\BaseController;

// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// Description: Use to add extra module to system. that module include function declared on .module.pinc file with the $reg array
 
///<summary>represent application module class </summary>
/**
* represent application module class
*/
final class IGKAppModule extends BaseController{
    private $m_dir;
    private $m_doc;
    private $m_fclist;
    private $m_listener;
    private $m_src;
    private $m_initializer; // used to extend modul class properties
    private $m_configs;
    public function config($name, $default=null){
        return igk_conf_get($this->m_configs, $name, $default, 1);
    }

    public function initClass($classname){
        if (class_exists($classname)){
            $this->m_initializer = new $classname();
        }
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $args
    */
    function __call($n, $args){
        $fc=igk_getv($this->m_fclist, $n);
        if($fc){
            igk_push_env(__CLASS__."/callee", $n);
            $o=call_user_func_array($fc, $args);
            $dc=igk_pop_env(__CLASS__."/callee");
            return $o;
        } 
        igk_die("/!\\ function {$n} not define");
        return null;
    }
    public function setEnvParam($name, $value){
        igk_set_env("module://".$this->name."/".$name, $value);
        //igk_wln_e($this->name);
        return $this;
    }
    ///<summary></summary>
    ///<param name="dir"></param>
    /**
    * 
    * @param mixed $dir
    */
    public function __construct($dir){
        parent::__construct();
        $this->m_dir=IGKIO::GetDir($dir);
        $this->mm_fclist=array();
        $tf = $dir."/.config";
        $c=realpath($tf);
        if(!file_exists($c)){
            $configs=array();
            $this->_initconfig($configs);
            $o="<?php\n";
            if(count($configs) > 0){
                foreach($configs as $k=>$m){
                    $o .= "\$config[\"{$k}\"] = \"{$m}\";\n";
                }
                igk_io_w2file($tf, $o);
            }
        }
        $c=realpath($dir."/.module.pinc");
        if(file_exists($c)){
            $this->_init($c);
        }

        $classLib = $this->getDeclaredDir()."/Lib/Classes";
       

        if (is_dir($classLib)){
            $dir = $this->getDeclaredDir();
            if (!empty($dir) &&  is_link($dir)){
                $dir = @readlink($dir);
            } 
            if (!is_dir($dir)){
                $dir = "";
            } 
            $entry_ns = $this->config("entry_NS", str_replace("/","\\", igk_get_module_name($dir)));
            $libdir=$classLib;
            spl_autoload_register(function($n)use($entry_ns, $libdir){
             
                $fc = "";
                if (!empty($entry_ns) && (strpos( $n, $entry_ns)===0)){
                    $cl = ltrim(substr($n, strlen($entry_ns)), "\\");
                    if (file_exists($fc = igk_io_dir($libdir."/".$cl.".php"))){
                        include($fc);
                        if (!class_exists($n, false)){
                            igk_die("file loaded but class $cl does not exists");
                        }
                        return 1;
                    }
                } 
            });
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    function __sleep(){
        $this->m_fclist=array();
        $this->m_src=null;
        return array("m_dir");
    }
    ///<summary></summary>
    /**
    * 
    */
    function __wakeup(){
        $this->_init();
    }
    ///<summary></summary>
    ///<param name="c" default="null"></param>
    /**
    * 
    * @param mixed $c the default value is null
    */
    private function _init($c=null){
        $s=igk_io_read_allfile($c ?? $this->m_dir."/.module.pinc");
        $reg=function($name, $callback){
            $this->reg_function($name, $callback);
        };
        $data = eval("?>".$s);
        $this->m_src=$s;
        if ($data){
            $this->m_configs = $data; 
        }
    }
    ///<summary></summary>
    ///<param name="configs" ref="true"></param>
    /**
    * 
    * @param  * $configs
    */
    protected function _initconfig(& $configs){
        $configs["libdir"]=IGK_LIB_DIR;

    }
    ///<summary></summary>
    ///<param name="msg"></param>
    /**
    * 
    * @param mixed $msg
    */
    private function bindError($msg){
        $this->setParam(__METHOD__, $msg);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAppDocument(){
        return null;
    }
    ///<summary></summary>
    ///<param name="c" default="null"></param>
    /**
    * 
    * @param mixed $c the default value is null
    */
    public function getAppUri($c=null){
        $q="";
        if($this->Listener)
            $q="ctrl=".$this->Listener->Name;
        $u="n=".$this->Name.($q ? "&".$q: "")."".($c ? "&q=".$c: "");
        $s=base64_encode($u);
        return igk_getctrl(IGK_SESSION_CTRL)->getUri("invmodule&q=".$s);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCallee(){
        return igk_peek_env(__CLASS__."/callee");
    }
    ///<summary>get the inline calling function</summary>
    /**
    * get the inline calling function
    */
    public function getCaller(){
        return $this->m_caller;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetCanCreateFrameworkInstance(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCurrentDoc(){
        return $this->m_doc;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDeclaredDir(){
        return $this->m_dir;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDeclaredFileName(){
        return realpath($this->getDeclaredDir()."/.module.pinc");
    }
    ///<summary>get module environment configuration</summary>
    /**
    * get module environment configuration
    */
    public function getEnvironmentConfigs(){
        /** @var string $c */
        static $_configs=null;
        if($_configs === null){
            $_configs=array();
        }
        $_hash=spl_object_hash($this);
        if(isset($_configs[$_hash])){
            return $_configs[$_hash];
        }
        $configs=realpath($this->m_dir."/.config");
        if(file_exists($c)){
            $config=array();
            include($c);
            $_configs[$_hash]=(object)$config;
        }
        return $_configs[$_hash];
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getListener(){
        return $this->m_listener ?? igk_ctrl_current_view_ctrl();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getName(){
        return strtolower(str_replace("/", ".", igk_html_uri(substr($this->m_dir, strlen(igk_get_module_dir())))));
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="def" default="null"></param>
    ///<param name="register" default="false"></param>
    ///<return refout="true"></return>
    /**
    * 
    * @param mixed $n
    * @param mixed $def the default value is null
    * @param mixed $register the default value is false
    * @return *
    */
    public function & getParam($n, $def=null, $register=false){
        $l=$this->Listener;
        $h=null;
        if($l){
            $h=$l->getParam($n, $def, $register);
        }
        return $h;
    }
    ///<summary></summary>
    ///<param name="c" default="null"></param>
    /**
    * 
    * @param mixed $c the default value is null
    */
    public function getUri($c=null){
        return $this->getAppUri($c);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function methodExists($n){
        return isset($this->m_fclist[$n]);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="fc"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $fc
    */
    protected function reg_function($n, $fc){
        $this->m_fclist[$n]=$fc;
    }
    ///<summary></summary>
    ///<param name="doc"></param>
    /**
    * 
    * @param mixed $doc
    */
    private function setCurrentDoc($doc){
        $this->m_doc=$doc;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setListener($v){
        $this->m_listener=$v;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $v
    */
    public function setParam($n, $v){
        $l=$this->Listener;
        if($l){
            $l->setParam($this->Name."/{$n}", $v);
        }
    }
    public function set($name, $value){
        return $this->setEnvParam($name, $value);
    }
    public function get($name, $default=null){
        return $this->getEnvParam($name, $default);
    }

    public function view(){
        if ($this->methodExists(__FUNCTION__)){
            $fc = igk_getv($this->m_fclist, __FUNCTION__);
            $args = func_get_args();
            $fc(...$args); 
        }
    }
}
