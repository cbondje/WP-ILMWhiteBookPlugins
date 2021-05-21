<?php
namespace IGK\System\Html\Dom;

/**
 * dom factory to handle custom node method extension
 * @package 
 */
class Factory{
    static $sm_instance;
    private $m_actions;
    private function __construct(){        
        $this->m_actions = [];
    }
    public static function getInstance(){
        if (self::$sm_instance === null){
            self::$sm_instance = new self();
        }
        return self::$sm_instance; 
    }

    public static function Register($tagname, $funcName, callable $callback){
        if (!isset(self::getInstance()->m_actions[$tagname])){
            self::getInstance()->m_actions[$tagname] = [];
        }
        self::getInstance()->m_actions[$tagname][$funcName] = $callback;
    }
    public static function __callStatic($name, $arguments)
    {
        $funcName = $arguments[0];
        $callable = $arguments[1];
        self::Register($name, $funcName, $callable);
    }
    public function handle($name, $funcName){
        return isset($this->m_actions[$name][$funcName]);
    }
    public function invoke($name, $funcName, $arguments){
        if ($callback = $this->m_actions[$name][$funcName]){
            return call_user_func_array($callback, $arguments);
        }
        return null; // $callback(...$arguments); // call_user_func_array()
    }
}

