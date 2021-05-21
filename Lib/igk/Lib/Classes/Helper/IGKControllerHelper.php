<?php
namespace IGK\Helper;
class IGKControllerHelper{
    public static function GetViewFiles($controller){
        $dir=$controller->getViewDir();
        $t=igk_io_getfiles($dir, function($f){
            return preg_match("/^[^\.](.+)?\.phtml$/", basename($f));
        }, true);
        $ln=strlen($dir) + 1;
        return [$t, $ln];
    }
}