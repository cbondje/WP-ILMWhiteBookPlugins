<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Admin/Manager.php
// @desc: 
// @date: 20210517 10:28:32
namespace ILYEUM\WhiteBooks\Admin;

use ILYEUM\wp\actions;
use ilm_resources_gets as __;
use ILYEUM\Utility;
use ILYEUM\WhiteBooks\Models\Books;
use ILYEUM\WhiteBooks\Models\ModelBase;
use ILYEUM\wp\capability;
use ILYEUM\wp\database\driver;

/**
 * plugins manager
 * @package ILYEUM\WhiteBooks\Admin
 */
class Manager{
    protected $controller;
    public function __construct()
    {
        $this->controller = null;
        add_action(actions::ADMIN_INIT, function(){
             
        }, 5); 
 
        // initalize 
        add_action(actions::ADMIN_MENU, function(){
            add_menu_page(
                ilm_app()->configs->plugin_name,
                ilm_app()->configs->plugin_shortname,                
                capability::MANAGE_OPTIONS,
                ilm_app()->configs->plugin_uri,  
                [$this, "form"]
                );	
        }, 5);
 
        register_activation_hook(ilm_app()->plugins_file , function(){
            // activate the plugins
            $this->on_activate(...func_get_args());
        });
        register_deactivation_hook(ilm_app()->plugins_file , function(){
            //deactivate the plugins
            $this->on_deactivate(...func_get_args());
        });
        register_uninstall_hook(ilm_app()->plugins_file , [static::class, 'uninstall_plugins']);
    }
    public function getUriQuery($args=null){
        return implode("&", array_filter(["?page=".ilm_app()->configs->plugin_uri, $args]));
    }
   
    public function form(){
        $tfc = ilm_get_robjs("action", 0, $_REQUEST);
        if ($tfc->action && method_exists($this, $tfc->action)){
            return $this->{$tfc->action}();
        }

        $books = Books::select_all();
        ilm_getctrl()->view("admin.form.phtml", [
            "books"=>$books
        ]); 
        if (!defined("IGK_FRAMEWORK")){
            return;
        }

        $dv = igk_createnode("div");
        global $current_screen;
        // igk_wln_e($current_screen);
        // $dv->obdata(function()use($current_screen){
        //     apply_filters('screen_options_show_screen', true, $current_screen);
        //     $current_screen->show_screen_options();
        //     echo "render options";
        //     print_r(get_current_user());
        //     echo "user can install plugin : ".current_user_can('install_plugins');
        // });
        $form = $dv->form();
        $form->div()->setClass("wrap")->h1()->setClass("wp-heading-inline")->Content = "White Books"; 

        $form = $dv->form();
        $form->p()->actionbar(function($a){
            $a->a_get("add")->setClass("button")->Content = "Add Book";
            $a->a($this->getUriQuery("action=dumpdb"))->setClass("button")->Content = "Dump Pages";
            // $a->a_get("initialize")->setClass("button")->Content = "Initialize Db";
            // $a->a_get("reset")->setClass("button")->Content = "reset Db";
        });

        // Books::create([
        //     "book_name"=>"Book1"
    // ]);

    // ilm_environment()->querydebug = 1;
    // Books::delete();
    // $book = Books::create(["book_name"=>"Information de jour"]);
    // $book->book_title = "Pascal";
    // $book->update();
    // $book->delete();
    // Books::getDataAdapter()->listTables();

 
        if ($books){
            $form = $dv->form();
            $form->tablehost()->table()
            ->setClass("wp-list-table fixed striped table-view-list pages")
            ->header(
                [
                    "text"=>igk_createnode("input")->setAttributes(
                        ["type"=>"checkbox",
                        "class"=>"manage-column column-cb check-column"]
                    )->render()
                ], "picture", "name", "title", "desc", "download", ""
            )->loop($books)->host(function($n, $r){
                $tr = $n->tr();
                $tr->td()->checkbox("list[]");
                $tr->td()->google_icons("picture");
                $tr->td()->a_get("#")->Content = $r->book_name;
                $tr->td()->Content = $r->book_title;
                $tr->td()->Content = $r->book_desc;
                $tr->td()->Content = $r->book_download;
                $tr->td()->google_icons("edit");
                $tr->td()->google_icons("delete");
            });
        }else{
            $dv->p()->Content = "No books founds.";
        } 
        $dv->renderAJX();
        $fc = ILM_WHITE_BOOK_DIR."/Views/admin.form.phtml";
        //igk_wln("file: ".$fc);
        igk_io_w2file($fc, $dv->render((object)["Context"=>"XML", "Indent"=>1]));

    }
    private function _view_users(){

    }


     /**
     * uninstall plugins
     * @return void 
     */
    public static function uninstall_plugins(){
        $s = new static;
        $s->on_uninstall();
    }
    /**
     * on install
     * @return void 
     */
    protected function on_activate(){
        $driver = ilm_environment()->getClassInstance(driver::class);
        $tab = ModelBase::GetModels();
        $db_config = ilm_get_db_config();
        if ($db_data = $db_config){ 
            //ilm_environment()->querydebug = 1;
            $ctrl = ilm_getctrl($this->controller, false);
            $driver->beginInitDb();
            foreach($db_data as $table=>$inf){
                $table = Utility::GetTableName($table, $ctrl);
                if (!$driver->tableExist($table)){
                    $driver->createTable($table, $inf->ColumnInfo, $inf->Description);
                }
            }
            $driver->endInitDb(); 
            //exit;
        } 
    }
    /**
     * on activate
     * @return void 
     */
    protected function on_deactivate(){
        $driver = ilm_environment()->getClassInstance(driver::class);
        $tab = ModelBase::GetModels(); 
        $driver->foreignCheck(false);
        foreach($tab as $m){
            $m::drop();
        }
        $driver->foreignCheck(true); 
    }
    /**
     * on remove plugins
     * @return void 
     */
    protected function on_uninstall(){
        $this->on_deactivate();
    }


    public function dumpdb(){
        $xml = igk_createxmlnode("dataschema");
        $tab = Books::query("SHOW TABLES");
        $ul = igk_createnode("ul");
        $db = DB_NAME;
        $driver = Books::getDataAdapter();
        foreach($tab as $t){
            $tbname =  $t->{'Tables_in_'.$db};
            $g = $driver->select($tbname);
            $li = $ul->li();
            $li->Content = $tbname;//"name:". $t->{'Tables_in_'.$db};
            $table = $li->table();
            $h = 0;
            if ($tbname === "wplq_posts")
            {
                // echo Utility::To_JSON($g);
                igk_io_w2file("/Volumes/Data/temp/out_page.json", Utility::To_JSON($g) );
                $keys = null;
                foreach($g as $k=>$v){
                    if ($keys === null){
                        $keys = array_keys($v->toArray());
                    }
                   
                    $rr = $xml->add("row"); 
                    
                    foreach($keys as $r){
                        if ($r=="post_content")   {
                            $b = igk_createxmlcdata()->setContent($v->post_content);
                            $rr->$r()->add($b);
                        }else{
                            $rr->$r()->Content = $v->$r;
                        }
                    }
                }
                igk_io_w2file("/Volumes/Data/temp/out_page.xml", "<?xml version=\"1.0\" encoding=\"utf-8\" ?>".$xml->render((object)["Indent"=>true]));
                // foreach($g as $k){
                //     if (!$h){
                //         $tr = $table->tr();
                //         foreach(array_keys($k->toArray()) as $c){
                //             $tr->td()->Content = $c;
                //         }
                //     }
                //     $tr = $table->tr();
                //     foreach($k->toArray() as $r=>$v){
                //         if ($r=="post_content"){

                //             $tr->td()->textarea()->Content = $v;
                //         }else{
                //             $tr->td()->Content = $v;
                //         }
                //     }
                // }
            }
        }
        $ul->renderAJX(); 
        
    }
}