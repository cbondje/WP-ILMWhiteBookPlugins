<?php
namespace IGK\Actions;
 
use Closure;
use IGK\Actions\IActionProcessor;
use IGK\System\Exceptions\ActionNotFoundException;
use IGK\System\Exceptions\ArgumentTypeNotValidException;
use IGK\System\Exceptions\NotInjectableTypeException;
use IGK\System\Exceptions\RequireArgumentException;
use IGK\System\Http\Request;
use IGKActionBase;
use ReflectionFunction;

/**
 * default action dispactcher
 */
class Dispatcher implements IActionProcessor{
    private $host ;
    private static $sm_macro;
    private static $sm_matches =[
        "int"=>"[0-9]+",
        "float"=>"(([0-9]+)\.[0-9]+)|([0-9]+)(\.[0-9]+)?"
    ];

    public function __construct(?IGKActionBase $host){
        $this->host = $host;
    } 
    public static function __callStatic($name, $args){
   
        if (self::$sm_macro===null){
            self::$sm_macro = [];
            self::$sm_macro["Dispatch"]= function($fc, ...$args){
                $g = new ReflectionFunction($fc); 
                $cl = null; 
                $required =  $g->getNumberOfRequiredParameters();
 
                // | try to inject parameter
                if (( $required >= 1) && 
                ($parameters = $g->getParameters()) && 
                ($cl = $parameters[0]->getType()) && 
                ($cl->getName() ===  Request::class)){
                    $req =  Request::getInstance();
                    $req->setParam($args);
                    $args = [$req]; 
                    for($i = 1; $i < count($parameters); $i++){
                        if (($p = $parameters[$i]->getType()) && (class_exists( $type = $p->getName()))){
                            
                            $c = new $type();
                            $args[] = $c;
                            continue;
                        }
                        throw new NotInjectableTypeException($i);
                    }
                } elseif (isset($parameters)){
                   

                    for($i = 0; $i < count($parameters); $i++){
                        if (!$parameters[$i]->isOptional()){
                            if ($i >= count($args)){
                                throw new RequireArgumentException($required, count($args));
                            }                             
                        }
                        if (($p = $parameters[$i]->getType()) && (class_exists( $type = $p->getName()))){
                            
                            $c = new $type();
                            $args[$i] = $c;
                            continue;
                        } else { 
                            if ($p){
                                $tname =$p->getName();
                                $v = igk_getv($args, $i);
                                // igk_wln("type : ", $tname);
                                if ($tname == "array"){
                                    $args[$i] = $v ? explode(",", $v) : [];// implode(",", $args[$i]);                                    
                                }else {
                                    $pattern = igk_getv(self::$sm_matches, $tname,".+");
                                    if ($v && !preg_match_all("#^".$pattern."$#", $v)){
                                        throw new ArgumentTypeNotValidException($i);
                                    }
                                } 
                            }
                        } 
                    }
                }  
                return $fc(...$args);
            }; 
        }
        if (is_callable($fc = igk_getv(self::$sm_macro, $name))){   
            
            return $fc(...$args);
        } 
        return (new static(null))->$name(...$args);
    }  
    public function __call($name, $arguments)
    {     
        if (is_callable($g =  [$this->host, $name]) 
        &&  ($fc = Closure::fromCallable($g)->bindTo($this->host))
        ){      
            $targs = array_merge([$fc] , $arguments);       
            return self::__callStatic("Dispatch", $targs);   
        }else{
            if ( $this->host instanceof IActionProcessor)
                return $this->host->__call($name, $arguments);
        }
        throw new ActionNotFoundException($name);   
    }
}