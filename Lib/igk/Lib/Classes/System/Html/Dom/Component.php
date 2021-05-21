<?php

namespace IGK\System\Html\Dom;

class Component{
    public static function __callStatic($name, $arguments)
    {
        $fc = function($host)use($name, $arguments){
            
            array_unshift($arguments, $name, $host);
            return call_user_func_array([static::class, "viewComponent"], $arguments);
        };
        return $fc;
    }
    public static function viewComponent($name, $host, $controller, $args){
        if (file_exists($file = $controller::getComponentsDir()."/".$name.".phtml")){
            return $controller->loader->loadComponent($file, $host, ...$args);
        }
        else {
            die("component:".$name." not found");
        }
    }   
}