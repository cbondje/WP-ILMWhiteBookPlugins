<?php

namespace IGK\System\Console\Commands;

use IGK\System\Console\App;
use IGK\System\Console\AppCommand;
use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use IGK\System\IO\File\PHPScriptBuilder;
use IGKCtrlInitListener;
use IGKIO;
use \IGKApplicationController;
use \IGKControllerManagerObject;
 
class MakeViewCommand extends AppExecCommand{
    var $command = "--make:view"; 
 
    var $category = "make";

    var $desc  = "make new project's view";

    var $options = [
        "--action"=>"enable action",
        "--dir"=>"enable view dir"
    ]; 
    public function exec($command, $name="", $viewname=""){
        if (empty($name)){
            return false;
        } 
        if (empty($viewname)){
            Logger::danger("view name required");
            return false;
        } 
        Logger::info("make view ...".$name);
        $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
                   
        $action = property_exists($command->options, "--action");
        $is_dir = property_exists($command->options, "--dir");
        $ctrl = igk_getctrl(str_replace("/", "\\", $name), false);
        if (!$ctrl){
            Logger::danger("controller $name not found");
            return false;
        }
  
        $dir = $ctrl->getViewDir();
        if ($is_dir){
           $dir .=  "/$viewname";
           $viewname =  IGK_DEFAULT_VIEW; 
        }       

        $bind = [];

        $bind[$dir."/{$viewname}.phtml"] = function($file)use($viewname, $author){           
            $builder = new PHPScriptBuilder();
            $fname = $viewname.".phtml";
            $builder->type("function")->name($viewname)
            ->author($author)
            ->defs("\$t->clearChilds();")
            ->doc("view entry point")
            ->file($fname)
            ->desc("view ".$viewname);
            igk_io_w2file( $file,  $builder->render());
        };

       

        foreach($bind as $n=>$c){
            if (!file_exists($n)){
                $c($n, $command);
                Logger::info("generate : ".$n);
            }
        }
        
        IGKControllerManagerObject::ClearCache(); 
        Logger::success("done\n");
    }
    public function help(){
        Logger::print("-");
        Logger::info("Make new Balafon's PROJECT view");
        Logger::print("-\n");
        Logger::print("Usage : ". App::gets(App::GREEN, $this->command). " ctrl name [options]" );
        Logger::print("\n\n");
    }
}