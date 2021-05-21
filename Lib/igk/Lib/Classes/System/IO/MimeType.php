<?php

namespace IGK\System\IO;


class MimeType{
    public static function FromExtension($ext){
        $mime = igk_getv(igk_header_mime(),$ext, "text/plain");
        return $mime;
    }
    public static function FromType($type, $ext){
        static $define ;
        if ($define===null){
            $define = [];
        }
        return igk_getv($define, $type, $ext);
    }
}