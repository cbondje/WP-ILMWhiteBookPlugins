<?php
// @author: C.A.D BONDJE DOUE
// @file: Request.php
// @desc: 
// @date: 20210517 10:55:05
namespace ILYEUM;

use function ilm_getv as getv;

class Mime{
    static $sm_mimes = [
        "application/pdf"=>"pdf"
    ];
    public static function GetExt($mimetype){
        return getv(self::$sm_mimes, $mimetype, "txt");
    }
}