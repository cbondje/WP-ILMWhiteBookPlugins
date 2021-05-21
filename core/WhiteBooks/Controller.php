<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Controller.php
// @desc: 
// @date: 20210517 09:20:36
namespace ILYEUM\WhiteBooks;
use  ILYEUM\WhiteBooks\Loader;
use function ilm_environment as environment;
/**
 * represent the plugins controller
 */
class Controller{
     /**
     * default entry uri
     */
    const ENTRY_URL = "/wbook/";

    public function getUri(){
        return "";
    }
    public function view(){
        return $this->getLoader()->view(...func_get_args());
    }
    public function __get($n){
        if (method_exists($this, $fc="get".$n)){
            return $this->$fc();
        }
    } 
    public function getLoader(){
        return environment()->getClassInstance(Loader::class);
    }
    public function getDeclaredDir(){
        return __DIR__;
    }
    public function getDeclaredFile(){
        return __FILE__;
    }
}