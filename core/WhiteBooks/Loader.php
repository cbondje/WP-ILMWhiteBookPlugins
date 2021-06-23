<?php 
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Loader.php
// @desc: 
// @date: 20210517 09:20:36
namespace ILYEUM\WhiteBooks;
use function ilm_getv as getv;
use function ilm_getctrl as getctrl;

class Loader{
    public function view($name, ...$args){
       
        $loader = (function(){
            extract(getv(array_slice(func_get_args(), 1), 0, []));            
            // igk_trace();
            // igk_wln_e("to inc:".func_get_arg(0));
            include(func_get_arg(0));
        })->bindTo(getctrl());

        foreach([".phtml", ""] as $ext){
            if (file_exists($fc = __DIR__."/Views/".$name.$ext)){
                return $loader($fc, ...$args);
            }
        }
    }
}