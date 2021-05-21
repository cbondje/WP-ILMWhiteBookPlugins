<?php

namespace ILYEUM\WhiteBooks\Pages;

use ILYEUM\RouteHandler;
use ILYEUM\WhiteBooks\Controller;
use ILYEUM\WhiteBooks\Models\Books;
use ILYEUM\WhiteBooks\Widgets\BookDownload;
use ILYEUM\wp\filters;
use function ilm_server as server;
use function ilm_getr as getr;
use function ilm_getctrl as getctrl;
use function ilm_createnode as createnode;

class Init{
   
    const DOWNLOAD_BOOK_DOC_AJX= "wp_ajax_ilyeum_download_book";
    const DOWNLOAD_BOOK_PUBLIC_DOC_AJX= "wp_ajax_nopriv_ilyeum_download_book";

    public function __construct(){
        $this->initialize();
    }
    protected function initialize(){
        //------------------------------------------------
        // | Parse request to access the page view process
        //------------------------------------------------
        add_filter(filters::DO_PARSE_REQUEST, function($x, $wp=null, $extra_args=null){	
            $routes = RouteHandler::GetRoutes();            
            foreach($routes as $p){
                if ($p->match()){
                    return false;
                }
            };
            if (strpos(server()->REQUEST_URI, Controller::ENTRY_URL) === 0){
                getctrl()->view("page");
                return false;
            }
            return $x;
        });

        //-------------------------------------------
        // | Initialize routes
        //-------------------------------------------
        add_action(self::DOWNLOAD_BOOK_DOC_AJX, [$this, "download"]);
        add_action(self::DOWNLOAD_BOOK_PUBLIC_DOC_AJX, [$this, "download"]);

        $_init_script = function (){
            $this->_init_env();
        };
        add_action("admin_enqueue_scripts",  $_init_script);
        add_action("wp_enqueue_scripts",  $_init_script);

    }
    protected function _init_env(){
        //because of warning error 
        //-------------------------------------------
        // | enqueue script 
        //-------------------------------------------
        wp_enqueue_script('ilyeum_white_book_mainjs', plugin_dir_url(ilm_getctrl()->getDeclaredFile())."assets/js/main.js",  array('jquery'), '1.0', true);

        //-------------------------------------------
        // | enqueue style
        //-------------------------------------------
        wp_enqueue_style('ilyeum_white_book_maincss', plugin_dir_url(ilm_getctrl()->getDeclaredFile())."assets/css/main.css", '1.0', true);
        //-------------------------------------------
        // | passing variable to local script
        //-------------------------------------------        
        wp_localize_script('ilyeum_white_book_mainjs', 'admin_url', admin_url('admin-ajax.php'));
        
    }
    public function download(){
        if (!($id = intval(getr("id"))) || !($book = Books::select_row($id))){
         //   wp_die(500);
        }
        $step = getr("step", 0);
        //--download book information
        $book->book_download ++;
        try{
        $book->update();
        }
        catch(\Error $ex){
            echo "rerror";
        }
        // exit;
        
        // $step = intval(getr("step"));
        $d = igk_createnode('div');
        $d->setClass("dialog");
        $box = $d->div()->setClass("box");
        $box->p()->Content = "Please register Before download";
        $box->form()->ajx()->fields(
            [
                "firstname"=>[],
                "lastname"=>[],
                "email"=>[],
            ]
        )->hiddenFields([
            "id"=>$id,
            "step"=>$step
        ])->actionbar(function($bar){
            $bar->submit("btn.confirm", "download");
        });
        $d->renderAJX();
      
        // echo "download document"; 
        wp_die();
    }
}