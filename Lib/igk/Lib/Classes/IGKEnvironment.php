<?php
namespace IGK;

use Exception;

///<summary>use to manage Server Environment</summary>
/**
* use to manage Server Environment
*/
final class IGKEnvironment implements \ArrayAccess{
    private $m_envs;
    private static $sm_instance;
    // | default FOUR ENVIRONMENT TYPE
    private static $env_keys = [
        "DEV"=>"development",
        "TST"=>"testing",
        "ACC"=>"acceptance",
        "OPS"=>"production"
    ];
    public function getEnvironments(){
        return $this->m_envs;
    }
    /**
     * create an environment class instance
     * @param mixed $classname class name declaration
     * @return mixed 
     * @throws Exception 
     */
    public function createClassInstance($classname){
        $b = $this->instances;
        if ($b===null){
            $b = [];
        }
        if (!isset($b[$classname])){
            $b[$classname] = new $classname();
        }
        return igk_getv($b, $classname);
    }
    public static function ResolvEnvironment($n){        
        if (($index = array_search(strtolower($n), self::$env_keys))===false){
            return "DEV";
        }
        return $index;
    }

    public function setArray($name, $key, $value){
        $tab = $this->get($name);
        if (!is_array($tab)){
            if ($tab!==null) die("property name already contains a non array value");
            $tab = array();
        }
        $tab[$key] = $value;
        $this->$name = $tab;
    }
    ///<summary></summary>
    /**
    * 
    */
    private function __construct(){
        $t=[];
        foreach($_SERVER as $k=>$v){
            if(preg_match("/^IGK_/i", $k)){
                $t[$k]=$v;
            }
        }
        $this->m_envs=$t;
    }
    public function __debugInfo()
    {
        return null;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function & __get($n){      
        return $this->get($n);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
   public function __isset($v){
		return array_key_exists($v, $this->m_envs);
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
        $this->OffsetSet($n, $v);
		return $this;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __sleep(){
        igk_die("Sleep Environment: Operation Not allowed ".__CLASS__);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __wakeup(){}
    ///<summary></summary>
    ///<param name="var"></param>
    /**
    * 
    * @param mixed $var
    */
    public function & get($var, $default=null){
		$t = null;
		if (array_key_exists($var, $this->m_envs)){
			$t = & $this->m_envs[$var];
        }
        if ($t===null)
            $t = $default;
        //$t = igk_getv($this->m_envs, $var);
		return $t;
    }
    ///<summary>create a environment class </summary>
    public static function GetClassInstance($classname){
        static $instance;
        if ($instance ===null)
            $instance = [];
        if (isset($instance[$classname])){
            return $instance[$classname];
        }
        $c = new $classname();
        $instance[$classname] = $c;
        return $c;

    } 
    ///<summary></summary>
    ///<return refout="true"></return>
    /**
    * 
    * @return *
    */
    public static function & getInstance(){
        !($c= self::$sm_instance) && ($c = self::$sm_instance=new IGKEnvironment());
        return $c;
    }

    ///<summary></summary>
    /**
    * 
    */
    public function getVars(){
        return $this->m_envs;
    }
    ///<summary>check wether environment is on environment mode</summary>
    ///<remark>default environment mode is *development</summary>
    /**
    * check wether environment is on environment mode
    */
    public function is($env_mode){         
        if(array_key_exists($env_mode, self::$env_keys)){
            $env_mode = self::$env_keys[$env_mode];
        }
        return igk_server()->ENVIRONMENT == $env_mode;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function IsWebApp(){
        return $this->get("IGK_APP") == "WEBAPP";
    }
    public function context(){
        return $this->get("app_context", "web");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function name(){
        return igk_server()->ENVIRONMENT;
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function OffsetExists($i){
        return isset($this->m_envs[$i]);
    }
    ///<summary></summary>
    ///<param name="v"></param>
    ///<return refout="true"></return>
    /**
    * 
    * @param mixed $v
    * @return *
    */
    public function & offsetGet($v){
        $n=& $this->m_envs[$v];
        return $n;
    }
    ///<summary></summary>
    ///<param name="i"></param>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $i
    * @param mixed $v
    */
    public function offsetSet($i, $v){
        if($v === null)
            unset($this->m_envs[$i]);
        else
            $this->m_envs[$i]=$v;

    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function OffsetUnset($i){
        unset($this->m_envs[$i]);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function serialize(){
        die("not allowed ".__CLASS__);
    }
    ///<summary>set localy variable</summary>
    /**
    * set localy variable
    */
    public function set($k, $v){
        if($v === null){
            unset($this->m_envs[$k]);
        }
        else
            $this->m_envs[$k]=$v;
    }
}