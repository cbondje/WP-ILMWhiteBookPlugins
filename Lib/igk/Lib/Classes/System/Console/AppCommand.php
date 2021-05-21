<?php


namespace IGK\System\Console;

use ReflectionClass;

abstract class AppCommand {

    const ENV_KEY = "balafon/command_args";

    /**
     * register command name
     * @var mixed
     */
    var $command;

    /**
     * register callable
     * @var mixed
     */
    var $callable;

    /**
     * register description
     * @var mixed
     */
    var $desc;

    /**
     * define the command category
     * @var mixed
     */
    var $category;
    
    public static function Register($command, callable $callable, $desc=""){
        $o = igk_createobj();
        $o->command = $command;
        $o->description = $desc;
        $o->callable = $callable;
        igk_push_env(self::ENV_KEY, $o);
    }
    public static function GetCommands(){
        static $loaded_command = null;
        if ($loaded_command === null){

            $loaded_command = [];

            foreach(get_declared_classes() as $cl){
                if (is_subclass_of($cl, __CLASS__)){
                    if (!(new ReflectionClass($cl))->isAbstract()){
                        $b = new $cl();
                        if (empty($b->command)){
                            die("command : ".$cl. " not specified");
                        } 
                        $loaded_command[$b->command] = $b; 
                    }
                }
            }
            if (file_exists($file = igk_io_cachedir()."/.command.list.php")){
                $list = include($file);
                foreach($list as $ctrl=>$b){
                    if ($m = igk_getctrl($ctrl, false)){
                        $m::register_autoload();
                        foreach($b as $c){
                            $b = new $c();
                            if (empty($b->command)){
                                die("command : ".$c. " not specified");
                            } 
                            $loaded_command[$b->command] = $b; 
                        }
                    }
                }
            }
        }

        return  array_merge($loaded_command,  igk_environment()->get(self::ENV_KEY, [])); 
    }
    /**
     * execute command
     * @param mixed $args 
     * @param mixed $command 
     * @return mixed 
     */
    public function run($args, $command){
        if ($fc = $this->callable){
            $argument = func_get_args();
            return $fc(...$argument);
        }
    }
    /**
     * help view
     * @return void 
     */
    public function help(){
        Logger::info($this->command);
        Logger::print($this->desc);
    }
}