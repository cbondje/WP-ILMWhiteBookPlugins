<?php

// __dir__

$web = "/index.php";


// if (!defined('IGK_PHAR_REQUEST') &&
// isset($_SERVER["REQUEST_URI"]) && ($rui = $_SERVER["REQUEST_URI"]) && ($rui!='/') && ($rui!= $web))
// {
	// define('IGK_PHAR_REQUEST', __FILE__);
	// $args = explode('?', $rui);
	// $furi = $args[0];
	// $query= count($args)>1?$args[1]:null;//igk_getv($args,1);
	// $ln = strlen($furi);
	// if (($ln>1)&&( $furi[$ln-1]=='/')){
		// $furi = substr($furi,0, $ln-1);
	// }
    // if (file_exists($furi) || file_exists($furi = $dir.$furi."/index.php")){
		// if (preg_match("/\.ph(p|tml)$/i",$furi)){
			// include($furi);
			// exit();
		// }
        // IGKPhar::Cacheout();
        // header('Content-Type: text/javascript');
        // echo file_get_contents($furi);
        // exit();
    // }
	// // echo (getcwd()." : failed : ".$furi . " ".file_exists($furi).
	// // " -- ". file_exists(getcwd().$furi) );
	// 
// }

//present cache out lib
final class IGKPhar
{
	public static function Cacheout($second=3600){
		$ts = gmdate("D, d M Y H:i:s", time() + $second) . " GMT";
		header("Expires: {$ts}");
		header("Pragma: cache");
		header("Cache-Control: max-age={$second}, public");
	}

	//----------------------------------------------------------------------------
	//export setting
	//----------------------------------------------------------------------------
	///<summary> check if file exists on current context</summary>
	///<param name="file">the relative path to phar file</param>
	public static function fileExists($file){
		return file_exists($file);
	}
	public static function runningDir(){
		return dirname(Phar::running());
	}
}


//handle index request

define("IGK_PHAR_CONTEXT",1);
define("IGK_INDEX_FILE", __FILE__);
define('IGK_APP_DIR', $dir);
define('IGK_NO_TRACELOG',1);
include_once('Lib/igk/igk_framework.php');
define("IGK_MAIN_FILE", igk_html_uri(PHar::running(false)));


 
// handle key
$key = 'phar://handlerequest';
$uri = igk_io_request_uri();
if (!empty($uri) && ($uri!= $web) && ($uri !='/') && !igk_get_env($key)){
    igk_set_env($key, $uri);
    igk_sys_handle_request($uri);
    igk_set_env($key, null);
}
igk_sys_render_index(__FILE__);