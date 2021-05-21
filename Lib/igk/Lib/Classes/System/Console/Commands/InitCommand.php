<?php

namespace IGK\System\Console\Commands;

use IGK\System\Console\AppCommand;
use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use IGK\System\IO\File\PHPScriptBuilder;
use IGKIO;
use ReflectionClass;

class InitCommand extends AppExecCommand{
    var $command = "--command:init";

    var $desc  = "initialize balafon command cache";

    public function exec($command){
        $t = [
            igk_io_projectdir()
        ];
        $commands = [];
        $commands_list = [];
        $ctrls = igk_app()->getControllerManager()->getControllers();
        foreach($t as $dir){
            foreach($ctrls as $c){
                
                if (strstr($c->getDeclaredDir(), $dir)){
                    $cldir = $c::classdir();
                    if (!isset($commands[$cldir])){
                        $classname = get_class($c);
                        $c::register_autoload();
                        foreach(igk_io_getfiles($cldir."/Commands", "/\.php$/")  as $file){
                            if ($clpath = $c::resolvClass("Commands/".igk_io_basenamewithoutext($file))){
                                if ((new ReflectionClass($clpath))->isAbstract() || !is_subclass_of($clpath, AppCommand::class) ){
                                    continue;
                                }
                                if (!isset($commands_list[$classname])){
                                    $commands_list[$classname] = [];
                                }
                                $commands_list[$classname][] = $clpath;
                                Logger::success("register: ". $clpath);
                            }
                        }
                        $commands[$cldir] = 1;
                    }
                }

            }
        }
        $defs = "return [";
        $i = 0;
        foreach($commands_list as $ctrl=>$lsts){
            if ($i){
                $defs.=",\n";
            }
            $defs.=" \"$ctrl\"=>[\n";
            $y = 0;
            foreach($lsts as $t){
                if ($y){
                    $defs.=",\n";
                }
                $defs .= "$t::class";
                $y = 1;
            }
            $defs.="\n]";
            $i = 1;
        }
        $defs .= "];";
        $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
        $builder = new PHPScriptBuilder();
        $builder->type("function")
        ->author($author)
        ->defs($defs)
        ->desc("command list cache");

        igk_io_w2file(igk_io_cachedir()."/.command.list.php",$builder->render());
        Logger::print($builder->render());
    }
}