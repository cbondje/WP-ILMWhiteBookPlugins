<?php

namespace ILYEUM\WhiteBooks\Pages;

use IGK\System\Http\RequestException;
use IGKValidator;
use ILYEUM\Mime;
use ILYEUM\RouteHandler;
use ILYEUM\WhiteBooks\BookStorage;
use ILYEUM\WhiteBooks\Controller;
use ILYEUM\WhiteBooks\Models\Books; 
use ILYEUM\WhiteBooks\Models\BookClients as Clients; 
use ILYEUM\wp\filters;
use function ilm_server as server;
use function ilm_getr as getr;
use function ilm_getctrl as getctrl;
use function ilm_get_robjs as getrobjs;
use function ilm_createnode as createnode;
use function ilm_resources_gets as __;
use function igk_download_file as download_file;


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
            $request_uri = server()->REQUEST_URI;  
            
            foreach($routes as $p){
                if ($p->match()){
                    return false;
                }
            };
            if (strpos($request_uri, Controller::ENTRY_URL) === 0){
                $args = array_slice(array_filter(explode("/", parse_url($request_uri, PHP_URL_PATH))), 1);
                getctrl()->view("page", ["args"=>$args]);
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
        // wp_enqueue_script( 'media-editor' );
        // enqueue media if necessary
        wp_enqueue_media();
        //because of warning error 
        $js_version = ilm_app()->configs->get("js_version", "1.0");
        $css_version = ilm_app()->configs->get("css_version", "1.0"); 
        //-------------------------------------------
        // | enqueue script 
        //-------------------------------------------
        wp_enqueue_script('ilyeum_balafon_js', plugin_dir_url(ilm_getctrl()->getDeclaredFile())."assets/js/balafon.js",  [], IGK_VERSION, true);
        wp_enqueue_script('ilyeum_white_book_mainjs', plugin_dir_url(ilm_getctrl()->getDeclaredFile())."assets/js/main.js",  array('jquery'), $js_version, true);

        //-------------------------------------------
        // | enqueue style
        //-------------------------------------------
        wp_enqueue_style('ilyeum_balafon_corecss', plugin_dir_url(ilm_getctrl()->getDeclaredFile())."assets/css/core.css", [], IGK_VERSION);
        wp_enqueue_style('ilyeum_white_book_maincss', plugin_dir_url(ilm_getctrl()->getDeclaredFile())."assets/css/main.css", [], $css_version);
        wp_enqueue_style('ilyeum_google_materials', plugin_dir_url(ilm_getctrl()->getDeclaredFile())."assets/css/google_material.css", [], "1.0");
        //-------------------------------------------
        // | passing variable to local script
        //-------------------------------------------        
        // wp_localize_script('ilyeum_white_book_mainjs', 'admin_url', admin_url('admin-ajax.php'));

        wp_add_inline_script('ilyeum_white_book_mainjs', 'var admin_url="'. admin_url('admin-ajax.php').'";' , "before");
        
    }
    public function download(){   
        if (!($id = intval(getr("id"))) || !($book = Books::select_row($id))){
         //   wp_die(500);
            throw new RequestException(500, "book not found");
        }
        $file = BookStorage::GetFile($book->book_path); 
        if (!file_exists($file)){
            throw new RequestException(500, "book file path not found");
        }
        $step = getr("step", 0);
        
        //--download book information
        if (igk_server()->REQUEST_METHOD == "POST"){
            if ($step == 300){               
                try{
                    ilm_environment()->querydebug = 1;
                    $getfile = false;
                    $obj = getrobjs("firstname|lastname|email|function");
                    if (
                        IGKValidator::IsEmail($obj->email) && 
                        ($rp = Clients::createIfNotExists([
                        "bookclients_email"=>$obj->email
                    ]))){
                        $getfile = true;

                        $rp->bookclients_firstname = $obj->firstname;
                        $rp->bookclients_lastname = $obj->lastname;
                        $info = $rp->bookclients_info;
                        
                        if(empty($info) || !$info){
                            $info = [];
                        }
                        if (isset($obj->function)){
                            $info["function"] = $obj->function;
                        }
                        $rp->bookclients_info = json_encode($info);
                        $rp->update();
                    }
                    if ($getfile){
                        $book->book_download ++;
                        $book->update(); 
                        $name = $book->book_name;
                        $ext = Mime::GetExt($book->book_mimetype);
                        if (!preg_match("/\.{$ext}$/", $name)){
                            $name .= ".".$ext;
                        }
                        download_file($name, $file,  $book->book_mimetype);
                    }else {
                        // ilm_environment()->querydebug = 1;
                        // igk_wln("valid email: ",  IGKValidator::IsEmail($obj->email));
                        // igk_wln("create: ", Clients::createIfNotExists([
                        //     "bookclients_firstname"=>$obj->firstname,
                        //     "bookclients_lastname"=>$obj->lastname,
                        //     "bookclients_email"=>$obj->email
                        // ]));
                        igk_ajx_toast(__("something bad happened") . $obj->email, "igk-danger");
                    }
                    igk_exit();                   

                }
                catch(\Error $ex){ 
                    igk_ilog($ex->getMessage());
                    throw new RequestException(500, $ex->getMessage(), $ex);
                }                
            }
        }
        $d = igk_createnode('div');
        // $d->setClass("dialog");
        $box = $d->div()->setClass("box");
        $box->p()->setStyle("max-width: 360px")->Content = __("Please register Before download.");
        // wordpress ajax demand handler
        $form = $box->form("".admin_url('admin-ajax.php'));


        $form->setAttribute("enctype","application/x-www-form-urlencoded")
        ->setAttribute("onsubmit", "ns_igk.winui.controls.panelDialog.close();")
        ->setAttribute("oninput", "\$igk(this).select('#btn_confirm').first().disabled(!((this.firstname.value.length>0) && (this.lastname.value.length>0) && (this.email.value.length>0) ));")
       ;

        // form to present
        $row = $form->row();
        $row->col("igk-col-12-11")
        ->fields(
            [
                "firstname"=>["label_text"=>__("firstname")],
                "lastname"=>["label_text"=>__("lastName"), "required"=>1],
                "function"=>["label_text"=>__("function"), "required"=>0, "attribs"=>["placeholder"=>__("function in your company")]],
                "email"=>["label_text"=>__("Email"), "type"=>"email", "required"=>1],
            ]
        );

        $row->col("igk-col-12-6 dispn")->div()->p()->Content = "";

        $form->hiddenFields([
            "id"=>$id,
            "step"=>300,
            "action"=>'ilyeum_download_book',
            "url"=>base64_encode("/wbook/download/".$id)
        ])->actionbar(function($bar){
            $bar->submit("btn.confirm", "download")
            ->setAttribute("id", "btn_confirm")
            ->setAttribute("disabled","disabled")
            ->setStyle("width:auto");
        });
        igk_ajx_panel_dialog(__("Get Book"). " - ".stripslashes($book->book_title), $d);         
        exit();
    }
}