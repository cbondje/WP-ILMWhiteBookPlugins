<?php

namespace IGK\System\Console\Commands;

use IGK\System\Console\App; 
use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use IGK\System\IO\File\PHPScriptBuilder; 
use \IGKControllerManagerObject;
use IGKDbUtility;

class MakeModelUtilityCommand extends AppExecCommand
{
    var $command = "--make:model-utility";

    var $category = "make";

    var $desc  = "make new project's model utility";

    var $options = [];
    public function exec($command, $name = "", $modelname = "")
    {
        if (empty($name)) {
            return false;
        }
        if (empty($modelname)) {
            Logger::danger("model utility name required");
            return false;
        }
        Logger::info("make model utility class ..." . $name);
        $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);

        $ctrl = igk_getctrl(str_replace("/", "\\", $name), false);
        if (!$ctrl) {
            Logger::danger("controller $name not found");
            return false;
        }

        $clname = ucfirst(igk_str_ns($modelname)) . "ModelUtility";
        $ns = $ctrl->getEntryNamespace();
        if (!empty($ns)) {
            $ns = str_replace("/", "\\", $ns . "/ModelUtilities");
        }

        $bind = [];
        $bind[$ctrl::classdir() . "/ModelUtilities/" . $clname . ".php"] = function ($file) use ($clname, $author, $ns) {
            $builder = new PHPScriptBuilder();
            $fname = basename($file);
            $builder->type("class")->name($clname)
                ->author($author)
                ->defs("")
                ->doc("view entry point")
                ->file($fname)
                ->namespace($ns)
                ->extends(IGKDbUtility::class)
                ->desc("module utility " . $clname);
            igk_io_w2file($file,  $builder->render());
        };



        foreach ($bind as $n => $c) {
            if (!file_exists($n)) {
                $c($n, $command);
                Logger::success("generate : " . $n);
            }
        }

        IGKControllerManagerObject::ClearCache();
        Logger::success("done\n");
    }
    public function help()
    {
        Logger::print("-");
        Logger::info("Make new db modeul utility");
        Logger::print("-\n");
        Logger::print("Usage : " . App::gets(App::GREEN, $this->command) . " ctrl name [options]");
        Logger::print("\n\n");
    }
}
