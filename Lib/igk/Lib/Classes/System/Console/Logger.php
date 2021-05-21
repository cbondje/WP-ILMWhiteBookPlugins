<?php

namespace IGK\System\Console;

class Logger{
    static $sm_logger;

    public static function SetLogger($logger){
        self::$sm_logger = $logger;
    }

    public static function __callStatic($name, $arguments)
    {
        if (self::$sm_logger){
            self::$sm_logger->$name(...$arguments);
        }
    }
}