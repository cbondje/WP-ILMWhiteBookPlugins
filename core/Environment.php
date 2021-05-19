<?php
// @author: C.A.D BONDJE DOUE
// @file: Environment.php
// @desc: 
// @date: 20210517 09:09:06
namespace ILYEUM;


class Environment{
    static $sm_instance;
    private $m_envs;

    private function __construct()
    {
        $this->m_envs = [];
    }
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
        if (!($g = ilm_getv($class_i, $classname))){
            $g = new $classname();
            $class_i[$classname] = $g;
        }
        return $g;
    }
    public function is($name){

        return false;
    }
    public function __get($n){
        return ilm_getv($this->m_envs, $n);
    }
    public function __set($n,$v){
        if ($v===null){
            unset($this->m_envs[$n]);
        }else{
            $this->m_envs[$n] = $v;
        }
    }
    public function get($n, $default=null){
        if ($this->m_envs && key_exists($n, $this->m_envs)){
            return $this->m_envs[$n];
        }
        return $default;
    }
    public function set($n, $value){
        $this->$n = $value;
    }
}