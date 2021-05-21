<?php

namespace IGK\System\Console;

use IGK\System\Configuration\XPathConfig;
use Closure;
use Exception;
use IGKApp;
use IGKXmlNode;
use stdClass;
use Throwable;

///<summary>represent Balafon CLI console Application</summary>
class App{
    const GREEN = "\e[1;32m";
    const YELLOW = "\e[0;33m";
    const YELLOW_B = "\e[1;33m";
    const YELLOW_I = "\e[3;33m";
    const RED = "\e[1;31m";
    const BLUE = "\e[0;34m";
    const BLUE_B = "\e[1;34m";
    const BLUE_I = "\e[3;34m";
    const PURLPLE = "\e[3;35m";
    const AQUA = "\e[3;36m";
    const END = "\e[0m";
    /**
     * application version
     * @var string
     */
    public $version = "0.1.0";
    /**
     * available command
     * @var mixed
     */
    public $command = [];
    /**
     * setup the base command
     * @var mixed
     */
    protected $basePath;

    /**
     * store application configuration
     */
    protected $configs;

    public function getConfigs(){
        return $this->configs;
    }
    /**
     * run application command line
     * @param array $command 
     * @return void 
     * @throws Exception 
     */
    public static function Run($command=[], $basePath=null){ 
        $app = (new static);
        if ($basePath === null){
            $basePath = getcwd();
        }
        igk_environment()->set("app_context", "balafon");
        $app->basePath = $basePath;
        Logger::SetLogger(new ConsoleLogger($app));
        $app->boot();

        if ($command_args = AppCommand::GetCommands()){ 
            foreach($command_args as $c){
                $t = [];
                $callbable = null;
                if ($c instanceof AppCommand ){
                    $callbable = [$c, "run"];
                } else {
                    $callbable = $c->callable;
                }
                $command[$c->command] = [
                    $callbable,
                    $c->desc,
                    $c->category
                ];
 
            }
        }


        $handle = [];
        foreach($command as $n=>$b){
            if(count($c = explode(",", $n))>1){
                array_map(function($i)use(& $handle, $b){
                    $handle[trim($i)] = $b; 
                }, $c);
            }else {
                $handle[trim($n)] = $b;
            }
        }
        ksort($command);
        $app->command = $command;
        
        $tab = array_slice(igk_server()->argv, 1);

        $command = igk_createobj();
        $command->app = $app;
        $command->command = $tab;
        $command->{"exec"}= null;
        $command->storage = array(); // function storage
        $command->waitForNextEntryFlag = false;
        $command->options = new stdClass();
        $action = null;
        $args = [];

        foreach($tab as $v){
             
            if ($command->waitForNextEntryFlag){
                $action($v, $command, []);
                $command->waitForNextEntryFlag = false;
            }
            if ( isset($handle[$v]) ){
                $action = is_callable($handle[$v])?$handle[$v]: $handle[$v][0];
                $action($v, $command, implode(":", array_slice($c,1)));
            }
            else {
                $c = explode(":", $v); 
                if (isset($handle[$c[0]]))
                {
                    if (isset($handle[$v])){
                        $action = is_callable($handle[$v])?$handle[$v]: $handle[$v][0];
                        $action($v, $command, implode(":", array_slice($c,1)));
                    }
                }else {

                    if ($c[0][0]=="-"){
                        $command->options->{$c[0]} = implode("", array_slice($c,1));
                    }
                    else
                        $args[] = $v;
                }
            }
        }

        try{
            $action = $command->exec; //($v, $command, implode(":", array_slice($c,1)));
            if ($action){
                return $action($command , ...$args); 
            }else{
                Logger::danger("no action found");
            }
        } catch (Exception $ex){
            $app->print(self::gets(self::RED, "error:"). $ex->getMessage());
        }
        catch (Throwable $ex){
            Logger::danger("error: throw: ".$ex->getMessage());
            Logger::print($ex->getFile().":".$ex->getLine());
            igk_show_exception_trace($ex->getTrace(), 0);
        }
        $app->showHelp();
    }
    protected function boot(){
        define('IGK_FRAMEWORK_ATOMIC', 1); 
        if (file_exists($configFile = $this->basePath."/balafon.config.xml")){
            $c = igk_conf_load_file($configFile, "balafon");
            $this->configs= new XPathConfig($c); 
            $this->print_debug("configuration loaded: ".$configFile);   
            // $this->print_debug($this->getLogFolder());       
            $c = $this->configs->get("env");
          
            if ($c)
            foreach($c as $env){
                defined($env->name) || define($env->name, 
                preg_match("/_DIR$/", $env->name)? realpath($env->value) : 
                    $env->value
                ); 
            }
             
        }
        defined('IGK_APP_DIR') || define("IGK_APP_DIR", getcwd());
        defined('IGK_BASE_DIR') || define("IGK_BASE_DIR", getcwd());
        // setup the log folder
        if (!defined('IGK_LOG_FILE') && ($logFolder  = $this->getLogFolder())){
            define('IGK_LOG_FILE', $logFolder."/.".IGK_TODAY.".cons.log");
        }
        igk_loadlib(dirname(__FILE__)."/Commands");
        date_default_timezone_set('Europe/Brussels');
        IGKApp::InitSingle(); 
        if (defined('IGK_DOCUMENT_ROOT'))
            igk_server()->IGK_DOCUMENT_ROOT = realpath(constant('IGK_DOCUMENT_ROOT'));
         
        igk_hook("console::app_boot", $this);
    }
    public function print(...$text){
        foreach($text as $s){ 
            echo $s. PHP_EOL;
        }
    }
    public function print_debug(...$text){    
        if (igk_is_debug())
            $this->print(...$text); 
    }
    public function showHelp(){
        $this->print("BALAFON CLI-UTILITY");;
        $this->print("Author: C.A.D BONDJE DOUE");
        $this->print(sprintf("Version:  %s", self::gets(self::GREEN, $this->version)));
        $this->print(""); 
        $this->print(self::gets(self::YELLOW, "Usage:"));
        $this->print("\tbalafon [command] [options] [arguments]");
        $this->print("");
        $this->print("");

        $groups = [];
        array_walk($this->command, function($c,$key)use(& $groups){
            
            $cat = igk_getv($c, 2, "");
             
            if (!isset($groups[$cat]))
                $groups[$cat] = [];
            $groups[$cat][$key] = $c;
        } );

        ksort($groups); 
        //igk_wln("groups: ", array_shift($groups));
        $key=key($groups);
        while((count($groups)>0) && ( $g = array_shift($groups))){
            if (!empty($key)){
                Logger::print("groups: ".$key);
                Logger::print("");
            }

        foreach($g as $n=>$c){
                $s = " ".self::GREEN.$n."\e[0m \r\t\t\t\t";
            
                if (is_array($c) && is_array($c[1])){
                    $s .= (igk_getv($c[1], "desc"));
                } 
                else  if (! ($c instanceof Closure)){
                    $s.= (igk_getv($c, 1));
                }

                $this->print($s."\n");
            }
            $key=key($groups);
        }
        $this->print("");
    }
    public function getLogFolder(){
        if($this->configs){
            return $this->configs->get("logFolder");
        }
    }
    public static function gets($color, $s){
        return $color.$s."\e[0m";
    }
}