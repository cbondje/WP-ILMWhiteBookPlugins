<?php
// @author: C.A.D BONDJE DOUE
// @file: Utility.php
// @desc: 
// @date: 20210517 09:16:52
namespace ILYEUM;

use stdClass;
use function ilm_getv as getv;

class Utility{
     /**
     * convert raw to json.
     * @param mixed $raw 
     * @param mixed|null $options , ignore_empty=1|0 , default_ouput='{}'
     * @return mixed 
     * @throws Exception 
     */
    public static function To_JSON($raw , $options=null, $json_option = JSON_FORCE_OBJECT){
        $ignoreempty = getv($options, "ignore_empty", 0);
        $default_output = getv($options, "default_ouput", "{}");
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
    public static function GetTableName($table, $ctrl=null){    
        $p = ilm_app()->configs->db_prefix;
        return str_replace("%prefix%", $p, $table);
    }
}