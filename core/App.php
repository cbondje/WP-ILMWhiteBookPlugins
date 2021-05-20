<?php

namespace ILYEUM;

use ILYEUM\wp\actions;

require_once(__DIR__."/core.php");
require_once(__DIR__."/ConfigHandler.php");
class App{
    private static $sm_instance;

    private $configs; 
    private $plugins_file;

    public function getPluginFile(){
        return $this->plugins_file;
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
        if (file_exists($file = implode(DIRECTORY_SEPARATOR, [ILM_BASE_DIR, "configs/{$name}"]))){
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
        self::$sm_instance->plugins_file = $file;
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
        add_action('widgets_init', function() use ($ms){
			$tab = $this->configs->wp_widgets;
			foreach($tab as $k){
				register_widget($k);
			}
		}); 
    }
}