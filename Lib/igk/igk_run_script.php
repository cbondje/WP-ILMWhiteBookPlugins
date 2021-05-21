<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: run script

require_once(dirname(__FILE__)."/igk_framework.php");
igk_display_error(1);
if(!igk_cmp_version(phpversion(), "7.0")){
    igk_die("version requirement 7.0+");
}
spl_autoload_register(function($n){
    $cdir=igk_io_dir(IGK_LIB_DIR."/".IGK_CLASSES_FOLDER);
    $s=str_replace("IGK\\Core\\", $cdir, $n);
    $f=igk_io_dir($s.".php");
    if(file_exists($f)){
        $functions=get_defined_functions()["user"];
        $classes=get_declared_classes();
        $source=igk_count($functions);
        $clcount=igk_count($classes);
        $g=dirname(substr($f, strlen($cdir)));
        $ns="IGK\\Core";
        if(!empty($g) && ($g !== '.')){
            $ns .= "\\".str_replace("/", "\\", $g);
        }
        igk_include_script($f, $ns);
    }
});
igk_reg_cmd_command("manager", IGKBalafonFrameworkManager::class);
$header=<<<EOF
Balafon run_script command.
version : 1.0
author: C.A.D. BONDJE DOUE
EOF;
define("IGK_APP_DIR", realpath(dirname(__FILE__)."/../../"));
chdir(IGK_APP_DIR);
$argv=igk_getv($_SERVER, "argv");
if(!$argv){
    igk_wln("argument not valid");
    return -2;
}
$arg_c=0;
if(($arg_c=igk_count($argv)) < 2){
    igk_wln($header);
    igk_wln("usage : [argument not valid]");
    return -1;
}
$s=igk_getv($_SERVER, "SCRIPT_FILENAME") == __FILE__;
if($s){
    igk_set_env("sys://func/igk_is_cmd", 1);
}
$c=igk_get_cmd_command(igk_getv($argv, 1));
if($c){
    igk_view_handle_actions(basename(__FILE__), $c, array_slice($argv, 2));
    igk_exit();
}
$g=0;
for($i=1; $i < $arg_c; $i++){
    $f=igk_getv($argv, $i);
    if(file_exists($f)){
        $g=include_once($argv[$i]);
    }
    else{
        igk_wln("file not found :".$f);
    }
    if($g)
        return $g;
}
unset($arg_c);