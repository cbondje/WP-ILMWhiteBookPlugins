<?php

namespace  IGK\System\Console;

abstract class AppExecCommand extends AppCommand{
    public function __construct()
    {
        $this->handle = [$this, "exec"];
    }
    public function run($args, $command)
    {
        if ($this->handle){

            $command->exec = function($command){
                if (property_exists($command->options, "--help")){
                    return $this->help();
                }
                $fc = $this->handle;
                $args = func_get_args();
                return $fc(...$args);

            };
        }
    }
    public abstract function exec($command);
}