<?php

namespace ILYEUM;

use IGKApp;
use ILYEUM\wp\actions;
if (!defined("IGK_FRAMEWORK")){
    define("IGK_APP_DIR", realpath(__DIR__."/../")."/application");
    if (isset($_SERVER["ENVIRONMENT"]) && ($_SERVER["ENVIRONMENT"] == "development")){
        defined('IGK_DEFAULT_FOLDER_MASK') || define('IGK_DEFAULT_FOLDER_MASK', 0777);
        defined('IGK_DEFAULT_FILE_MASK') || define('IGK_DEFAULT_FILE_MASK', 0775);
    }
    define('IGK_NO_WEB', 1);
    defined('IGK_BASE_DIR') || define('IGK_BASE_DIR',realpath(__DIR__."/../"));
    defined('IGK_NO_TEMPLATE') && define('IGK_NO_TEMPLATE', 1);
    if (file_exists($fc = dirname(__FILE__)."/../Lib/igk/igk_framework.php")){
        require_once($fc);
    } else {
        die("require framework missing");
    }
}
require_once(__DIR__."/core.php");
require_once(__DIR__."/functions.php");
require_once(__DIR__."/ConfigHandler.php");




class App{
    private static $sm_instance;

    private $configs; 
    private $plugin_file;
    private $core_app;


    public function getPluginFile(){
        return $this->plugin_file;
    }
    public function getConfigs(){
        return $this->configs;
    }
    public function __get($n){
        if (method_exists($this, $fc = "get".$n)){
            return $this->$fc();
        }
    }

    public static function getInstance(){
        return self::$sm_instance;
    }
    public function loadJsonConfig($name){
        if (file_exists($file = implode(DIRECTORY_SEPARATOR, [self::$sm_instance->configs->config_dir, "{$name}"]))){
            return json_decode(file_get_contents($file));
        }
        return null;
    }
    /**
     * boot plugins
     * @return App 
     * @throws TypeError 
     */
    public static function boot($file){
        spl_autoload_register(function($n){           
            $f = str_replace("\\", "/", $n);
            $ns = self::$sm_instance->configs->plugin_entry_ns;
   
            if (strpos($f, $ns."/")===0){
                $f = substr($f, 7); 
            } 
            if(file_exists($f=__DIR__."/".$f.".php")){
                include_once($f);
                if(!class_exists($n, false) && !interface_exists($n, false)
                && !trait_exists($n, false)
                    ){
                    die("file loaded but not content class {$n} definition");
                }
                return 1;
            } 
            return 0;
        }); 
   

        self::$sm_instance = new static;
        self::$sm_instance->plugin_file = $file;
        self::$sm_instance->initialize();
        return self::$sm_instance;
    }
    /**
     * .ctrl
     * @return void 
     */
    private function __construct(){
    }
    private function initialize(){        
        $configs = [];
        require(ILM_WHITE_BOOK_DIR."/Configs/config.php");
        $this->configs = new ConfigHandler($configs); 
        
        foreach([
            \ILYEUM\WhiteBooks\Admin\Manager::class,
            \ILYEUM\WhiteBooks\Pages\Init::class,
            ] as $c){
                ilm_environment()->getClassInstance($c);
        }  
        // | init widget
        add_action('widgets_init', function(){
			$tab = $this->configs->wp_widgets;
			foreach($tab as $k){
				register_widget($k);
			}
		}); 
        igk_environment()->basedir = rtrim(plugin_dir_path($this->plugin_file), "/");        
        $this->core_app = IGKApp::InitNewInstance();
    }
}

 