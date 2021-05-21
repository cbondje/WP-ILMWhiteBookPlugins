<?php

namespace IGK\System\Console\Commands;

use IGK\System\Console\App;
use IGK\System\Console\AppCommand;
use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use IGK\System\IO\File\PHPScriptBuilder;
use IGKActionBase;
use IGKCtrlInitListener;
use IGKIO;
use \IGKApplicationController;
use \IGKControllerManagerObject;
 
class MakeClassCommand extends AppExecCommand{
    var $command = "--make:class";

    var $category = "make";

    var $desc = "make a new class";

    public function exec($command, $classPath=null) {
        if (empty($classPath)){
            Logger::danger("class path can't be empty");
            return -1;
        }
        $ctrl = igk_getv($command->options, "--controller");
        $extends = igk_getv($command->options, "--extends");
        $desc = igk_getv($command->options, "--desc");
        $force = property_exists($command->options, "--force");
        $dir = ""; 
        $ns = "IGK";
        if (!empty($ctrl) && ($ctrl = igk_getctrl($ctrl, false))){
            $dir = $ctrl::classdir();
            $ns = $ctrl->getEntryNamespace();
        } else {
            $dir = igk_io_sys_classes_dir();
        }
        $g = igk_io_dir($classPath);
        if (strpos($g, $gs = igk_io_dir($ns)."/")===0){
            $g = ltrim(substr($g, strlen($gs)), "/");
        }
        //if ($ctrl){
            $ns = str_replace("/", "\\", $ns ."/".dirname($g)); 
        // }

        $fname = igk_io_dir($g).".php";
        if (!file_exists($file = $dir."/".$fname) || $force ){ 
            $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
            $builder = new PHPScriptBuilder();
            $builder->type("class")
            ->namespace($ns)
            ->author($author)
            ->file(basename($file))
            ->extends($extends)
            ->name(igk_io_basenamewithoutext($file))
            ->desc($desc);            
            //igk_wln_e($file, $builder->render());
            igk_io_w2file($file, $builder->render());
            return 200;
        } 
        return 400;
    }
}