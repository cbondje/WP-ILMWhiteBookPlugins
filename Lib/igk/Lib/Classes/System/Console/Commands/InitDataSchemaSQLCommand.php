<?php

namespace IGK\System\Console\Commands;

use IGK\Controllers\BaseController;
use IGK\Helper\Utility;
use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use IGKEvents;
use IGKNonVisibleControllerBase;

/**
 * initialize data schema
 * @package IGK\System\Console\Commands
 */
class InitDataSchemaSQLCommand extends AppExecCommand{
    var $command = "--db:schema";
    var $desc = "initialize from db schema file"; 
    var $category = "db";
    public function exec($command, $file=null, $ctrl=null)
    {    
        DbCommand::Init($command);
        if (!$file || !file_exists($file)){
            Logger::danger("file not found");
            return -1;
        }
        $options = igk_getv($command->options, "-option");
        $resolvname = $options != "json";
        if (!$ctrl  || !($ctrl = igk_getctrl($ctrl))){
            $ctrl = new InitDataSchemaController();
        }
        $schema = igk_db_load_data_schemas($file, $ctrl, $resolvname);
        if (!$schema){
            Logger::danger("schema not valid");
            return -2;
        }
       
        igk_set_env(IGK_ENV_DB_INIT_CTRL, $ctrl); 
        $tables = igk_getv($schema, "tables"); 
        switch( $options )
        {
            case 'json':
                echo Utility::To_JSON($tables, [
                    "ignore_empty"=>1,
                ], JSON_PRETTY_PRINT);
                exit;
        }
        igk_notification_push_event("sys://db/init_complete", $ctrl);
        igk_hook(IGKEvents::HOOK_DB_INIT_ENTRIES, array($ctrl));
        igk_hook(IGKEvents::HOOK_DB_INIT_COMPLETE, ["controller"=>$ctrl]);
        Logger::success("Schema complete");
        return 0;
    }
    public function help(){
        parent::help();
        Logger::print("file [-option:[json]]");
    }
}

class InitDataSchemaController extends IGKNonVisibleControllerBase{

}