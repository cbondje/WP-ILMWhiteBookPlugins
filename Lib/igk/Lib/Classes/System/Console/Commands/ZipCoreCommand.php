<?php
namespace IGK\System\Console\Commands;

use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;

class ZipCoreCommand extends AppExecCommand{

    var $command = "--zipcore";

    var $desc = "zip balafon core";


    public function exec($command, $path=null){
       
        if (!extension_loaded("zip") && !function_exists('zip_open')){
            Logger::danger("zip utility function not found");
            return -1;
        }
        $fname = "/balafon.".IGK_VERSION."-".date("Ymd").".zip";
        if ($path == null){
            $path = getcwd().$fname;
        } else if (is_dir($path)){
            $path = $path.$fname;
        }
        if (igk_sys_zip_core($path)){
            Logger::print("out file : ".$path);
            Logger::success("zip complete");
        }
    }
}