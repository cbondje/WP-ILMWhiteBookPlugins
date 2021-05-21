<?php

/**
 * google asset management
 * @package 
 */
class GoogleAssets{
    public static function Icon($name):callable{
        return function($n)use($name){
            $n->google_icons(strtolower(str_replace(" ", "_", $name)));
        };
    } 
}