<?php

use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use IGK\System\IO\File\PHPScriptBuilder;

class FixCssCommand extends AppExecCommand{
    var $command = "--fix:css";
    var $category = "Fixing";
    var $desc = "fix css file";
    
    /**
     * exec the command
     */
    public function exec($command, $path=null, $ctrl=null)
    { 
        Logger::info("fix css file");
        if (!empty($path) && file_exists($f = realpath($path))){
            $ext = igk_io_path_ext($f);
            if ($ext!="pcss"){
                return -1;
            } 
            $invoke = function(){
            $ctrl = new ExpressionHandler();
            $def = [];
            $cl = [];
            $css_m = "fixlang";
            $prop = [];
            $root = [];
            $xsm_screen= [];
            $sm_screen= [];
            $xlg_screen= [];
            $xxlg_screen= [];
            include(func_get_arg(0));
            $o = "";
            foreach(get_defined_vars() as $b=>$n){
                if (is_object($n) || is_array($n) || empty($n) || in_array($b, self::ignore_list()))
                {
                    continue;
                }
                $o.= "\$".$b ." = ".self::ValueToString($n) . ";".PHP_EOL;
            }
            foreach(compact(
                "def", "xsm_screen", "sm_screen",
                "xlg_screen",
                "xxlg_screen",
                "prop", 
                "cl") 
                as $k=>$m){
                ksort($m);
                $o .= " // + | definition for \$".$k. " ".PHP_EOL;
                $_isd = 0;
                foreach($m as $t=>$s){
                    if (strpos($t, $css_m)===0){
                        if (!empty($mm = str_replace($css_m, "", $t)))
                            $t = "\$css_m . '".$mm."'";
                        else 
                            $t = "\$css_m";
                    } else {
                        $t = "'".$t."'";
                    }
                    $o .= "\${$k}[{$t}] = \"{$s}\";".PHP_EOL;                    
                    $_isd = 1;
                }
                if ($_isd)
                $o .= PHP_EOL;
            }   
            return $o;             
        };
        $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
        $o = $invoke($f);
        self::ignore_list(true);
            $builder = new PHPScriptBuilder();
            $builder->desc("lang file")->type("function")
            ->file(basename($f))
            ->author($author)
            ->defs($o);
            igk_io_w2file($f, $builder->render()); 
            Logger::success("fix css file: ".$f);
        } else {
            Logger::danger("file not found or not defined");
        }

    }
    public static function ignore_list($clear=false){
        if ($clear){
            igk_environment()->set(__METHOD__, 0);
            return null;
        }
        if ($g = igk_environment()->get(__METHOD__)){
            return $g;
        }
        $g = ["css_m"];
        igk_environment()->set(__METHOD__, $g);
        return $g;
    }
    static function ValueToString($v){
        if (is_string($v)){
            return '"'.$v.'"';
        }
        return $v;
    }
}

class ExpressionHandler{
    private static function get_arg_s($arguments){
        return array_map(function($v){
            if (is_string($v)){
                return '"'.$v.'"';
            }
            return $v;
        }, $arguments );
    }
    public static function __callStatic($name, $arguments)
    {
        return "\".\$ctrl::".$name."(".implode(", ",self::get_arg_s($arguments)).").\"";
    }
    public static function __call($name, $arguments)
    {
        return "\$ctrl->".$name.("");
    }
}