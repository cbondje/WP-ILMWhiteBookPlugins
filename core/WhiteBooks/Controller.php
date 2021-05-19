<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Controller.php
// @desc: 
// @date: 20210517 09:20:36
namespace ILYEUM\WhiteBooks;

/**
 * represent the plugins controller
 */
class Controller{
    public function getUri(){
        return "";
    }
    public function view(){
        $loader = function(){
            extract(array_slice(func_get_args(), 1));
            include(func_get_arg(0));
        };
        if (file_exists($fc = __DIR__."/Views/".func_get_arg(0))){
            $loader($fc, ...array_slice(func_get_args(),1)) ;
        }
    }
}