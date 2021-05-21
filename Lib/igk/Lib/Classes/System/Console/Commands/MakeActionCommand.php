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
 
class MakeActionCommand extends AppExecCommand{
    var $command = "--make:action"; 
 
    var $category = "make";

    var $desc = "make new project's action";

    var $options = [ 
        "--type"=>"defaut action type class"
    ]; 
    public function exec($command, $name="", $actionName=""){
        if (empty($name)){
            return false;
        } 
        if (empty($actionName)){
            Logger::danger("action name required");
            return false;
        } 
        Logger::info("make action ...".$name);
        $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
        $type = igk_str_ns(igk_getv($command->options, "--type", IGKActionBase::class));
        $type = igk_getv([
            "project"=>ProjectDefaultAction::class,
            "def"=>IGKActionBase::class
        ], $type, $type);
        
        $ctrl = igk_getctrl(str_replace("/", "\\", $name), false);
        if (!$ctrl){
            Logger::danger("controller $name not found");
            return false;
        }
        if (!$type || !class_exists($type) || !(($type==IGKActionBase::class) || is_subclass_of($type, IGKActionBase::class))){
            Logger::danger("type class not found");
            return false;
        }
        $ns = $ctrl->getEntryNamespace();
        $dir = $ctrl::classdir(); 
        $bind = [];
        $actionName = ucfirst($actionName);
        $clname = igk_str_ns($actionName);

        $bind[$dir."/Actions/{$clname}Action.php"] = function($file)use($actionName, 
            $author,$clname, $ns, $type){           
            $builder = new PHPScriptBuilder();
            $fname = $actionName.".phtml";
            if (!empty($ns)){
                $ns.="\\";
            }
            $ns .= "Actions";
            $builder->type("class")->name(igk_io_basenamewithoutext($file))
            ->author($author)
            ->namespace($ns)
            ->defs("")
            ->doc("view action")
            ->file($fname)
            ->extends($type)
            ->desc("view action ".$actionName);
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
        Logger::info("Make new Balafon's PROJECT action");
        Logger::print("-\n");
        Logger::print("Usage : ". App::gets(App::GREEN, $this->command). " ctrl name [options]" );
        Logger::print("\n\n");
    }
}