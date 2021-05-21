<?php
// @file: igk_core.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

defined("IGK_FRAMEWORK") || die("REQUIRE FRAMEWORK - No direct access allowed");
define(basename(__FILE__), 1);
use function igk_resources_gets  as __;

///<summary>autoload class function</summary>
/**
 * autoload class in dirs
 */
function igk_auto_load_class($name, $entryNS, $classdir, & $refile=null ){
    static $bindfile = null;
    if ($bindfile === null){
        $bindfile = function(){
            include_once(func_get_arg(0));
        };
    } 
  

    if(empty($entryNS) || (strpos($name, $entryNS) === 0)){
        $n = $name;
        if(!empty($entryNS)){
            $n=substr($name, strlen($entryNS));
            while((strlen($n) > 0) && ($n[0] == "\\")){
                $n=substr($n, 1);
            }
        }
        if (!is_array($classdir)){
            $classdir = [$classdir];
        }
        // | use to fit class path entry namespace
        $gdir = 0;
   
        while($tdir = array_shift($classdir)){
            if($gdir){  
                $tdir = dirname($tdir);
            }
            if(file_exists($file=igk_io_dir($tdir."/".$n.".php"))){            
                $bindfile($file);            
                $refile = $file;
                igk_hook(IGKEvents::HOOK_AUTLOAD_CLASS, [
                    $name,
                    $file
                ]);
                return 1;
            } 
            if (igk_is_debug()){
                igk_ilog($file);
            } 
            igk_debug_wln("try loadind 444 ".$name, $entryNS, "not found : ".$file, "?".file_exists($file));
            $gdir = 1; 
        } 
    }
    return 0;
}
function igk_io_get_script($f, $args=null){
    if (file_exists($f)){
        return "?>".file_get_contents($f);
    }
    return null;
}
function & igk_toarray($tab){
	$t = (array)$tab;
    return $t;
}

///<summary>evalute constant and get the value</summary>
///<return>null if constant not defined</return>
/**
* evalute constant and get the value
*/
function igk_const($n){
    if(defined($n)){
        return constant($n);
    }
    return null;
}
/**
 * check value for assertion
 * @return void 
 */
function igk_check($b):bool{
    switch(true){
        case is_bool($b): return $b;
        case is_object($b): 
            if (method_exists($b, "success")){
                return $b->success();
            }
            return true;
        case is_array($b):
            return !empty($b);
    }
    return false;
}
///<summary>check if a constant match the defvalue</summary>
/**
* check if a constant match the defvalue
*/
function igk_const_defined($ctname, $defvalue=1){
    if(defined($ctname))
        return constant($ctname) == $defvalue;
    return false;
}
///<summary></summary>
///<param name="class"></param>
///<param name="obj" ref="true"></param>
///<param name="callback"></param>
/**
* 
* @param mixed $class
* @param  * $obj
* @param mixed $callback
*/
function igk_create_instance($class, & $obj, $callback){
    if($obj === null){
        $obj=$callback($class);
    }
    return $obj;
}
///<summary>convert system path to uri scheme</summary>
/**
* convert system path to uri scheme
*/
function igk_html_uri($uri){
    if(is_object($uri)){
        igk_die(__FUNCTION__." passing object is not allowed;");
    }
    return str_replace("\\", "/", $uri);
}
///get basename without extension
/**
*/
function igk_io_basenamewithoutext($file){
    return igk_io_remove_ext(basename($file));
}
///<summary></summary>
///<param name="fname"></param>
/**
* 
* @param mixed $fname
*/
function igk_io_path_ext($fname){
    if(empty($fname))
        return null;
    return ($t=explode(".", $fname)) > 1 ? array_pop($t): "";
}
///<summary>Remove extension from filename @name file name</summary>
/**
* Remove extension from filename @name file name
*/
function igk_io_remove_ext($name){
    if(empty($name))
        return null;
    $t=explode(".", $name);
    if(count($t) > 1){
        $s=substr($name, 0, strlen($name) - strlen($t[count($t)-1])-1);
        return $s;
    }
    return $name;
}
function igk_io_inject_uri_arg($uri, $name, & $fragment = null){
    $g = parse_url($uri);
	if (!empty($fragment = igk_getv($g, "fragment"))){
		$fragment="#".$fragment;
	}	
	$uri = explode("?",$uri)[0]."?";
	if (!empty($query = igk_getv($g, "query"))){
		parse_str($query, $info);
		unset($info[$name]);
		$uri = explode("?",$uri)[0]."?".http_build_query($info)."&"; 
	} 
    return $uri;
}
/**
 * build info query args
 */
function igk_io_build_uri($uri, ?array $query= null, & $fragment=null){
    $g = parse_url($uri);
	if (!empty($fragment = igk_getv($g, "fragment"))){
		$fragment="#".$fragment;
	}	
    $info = $query ?? [];
	$uri = explode("?",$uri)[0];
	if (!empty($tquery = igk_getv($g, "query"))){
		parse_str($tquery, $info);
        if ($info && $query){
            $info = array_merge($info, $query);
        }
	} 
    $uri = $uri."?".http_build_query($info); 
    return $uri;
}
///<summary>detect that the environment in on mand line mode</summary>
/**
* detect that the environment in on command line mode
*/
function igk_is_cmd(){
    return igk_get_env("sys://func/".__FUNCTION__) || (isset($_SERVER["argv"]) && !isset($_SERVER["SERVER_PROTOCOL"]));
}
function igk_is_null_or_empty($c){
    return ($c === null) || empty($c);
}
///<summary></summary>
///<param name="name"></param>
/**
* 
* @param mixed $name
*/
function igk_load_library($name){
    static $inUse=null;
    if($inUse === null){
        $inUse=array();
    }
    $lib=IGK_LIB_DIR."/Library/";
    $c=$lib."/igk_".$name.".php";
    $ext=igk_io_path_ext(basename($name));
    if(empty($ext) || ($ext != ".php"))
        $ext=".php";
    if((file_exists($c) || file_exists($c=$lib."/".$name.$ext)) && !isset($inUse[$c])){
        include_once($c);
        $inUse[$c]=1;
        return 1;
    }
    return 0;
}

function igk_wl_tag($tag){
    echo "<$tag>";
    foreach(array_slice($tab = func_get_args(), 1) as $c){
        igk_wl($c);
    }
    echo "</$tag>";
}
///<summary>shortcut to get server info data</summary>
/**
* shortcut to get server info data
*/
function igk_server(){
    return IGKServer::getInstance();
}
///<summary>download zip core </summary>
/**
* download zip core
*/
function igk_sys_download_core($download=1){
    $tfile=tempnam(sys_get_temp_dir(), "igk");
    $zip=new ZipArchive();
    if($zip->open($tfile, ZIPARCHIVE::CREATE)){
        igk_zip_dir(IGK_LIB_DIR, $zip, "Lib/igk", "/\.(vscode|git|gkds)$/");
        $manifest=igk_createxmlnode("manifest");
        $manifest["xmlns"]="https://www.igkdev.com/balafon/schemas/manifest";
        $manifest["appName"]=IGK_PLATEFORM_NAME;
        $manifest->add("version")->Content=IGK_VERSION;
        $manifest->add("author")->Content=IGK_AUTHOR;
        $manifest->add("date")->Content=date("Ymd His");
        $zip->addFromString("manifest.xml", $manifest->render());
        $zip->addFromString("__lib.def", "");
        $zip->close();
    }
    if($download)
        igk_download_file("Balafon.".IGK_VERSION.".zip", $tfile, "binary", 0);
    return $tfile;
}


///<summary>return a list of project installed controllers</summary>
function igk_sys_project_controllers(){
    if (!IGKApp::IsInit()){
        return null;
    }
    $c = igk_app()->getControllerManager()->getControllers();
    $dir = igk_io_projectdir();
    $projects_ctrl = [];
    foreach($c as $k){
        if (strstr($k->getDeclaredDir(), $dir)){
            $projects_ctrl[] = $k;
        }
    }
    return $projects_ctrl;
}
///<summary></summary>
///<param name="msg"></param>
/**
* 
* @param mixed $msg
*/
function igk_wl($msg){
    if (file_exists(IGK_LIB_DIR.'/Inc/igk_trace.pinc'))
        include(IGK_LIB_DIR.'/Inc/igk_trace.pinc');
    $tab = func_get_args();
    while($msg = array_shift($tab)){
    if(is_array($msg) || is_object($msg)){
        igk_log_var_dump($msg);
    }
    else
        echo $msg;
    }
}
///<summary></summary>
///<param name="p"></param>
/**
* 
* @param mixed $p
*/
function igk_wl_pre($p){
    echo "<pre>";
    print_r($p);
    echo "</pre>";
}
function igk_dump_pre($p){
    echo "<pre>";
    var_dump($p);
    echo "</pre>";
}
function igk_dev_wln(){
    if (igk_environment()->is("DEV")){
        call_user_func_array("igk_wln", func_get_args());
    }
}
function igk_dev_wln_e(){
    if (igk_environment()->is("DEV")){
        call_user_func_array("igk_wln", func_get_args());
        igk_exit();
    }
}
// function igk_wln_set($prop, $value){
//     $s = igk_env_get($k = "sys://igk_wln");
//     if ($s === null)
//         $s = [];
//     if ($value == null){
//         unset($s[$prop]);
//     }else
//         $s[$prop] = $value;
//     igk_env_set($k, $s);
// }

 

///<summary></summary>
///<param name="msg" default=""></param>
/**
* 
* @param string|mixed $msg the default value is ""
*/
function igk_wln($msg=""){
    if (file_exists(IGK_LIB_DIR.'/Inc/igk_trace.pinc'))
        include(IGK_LIB_DIR.'/Inc/igk_trace.pinc');
 
    // $LF = igk_getv($options =  igk_environment->get("sys://igk_wln"), "lf", "<br />");
 
    if(!($lf=igk_get_env(IGK_LF_KEY))){
        $v_iscmd=igk_is_cmd();
        $lf=$v_iscmd ? IGK_CLF: "<br />";
    }
    
    foreach(func_get_args() as $k){
        $msg=$k;
        if(is_string($msg) || is_numeric($msg))
            igk_wl($msg.$lf);
        else{
            if($msg !== null){
                if(is_object($msg)){
                    if(igk_reflection_class_extends($msg, IGKHtmlItem::class)){
                        igk_wl($msg->Render().$lf);
                        continue;
                    }
                    var_dump($msg);
                }
                else{
                    igk_log_var_dump($msg, $lf);
                    continue;
                }
                igk_wl($lf);
            }
            else{ 
                igk_wl(__FUNCTION__."::msg is null".$lf);
            }
        }
    }
}
///<summary>write line to buffer and exit</summary>
/**
* write line to buffer and exit
*/
function igk_wln_e($msg){     
    igk_set_env('TRACE_LEVEL', 3);    
    call_user_func_array('igk_wln', func_get_args());
    igk_exit();
}

///<summary>utility to write html content </summary>
///<param name="args"> mixed| 1 array is attribute or next is considered as content to render </summary>
function igk_tag_wln($tag, $args=''){
	$attr= "";
	$targs = array_slice(func_get_args(), 1);
	if (is_array($args) && (func_num_args() > 2)) {
		$attr = " ".igk_html_render_attribs($args);
		$targs = array_slice($targs, 1);
	}
	ob_start();
	call_user_func_array('igk_wln', $targs);
	$s = ob_get_contents();
	ob_end_clean();
	$o = "<{$tag}".$attr;
	if (empty($s)){
		$o.= "/>";
	}else {
		$o.="> ".$s."</{$tag}>";
	}
	igk_wl($o);
}



///<summary></summary>
///<param name="ctrl"></param>
/**
* 
* @param mixed $ctrl
*/
function igk_app_is_appuser($ctrl){
    return ($u=$ctrl->User) && $u->clLogin == $ctrl->Configs->{'app.DefaultUser'};
}
///<summary>get if application is on uri demand</summary>
/**
* get if application is on uri demand
*/
function igk_app_is_uri_demand($app, $function){
    return (igk_io_currentUri() == $app->getAppUri($function));
}
///<summary>encrypt in sha256 </summary>
function igk_encrypt($data,$prefix=null){ 
    if ($prefix===null){
        $prefix = defined("IGK_PWD_PREFIX")? IGK_PWD_PREFIX : "";
    }
    return hash("sha256", $prefix.$data);
}
function igk_sys_copyright(){
    return "IGKDEV &copy; 2011-".date('Y')." ".__("all rights reserved");
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
    
    if (igk_is_cmd()){
        for($i=$depth; $i < count($callers); $i++, $tc++){
            //+ show file before line to cmd+click to be handle
            $f=igk_getv($callers[$i], "function");
            $c=igk_getv($callers[$i], "class", "__global");
            $o.= igk_getv($callers[$i], "file").":".igk_getv($callers[$i], "line") . PHP_EOL;
        } 
        echo $o;
        return;
    }

    $o .= "<div>".$sep;
    $o .= "<table>".$sep;

    if ($header){
        $o .= "<tr>";
        $o .= "<th>&nbsp;</th>";
        $o .= "<th>".__("Line")."</th>";
        $o .= "<th>".__("File")."</th>";
        $o .= "<th>".__("Function")."</th>";
        $o .= "<th>".__("In")."</th>";
        $o .= "</tr>".$sep;
    }
    $_base_path = !igk_environment()->is("DEV") && defined("IGK_BASE_DIR");
  
    for($i=$depth; $i < count($callers); $i++, $tc++){
 
        $f=igk_getv($callers[$i], "function");
        $c=igk_getv($callers[$i], "class", "__global");
        $o .= "<tr>";
        $o .= "<td>".$tc."</td>";
        $o .= "<td>".igk_getv($callers[$i], "line")."</td>";

        $o .= "<td>";
        $g = igk_getv($callers[$i], "file");
        if ($_base_path){
            $g = igk_io_basepath($g);
        }
        $o .= $g; 

        $o .= "</td>";
        $o .= "<td>".$f."</td>";
        $o .= "<td>".$c."</td>";
        $o .= "</tr>".$sep;
        if ($count>0){
            $count--;
            if ($count==0)
                break;  
        }
    }
    $o .= "</table>".$sep;
    $o .= "</div>".$sep;
    echo $o;
}

///<summary>get system directory presentation</summary>
/**
* get system directory presentation
*/
function igk_io_dir($dir, $separator=DIRECTORY_SEPARATOR){
    $d=$separator;     
    $out=IGK_STR_EMPTY;
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