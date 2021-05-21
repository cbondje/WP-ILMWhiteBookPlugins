<?php

namespace IGK\System\Http;

use IGKException;

class Route
{
    static $sm_actions = [];
    static $sm_forceresolv;
    static $sm_name_list;

    protected $verb = ["GET", "POST"];

    protected $path = "";

    /**
     * load route config files
     * @param mixed $controller 
     * @return void 
     */
    public static function LoadConfig($controller)
    {
        if (file_exists($cf = $controller::configFile("routes"))) {
            $inc = function () {
                include_once(func_get_arg(0));
            };
            $inc($cf);
        }
    }
    public static function Uri_List($controller, $classpath)
    {
        self::LoadConfig($controller);
        $t = self::GetAction($classpath);
        return $t;
    }

    public static function GetMatchAll(): Route
    {
        static $sm_route;
        if ($sm_route === null) {
            $sm_route = new Route();
            $sm_route->path = "*";
            $sm_route->verb = ["*"];
        }
        return $sm_route;
    }

    ///<summary>register action provider</summary>
    /**
     * register action provider
     * @param mixed $actionClass 
     * @param mixed $path 
     * @param mixed $handleClass 
     * @return RouteActionHandler 
     */
    public static function RegisterAction($actionClass, $path, $handleClass)
    {
        if (!isset(self::$sm_actions[$actionClass])) {
            self::$sm_actions[$actionClass] = [];
        }
        $c = new RouteActionHandler($path, $handleClass, $actionClass);
        self::$sm_actions[$actionClass][] = $c;
        self::$sm_forceresolv = 1;
        self::$sm_name_list = [];
        return $c;
    }
    ///<summary>get action Provider</summary>
    public static function GetAction($actionClass)
    { 
        return igk_getv(self::$sm_actions, $actionClass);
    }
    public static function __callStatic($name, $arguments)
    {
        $verbs = explode('|', 'POST|GET|STORE|HEAD|PUT');

        if (in_array($v = strtoupper($name), $verbs)) {
            $fc = static::RegisterAction(...$arguments);
            $fc->setVerb([$v]); 
            return $fc;
        }
        throw new IGKException("operation not allowed");
    }
    public static function GetRouteByName($name, $classPath = null)
    {
        $actions = null;
        if ($classPath!==null){            
            if ($ac = igk_getv(self::$sm_actions, $classPath)){
                $actions = [$ac];
            }else{
                $action = [];
            }
        }else{
            //search in all actions
            $actions = self::$sm_actions;
        }

        foreach ($actions as $actions) {
                          foreach ($actions as $a) {                 
                if ($name == $a->getName()) { 
                        return $a;
                    
                }
            }
        }       
        return null;
    }
}
