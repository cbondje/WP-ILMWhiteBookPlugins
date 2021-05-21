<?php
namespace IGK\Helper;

use Exception; 
use IGK\System\Http\RequestUtility;
use stdClass;

abstract class Utility {
    public static function PostCref(callable $callback, $valid=1, $method="POST"){
        if (igk_server()->method($method) && igk_valid_cref($valid)){
            return $callback();
        }
        return false;
    }
    /**
     * 
     * @param mixed $paramHandler 
     * @param mixed $requestName 
     * @param mixed $paramName 
     * @param bool $update 
     * @return mixed 
     */
    public static function RequestGet($paramHandler, $requestName, $paramName, $update=true){
       return RequestUtility::RequestGet(...func_get_args());
    }
    /**
     * get the email display
     * @param mixed $r 
     * @return string 
     */
    public static function GetUserEmailDisplay($r){
        return implode(" ",array_filter([
            strtoupper($r->clLastName), ucfirst($r->clFirstName),
            "&lt;".$r->clLogin."&gt;"]));
    }
    /**
     * get the user fullname display
     * @param mixed $r 
     * @return string 
     */
    public static function GetFullName($r){
        return implode(" ", array_filter([ strtoupper($r->clLastName), ucfirst($r->clFirstName)]));
    }
    /**
     * convert raw to json.
     * @param mixed $raw 
     * @param mixed|null $options , ignore_empty=1|0 , default_ouput='{}'
     * @return mixed 
     * @throws Exception 
     */
    public static function To_JSON($raw , $options=null, $json_option = JSON_FORCE_OBJECT){
        $ignoreempty = igk_getv($options, "ignore_empty", 0);
        $default_output = igk_getv($options, "default_ouput", "{}");
        if(is_string($raw)){
            $sraw = json_decode($raw);
            if (json_last_error() === JSON_ERROR_NONE){
                if (!$ignoreempty){
                    return $raw;
                }
                $raw = $sraw;
            }else 
            return $default_output;
        }  
        $tab = [["r"=>$raw, "t"=>new stdClass()]];
        $root = null;
        while($m = array_shift($tab)){            
            $c = $m["t"];
            $raw = $m["r"];
            if (!$root)
                $root = $c;
            if (is_object($raw) || is_array($raw)){
                foreach($raw as $k=>$v){

                    if ($ignoreempty &&  (($v === null) || ($v =="")))
                        continue;
                    // | check if toArray method exists to get the array 
                    if (is_object($v) && method_exists($v, "toArray")){                       
                        $c->$k = new stdClass();
                        array_unshift($tab, ["r"=>$v->toArray(), "t"=>$c->$k]); 
                        continue;
                    }
                    if (is_object($v) || is_array($v)){
                        $c->$k = new stdClass();
                        array_unshift($tab, ["r"=>$v, "t"=>$c->$k]);
                    }else{
                        $c->$k = $v;
                    }
                } 
            }
        }
        return json_encode($root, $json_option);
    }
}