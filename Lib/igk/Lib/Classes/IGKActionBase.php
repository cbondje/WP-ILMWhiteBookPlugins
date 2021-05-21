<?php



///<summary>Represente view's action definition</summary>

use IGK\Actions\IActionProcessor;
use IGK\Controllers\BaseController;
use IGK\System\Exceptions\ActionNotFoundException;

/**
* Represente view's action definition
*/
abstract class IGKActionBase implements IActionProcessor{
    protected $ctrl;
    protected $context;
	var $handleAllAction;
    protected static $macro;
    /**
     * extends default faction with macro function
     * @param mixed $name 
     * @param mixed $callback 
     * @return void 
     */
    public static function Register($name, $callback){
        if (igk_environment()->is("DEV")){
            igk_trace(); // "ksdf");
            igk_exit();
        }
        self::$macro[$name] = $callback;
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    public function Initialize($ctrl){
        $this->ctrl=$ctrl;
        return $this;
    }
	///<summary>for action return the current user id</summary>
    /**
     * 
     * @return mixed 
     * @throws Exception 
     */
	public function getUserId(){
		return igk_sys_current_user_id();
    }
    /**
     * 
     * @param mixed $ctrl 
     * @param mixed|null $context 
     * @return object 
     * @throws Exception 
     */
    public static function Init($ctrl, $context=null){
        $cl = static::class;
        if ($cl == __CLASS__){
            igk_die("Operation not allowed");
        } 
        $o = new $cl();
        $o->ctrl = $ctrl; 
        $o->context = $context;
        return $o;
    }
    public static function __callStatic($name, $arguments)
    {  
        return (new static)->$name(...$arguments);
    }
    /**
     * 
     * @param mixed $fname 
     * @param mixed $args 
     * @param int $exit 
     * @param int $flag 
     * @return mixed 
     * @throws Exception 
     */
    protected function Handle($fname, $args, $exit=1, $flag=0){ 
        $ctrl = null; 
        if ($fname instanceof BaseController){           
            if (func_num_args()<3){
                throw new \Exception("Require 3 argument in that case");
            }
            $ctrl = $fname;
            $c = func_get_args();
            array_shift($c);

            extract([
                "fname"=>$c[0],
                "args"=>$c[1],
                "exit"=>igk_getv($c, 2, 1),
                "flag"=>igk_getv($c, 3, 0)
            ], EXTR_OVERWRITE); 
        }
 
        $this->ctrl = $ctrl ? $ctrl : igk_ctrl_current_view_ctrl();
        $b = $this->getActionProcessor();
        if (is_string($b)){
            if (!class_exists($b)){
                return false;
            }
            $cargs = [$this];
            $b = new $b(...$cargs); 
        }  
        return igk_view_handle_actions($fname, $b, $args, $exit, $flag );
    }
    public function __call($name, $arguments){ 
        if ($fc = igk_getv(self::$macro, $name)){
            return $fc(...$arguments);
        } 
        // dispath to methods
        if (method_exists($this, $fc = $name."_".igk_server()->REQUEST_METHOD)){
            return $this->$fc(...$arguments);
        }    
        throw new ActionNotFoundException($name);   
    }
    /**
     * 
     * @return string|object classname or IActionProcessor Object 
     */
    protected function getActionProcessor(){
        return IGK\Actions\Dispatcher::class;
    }

    public function getController(){
        return $this->ctrl;
    }
    public function __get($n){
        if (method_exists($this, $fc = "get".$n)){
            return $this->$fc();
        }
        return null;
    }
}
