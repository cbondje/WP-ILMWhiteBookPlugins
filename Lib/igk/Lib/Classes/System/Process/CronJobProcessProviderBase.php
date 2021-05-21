<?php

namespace IGK\System\Process;

use IGK\Controllers\BaseController;

/**
 * represent the base providers
 * @package IGK\System\Process
 */
abstract class CronJobProcessProviderBase{
    protected $fields = [];
    public function treat($options){
        return igk_get_robjs($this->fields, 0, $options);
    }
    public function exec($name, $options, ?BaseController $ctrl=null){
        $options= $this->treat($options);
        if ($ctrl!=null){
            if (!($ctrl = igk_getctrl($ctrl,false)) || 
                !file_exists($file = $ctrl::classdir()."/CGI/Crons/".$name.".php")
                ){            
                return false;
            }
            
            $fc=function($ctrl){
                extract((array)func_get_args(2));
                return include(func_get_arg(1));
            };
            return $fc($ctrl, $file, $options);            
        }
        $dir = igk_io_sys_classes_dir();
        if (file_exists($file = $dir ."/CGI/Crons/".$name.".php")){
            $fc=function(){
                extract((array) func_get_arg(1));
                return include(func_get_arg(0));
            }; 
            return $fc($file, $options);            
        }
        return false;
    }
}