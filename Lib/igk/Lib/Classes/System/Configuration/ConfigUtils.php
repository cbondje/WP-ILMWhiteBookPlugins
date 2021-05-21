<?php

namespace IGK\System\Configuration;

use IGKCSVDataAdapter;

class ConfigUtils{
    private static function LoadDataFile($file, & $data){
        $f=IGKCSVDataAdapter::LoadData($file, [
            "separator"=>","
        ]);
        if($f !== null){
            foreach($f as  $v){
                $data[$v[0]]=trim(igk_getv($v, 1));
            }
        }
    }
    public static function LoadData($file, & $data, $autocontext=true){
        self::LoadDataFile($file, $data);
        if ($autocontext && ($ctx = igk_environment()->context()) != "web"){
            $dir = dirname($file);            
            $ext = igk_io_path_ext($file);
            if (file_exists($fc = $dir ."/".implode(".",[igk_io_basenamewithoutext($file), $ctx, $ext]))){
                    self::LoadDataFile($fc, $data);
            }
        }
    }
}