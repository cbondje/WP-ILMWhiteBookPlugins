<?php 


namespace IGK\System\Configuration;

use ArrayAccess;
use IGK\IGKEnvironment;
use IGKObject;

///<summary>Represente class: ControllerConfigData</summary>
/**
* Represente ControllerConfigData class
*/
class ControllerConfigData extends IGKObject implements ArrayAccess{
    private $ctrl;
    private $m_changed=0;
    private $m_configs;

    public function to_array(){
        return (array)$this->m_configs;
    }
    public function to_json(){
        return json_encode($this->m_configs);
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    public function __construct($ctrl){
        if(!$ctrl)
            igk_die(__("ctrl can't be null"));
        $this->ctrl=$ctrl;
        $this->m_changed=0;
        $this->m_configs=igk_createobj();
        register_shutdown_function(function(){
            if($this->m_changed){
                $this->storeConfig();
            }
        });
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function __get($n){
        return igk_conf_get($this->m_configs, $n);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function __isset($n){
        return isset($this->m_configs->$n);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $v
    */
    public function __set($n, $v){
        igk_conf_set($this->m_configs, $v, $n);
        $this->m_changed=1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigFile(){
        return igk_io_dir($this->ctrl->getDataDir()."/".IGK_CTRL_CONF_FILE);
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initConfigSetting($t){
        $f=$this->getConfigFile();
        $def = strtolower(IGKEnvironment::ResolvEnvironment(igk_server()->ENVIRONMENT));

        if(file_exists($f)){
            $div=igk_createxmlnode("dummy-configs");
            $div->loadFile($f);
            $d=igk_getv($div->getElementsByTagName("config"), 0);
            if($d){
                foreach($d->Childs as $k){
                    if($k->ChildCount<=0){
                        $t->{$k->
                        TagName}=$k->innerHTML;
                    }
                    else{
                        $v_ob=igk_createobj();
                        igk_conf_load($v_ob, $k);
                        $t->{$k->TagName}=$v_ob;
                    }
                }
            }
            // | ----------------------------------------------------------
            // | UPDATE the configuration file to match allowed environment
            // | ----------------------------------------------------------
            if ($m = igk_getv($t, "env.".$def)){  
                foreach($m as $c=>$p){
                    if (strpos($c, "env.")===0){
                        die("invalid xml configuration file");
                    }
                    $t->$c = $p;
                } 
            }
        
        }
        if($this->ctrl->getIsSystemController()){
            $t->clRegisterName=IGK_DOMAIN.".".$this->ctrl->getName();
        }
        else{
            if(!isset($t->clRegisterName)){
                $t->clRegisterName=igk_sys_getconfig("website_prefix", "igk").".".$this->ctrl->getName();
            }
        }
        $this->m_changed=0;
        $this->m_configs=$t;
        return $t;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function LoadSetting(){}
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function offsetExists($n){
        return isset($this->m_configs->$n);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function offsetGet($n){
        return igk_getv($this->m_configs, $n);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $v
    */
    public function offsetSet($n, $v){
        $this->m_configs->$n=$v;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function offsetUnset($n){
        unset($this->m_configs->$n);
    }
    ///<summary>reload configuration setting</summary>
    /**
    * reload configuration setting
    */
    public function reloadConfiguration(){
        igk_die(__METHOD__." Not implement");
    }
    ///<summary> setup
    /**
    *  setup
    */
    private function setupCtrlConfigSettings(){
        igk_die(__METHOD__." Not implement");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function storeConfig(){         
        $this->m_changed = 0;  
        $d = igk_createxml_config_data($this->m_configs);  
        return igk_io_w2file($this->getConfigFile(), $d->render());
    }
    public function get($xpath, $default= null){
        return igk_conf_get($this->m_configs, $xpath, $default);
    }
}