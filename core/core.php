<?php
// @author: C.A.D. BONDJE DOUE
// @file: 
// @desc: 
// @date: 20210517 12:19:00


use ILYEUM\ConfigHandler;
use ILYEUM\wp\database\driver;

define("ILM_BASE_DIR", dirname(__DIR__));
define("ILM_WHITE_BOOK_DIR", __DIR__."/WhiteBooks");
define("ILM_VERSION", "1.0");

/**
 * 
 * @return mixed 
 */
function ilm_environment(){
    return ILYEUM\Environment::getInstance();
}

function ilm_app(){
    return ILYEUM\App::getInstance();
}
/**
 * get request variable
 * @param mixed $var 
 * @param mixed|null $default 
 * @param mixed|null $tab 
 * @return mixed 
 */
function ilm_getr($var, $default=null, $tab=null){
    if ($tab===null)
        $tab = $_REQUEST;
    if (key_exists($var, $tab)){
        return $tab[$var];
    }
    return $default;
}
 
function ilm_getev($value, $default){
if(($value == null) || empty($value)){
    if(is_callable($default) && ($default instanceof Closure))
        return $default();
    return $default;
}
return $value;
}
/**
 * return string namespace presentation
 */
function ilm_ns_name($ns){
    return str_replace("/", "\\", $ns);
}

function ilm_die($msg){
    die($msg);
}
function ilm_wln(){
    ob_start();
    foreach(func_get_args() as $arg){
        print_r($arg);
    }
    echo ob_get_clean();
}
function ilm_getv($array, $key, $default=null){
    return ilm_getpv($array, array($key), $default);
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
function ilm_getpv($array, $key, $default=null){
    $n=$key;
    if(!is_array($n)){
        $n=explode("/", $n);
    }
    if(($array === null) || (empty($key) && ($key !== 0))){
        return $default;
    }
    if($key === null){
        ilm_die(__FUNCTION__." key not defined");
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
function ilm_getctrl(){
    return ilm_environment()->getClassInstance(ILYEUM\WhiteBooks\Controller::class);
}


///<summary></summary>
///<param name="depth"></param>
/**
* 
* @param mixed $depth the default value is 0
*/
function ilm_trace($depth=0, $sep="", $count=-1, $header=0){
    $callers=debug_backtrace();
    $o="";
    $tc=1; 
    for($i=$depth; $i < count($callers); $i++, $tc++){
        //+ show file before line to cmd+click to be handle
        $f=ilm_getv($callers[$i], "function");
        $c=ilm_getv($callers[$i], "class", "__global");
        $o.= ilm_getv($callers[$i], "file").":".ilm_getv($callers[$i], "line") . PHP_EOL;
    } 
    echo $o; 
}

function ilm_wln_e(){
    ilm_wln(...func_get_args());
    ilm_exit();
}
function ilm_exit(){
    exit;
}
///<summary>get system directory presentation</summary>
/**
* get system directory presentation
*/
function ilm_io_dir($dir, $separator=DIRECTORY_SEPARATOR){
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
function ilm_db_create_row($table){
    $raw = null;
    $cf = ilm_get_db_config();
    if ($tinfo = ilm_getv($cf, $table)){
        $raw = (object)[];
        foreach($tinfo->ColumnInfo as $t){
            $raw->{$t->clName} = ilm_getv($t, "clDefault");
        }
    }
    return $raw;
}

function ilm_resources_gets(){
    return ilm_environment()->getClassInstance(\ILYEUM\Resources::class)->gets(...func_get_args());
}
function ilm_get_db_config(){
    if ($db_config = ilm_environment()->get("db_config")){
        return $db_config;
    }
    $r = ilm_app()->loadJsonConfig("data.json");
    foreach($r as $t=>$k){
        $r->$t->ColumnInfo = new ConfigHandler((array)$k->ColumnInfo);
    }  
    ilm_environment()->set("db_config", $r);
    return $r;    
}
function ilm_db_get_table_info($table){
    return ilm_getv(ilm_get_db_config(), $table);
}

function ilm_get_robjs($list, $replace=0, $request=null){
    return ilm_get_robj(is_string($list)? explode("|", $list): $list, $replace, $request);
}
function ilm_log(){
    if (function_exists("igk_ilog")){
        return igk_ilog(...func_get_args());
    }
}

///<summary>retreive requested object as object</summary>
///<param name="callbackfilter">callable that will filter the request available key</param>
/**
* retreive requested object as object
* @param mixed $closure callbackfilter callable that will filter the request available key
*/
function ilm_get_robj($callbackfilter=null, $replace=0, $request=null){
    $t=array();
    $m = $callbackfilter;
    if($m === null){
        $callbackfilter=function(& $k, $v, $rp){
            $rgx="/^cl/i";
            $p=preg_match($rgx, $k);
            if($p && $rp)
                $k=preg_replace($rgx, "", $k);
            return $p;
        };
    }
    else{
        if(is_string($m)){
            $callbackfilter=function(& $k, $v, $rp) use ($m){
                $rgx="/".$m."/i";
                $p=preg_match($rgx, $k);
                if($p && $rp){
                    $k=preg_replace($rgx, "", $k);
                }
                return $p;
            }; 
        }elseif (is_array($m)){
            $t = array_fill_keys($m, null);
            $callbackfilter = function (& $k, $v, $rp)use($m){
                if (in_array($k, $m)){
                    return true;
                }
                return false;
            };
        }
    }
    $request=$request ?? $_REQUEST;
    foreach($request as $k=>$v){
        if($callbackfilter($k, $v, $replace)){
            $t[$k]=ilm_str_quotes($v);
        }
    }
    return (object)$t;
}
function ilm_str_quotes($content){
    if(ini_get("magic_quotes_gpc") && is_string($content)){
        $content=stripcslashes($content);
    }
    return $content;
}
function ilm_db_create_options(){
    return (object)[
        "@callback"=>null,
        "Sort"=>null,
        "SortColumn"=>null
    ];
}
/**
 * utility parse uri
 * @param mixed $u 
 * @return mixed 
 */
function ilm_html_uri($u){
    return str_replace("\\", "/", $u);
}

function ilm_server(){
    return ILYEUM\Server::getInstance();
}