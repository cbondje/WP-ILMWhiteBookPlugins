<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Models/ModelBase.php
// @desc: 
// @date: 20210517 08:57:52
namespace ILYEUM\WhiteBooks\Models;

use Closure;
use Exception;
use ILYEUM\Utility;
use ReflectionClass;

abstract class ModelBase{
    static $macros;
    /**
     * the base table
     * @var mixed
     */
    protected $table;

    /**
     * raw data
     * @var mixed
     */
    protected $raw;

    /**
     * 
     * @var mixed
     */
    protected $primaryKey = "clId";

    /**
     * column use for display
     * @var string
     */
    protected $display = "clName";

    /**
     * base model controller
     * @var string
     */
    protected $controller;

    /**
     * class used for factory
     * @var mixed
     */
    protected $factory;

    /**
     * field list use to create forms
     * @var array
     */
    protected $form_fields = [];

    /**
     * fillable list use data
     * @var mixed
     */
    protected $fillable;


    /**
     * for mocking object
     * @var mixed
     */
    private $is_mock;

    /**
     * define unset field for update
     * @var mixed
     */
    protected $update_unset;

    public function getUpdateUnset(){
        return $this->update_unset;
    }

    public function getFactory(){
        if ($this->factory === null){
            $name = basename(igk_io_dir(get_class($this)));
            $this->factory = $this->getController()::ns("Database\\Factories\\".$name."Factory");
        }
        return $this->factory;
    }
    public function set($name, $value){
        $this->raw->{$name} = $value;
        return $this;
    }
    

	public function display(){
		return $this->{$this->display};
	}
    public function getPrimaryKey(){
        return $this->primaryKey;
    }
    public function getFormFields(){
        return $this->form_fields;
    }
    public function getDisplay(){
        return $this->display;
    }
    
    

    public function __construct($raw=null)
    {
        $this->raw = igk_db_create_row($this->getTable());
        if (!$this->raw ){
            die("failed to create db row: ".$this->getTable());
        }
        if ($raw){
            foreach($raw as $k=>$v){
                if (property_exists($this->raw, $k)){
                    $this->raw->$k = $v;
                }
            }   
        }
    }
    public function __set($name, $value){
        if (property_exists($this->raw, $name)){
            $this->raw->$name = $value;
            return;
        }
        throw new Exception("Failed to access ".$name);
    }
    public function __get($name){  
        if (method_exists($this, $m = "get".$name )){
			return $this->$m();
		}        
        return igk_getv($this->raw, $name);
    }

    public function offsetExists($offset) { }

	public function offsetGet($offset) {
		return $this->$offset;
	 }

	public function offsetSet($offset, $value) {
		$this->$offset = $value;
	 }

	public function offsetUnset($offset) { } 

	public function geturi(){ 
		return $this->clhref;
	}

	 
    /**
     * return the current table string
     * @return mixed 
     */
    public function getTable(){
        $ctrl = $this->getController();
        return Utility::GetTableName($this->table, $ctrl); 
    }
    public function getController(){
        return igk_getctrl($this->controller, false);
    }
    public function getDataAdapter(){
        return igk_environment()->getClassInstance(ILYEUM\wp\database\driver::class);
    }

    /**
     * disable debug
     * @return null 
     */
    public function __debugInfo()
    {
        return null;
    }

    /**
     * calling static member function
     * @param mixed $name 
     * @param mixed $arguments 
     * @return mixed 
     * @throws Exception 
     */
    public static function __callStatic($name, $arguments)
    {
        if (self::$macros === null){
            // 
            // + initialize macro definition
            //
            self::$macros = [
                "create"=>function($raw=null){                     
                    $c= new static($raw); 
                    if ($c->raw){
                        if ($g = $c->insert($c->raw)){
                            $c->raw = $g->raw;;
                        }else{
                            return null;
                        }
                    }
                    return $c;
                },
                "registerMacro"=>function($name, Callable $callback){
                    
                    if (is_callable($callback)){
                        $callback = Closure::fromCallable($callback);
                    }
                    if (__CLASS__ == static::class){
                        self::$macros[$name] = $callback;     
                    }else { 
                        self::$macros[igk_ns_name(static::class."/".$name)] = $callback; 
                    }
                },
                "unregisterMacro"=>function($name){
                    unset(self::$macros[igk_ns_name(static::class."/".$name)]);
                },
                "registerExtension"=>function($classname){  
                    
                    $f = new ReflectionClass($classname);
                    foreach($f->getMethods() as $k){
                        if ($k->isStatic()){
                            self::$macros[$k->getName()] = [$classname, $k->getName()];
                        }
                    }
                },
                "getMacroKeys"=>function(){
                    return array_keys(self::$macros);
                },
                "getInstance"=>function($name){
                    return igk_environment()->createClassInstance(static::class);
                }
            ];
            // register call extension
            $f = new ReflectionClass(ModelEntryExtension::class);
            foreach($f->getMethods() as $k){
                if ($k->isStatic()){
                    self::$macros[$k->getName()] = [ModelEntryExtension::class, $k->getName()];
                }
            } 
            if (file_exists($file = __DIR__."/DefaultModelEntryExtensions.pinc")){
                require_once($file);
            }
        } 
        $_instance_class = igk_environment()->createClassInstance(static::class);
        $_instance_class->is_mock = 1;

        if ($fc = igk_getv(self::$macros, $name)){
            $bind = 1;
            if (is_array($fc)){

                array_unshift($arguments, $_instance_class); 
                $bind = 0;
            } 
            if ($bind && (static::class !== __CLASS__)){
                $fc = Closure::bind($fc, null, static::class); 
                if (!$fc){
                    igk_die("Can't bind : ", $name);
                }
            }            
            return $fc(...$arguments);
        } 
        if ($fc = igk_getv(self::$macros, igk_ns_name(static::class."/".$name))){
            $fc = $fc->bindTo($_instance_class);
            return $fc(...$arguments);
        }
        if (static::class === __CLASS__){
            return;
        }   
        $c = $_instance_class;
        if (method_exists($c, $name)){
            return $c->$name(...$arguments);
        }
        igk_wln(array_keys(self::$macros));
        die("ModelBase: failed to call [".$name."]");
    }

    /**
     * call macro on this model
     * @param mixed $name 
     * @param mixed $arguments 
     * @return mixed 
     * @throws Exception 
     */
    public function __call($name, $arguments){

        static $regInvoke;

        if ($regInvoke === null){
            $regInvoke = 1;
        }
        if ($fc = igk_getv(self::$macros, igk_ns_name(static::class."/".$name))){
            $fc = $fc->bindTo($this); 
            return $fc(...$arguments);
        } 
        
        if ($fc = igk_getv(self::$macros, $name)){
            if (is_callable($fc)){
                $fc = Closure::fromCallable($fc);
            }
            array_unshift($arguments, $this);            
            //$fc = $fc->bindTo($this); 
            return $fc(...$arguments);
        }   
        if (igk_environment()->is("DEV")){
            igk_trace();
            igk_wln_e("failed to call ", $name );
        }
    }

    /**
     * model to json
     * @param mixed|null $options 
     * @return string|false 
     */
    public function to_json($options=null){
        return Utility::To_JSON($this->raw, $options);
    }

    public function is_mock(){
        return $this->is_mock;
    }
    /**
     * return raw data
     * @return mixed 
     */
    public function to_array(){
        return (array)$this->raw;
    }
    public function save(){     
        $pkey = $this->primaryKey;
        $r = $this->getDataAdapter()->update($this->getTable(), $this->raw, [$this->primaryKey=>$this->$pkey]);    
        return $r && $r->success(); 
    }
}