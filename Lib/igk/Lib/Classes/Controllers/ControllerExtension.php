<?php

namespace IGK\Controllers;

use Exception;
use Faker\Provider\Base;
use IGK\Models\Migrations;
use IGK\Models\ModelBase;
use IGK\Models\ModelEntryExtension;
use IGK\System\Console\Logger;
use IGK\System\Http\Route;
use IGK\System\Http\RouteActionHandler;
use IGK\System\IO\File\PHPScriptBuilder;
use IGKQueryResult;
use IGKResourceUriResolver;
use IGKSQLQueryUtils;
use Throwable;

abstract class ControllerExtension{
    /**
     * extends to get the base controller from class
     * @param BaseController $ctrl 
     * @return BaseController 
     */
    public static function ctrl(BaseController $ctrl){
        return $ctrl;
    }
    /**
     * resolv asset path
     * @param BaseController $ctrl 
     * @param mixed $path 
     * @return mixed 
     */
    public static function asset(BaseController $ctrl, $path){
        $f = implode("/", [$ctrl->getDataDir(), IGK_RES_FOLDER, $path]);
        if (!file_exists($f))
            return null;
        $t = IGKResourceUriResolver::getInstance()->resolve($f); 
        if (empty($t)){
            igk_wln_e(file_exists($f), "-".$t, $f);
        }
        return $t;
    }
    public static function baseUri(BaseController $ctrl){
        return igk_io_baseuri() === $ctrl->getAppUri()? 
	        $ctrl->getAppUri() : igk_io_baseuri();        
    }
    
    /**
     * return asset content if exists
     * @param BaseController $ctrl 
     * @param mixed $path 
     * @return string|false|void 
     */
    public static function asset_content(BaseController $ctrl, $path){
        $f = implode("/", [$ctrl->getDataDir(), IGK_RES_FOLDER, $path]);
        if (file_exists($f)){
            return file_get_contents($f);
        }
    }
    public static function configFile(BaseController $ctrl, $name){
        return self::configDir($ctrl). "/{$name}.php";
    }
    public static function configDir(BaseController $ctrl){
        return $ctrl->getDeclaredDir()."/Configs";
    }
  
    public static function uri(BaseController $ctrl, $name){
        return $ctrl->getAppUri($name);
    }

    public static function db_query(BaseController $ctrl, $query){
        $ad = igk_get_data_adapter($ctrl);
        return $ad->sendQuery($query);
    }
    public static function db_add_column(BaseController $ctrl, $table, $info, $after=null){
        $ad = igk_get_data_adapter($ctrl);         
        if (!$ad->grammar->exist_column($table, $info->clName)){
            if ($query = $ad->grammar->add_column($table, $info, $after)){                
                if ($r = $ad->sendQuery($query)){                    
                    if ($info->clLinkType){
                        $query_link = $ad->grammar->add_foreign_key($table, $info);
                        $ad->sendQuery($query_link);
                    }
                    return true;
                }
            }
        }  
    }
    public static function db_rm_column(BaseController $ctrl, $table, $info){
        $ad = igk_get_data_adapter($ctrl);
        $is_obj = is_object($info);
        if ($is_obj){
            $name = $info->clName;
        }else {
            $name = $info;
        } 
        if ($ad->grammar->exist_column($table, $name)){
            if ($is_obj && $info->clLinkType &&  
                ($query = $ad->grammar->remove_foreign($table, $name))){           
                $ad->sendQuery($query);
            }
            $query = $ad->grammar->rm_column($table, $name);
            return $ad->sendQuery($query);
        }
        return false;
    }

    public static function db_rename_column(BaseController $ctrl, $table, $column, $new_table){
        $ad = igk_get_data_adapter($ctrl);
        if ($v = $ad->grammar->exist_column($table, $column)){             
            if($query = $ad->grammar->rename_column($table, $column, $new_table)){
                return $ad->sendQuery($query);
            }
        } 
        return false;
    }

    public static function db_change_column(BaseController $ctrl, $table, $info){
        $ad = igk_get_data_adapter($ctrl);         
        if ($ad->grammar->exist_column($table, $info->clName)){
            if ($query = $ad->grammar->change_column($table, $info)){                
                if ($r = $ad->sendQuery($query)){                    
                    if ($info->clLinkType){
                        $query_link = $ad->grammar->add_foreign_key($table, $info);
                        $ad->sendQuery($query_link);
                    }
                    return true;
                }
            }
        }  
    }

    /**
     * resolv controller name key
     * @param BaseController $ctrl 
     * @param mixed $name array|string
     * @return mixed  array if name is array string name or null
     */
    public static function name(BaseController $ctrl, $name){
        if (is_string($name)){
            return implode("/", [get_class($ctrl), $name]);
        } else {
            if (is_array($name)){
                $cl = get_class($ctrl);
                return array_map(function($c)use($cl){
                    return implode("/", [$cl, $c]);
                }, $name);
            }
        }
    }

    /**
     * return notify key name
     */
    public static function notifyKey(BaseController $ctrl, $name=null){
        return static::name($ctrl, "notify".($name ? "/".$name: ""));
    }
    /**
     * return system controller hook name
     */
    public static function hookName(BaseController $ctrl, $name=null){
        return static::name($ctrl, "hook".($name ? "/".$name: ""));
    }

    public static function seed(BaseController $ctrl, $classname=null){
        //get all seed class and run theme        
        if (igk_is_null_or_empty($classname)){
            $classname = "Database/Seeds/DataBaseSeeder";
            //$classname = igk_str_ns(implode("/", array_filter([$ctrl->getEntryNamespace(), $classname])));
         
        }else{
            //try to resolv class 
            if (file_exists($f = $ctrl->classdir()."/Database/Seeds/".$classname.".php")){
                $classname = implode("/", array_filter([$ctrl->getEntryNamespace(), "Database/Seeds/".$classname]));
            }

        }
        $ctrl::register_autoload();
        $g = self::ns($ctrl, $classname ); 
        if (class_exists($g)){
            Logger::info("run seed ".$classname);
            $o = new $g();
            return $o->run();
        }else{
            Logger::danger("class not found: ".$classname);
        } 
    }
    public static function migrate(BaseController $ctrl, $classname=null){
        
        if ($ctrl->getUseDataSchema()){
            $f = igk_db_load_data_schemas(igk_db_get_schema_filename($ctrl), $ctrl); 
            if ($m = igk_getv($f, "migrations")){ 
                try{
                    foreach($m as $t){
                        $t->upgrade(); 
                    }
                }
                catch(Exception $ex){
                    igk_wln_e("some error", $ex->getMessage());
                }
            }
        }

        $rgx = "/^[0-9]{8}_[0-9]{4}_(?P<name>(".IGK_IDENTIFIER_PATTERN."))/i";
        //get all seed class and run theme
        $dir = $ctrl->getSourceClassDir()."/Database/Migrations";
        $runbatch = 1;
        if (!$tab = igk_io_getfiles($dir, "/\.php/")){
            return 0;
        }
        sort($tab); 
        if ($m = Migrations::select_query(null,[
            "Columns"=>[
                ["Max(`migration_batch`) as max"]
            ]
        ])){
            $m = $m->getRows()[0]; 
            $runbatch = $m->max + 1;
        }
        foreach($tab as $file){
            $t = igk_io_basenamewithoutext($file);
            if (preg_match_all($rgx, $t, $tinf)){

                $name = $tinf["name"][0];
                $cb = $ctrl::ns("Database/Migrations/{$name}");
                include_once($file); 
                try{
                    if(!($cr = Migrations::select_row([
                        "migration_name"=>$t
                        ])) || ($cr->migration_batch==0))
                        {
                        Logger::info("init:".$t);
                        (new $cb())->up();
                            if (!$cr){
                            ($r = Migrations::create([
                                "migration_name"=>$t,
                                "migration_batch"=>$runbatch
                                ]) )?  
                                Logger::success("complete:".$t) :
                                Logger::danger("Failed to migrate: ".$t);
        
                            if (!$r){
                                return false;
                            }
                        }
                        else {
                            $r->migration_batch = $runbatch;
                            $r->update();
                        }
                    }
                    
                } catch( Throwable $tex){
                    Logger::print($tex->getMessage());
                    Logger::danger("failed to init: ".$t. ":".$tex->getMessage());
                }
            }

        }
    
    }
    public static function InitDataBaseModel(BaseController $ctrl){
        $c = $ctrl->getSourceClassDir()."/Models/";
        $tb = $ctrl->getDataTableInfo();
        $ns = igk_db_get_table_name("%prefix%", $ctrl);

        if (!file_exists($base_f = $c."ModelBase.php")){
            igk_io_w2file($base_f, self::GetDefaultModelBaseSource($ctrl));
        }
        foreach($tb as $k=>$v){
            //remove prefix
            $gs = !empty($ns) && strpos($k, $ns) === 0;
            $t =  $gs ? str_replace($ns, "", $k) : $k;
            $name = preg_replace("/\\s/", "_", $t);
            $name = implode("", array_map("ucfirst", array_filter(explode("_",$name))));
            //generate class name 
            
            $file = $c.$name.".php"; 
            if (($name != "Logginattempts") && file_exists($file)){
                continue;
            }
            $table = $k;
            if ($gs){
                $table = "%prefix%".$t;
            }

            igk_io_w2file($file, self::GetModelDefaultSourceDeclaration($name, $table, $v , $ctrl));
        }
    }

    public static function register_autoload(BaseController $ctrl){
        $k="sys://autoloading/".igk_base_uri_name($ctrl->getDeclaredDir());
        if(igk_get_env($k))
            return;
        igk_set_env($k, 1);
        $fc = function(){
            $args = func_get_args(); 
            return BaseController::Invoke($this, "auto_load_class", $args);
        };
        $fc = $fc->bindTo($ctrl);
        igk_register_autoload_class($fc);
    }
    public static function ns(BaseController $ctrl, $path){
        $cl = $path;
        if ($ns = $ctrl::Invoke($ctrl, "getEntryNamespace")){
            $cl = implode("/", array_filter([$ns,$cl]));
        }
        $cl = str_replace("/", "\\", $cl);
        return $cl;
    }
    /**
     * resolv class from controller entry namespace
     * @param BaseController $ctrl 
     * @param mixed $path 
     * @return string|string[]|null 
     */
    public static function resolvClass(BaseController $ctrl, $path){
        $cl = $ctrl::ns($path);
        $ctrl::register_autoload();    
        if (class_exists($cl)){
            return $cl;
        }
        return null;
    }
    public static function getAutoresetParam(BaseController $ctrl, $name, $default=null){
        $d = $ctrl->getParam($name, $default);
            $ctrl->setParam($name, null);
        return $d;
    }
    private static function GetModelDefaultSourceDeclaration($name, $table, $v, $ctrl){
        $ns =  self::ns($ctrl, "");
 
        $uses = [];
        $gc = 0;
        $extends = implode("\\", array_filter([$ns, "Models\\ModelBase"]));

        $c = $ctrl->getSourceClassDir()."/Models/";
        if( ($name!="ModelBase") && file_exists($c."/ModelBase.php")){
            $uses[] =  implode("\\", array_filter([$ns, "Models\\ModelBase"]));
            $gc = 1;
        }else {
            $uses[] = ModelBase::class;
        }
        $o = "\t/** \n\t */\n";
        $o .= "\tprotected \$table = \"{$table}\"; ".PHP_EOL;

        if (!$gc && $ctrl){
            $cl = get_class($ctrl);
            $uses[] = "$cl::class";
            $o .= "\t/**\n\t */\n";
            $o.= "\tprotected \$controller = {$cl}::class; ".PHP_EOL;
        }
        $key = "";
        foreach($v["ColumnInfo"] as $cinfo){
            if ($cinfo->clIsPrimary){
                $key = $cinfo->clName;
            }
        }
        if ($key!="clId"){
            $o .= "\t/**\n\t *override primary key \n\t */\n";
            $o .= "\tprotected \$primaryKey = \"{$key}\"; ".PHP_EOL;
        }
        $base_ns = implode("\\", array_filter([$ns, "Models"]));
        $builder = new PHPScriptBuilder();
        $builder->type("class")
        ->author(IGK_AUTHOR)
        ->extends($extends)
        ->name($name)
        ->namespace($base_ns)
        ->defs($o)
        ->uses($uses);

        return $builder->render(); 
    }
    private static function GetDefaultModelBaseSource(BaseController $ctrl){
        $o = "";
        $cl = "";
        if ($ctrl){
            $cl = get_class($ctrl);
        }
        $ns = implode("\\", array_filter([self::ns($ctrl, ""), "Models"])); 
        $o = "<?php ".PHP_EOL;
        if ($ns){
            $o .= "namespace $ns; ".PHP_EOL;
        }                
        $o .=  "use ".ModelBase::class." as Model;".PHP_EOL;
        if ($cl){
        $o .=  "use {$cl}::class;".PHP_EOL;
        }

        $o .= "\n\n/** \n */\n";
        $o .= "class ModelBase extends Model {".PHP_EOL;
         
        if ($cl){         
            $o .= "\t/**\n\t * Base source controller \n\t */\n";
            $o.= "\tprotected \$controller = {$cl}::class; ".PHP_EOL;
        }
        $o .= "}".PHP_EOL; 
        return $o;
    }

   

    public static function login(BaseController $ctrl, $user=null, $pwd=null, $nav=true) {
     
            $u = $user;
       
            // igk_wln_e("login, $user, $pwd" , $ctrl->User, "uri?".igk_app_is_uri_demand($ctrl, __FUNCTION__));

            if (!igk_environment()->viewfile && igk_app_is_uri_demand($ctrl, __FUNCTION__) && file_exists($file = $ctrl->getViewFile(__FUNCTION__, false))){
                $ctrl->loader->view($file, compact("u", "pwd", "nav"));
                return false;
            }  
            $c=igk_getctrl(IGK_USER_CTRL);
            $f=0; 
            if($ctrl->User === null){
                if(is_object($u)){
                    if(igk_is_array_key_present($u, array("clLogin", "clPwd"))){
                        $c->setUser($u);                 
                        $ctrl->checkUser(false);
                        $f=1;
                    } 
                }
                else{  
                    if($c->connect($u, $pwd)){
                        $ctrl->checkUser(false);
                        $f=1;
                    } 
                    if(!$f){
                        igk_notifyctrl("notify/app/login")->addErrorr("e.loginfailed");
                    }
                }
            }
            if($nav){
                if($f){
                    ($b=igk_getr("goodUri")) || ($b=$ctrl->getAppUri());
                    igk_navto($b);
                }
                else{
                    $b=igk_getr("badUri") ?? $ctrl->getAppUri();
                    if($b){
                        igk_navto($b);
                        igk_exit();
                    }
                }
            } 
            return $ctrl->User !== null;
    }
    public static function classdir(BaseController $controller){
        return BaseController::Invoke($controller, "getClassesDir");
    }

     ///<summary>check user auth demand level</summary>
    /**
    * check user auth demand level
    */
    public static function IsUserAllowedTo(BaseController $controller, $authDemand=null){
        $user = $controller->getUser();
        if($user === null){
            return false;
        }
        if($user->clLevel == -1)
            return true;
        return $user->auth($authDemand);
    }
    public static function getUser(BaseController $controller, $uid=null){
        $u = $uid === null ? igk_app()->session->getUser() : 
         igk_get_user($uid);
        return $u;
    }
    public static function checkUser(BaseController $controller, $nav=true, $uri=null){
        $r=true;
        $u=igk_app()->Session->User;
        $ku=$controller->User;       
        
        if($ku == null){
            if($u != null){
                $controller->User= $controller->initUserFromSysUser($u);
            }
            else
                $r=false;
        }
        if($nav && !$r){
            $m=igk_io_base_request_uri();
            $s="";
            $u=($uri == null ? $controller::uri(""): $uri);
            if(!empty($m)){
                $s="q=".base64_encode($m);
                $u .= ((strpos($u, "?") === false) ? "?": "&").$s;
            }
            igk_navto($u);
        }
        return $r;
    }
   ///<summary></summary>
    /**
    * 
    */
    public static function dropDb(BaseController $controller, $navigate=1, $force=false){
    
        $ctrl=$controller;
        
        $func=function() use ($ctrl){
            $rdb=$ctrl->getDb();
            if($rdb && method_exists($rdb, "onStartDropTable")){
                $rdb->onStartDropTable();
            }
            igk_raise_event("sys://db/startdroptable", $ctrl);
        };
        if ($ctrl->getCanInitDb()){
            
        
        if(!igk_getv($ctrl->getConfigs(), "clDataSchema")){
            $db=igk_get_data_adapter($ctrl, true);
            if(!empty($table = $ctrl->getDataTableName()) && 
                $ctrl->getDataTableInfo() && 
                $db && $db->connect()) {
                $table = igk_db_get_table_name($table, $ctrl);
                $func();
                $db->dropTable($table); // ctrl->getDataTableName());
                $db->close();
            }
        }
        else{
            $tb=$ctrl::Invoke($ctrl, "loadDataFromSchemas");
            $db=igk_get_data_adapter($ctrl, true);
            if($db){
                if($db->connect()){
                    $tables = igk_getv($tb, "tables");
                    $v_tblist= [];
                    foreach(array_keys($tables) as $k){
                        $v_tblist[$k]=$k;
                    }
                    $func(); 
				    $db->dropTable($v_tblist);
                    $db->close();
                }
            }
        }
    }
        if ($navigate){
            $controller->View();
            igk_navtocurrent();
        }
        return 1;
        

        // $n=igk_getr("n");
        // if($n != null){
        //     $ad =igk_get_data_adapter($controller, true); 
        //     $r =  $ad->sendQuery("Drop DataBase `".$n."`;"); 
        // }
        // if ($navigate){
        //     $controller->View();
        //     igk_navtocurrent();
        // } 
    }

     
    public static function logout(BaseController $ctrl,$navigate=1){
        igk_app_is_uri_demand($ctrl, __FUNCTION__);
        $ctrl->setUser(null);
        igk_getctrl(IGK_USER_CTRL)->logout();
        if($navigate)
            igk_navto($ctrl->getAppUri());
    }
    /**
     * init controller from function definition
     */
    public static function initDbFromFunctions(BaseController $controller){
        $tbname = igk_db_get_table_name($controller->getDataTableName(), $controller);
        if (empty($tbname)){
            return false;
        }
        $v_tab= $controller->getDataTableInfo();// ?? igk_db_get_table_info($tbname);
        
        if(!$v_tab)
            return; 
        $db=igk_get_data_adapter($controller, true);
        igk_hook(IGK_NOTIFICATION_INITTABLE, [$controller, $tbname, $v_tab]);
        try {
            $s=$db->createTable($tbname, $v_tab, null, null, $db->DbName);
            $controller::Invoke($controller, "initDataEntry", [$db, $tbname]);
        }
        catch(Exception $ex){
            $db->close();
            igk_wln($ex->xdebug_message ?? $ex->getMessage());
            igk_wln_e("failed to create dbtable. ".get_class($controller)." : ".$controller->getDeclaredFileName());
        }   
    }

    
    public static function getInitDbConstraintKey(BaseController $controller){
        $cl= str_replace("_", "",  str_replace("\\", "_", get_class($controller)));
        return $cl."_ck_";// constraint key
    }

    public static function getComponentsDir(BaseController $controller){
        return $controller::classdir()."/Components";
    }
    public static function getTestClassesDir(BaseController $controller){
        return dirname($controller::classdir())."/".IGK_TESTS_FOLDER;
    }

    ///<summary>set environment parameter for this controller</summary>
    /**
    * set environment parameter for this controller
    */
    public static function setEnvParam(BaseController $controller, $key, $value, $default=null){
        return igk_set_env(igk_ctrl_env_param_key($controller)."/".$key, $value, $default);
    }
     ///<summary>get environment parameter for this controller</summary>
    /**
    * get environment parameter for this controller
    */
    public static function getEnvParam(BaseController $controller, $key, $default=null){
        return igk_get_env(igk_ctrl_env_param_key($controller)."/".$key, $default);
    }


    
    /**
     * Routing macros
     * @param BaseController $controller 
     * @param mixed $routename 
     * @param mixed|null $path 
     * @return mixed 
     */
    public static function getRouteUri(BaseController $controller, $routename, $path=null){
        if ($route = Route::GetRouteByName($routename)){
            return RouteActionHandler::GetRouteUri($route, $controller, $path); 
        }
        return null;
    }

    ///<summary>Dispatch model utility</summary>
    /**
     *  Dispatch model utility 
     * @param BaseController $controller 
     * @param mixed $modelname 
     * @param mixed $funcName 
     * @param mixed $args 
     * @return mixed 
     */
    public static function dispatchToModelUtility(BaseController $controller, $modelname, $funcName, ...$args){
        if ($mod = $controller->loader->model($modelname)){
            $func = $funcName;
            return $mod->$func(...$args);
        }
        return false;
    }

    ///<summary>get model utility</summary>
    /**
     * get model utility
     * @param BaseController $controller 
     * @param mixed $modelname 
     * @return mixed 
     */
    public function modelUtility(BaseController $controller, $modelname){
        return $controller->loader->model($modelname);
    }


}