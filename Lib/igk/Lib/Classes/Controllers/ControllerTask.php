<?php 

namespace IGK\Controllers;

use Exception;
use IGK\System\Http\Route;
use IGK\System\Http\RouteActionHandler;
/**
 * controller task : routable controller action
 * note: a Balafon Page is a ControllerTask
 * @package IGK\Controllers
 */
abstract class ControllerTask{
    
    protected $controller;

    /**
     * 
     * @var RouteActionHandler
     */
    protected $route;

    public function __construct($controller, ?RouteActionHandler $route=null)
    {
        $this->controller = $controller;
        $this->route = $route;  
        $this->init(); 
    }
    protected function init(){
        if (!$this->route){
            Route::LoadConfig($this->controller);
        }
    }
    /**
     * index start entry task
     * @return mixed 
     */
    abstract function index();
    
    public function __call($name, $args)
    {
        $n = $name."_".igk_server()->REQUEST_METHOD;
        if (method_exists($this, $n)){
            return $this->$n(...$args);
        }
        array_unshift($args, $name);
        return $this->index(...$args);
    }


}