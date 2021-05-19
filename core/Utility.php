<?php
// @author: C.A.D BONDJE DOUE
// @file: Utility.php
// @desc: 
// @date: 20210517 09:16:52
namespace ILYEUM;


class Utility{
    public static function To_JSON($raw , $options=null){
        $ignoreempty = ilm_getv($options, "ignore_empty", 0);
        $default_output = ilm_getv($options, "default_ouput", "{}");
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
        $c = new \stdClass();
        if (is_object($raw) || is_array($raw)){
            foreach($raw as $k=>$v){

                if ($ignoreempty &&  (($v === null) || ($v =="")))
                    continue;
                $c->$k = $v;
            } 
        }
        return json_encode($c);
    }
    public static function GetTableName($table, $ctrl=null){    
        $p = ilm_app()->configs->db_prefix;
        return str_replace("%prefix%", $p, $table);
    }
}