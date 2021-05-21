<?php
namespace IGK\Tests;

use IGK\Controllers\BaseController;

class Utils{
    public static function CreateController($classname){
    
        if (class_exists($classname) && is_subclass_of($classname , BaseController::class) && !igk_reflection_class_isabstract($classname)){
        
            $o = new $classname();
            if (!isset(igk_environment()->AutoLoad[$classname])){ 
                $o::register_autoload();
                igk_environment()->setArray("AutoLoad", $classname, 1); 
            }            
            return $o;
        } 
        throw new \Exception("Class [ {$classname} ] not found");
    }
    public static function GetDefaultController($classname){
        $controller = $app = igk_get_defaultwebpagectrl();
        if (!$app || (get_class($app)!== $classname))
            $controller = Utils::CreateController($classname);
        return $controller;
    }

    public static function CheckControllerDataBase($test, $controllerClass, $model=null){        
        $controller = $controllerClass;
        if (is_string($controllerClass) && !($controller = self::CreateController($controllerClass))){
            $test->fail("controller not created");
            return false;
        }
        $model = $model ?? $controller->getDb(); 
        if ($tb=igk_db_get_ctrl_tables($controller)){
            foreach($tb as $k){                
                $test->assertTrue(
                        $model->select_count(null,$k) !== -1                        
                        , "Table $k not present");
            } 
        }else {
            $test->fail("no tables : ".get_class($controller));
        }

    }
    public static function PostView(BaseController $controller, $view="default", $params=null){
        self::SendView($controller, $view, $params, "POST");
    }
    public static function GetView(BaseController $controller, $view="default", $params=null){
        self::SendView($controller, $view, $params, "GET");
    }
    public static function SendView(BaseController $controller, $view="default", $params=null, $method="GET"){
        igk_server()->REQUEST_METHOD = $method;
        $controller->loader->View($view, ["params"=>self::GetParams($params)]);
    }
    private static function GetParams(...$params){
        if (is_string($params)){
            return explode("/", $params);
        }
        if (is_array($params)){
            return array_values($params);
        }
        return array();
    }
    
}