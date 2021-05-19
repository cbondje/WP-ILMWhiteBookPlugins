<?php
// @author: C.A.D BONDJE DOUE
// @file: Environment.php
// @desc: 
// @date: 20210517 09:09:06
namespace ILYEUM;


class Environment{
    static $sm_instance;
    public static function getInstance(){
        if (self::$sm_instance ===null){
            self::$sm_instance = new Environment();
        }
        return self::$sm_instance;
    }
    public static function getClassInstance($classname){
        static $class_i;

        if ($class_i === null){
            $class_i = [];
        }
        if (!($g = igk_getv($class_i, $classname))){
            $g = new $classname();
            $class_i[$classname] = $g;
        }
        return $g;
    }

}