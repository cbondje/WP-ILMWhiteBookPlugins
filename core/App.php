<?php

namespace ILYEUM; 

require_once(__DIR__."/core.php");

class App{
    private static $sm_instance;
    public static function boot(){
        spl_autoload_register(function($n){           
            $f = str_replace("\\", "/", $n);
            if (strpos($f, "ILYEUM/")===0){
                $f = substr($f, 7); 
            } 
            if(file_exists($f=__DIR__."/".$f.".php")){
                include_once($f);
                if(!class_exists($n, false) && !interface_exists($n, false)
                && !trait_exists($n, false)
                    ){
                    die("file loaded but not content class {$n} definition");
                }
                return 1;
            } 
            return 0;
        });     

        $sm_instance = new app();
        return $sm_instance;
    }
    
    private function __construct(){

    }
}