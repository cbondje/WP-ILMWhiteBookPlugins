<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: redirection handler
// file: igk_redirection.php
// description: redirection handler
//TODO : BASE : 

if(defined("IGK_REDIRECTION") && (IGK_REDIRECTION == 1))
    return;
define("IGK_REDIRECTION", 1);
require_once(dirname(__FILE__)."/igk_framework.php");
require_once(dirname(__FILE__)."/igk_mysql_db.php");
$server_info=(object)array();



foreach(array(
        "REQUEST_URI"=>'',
        "SERVER_PROTOCOL"=>'',
        "REDIRECT_STATUS"=>'',
        "REDIRECT_URL"=>'',
        "REDIRECT_REQUEST_METHOD"=>'GET',
        "REDIRECT_QUERY_STRING"=>''
    ) as $k=>$v){
    $server_info->$k=igk_getv($_SERVER, $k, $v);
}
if(!isset($igk_index_file))
    $igk_index_file=igk_io_fullpath(dirname(__FILE__)."/../../index.php");
if(!is_file($indexdir=dirname($igk_index_file))){
    $c=realpath($indexdir);
    if($c)
        @chdir($indexdir);
}
if(!file_exists($igk_index_file)){
    echo("<div>/!\\ Index file not exist. please reinstall the igk <a href='./Lib/igk/igk_init.php'>balafon</a> core lib.</div> {$igk_index_file}");
    igk_exit();
}
header("Status: 200 OK");
header($server_info->{'SERVER_PROTOCOL'}
. " 200 OK");
if(!defined("IGK_APP_DIR")){
    include_once($igk_index_file);
}
else{
    if(!isset($no_start)){
        igk_sys_render_index($igk_index_file, 0);
    }
}
unset($igk_index_file);
header("Content-Type:text/html");
if(!defined('IGK_APP_DIR'))
    define("IGK_APP_DIR", getcwd());
$defctrl=igk_get_defaultwebpagectrl();
$app=igk_app();
$code=igk_getv($_REQUEST, "__c", 902);
$query=$server_info->{'REQUEST_URI'};
$redirect=$server_info->{'REDIRECT_URL'};
$redirect_status=$server_info->{'REDIRECT_STATUS'};
$r=$server_info->{'REDIRECT_REQUEST_METHOD'};
igk_sys_handle_res($query);
switch($code){
    case 901:
    if($redirect == "/sitemap.xml"){
        include(dirname(__FILE__)."/igk_sitemap.php");
        igk_exit();
    }
    /// TASK: handle query option on system command

    $rx="#^(".igk_io_baseUri().")?\/!@(?P<type>".IGK_IDENTIFIER_RX.")\/\/(?P<ctrl>".IGK_FQN_NS_RX.")\/(?P<function>".IGK_IDENTIFIER_RX.")(\/(?P<args>(.)*))?(;(?P<query>[^;]+))?$#i";
    $c=preg_match_all($rx, $redirect, $ctab);
    if($c > 0){
        igk_getctrl(IGK_SYSACTION_CTRL)->invokePageAction($ctab["type"][0], $ctab["ctrl"][0], $ctab["function"][0], $ctab["args"][0]);
        return;
    }
    break;
    case 904:
    header("Status: 404");
    header("HTTP/1.0 404 Not Found");
    igk_exit();
    break;
    case 403:
    igk_set_header($code);
    igk_sys_show_error_doc($code);
    igk_exit();
    break;
    case 404:
    if(igk_getr("m") == "config"){
        igk_navto("/Configs");
        igk_exit();
    }
    break;
}
$args=igk_getquery_args($server_info->{'REDIRECT_QUERY_STRING'});
$_REQUEST=array_merge($_REQUEST, $args);
if($r == "POST" && ($code < 900)){
    //DEBUG: Posted data are lost
    igk_is_debug() && igk_wln_e($_POST);
}
$app=igk_app();
$v_ruri=igk_io_base_request_uri();
$tab=explode('?', $v_ruri);
$uri=igk_getv($tab, 0);
$params=igk_getv($tab, 1);
$page=$uri;
$lang=null;
 

if( ($actionctrl=igk_getctrl(IGK_SYSACTION_CTRL)) && igk_io_handle_redirection_uri($actionctrl, $page, $params, 1))
    return;
try {
    if(igk_sys_ispagesupported($page)){
        $tab=$_REQUEST;
        igk_resetr();
        $_REQUEST["p"]=$page;
        $_REQUEST["l"]=$lang;
        $_REQUEST["from_error"]=true;
        $app->ControllerManager->InvokeUri();
        igk_render_doc();
        igk_exit();
    }
}
catch(Exception $ex){}
if(!empty($page) && ($page != "/")){
    $dir=getcwd()."/Sites/".$page;
    if(is_dir($dir)){
        chdir($dir);
        R::ChangeLang($lang);
        $IGK_APP_DIR=$dir;
        igk_wln_e("dir : ::: ", $dir, "page ".$page);
        include("index.php");
        igk_exit();
    }
}
$page=$uri;
if($defctrl !== null){
    if($defctrl->handle_redirection_uri($page)){
        igk_exit();
    }
}
///TASK: HANDLE RESOURCES

$suri=$server_info->{'REQUEST_URI'};
if(preg_match("/\.(jpeg|jpg|bmp|png|gkds)$/i", $suri)){
    header("Status: 301");
    header($server_info->{'SERVER_PROTOCOL'}
    . " 301 permanent");
    igk_exit();
}
igk_set_header(301);
//igk_show_error_doc(null, $code, $redirect);
igk_exit();