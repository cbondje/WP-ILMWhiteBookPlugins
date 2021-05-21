<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: initialize framework
// file: igk_init.php

define("IGK_INIT_SYSTEM", 1);
///<summary></summary>
///<param name="v1"></param>
///<param name="v2"></param>
/**
* 
* @param mixed $v1
* @param mixed $v2
*/
function igk_init_cmp_version($v1, $v2){
    while(($tb1=explode(".", trim($v1))) && count($tb1) < 4){
        $v1 .= ".0";
    }
    while(($tb2=explode(".", trim($v2))) && count($tb2) < 4){
        $v2 .= ".0";
    }
    $c=count($tb1);
    if($c == count($tb2)){
        $i=0;
        while(($i < $c) && ($tb1[$i] === $tb2[$i])){
            $i++;
        }
        if($i < $c){
            if($tb1[$i] < $tb2[$i]){
                return -1;
            }
            return 1;
        }
    }
    return strcmp($v1, $v2);
}
///<summary></summary>
///<param name="igk"></param>
///<param name="wizeinstall"></param>
/**
* 
* @param mixed $igk
* @param mixed $wizeinstall
*/
function igk_init_setparam($igk, $wizeinstall){
    $s=$igk->Session;
    $s->setParam("igk_wizeinstall", $wizeinstall);
    $s->setParam("igk_init", 1);
    $s->setParam("datetime", igk_date_now());
}
///<summary>compare two version</summary>
///<summary> check for requirement</summary>
/**
* compare two version
*  check for requirement
*/
function igk_requirement(){
    global $errors;
    $php_version=phpversion();
    $d=igk_init_cmp_version($php_version, "7.0");
    if(!($d>=0))
        $errors[]="01";
    return count($errors) == 0;
}

$errors=array();
if(!igk_requirement()){
    include(dirname(__FILE__)."/Views/error/requirement.phtml");
    igk_exit();
}

require_once($libfile = dirname(__FILE__)."/igk_framework.php");
require_once(dirname(__FILE__)."/igk_mysql_db.php");
$file2 = igk_server()->SCRIPT_FILENAME;
$is_startup = get_included_files()[0] == __FILE__;


$bdir = dirname(dirname(dirname($file2)));
$file = $bdir."/index.php";
$htaccess=$bdir."/.htaccess";


@session_start();
if(!empty(session_id())){
	@session_destroy();
}
$redirect=igk_getr("redirect", 1);
$wizeinstall=igk_getr("wizeinstall", !file_exists(IGKIO::GetDir($bdir."/Data/configure")));
 
IGKControllerManagerObject::ClearCache($bdir."/".IGK_CACHE_FOLDER);
if(file_exists($file)){

    include_once($file);
    if($redirect){
        igk_init_setparam(IGKApp::getInstance(), $wizeinstall);
	    header("Location: ../../Configs");
    }
}
else{
     
    $indexsrc=igk_getbaseindex_src($libfile);
    if(igk_io_w2file($file, $indexsrc, true)){
        defined("IGK_APP_DIR") || define("IGK_APP_DIR", dirname($file));
        $file=realpath($file);
        define("IGK_APP_INIT", 1);
        igk_io_w2file($htaccess, igk_getbase_access(), true);
        //igk_wln("file: ".$file , "**********". IGK_APP_DIR);
        include_once($file);
        if($redirect && is_dir("../../Configs")){
            igk_init_setparam(IGKApp::getInstance(), $wizeinstall);
            header("Location: ../../Configs");
        }else {
			igk_wln_e("config not found");
		}
    }
}
igk_exit();