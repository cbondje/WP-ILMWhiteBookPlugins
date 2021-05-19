<?php

/**
 * 
 * @return mixed 
 */
function igk_environment(){
    return ILYEUM\Environment::getInstance();
}

/**
 * return string namespace presentation
 */
function igk_ns_name($ns){
    return str_replace("/", "\\", $ns);
}

function igk_die($msg){
    die($msg);
}
function igk_wln(){
    ob_start();
    foreach(func_get_args() as $arg){
        print_r($arg);
    }
    echo ob_get_clean();
}
function igk_getv($array, $key, $default=null){
    return igk_getpv($array, array($key), $default);
}

///<summary></summary>
///<param name="array"></param>
///<param name="key"></param>
///<param name="default" default="null"></param>
/**
* 
* @param mixed $array
* @param mixed $key
* @param mixed $default the default value is null
*/
function igk_getpv($array, $key, $default=null){
    $n=$key;
    if(!is_array($n)){
        $n=explode("/", $n);
    }
    if(($array === null) || (empty($key) && ($key !== 0))){
        return $default;
    }
    if($key === null){
        igk_die(__FUNCTION__." key not defined");
    } 
    $def=$default;
    $o=null;
    $ckey=""; 
    while($array && (($q=array_shift($n)) || ($q === 0))){
        $o=null;
        $ckey=$q;
        if(is_array($array) && isset($array[$q]))
            $o=$array[$q];
        else if(is_object($array)){ 
            if(isset($array->$q))
                $o=$array->$q;
            else{
                $t=class_implements(get_class($array));
                if(isset($t["ArrayAccess"])){
                    $o=$array[$q];
                }
            }
        }
        $array=$o;
    }
    if($o === null){
        if( !is_string($def) && is_callable($def)){           
            $o=call_user_func_array($def, array());
            $array[$ckey]=$o;
        }
        else{
            $o=$def;
        }
    }
    return $o;
}
function igk_getctrl(){
    return igk_environment()->getClassInstance(ILYEUM\WhiteBooks\Controller::class);
}


///<summary></summary>
///<param name="depth"></param>
/**
* 
* @param mixed $depth the default value is 0
*/
function igk_trace($depth=0, $sep="", $count=-1, $header=0){
    $callers=debug_backtrace();
    $o="";
    $tc=1; 
    for($i=$depth; $i < count($callers); $i++, $tc++){
        //+ show file before line to cmd+click to be handle
        $f=igk_getv($callers[$i], "function");
        $c=igk_getv($callers[$i], "class", "__global");
        $o.= igk_getv($callers[$i], "file").":".igk_getv($callers[$i], "line") . PHP_EOL;
    } 
    echo $o; 
}

function igk_wln_e(){
    igk_wln(...func_get_args());
    igk_exit();
}
function igk_exit(){
    exit;
}
///<summary>get system directory presentation</summary>
/**
* get system directory presentation
*/
function igk_io_dir($dir, $separator=DIRECTORY_SEPARATOR){
    $d=$separator;
    $r='/';
    $out="";
    if(ord($d) == 92){
        $out=preg_replace("/\//", '\\', $dir);
        $out=str_replace("\\", "\\", $out);
    }
    else{
        $d="/[\\\\]/";
        $out=preg_replace($d, '/', $dir);
        $out=str_replace("//", "/", $out);
    }
    return $out;
}
function igk_db_create_row($table){
    return (object)[];
}