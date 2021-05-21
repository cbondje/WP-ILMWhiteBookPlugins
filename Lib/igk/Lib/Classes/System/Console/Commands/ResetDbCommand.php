<?php
namespace IGK\System\Console\Commands;

use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;

class ResetDbCommand extends AppExecCommand{
    var $command = "--db:resetdb";
    var $desc = "reset database"; 
    var $category = "db";

    public function exec($command, $ctrl=null)
    {   
        DbCommand::Init($command); 
        $seed = property_exists($command->options, "--seed");
         
        if ($seed){
            $seed = $command->app->command["--db:seed"];
            $fc = $seed["0"];
            $fc("resetdb", $command); 
        }

        if ($ctrl){
            if ($c = igk_getctrl($ctrl, false)){            
                $c = [$c];
            } else{
                Logger::danger("controller not found");
                return -1;
            }
        } else {
            $c = igk_app()->getControllerManager()->getControllers(); 
        }
        if ($c) {
            foreach ($c as $m) {
                if ($m->getCanInitDb()){
                    $m->register_autoload();
                    $command->app->print("resetdb : " . get_class($m));
                    $m::resetDb(false, true);
                    Logger::success("complete: ".get_class($m));
                }
            }
            Logger::print("-"); 
            if (1 && $seed){
                $fc = $command->exec;
                $fc($command, $ctrl);
            }
            return 1;
        }
        return -1;
    }
}