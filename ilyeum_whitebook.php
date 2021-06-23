<?php
/*
* Plugin Name: ILYEUM - White Book
* Description: White book process plugins
* Plugin URI: //ilyeum.com/plugins
* Author: ilyeum.com , C.A.D. BONDJE DOUE
* Version: 0.1.0
* Author URI: //ilyeum.com
*/

 
if (file_exists($f = __DIR__."/core/App.php" ))
{
    // $_SERVER['REQUEST_METHOD'] = "POST";
    // require_once("/Volumes/Data/wwwroot/sites/8901/src/public/wp-blog-header.php");
    // ---------------------------------------------
    // | bootstrap core puglins
    // ---------------------------------------------
    require_once($f);
    unset($f);
    ILYEUM\App::boot(__FILE__);   
}