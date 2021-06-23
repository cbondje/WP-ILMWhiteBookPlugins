<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Admin/Manager.php
// @desc: 
// @date: 20210517 10:28:32
namespace ILYEUM\WhiteBooks\Admin;

use IGK\System\Http\RequestException;
use IGKApp;
use IGKException;
use IGKIO;
use ILYEUM\Exception;
use ILYEUM\wp\actions;
use function ilm_resources_gets as __;
use function ilm_getctrl as getctrl;
use function ilm_exit as _exit;
use function ilm_getr as getr;
use function ilm_getv as getv;
use ILYEUM\Utility;
use ILYEUM\WhiteBooks\BookStorage;
use ILYEUM\WhiteBooks\Controller;
use ILYEUM\WhiteBooks\Database\Migrates;
use ILYEUM\WhiteBooks\Models\BookClients;
use ILYEUM\WhiteBooks\Models\Books;
use ILYEUM\WhiteBooks\Models\ModelBase;
use ILYEUM\wp\capability;
use ILYEUM\wp\database\driver;
use ILYEUM\wp\filters;
use ILYEUM\wp\Models\WP_Post;
use ReflectionException;

use function ilm_environment as environment;
use function ilm_app as app;
use function ilm_log as _log;

/**
 * plugins manager
 * @package ILYEUM\WhiteBooks\Admin
 */
class Manager
{
    protected $controller;
    public function __construct()
    {
        $this->controller = null;
        add_action(actions::ADMIN_INIT, function () {

            if ($p = igk_getr("page") == ilm_app()->configs->plugin_uri) {
                $action = igk_getr("action");
                if (!empty($action)) {
                    if (method_exists($this, $a = "action_" . $action)) {
                        $this->$a();
                    }
                    igk_wln_e(__("action not found."));
                }
            }
        }, 5);

        // initalize 
        add_action(actions::ADMIN_MENU, function () {
            add_menu_page(
                ilm_app()->configs->plugin_name,
                __(ilm_app()->configs->plugin_shortname),
                capability::MANAGE_OPTIONS,
                ilm_app()->configs->plugin_uri,
                [$this, "form"]
            );
        }, 5);

        register_activation_hook(app()->PluginFile, function () {
            // activate the plugins
            $this->on_activate(...func_get_args());
        });

        register_deactivation_hook(app()->PluginFile, function () {
            //deactivate the plugins
            $this->on_deactivate(...func_get_args());
        });
        register_uninstall_hook(app()->PluginFile, [static::class, 'uninstall_plugins']);

        // test on activate
        // $this->on_deactivate();
        // $this->on_activate();
        // exit;
    }
    public function getUriQuery($args = null)
    {
        return implode("&", array_filter(["?page=" . app()->configs->plugin_uri, $args]));
    }

    public function form()
    {
        $tfc = ilm_get_robjs("action", 0, $_REQUEST);
        if ($tfc->action && method_exists($this, $tfc->action)) {
            return $this->{$tfc->action}();
        }

        //
        $dv = igk_createnode("div");

        $dv->div()->setClass("wrap")->h1()->setClass("wp-heading-inline")->Content = __("White Books");

        $control = $dv->addAJXTabControl();
        // $control->addTabPage(__("Books"), $this->getUriQuery("action=tab_options"));
        $control->addTabPage(__("Books"), $this->getUriQuery("action=tab_book"));
        $control->addTabPage(__("Clients"), $this->getUriQuery("action=tab_client"));
        $control->select(0);
        $dv->renderAJX();



        // igk_io_w2file(ILM_WHITE_BOOK_DIR . "/Views/admin.form.phtml", $dv->render((object)["Context" => "XML", "Indent" => 1]));
    }
    /**
     * uninstall plugins
     * @return void 
     */
    public static function uninstall_plugins()
    {
        $s = new static;
        $s->on_uninstall();
    }
    /**
     * on install
     * @return void 
     */
    protected function on_activate()
    {
        $driver = environment()->getClassInstance(driver::class);
        $tab = ModelBase::GetModels();
        $db_config = ilm_get_db_config();
        if ($db_data = $db_config) {
            // environment()->querydebug = 1;
            ob_start();
            $ctrl = getctrl($this->controller, false);
            $driver->beginInitDb();
            foreach ($db_data as $table => $inf) {
                $table = Utility::GetTableName($table, $ctrl);
                if (!$driver->tableExist($table)) {
                    $driver->createTable($table, $inf->ColumnInfo, $inf->Description);
                }
            }
            $driver->endInitDb();
            ob_get_clean();
        }
    }
    /**
     * on activate plugin
     * @return void 
     */
    protected function on_deactivate()
    {
        // environment()->querydebug = 1;
        $driver = environment()->getClassInstance(driver::class);
        $tab = ModelBase::GetModels();
        $driver->foreignCheck(false);
        foreach ($tab as $m) {
            $m::drop();
        }
        $driver->foreignCheck(true);
    }
    /**
     * on remove plugin
     * @return void 
     */
    protected function on_uninstall()
    {
        $this->on_deactivate();
    }

    /**
     * dump all database's table as json
     * @return never 
     * @throws Exception 
     */
    public function dump_db()
    {
        $tab = Books::query("SHOW TABLES");
        $gr = [];
        $db = DB_NAME;
        $driver = Books::getDataAdapter();
        foreach ($tab as $m) {
            $tn = $m->{'Tables_in_' . $db};
            $gr[$tn] = $driver->select($tn); // Books::getDataAdapter()->query(select_all();
        }
        $s = Utility::To_JSON($gr, ["ignore_empty" => 1], JSON_PRETTY_PRINT);
        igk_download_content("db_dump.json", strlen($s), $s, "binary");
        wp_die();
    }
    // public function dumpdb()
    // {
    //     $xml = igk_createxmlnode("dataschema");
    //     $tab = Books::query("SHOW TABLES");

    //     $db = DB_NAME;
    //     $driver = Books::getDataAdapter();
    //     $dv = igk_createnode("div");
    //     $dv->h2()->Content = "Dump Database";
    //     $form = $dv->form($this->getUriQuery("action=dumpdb"));
    //     $table = $form->table()
    //         ->setClass('wp-list-table widefat striped  ')
    //         ->header(["text" => "<input type='checkbox' id='cb-select-all-1' class='wp-toggle-checkboxes' />"], "name");


    //     $form->actionbar(function ($a) {
    //         $a->addinput("dump.btn", "submit", "Dump")->setClass("button");
    //     });
    //     // cb-select-all-1'
    //     $counter = 1;
    //     foreach ($tab as $t) {
    //         $tbname =  $t->{'Tables_in_' . $db};

    //         $tr = $table->tr();
    //         $tr->th()->setClass('check-column')->setAttribute("role", "row")->addInput("table[]", "checkbox", $tbname)->setAttribute("id", "cb-select-" . $counter);
    //         $ul = $tr->td()->ul();
    //         $li = $ul->li();
    //         $li->Content = $tbname; //"name:". $t->{'Tables_in_'.$db};
    //         $counter++;
    //         //$table = $li->table();
    //         // $h = 0;
    //         // if ($tbname === "wplq_posts")
    //         // {
    //         //     // echo Utility::To_JSON($g);
    //         //     igk_io_w2file("/Volumes/Data/temp/out_page.json", Utility::To_JSON($g) );
    //         //     $keys = null;
    //         //     foreach($g as $k=>$v){
    //         //         if ($keys === null){
    //         //             $keys = array_keys($v->toArray());
    //         //         }

    //         //         $rr = $xml->add("row"); 

    //         //         foreach($keys as $r){
    //         //             if ($r=="post_content")   {
    //         //                 $b = igk_createxmlcdata()->setContent($v->post_content);
    //         //                 $rr->$r()->add($b);
    //         //             }else{
    //         //                 $rr->$r()->Content = $v->$r;
    //         //             }
    //         //         }
    //         //     }
    //         //     //igk_io_w2file("/Volumes/Data/temp/out_page.xml", "<?xml version=\"1.0\" encoding=\"utf-8\" ? >".$xml->render((object)["Indent"=>true]));
    //         //     // foreach($g as $k){
    //         //     //     if (!$h){
    //         //     //         $tr = $table->tr();
    //         //     //         foreach(array_keys($k->toArray()) as $c){
    //         //     //             $tr->td()->Content = $c;
    //         //     //         }
    //         //     //     }
    //         //     //     $tr = $table->tr();
    //         //     //     foreach($k->toArray() as $r=>$v){
    //         //     //         if ($r=="post_content"){

    //         //     //             $tr->td()->textarea()->Content = $v;
    //         //     //         }else{
    //         //     //             $tr->td()->Content = $v;
    //         //     //         }
    //         //     //     }
    //         //     // }
    //         // }
    //     }
    //     // while(ob_get_level()>0){
    //     //     ob_end_clean();
    //     // }
    //   //  ob_end_clean();
    //     $dv->renderAJX(); 
    // }

    // | dump db link : wp-admin/admin.php?page=ilm%3A%2Fwhite_book&action=dumpdb
    protected function action_dumpdb()
    {
        $this->dump_db();
    }
    /**
     * view book page 
     * @return node new node
     */
    private function _view_books($books)
    {
        $form = igk_createnode("form")->setAttribute("id", "viewbooks");
        /// | WHITE BOOK ADMIN FORM TABLE
        $form->tablehost()->table()
            ->setClass("wp-list-table fixed striped table-view-list pages")
            ->header(
                [
                    "text" => igk_createnode("input")->setAttributes(
                        [
                            "type" => "checkbox",
                            "class" => "manage-column column-cb check-column"
                        ]
                    )->render()
                ],
                __("picture"),
                __("name"),
                __("title"),
                __("desc"),
                __("download"),
                "", //edit
                "", // link
                "", //download
                "" // copy link
            )->loop($books)->host(function ($n, $r) {
                $tr = $n->tr();
                $tr->td()->checkbox("list[]");
                $td = $tr->td();
                if ($r->book_picture) {
                    // ->setContent("picture:".$r->to_json())
                    $td->addXmlNode("img")->setAttributes(["src" => $r->book_picture, "alt" => "picture"]);
                } else {
                    $td->nbsp();
                }
                $tr->td()->Content = stripslashes($r->book_name);
                $tr->td()->Content = stripslashes($r->book_title);
                $tr->td()->Content = stripslashes($r->book_desc);
                $tr->td()->Content = $r->book_download ? intval($r->book_download) : "0";
                $tr->td()->a_get($this->getUriQuery("action=edit_book&id=" . $r->book_id))->google_icons("edit");
                $tr->td()->a_post($this->getUriQuery("action=book_link&id=" . $r->book_id))->google_icons("link");
                $tr->td()->a($this->getUriQuery("action=download_book&id=" . $r->book_id))->google_icons("download");
                $tr->td()->a_post($this->getUriQuery("action=drop_book&id=" . $r->book_id))->google_icons("delete");
            });

        return $form;
    }

    private function _view_clients($clients){

    }



    /**
     * book tab page
     * @param bool $exit 
     * @return void 
     * @throws ReflectionException 
     * @throws IGKException 
     */
    protected function action_tab_book($exit = true)
    {
        $books = Books::select_all();
        $dv = igk_createnode("div");
        $form = $dv->form();
        $form->div()->setClass("wrap")->h1()->setClass("wp-heading-inline")->Content = __("White Books");

        $form = $dv->form();
        $form->p()->actionbar(function ($a) {
            $a->a_get($this->getUriQuery("action=add_book"))->setClass("button")->Content = __("Add");
        });
        if ($books) {
            $form = $this->_view_books($books);
            $dv->add($form);
        } else {
            $dv->p()->Content = __("No books founds.");
        }
        $dv->renderAJX();
        if ($exit)
            _exit();
    }
    protected function action_tab_client()
    {
        // book page 

        $clients = BookClients::select_all();
        // ilm_getctrl()->view("admin.form.phtml", [
        //     "books"=>$books
        // ]); 
        // if (!defined("IGK_FRAMEWORK")){
        //     return;
        // }

        $dv = igk_createnode("div");

        $form = $dv->form();
        $form->div()->setClass("wrap")->h1()->setClass("wp-heading-inline")->Content = __("Clients");

        $form = $dv->form();

        if ($clients) {
            $form = $dv->form();
            $form->tablehost()->table()
                ->setClass("wp-list-table fixed striped table-view-list pages")
                ->header(
                    [
                        "text" => igk_createnode("input")->setAttributes(
                            [
                                "type" => "checkbox",
                                "class" => "manage-column column-cb check-column"
                            ]
                        )->render()
                    ],
                    "picture",
                    "lastname",
                    "firstname",
                    "email",
                    "function",
                    "",
                    "",
                    "",
                )->loop($clients)->host(function ($n, $r) {
                    $tr = $n->tr();
                    $tr->td()->checkbox("list[]");
                    if ($r->bookclients_picture) {
                        $tr->td()
                            // ->setContent("picture:".$r->to_json())
                            ->addXmlNode("img")->setAttributes(["src" => $r->book_picture, "alt" => "picture"]); //google_icons("picture");
                    } else $tr->td()->nbsp();


                    $tr->td()->Content = $r->bookclients_firstname;
                    $tr->td()->Content = $r->bookclients_lastname;
                    $tr->td()->Content = $r->bookclients_email;
                    $tinfo = json_decode($r->bookclients_info);
                    $tr->td_cell($tinfo ? getv($tinfo, "function", '-')  : 'unknow');
                    $tr->td()->nbsp();
                    $tr->td()->nbsp();
                    $tr->td()->nbsp();
                    //   $tr->td()->google_icons("edit"); 
                    //   $tr->td()->google_icons("delete");
                });
        } else {
            $dv->p()->Content = __("No Clients founds.");
        }
        $dv->renderAJX();
        exit();
    }
    protected function action_add_book()
    {
        $fields = Books::formFields();
        $s_data = [];
        $data = WP_Post::select_all_filter(function ($r) use (&$s_data) {
            $s_data[] = ["i" => $r->ID, "t" => $r->post_title];
        }, [
            "post_type" => "page",
            "post_status" => "publish"
        ], [
            "OrderBy" => [
                "post_title|ASC"
            ]
        ]);
        $fields["article_id"] = ["type" => "select", "data" => $s_data];
        // $fields["book_media"] = ["type"=>"media", "accept"=>"image", "attribs"=>[
        //     "onclick"=>"ilyeum.wp.openMediaFrame();"
        // ]];

        $fields["book_file"] = ["type" => "file", "accept" => "*.pdf", "attribs" => [
            "accept" => ".pdf"
        ]];

        if (igk_server()->REQUEST_METHOD === "POST") {
            if ($book = Books::requestAdd()) {
                $book->book_desc_post_id = getr("article_id");
                $book->book_author_id = get_current_user_id();
                if (isset($_FILES["book_file"])) {
                    $files = getv($_FILES, "book_file");
                    $n = igk_create_guid();
                    $book_path = implode("/", array_filter([ilm_app()->configs->book_dir, $n]));
                    IGKIO::CreateDir(dirname($book_path));
                    move_uploaded_file($files["tmp_name"], $book_path);
                    $book->book_path = $n;
                    $book->book_mimetype = $files["type"];
                }
                $book->update();
                igk_ajx_toast("book added", "igk-success");
            } else {
                igk_ajx_toast("book added", "igk-danger");
            }
            igk_ajx_panel_dialog_close();
            $this->action_tab_book();
        }
        array_walk($fields, function (&$m, $key) {
            $m["label_text"] = __($key);
            return $m;
        });

        // igk_wln_e($fields);

        $d = igk_createnode("div");
        $d->form($this->getUriQuery("action=add_book"))
            ->ajx(".igk-tabcontent")
            ->fields($fields)
            ->actionbar(function ($a) {
                $a->addInput("btn.submit", "submit", __("Add"))->setClass("button button-primary");
            });

        igk_ajx_panel_dialog(__("Add Book"), $d);
        exit();
    }
    protected function action_drop_book()
    {
        $id = getr("id");
        Books::delete(["book_id" => $id]);
        $n = igk_createnode("div");
        $n->setClass("igk-tabcontent");
        $n->addObData(function () {
            $this->action_tab_book(false);
        });
        igk_ajx_replace_node($n, ".igk-tabcontent");
    }
    protected function action_download_book()
    {
        $id = getr("id");
        $book = Books::select_row(["book_id" => $id]);
        if (file_exists($file = BookStorage::GetFile($book->book_path))) {

            $ext = getv([
                "application/pdf" => ".pdf"
            ], $book->book_mimetype);

            igk_download_file($book->book_name . $ext, $file, $book->book_mimetype);
            igk_exit();
        }
        throw new RequestException(404, "book not found");
    }


    public function action_edit_book()
    {
        $id = getr("id");
        if ($book = Books::select_row(["book_id" => $id])) {


            if (ilm_server()->REQUEST_METHOD == "POST") {
                if (ilm_getr("action") == "edit_book") {
                    // update white book
                    igk_ajx_panel_dialog_close();
                    $success = false;
                    $obj = ilm_get_robjs("name|title|desc");
                    if (!empty($obj->name)) {
                        $book->book_name = stripslashes($obj->name);
                        $book->book_title = stripslashes($obj->title);
                        $book->book_desc = stripslashes($obj->desc);

                        if ($book->update()) {
                            $success = true;
                        }
                    }
                    if ($success) {
                        igk_ajx_toast("book update", "igk-success");
                    } else {
                        igk_ajx_toast("book not updated", "igk-danger");
                    }
                    $books = Books::select_all(null, [
                        "Limit"=>100
                    ]);
                    $d = $this->_view_books($books);
                    igk_ajx_replace_node($d, "#viewbooks");
                    igk_exit();
                }
            }

            $d = igk_createnode("div");
            $form = $d->form($this->getUriQuery("action=edit_book"));
            $form->fields([
                "id" => ["type" => "hidden", "value" => $id],
                "name" => ["value" => stripslashes($book->book_name)],
                "title" => ["value" => stripslashes($book->book_title)],
                "desc" => ["type" => "textarea", "value" => stripslashes($book->book_desc)],
            ])->hiddenFields([
                "action" => "edit_book"
            ]);
            $form->ajx();
            $form->cref();

            $form->actionbar(function ($a) {
                $a->submit()->setClass("default");
            });

            igk_ajx_panel_dialog(__("Edit Book"), $d);
        } else {
            igk_ajx_toast("book not found", "igk-danger");
        }
        igk_exit();
    }
    public function action_book_link()
    {
        $id = getr("id");
        $book = Books::select_row(["book_id" => $id]);
        if ($book) {
            $uri = get_site_url() . "/wbook/download/" . $id;
            $d = igk_createnode("div");
            $d->label()->Content = $uri;
            $d->div()->a("#")->setClass("button")->setAttribute("onclick", "ilyeum.wp.copylink('{$uri}'); ns_igk.winui.controls.panelDialog.close(); return false;")->Content = "Copy";
            igk_ajx_panel_dialog(__("Book Link") . " - " . stripslashes($book->book_title), $d);
        } else {
            igk_ajx_toast("no book found", "igk-danger");
        }
        igk_exit();
    }


    protected function action_migrate()
    {
        (new Migrates())->run();
        $uri = get_admin_url()."admin.php".$this->getUriQuery("");
        igk_navto($uri);
       // die("run migration - not implement" . __METHOD__. "  : ".$uri);
    }
}
