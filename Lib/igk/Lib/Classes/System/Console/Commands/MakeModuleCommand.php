<?php

namespace IGK\System\Console\Commands;
use IGK\System\Console\AppCommand;
use IGK\System\Console\Logger;
use IGK\System\IO\File\PHPScriptBuilder;
use IGKIO;
 
class MakeModuleCommand extends AppCommand{
    var $command = "--make:module"; 
    var $category = "make";
    var $desc  = "make new module.";

    public function run($args, $command)
    {
        $command->exec = function($command, $name){
            Logger::print("generate module : " . $command->app::gets( $command->app::RED, $name));

            $dir = igk_html_uri(igk_get_module_dir()."/".$name);

            IGKIO::CreateDir($dir."/Views");
            IGKIO::CreateDir($dir."/Styles");
            IGKIO::CreateDir($dir."/Lib");
            IGKIO::CreateDir($dir."/Configs");
            if (file_exists($dir."/.global.php")){
                igk_io_w2file($dir."/.global.php", "<?php\n");
            }
            $bind = [];
            $bind[$dir."/.module.pinc"] = function($file, $command, $name){
                $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
                $e_ns = str_replace("/", "\\", $name);

                $definition = self::EntryModuleDefinition($author, $e_ns);
                $builder = new PHPScriptBuilder();
                $builder
                ->author($author)
                ->type("function")
                ->file("$file.php")
                ->name($name)  
                ->desc(igk_getv($command->options, "--desc"))
                ->defs("// + module definition\nreturn [\n$definition\n];")
                ->namespace($e_ns);
                igk_io_w2file($file, $builder->render());
            };
            $bind[$dir."/module.json"] = function($file, $command, $name){
                $o = igk_createobj();
                $o->name = $name;
                $o->author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
                $o->version = igk_getv($command->options, "--version", "1.0");
                igk_io_w2file($file, json_encode($o, JSON_PRETTY_PRINT));
            };
            foreach($bind as $path=>$callback){
                if (!file_exists($path)){
                    $callback($path, $command, $name); 
                }
            }
             
            Logger::success("done");
        };
    
    }
    static function EntryModuleDefinition($author=null, $e_ns=null, $version="1.0" ){
        
        return <<<EOF
//------------------------------------------------
// define entry name space
//
"entry_NS"=>"$e_ns",

//------------------------------------------------
// version
//
"version"=>"{$version}",

//-------------------------------------------------
// author
//
"author"=>"{$author}"
EOF;
    }
}