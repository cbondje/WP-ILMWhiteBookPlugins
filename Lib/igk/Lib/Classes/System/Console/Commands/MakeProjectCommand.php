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
 
class MakeProjectCommand extends AppExecCommand{
    var $command = "--make:project"; 
 
    var $category = "make";

    var $desc  = "make new project.";

    var $options = [
        "--type"=>"define the type of the project"
    ]; 
    public function exec($command, $name=""){
        if (empty($name)){
            return false;
        } 
        Logger::info("make project ...".$name);
        $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
                   
        $type = igk_getv($command->options, "--type", IGKApplicationController::class);
        $e_ns = igk_getv($command->options, "--entryNamespace", null);
        $desc = igk_getv($command->options, "--desc", null);
  
        $dir = igk_io_projectdir()."/$name";
        igk_init_controller(new IGKCtrlInitListener($dir, 'appsystem'));
        $defs=  "";

        if (!empty($e_ns)){
            $e_ns = igk_str_ns($e_ns);
            $defs.= "protected function getEntryNamespace(){ return {$e_ns}::class; }";
        }

        $bind = [];
        $ns = igk_str_ns($name);         
        $clname = ucfirst( basename(igk_io_dir($ns))."Project");
        $fname = $clname.".php";
        $bind[$dir."/$fname"] = function($file)use($type, $author,$defs, $desc, $clname, $fname ){
            $builder = new PHPScriptBuilder();
            $builder->type("class")->name($clname)
            ->author($author)
            ->defs($defs)
            ->doc("Controller entry point")
            ->file($fname)
            ->desc($desc)
            ->extends($type);
            igk_io_w2file( $file,  $builder->render());
        };

        $bind[$dir."/".IGK_VIEW_FOLDER."/default.phtml"]= function($file)use($author, $dir){

            
            
            $builder = new PHPScriptBuilder();
            
            $builder->type("function")
            ->author($author);
            
            igk_io_w2file( $file, 
            $builder->render());
        };

        foreach($bind as $n=>$c){
            if (!file_exists($n)){
                $c($n, $command);
            }
        }
        
        IGKControllerManagerObject::ClearCache();

        Logger::success("done\n");
    }
    public function help(){
        Logger::print("-");
        Logger::info("Make new Balafon PROJECT");
        Logger::print("-\n");

        Logger::print("Usage : ". App::gets(App::GREEN, $this->command). " name [options]" );
        Logger::print("\n\n");
    }
}