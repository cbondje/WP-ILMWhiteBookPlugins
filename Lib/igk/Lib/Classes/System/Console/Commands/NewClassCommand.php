<?php

namespace IGK\System\Console\Commands;

use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use IGK\System\IO\File\PHPScriptBuilder;
use IGKSQLQueryUtils;

class NewClassSQLCommand extends AppExecCommand{
    var $command = "--new";
    var $desc = "new type class builder "; 

    public function exec($command, $type=null, $name="", $dir=null)
    {    
        $desc = "";
        $rootns = "";
        if (property_exists($command->options, "-dir")){
            $dir = $command->options->{"-dir"};
        }else {
            $dir = getcwd();
        }
        if (property_exists($command->options, "-root-ns")){
            $root_ns = $command->options->{"-root-ns"};
        }

        $path = $name;
        if (!empty($root_ns)){
            if (strpos($name, $root_ns ) === 0){
                $path = substr($path, strlen($root_ns)+1);
            }
        }



        $file = implode("/", [$dir, $path.".php"]);

        if ( file_exists($file) &&  !property_exists($command->options, "--force")){
            return;
        }
           
        if (($ns = dirname($name))=="."){
            $ns = "";
        }
        $builder = new PHPScriptBuilder();
        $builder->type($type)->author(
            $command->app->getConfigs()->get("author", IGK_AUTHOR)       
        )
        ->namespace(igk_ns_name($ns))
        ->name(
            basename(igk_html_uri($name))
        )->file(igk_html_uri($path.".php")) ->desc($desc);

  
       // igk_wln_e(func_get_args(), $dir, $builder->render(), $file);
        igk_io_w2file($file, $builder->render());
        Logger::success("file created: ".$file);
    }
}