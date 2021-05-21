<?php

namespace IGK\Controllers;

use Closure;
use IGKObject;
use ReflectionFunction;

///<summary>represent a root controller entry</summary>
/**
 * represent a root controller entry
 */
abstract class RootControllerBase extends IGKObject{
	static $macros;

    public static function __callStatic($name, $arguments)
	{
		if (self::$macros===null){
			self::$macros = [
				"macrosKeys"=>function(){
					return array_keys(self::$macros);
				},
				"initDb"=>function(RootControllerBase $controller, $force=false){
					return include(IGK_LIB_DIR."/Inc/igk_db_ctrl_initdb.pinc"); 
				},
				"resetDb"=>function(RootControllerBase $controller, $navigate=true, $force=false){
				 	return include(IGK_LIB_DIR."/Inc/igk_db_ctrl_resetdb.pinc");
				},
				"getDb"=>function(){
					return null;
				},
				"getMacro"=>function($name) {
					return  igk_getv(self::$macros, $name);
				}
			];
		}
		$c = igk_getctrl(static::class);
 
		 
		
		if (isset(self::$macros[$name])){
			$fc = Closure::fromCallable(self::$macros[$name]);
			$fc = $fc->bindTo(null, static::class);
			$ref = (new ReflectionFunction($fc));		
			if (($ref->getNumberOfParameters()>0) && ($t = $ref->getParameters()[0]->getType()) ){
				if (($t == self::class) || is_subclass_of($t->getName(), self::class)){
					array_unshift($arguments, $c);
				}
			}
			return $fc(...$arguments);
		} 
		
		//if ($name == "getComponentsDir"){
			// method is probably protected
		if (method_exists($c, $name)){
			//invoke in controller context
			return $c::Invoke($c, $name, $arguments);
		}
		// 	igk_wln("cmethod ", method_exists($c, $name));
		// 	igk_wln_e("ok");
		// }
		array_unshift($arguments, $c); 

		return ControllerExtension::$name(...$arguments); 
	}
	public function __call($name, $argument){
        return static::__callStatic($name, $argument);
    }
}