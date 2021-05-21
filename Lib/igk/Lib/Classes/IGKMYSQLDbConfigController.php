<?php

use IGK\Resources\R;

use function igk_resources_gets as __;

///<summary> USE TO CONFIGURE MYSQL DATABASE ACCESS</summary>
/**
*  USE TO CONFIGURE MYSQL DATABASE ACCESS
*/
final class IGKMYSQLDbConfigController extends IGKConfigCtrlBase {
    const LOADTABLES_DB=0xa4;
    const SEARCH_DB=0xa4;
    const SELECTED_DB=0xa1;
    const TABINFO_DB=0xa3;
    const VIEWMYADMIN_DB=0xa2;
    static $sm_tabinfo;
 
    ///<summary></summary>
    ///<param name="tr"></param>
    ///<param name="tablename"></param>
    ///<param name="selectedDb" default="null"></param>
    /**
    * 
    * @param mixed $tr
    * @param mixed $tablename
    * @param mixed $selectedDb the default value is null
    */
    private function __addEditTable($tr, $tablename, $selectedDb=null){
        $tr->addTd()->addLi()->add("a", array("href"=>igk_js_post_frame($this->getUri("db_viewtableentries_ajx&n=".$tablename.($selectedDb ? "&from=".$this->selectedDb: IGK_STR_EMPTY)))))->add("img", array(
            "width"=>"16px",
            "height"=>"16px",
            "src"=>R::GetImgUri("edit_16x16"),
            "alt"=>__("tip.editdatabase")
        ));
    }
    ///<summary>send query request</summary>
    /**
    * send query request
    */
    public function __db_query_r_ajx(){
        $mysql=igk_get_data_adapter(IGK_MYSQL_DATAADAPTER, true);
        $q=igk_getr("clQuery");
        if(empty($q) || !igk_is_conf_connected())
            igk_exit();
        if(!preg_match("#^(SELECT|UPDATE|DELETE|SHOW|ALTER|INSERT|CREATE|DROP) (.)+#i", $q)){
            igk_wl("/!\\". __("Query not allowed : {0}",$q));
            igk_exit();
        }
        if (preg_match("/^SELECT /i",$q)){
            $g = $mysql->sendQuery("SELECT COUNT(*) as count FROM (".$q.") as dummy");

            
            if ($g->getRowAtIndex(0)->count > 50){
                $q .= " Limit 1, 50";
            } 
        }
 

        if($q){
            $this->setParam("query", $q);
            $g=$mysql->sendQuery($q, true);
            if($g && ($g->RowCount > 0)){
                $uri = igk_register_temp_uri(__CLASS__)."/page/";
                $selected = 1;
                $dv=igk_createnode("div");
                $dv->addDiv()->Content=$q;
                $dv->div()
                ->setStyle("min-height:400px;")
                ->tablehost()->setClass("posab fit overflow-y-a")
                ->addDbResult($g, $uri, $selected, igk_app()->Configs->db_query_page_result ?? 50, "#query-s-r");
                $dv->RenderAJX();
            } else {               
                if ($error = $mysql->getError()){
                        igk_wl("SQLError: ", $error);
                }               
            } 
        } 
    }
    public function page($view=0){
        // igk_ajx_replace_uri("#!/page/".$view);
        $q = $this->getParam("query");
        if($q && ($mysql=igk_get_data_adapter(IGK_MYSQL_DATAADAPTER, true)) && $mysql->connect()){

            $g=$mysql->sendQuery($q);
            if($g && ($g->RowCount > 0)){
                $uri = igk_register_temp_uri(__CLASS__)."/page/";
                $selected = $view;
                $dv=igk_createnode("div");
                $dv->addDiv()->Content=$q;
                $dv->addDiv()->setClass("igk-table-host overflow-x-a fitw bdr-1")
                ->addDbResult($g, $uri, $selected, 5);
                $dv->RenderAJX();
            }
            $mysql->close();
        }
    }
    ///<summary></summary>
    ///<param name="o"></param>
    ///<param name="e"></param>
    /**
    * 
    * @param mixed $o
    * @param mixed $e
    */
    private function __inittable_callback($o, $e){
        $tbname=$e[0];
        $desc=$e[1];
        $this->regTableDefinition($o, $tbname, $desc);
    }
    ///backup mysql database
    ///<summary>restore data base table from definition</summary>
    /**
    * restore data base table from definition
    */
    private function __restoredb($table, $header, $definition, $sync=0){
        throw new IGKNotImplementMethodException(__METHOD__);
    }
    ///get data from schemas
    /**
    */
    private function _addTable($tb, $ctrl){
        if(empty($tb))
            return false;
        $v_table=& $this->getLoadTables();
        if ($v_table===null){
            igk_die("loaded table is empty");
        }
        if(isset($v_table[$tb]) && ($v_table[$tb] !== $ctrl)){
            igk_die("Table '$tb' already loaded by : ".get_class($v_table[$tb]). "<br />".", Requested =&gt; ".get_class($ctrl). "<br />".", ". (new ReflectionClass(get_class($v_table[$tb])))->getFileName(). " Instance possibly missmatch");
            return false;
        }
        $v_table[$tb]=$ctrl;
        return true;
    }
    ///<summary></summary>
    ///<param name="h"></param>
    ///<param name="mysql"></param>
    /**
    * 
    * @param mixed $h
    * @param mixed $mysql
    */
    private function _db_viewTables($h, $mysql){
        $conf_title=array("class"=>"igk-cnf-title");
        $conf_search=$this->getFlag('db:searchtable');
        $d=$h->addDiv();
        $d->Content=__("User not allowed to view database");
        $d->addBr();
        if($mysql->connect()){
            $mysql->selectdb(igk_app()->Configs->db_name);
            try {
                $r=$this->_getTables($conf_search);
                $d->add("div", $conf_title)->Content=igk_app()->Configs->db_name. " Tables";
                $d->addHSep();
                $d->add(new IGKHtmlSearchItem($this->getUri("searchtable"), $conf_search))->setClass("dispb");
                $d->addHSep();
                $frm=$d->addForm();
                $frm["action"]=$this->getUri("tableview");
                $v_table=$frm->addTable();
                if($r && ($r->Rows != null)){
                    $v_theader=false;
                    foreach($r->Rows as $tab){
                        if($tab){
                            if(!$v_theader){
                                $v_theader=true;
                                $li=$v_table->addTr();
                                IGKHtmlUtils::AddToggleAllCheckboxTh($li);
                                $li->add("th", array("class"=>"fitw"))->Content=__(IGK_FD_NAME);
                                $li->add("th")->Content=IGK_HTML_SPACE;
                                $li->add("th")->Content=IGK_HTML_SPACE;
                            }
                            $tr=$v_table->addTr();
                            foreach($tab as  $v){
                                $s=$v;
                                $tr->addTd()->addInput("tname[]", "checkbox", $s);
                                $tr->addTd()->addLi()->addA(igk_js_post_frame($this->getUri("db_viewtableentries_ajx&n=".$s."&from=".igk_app()->Configs->db_name)))->Content=$s;
                                $this->__addEditTable($tr, $s, igk_app()->Configs->db_name);
                                $tr->addTd()->addLi()->addA(igk_js_post_frame($this->getUri("db_droptable_ajx&n=".$s."&from=".igk_app()->Configs->db_name)))->add("img", array(
                                    "width"=>"16px",
                                    "height"=>"16px",
                                    "src"=>R::GetImgUri("drop_16x16"),
                                    "alt"=>__("info.droptable")
                                ));
                            }
                        }
                    }
                    $div=$frm->addActionBar();
                    $div->addAJXA($this->getUri("db_dropSelectedTable_ajx"))->Content=__("btn.droptableselection");
                }
                igk_html_toggle_class($v_table, "tr");
            }
            catch(Exception $ex){}
            $mysql->close();
        }
        else{
            $h->addDiv()->Content="failed to connect to database";
        }
    }
    ///<summary></summary>
    ///<param name="searchkey" default="null"></param>
    /**
    * 
    * @param mixed $searchkey the default value is null
    */
    private function _getTables($searchkey=null){
        $r=null;
        $ad=igk_get_data_adapter($this, true);
        if($ad){
            $ad->connect(igk_app()->Configs->db_name);
            $r=$ad->listTables();
            if($r){
                $tab=$r->CreateEmptyResult($r);
                $ad->close();
                if(count($r->Columns) > 0){
                    $n=$r->Columns[0]->name;
                    if($searchkey != null){
                        $q=strtolower($searchkey);
                        foreach($r->Rows as $t){
                            if(strstr(strtolower($t->$n), $q)){
                                $tab->addRow(array("Table"=>$t->$n));
                            }
                        }
                        return $tab;
                    }
                    else{
                        foreach($r->Rows as $t){
                            $tab->addRow(array("Table"=>$t->$n));
                        }
                        return $tab;
                    }
                }
            }
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="searchkey" default="null"></param>
    /**
    * 
    * @param mixed $searchkey the default value is null
    */
    private function _getTablesFromSelectedDb($searchkey=null){
        if(empty($this->SelectedDb))
            return;
        $r=null;
        $mysql=igk_get_data_adapter($this, true);
        if($mysql){
            $mysql->connect($this->SelectedDb);
            $r=$mysql->listTables();
            $mysql->close();
            if($r && !$r->ResultTypeIsBoolean()){
                $tab=$r->CreateEmptyResult($r);
                $n=$r->Columns[0]->name;
                if($searchkey != null){
                    $q=strtolower($searchkey);
                    foreach($r->Rows as $t){
                        if(strstr(strtolower($t->$n), $q)){
                            $tab->addRow(array("Table"=>$t->$n));
                        }
                    }
                    return $tab;
                }
                else{
                    foreach($r->Rows as $t){
                        $tab->addRow(array("Table"=>$t->$n));
                    }
                    return $tab;
                }
            }
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    private function _initTableInfo(){
        if(igk_get_env("sys://db_init")){
            return;
        }
        igk_set_env("sys://db_init", 1);
        $cf=null;
        $no_db_cache = defined("IGK_NO_DBCACHE");
        if (!defined("IGK_NO_DBCACHE")){
            $cf = $this->getCacheFile();
        }
        if($no_db_cache || ($cf ===null) || !file_exists($cf)){
             
            $t=igk_sys_getall_ctrl();  
            foreach($t as  $v){
       
                if(igk_is_class_incomplete($v)){ 
                    continue;
                }
                if($v === $this)
                    continue;
                if($v->getUseDataSchema()){
                    $cr = $v->loadDataFromSchemas(); 
                    $r= igk_getv($cr, "tables"); 
                    if($r != null){
                        foreach($r as $kk=>$vv){
                            $this->_addTable($kk, $v);
                        }
                    }
                }
                else{
                    $r=$v->getDataTableInfo();
                    if($r != null){
                        $this->_addTable($v->getDataTableName(), $v);
                    }
                }
            } 
            if (!$no_db_cache)
                $this->_storeDbCache(1);
        }
        else{
            $this->_loadDbFromCache();
        }
        igk_set_env("sys://db_init", null);
    }
    ///<summary></summary>
    /**
    * 
    */
    private function _loadDbFromCache(){
        $f = null;
        if (!defined("IGK_NO_DBCACHE")){
            $f=$this->getCacheFile();
        }
        // igk_wln_e(defined("IGK_NO_DBCACHE"), $f);
        if($f && file_exists($f)){
            $txt=IGKIO::ReadAllText($f);
            $s=explode(IGK_LF, $txt);
            $t=& $this->getLoadTables();
            array_slice($t, count($t));
            $sk=array();
            foreach($s as  $v){
                if(empty($v))
                    continue;
                $db=explode(":", $v);
                if(count($db) < 1)
                    continue;
                $n=$db[1];
                if(isset($sk[$n]))
                    $ctrl=$sk[$n];
                else{
                    $ctrl=igk_getctrl_from_classname($db[1]);
                    $sk[$n]=$ctrl;
                }
                if($ctrl){
                    $t[$db[0]]=$ctrl;
                }
                else{
                    igk_ilog("No ctrl found for ".$db[0], __FUNCTION__);
                }
            }
        }
        else{
            $this->_initTableInfo();
        }
    }
    ///<summary></summary>
    ///<param name="frm"></param>
    /**
    * 
    * @param mixed $frm
    */
    private function _showDataBaseBackup($frm){
        $v_dir= igk_io_applicationdir()."/".IGK_BACKUP_FOLDER;
        $bckdiv=$frm->addDiv();
        $bckdiv->h2()->Content = __("title.backup");
   
        $v_table=$frm->addTable()->setClass('igk-table-striped');
        $v_table->setCallback("getIsVisible", "return \$this->HasChilds;");
        $v_hasfile=false;
        if(is_dir($v_dir)){
            $hdir=opendir($v_dir);
            $v_theader=false;
            while(($f=readdir($hdir))){
                if(($f == ".") || ($f == ".."))
                    continue;
                if(!$v_theader){
                    $v_theader=true;
                    $li=$v_table->addTr();
                    IGKHtmlUtils::AddToggleAllCheckboxTh($li);
                    $li->add("th")->Content=__(IGK_FD_NAME);
                    $li->add("th")->Content=IGK_HTML_SPACE;
                    $li->add("th")->Content=IGK_HTML_SPACE;
                }
                $li=$v_table->addTr();
                $li->addTd()->addInput(IGK_STR_EMPTY, "checkbox");
                $li->addTd()->add("a", array("href"=>$this->getUri("downloadbackupfile&file=".$f)))->Content=$f;
                $li->addTd()->addAJXA($this->getUri("db_dbRestore&file=".$f))->addImg()->setAttributes(array(
                    "width"=>16,
                    "height"=>16, 
                    "src"=>R::GetImgUri(trim("db_restore_16x16")),
                    "alt"=>__("restore")
                ));
                
                // = igk_svg_use("restore");
                //IGKHtmlUtils::AddImgLnk($li->add("td", array("class"=>"igk-table-img-action_16x16")), $this->getUri("db_dbRestore&file=".$f), "db_restore_16x16");
                IGKHtmlUtils::AddImgLnk($li->add("td", array("class"=>"igk-table-img-action_16x16")), $this->getUri("dropBackup&file=".$f), "drop_16x16", "16px", "16px", "lb.dropbackup");
                $v_hasfile=true;
            }
            closedir($hdir);
            if($v_hasfile){
                $bar=$frm->addActionBar();
                $bar->addABtn($this->getUri("ClearBackup"))->Content=__("btn.clearAllBackup");
            }
            else{
                $frm->addDiv()->Content=__("Msg.NoBackgup");
            }
        }
        igk_html_toggle_class($v_table, "tr");
    }
    ///<summary></summary>
    ///<param name="r"></param>
    ///<param name="$c"></param>
    ///<param name="conf_title" default="null"></param>
    /**
    * 
    * @param mixed $r
    * @param mixed $c
    * @param mixed $conf_title the default value is null
    */
    private function _showDataBases($r, $c, $conf_title=null){
        $conf_title=$conf_title ?? array("class"=>"igk-cnf-title");
        igk_html_add_title($c, "title.DATABASES");
        $c->addHSep();
        $sdb=$this->SelectedDb;
        if(!empty($sdb)){
            $c->addDiv()->addTip()->Content=__("tip.db.selecteddb", $sdb);
        }
        else{
            $c->addDiv()->Content=__("No Database selected");
        }
        $div=$c->addDiv();
        $div["class"]="no-wrap";
        $frm=$div->add("form", 1);
        $frm["action"]=$this->getUri("dataview");
        $v_table=$frm->addDiv()->setClass("overflow-x-a")->addTable()->setClass("igk-table-striped");
        $v_theader=false;
        if($r->RowCount > 0){
            if($this->SelectedDb == null)
                $this->SelectedDb=strtolower(igk_app()->Configs->db_name);
            foreach($r->Rows as $tab){
                if(!$v_theader){
                    $v_theader=true;
                    $li=$v_table->addTr();
                    IGKHtmlUtils::AddToggleAllCheckboxTh($li);
                    $li->add("th", array("class"=>"fitw"))->Content=__("clDataBaseName");
                    $li->add("th")->Content=IGK_HTML_SPACE;
                    $li->add("th")->Content=IGK_HTML_SPACE;
                }
                foreach($tab as  $v){
                    $tr=$v_table->addTr();
                    if(strtolower($v) == $this->SelectedDb){
                        $tr["class"]="+igk-selectdb-row";
                    }
                    $tr->addTd()->addInput(IGK_STR_EMPTY, "checkbox");
                    $tr->addTd()->addLi()->add("a", array("href"=>$this->getUri("selectdb&n=".$v)))->Content=$v;
                    $tr->addTd()->addLi()->add("a", array("href"=>$this->getUri("editdb&n=".$v)))->add("img", array(
                        "width"=>"16px",
                        "height"=>"16px",
                        "src"=>R::GetImgUri("edit_16x16"),
                        "alt"=>__("tip.editdatabase")
                    ));
                    $tr->addTd()->addLi()->add("a", array("href"=>$this->getUri("dropdb&n=".$v)))->add("img", array(
                        "width"=>"16px",
                        "height"=>"16px",
                        "src"=>R::GetImgUri("drop_16x16"),
                        "alt"=>__("tip.dropdatabase")
                    ));
                }
            }
        }
        $frm->addBr();
        igk_html_toggle_class($v_table, "tr");
        $frm=$div->add("form", 1);
        $frm["id"]="details";
        $frm["action"]=$this->getUri("viewtables");
        return $frm;
    }
    ///$c target node
    /**
    */
    private function _showSelectedDbTables($c, $conf_title=null, $selected=1){
        $k="request:".__FUNCTION__;
        $h=$this->getParam($k);
        $c->setId("tableview");
        if(!igk_is_ajx_demand()){
            if(!$h){
                $this->setParam($k, 1);
                $c->addDiv()->addAJXScriptContent($this->getUri("demandToShowDataBase_ajx"));
            }
            else{
                $this->getParam($k, null);
                igk_die(array(
                    "code"=>404,
                    "message"=>"/!\\ loaping on getting database is not allowed."
                ));
            }
            return;
        }
        igk_wln_e(__LINE__." selected table name");
        $db = "";
        $v_search=$this->getFlag('db:searchtable');
        $this->setParam($k, null);
        $tab=$this->_getTablesFromSelectedDb($db);
        if(count($tab)<=0){
            return;
        }
        igk_html_add_title($c, "title.Tables");
        $c->addHSep();
        $s=new IGKHtmlSearchItem($this->getUri("searchtable"), $v_search);
        $c->addDiv()->add($s);
        $div=$c->addDiv()->setId("igkdb_tablelist");
        $frm=$div->addForm();
        $frm["action"]=$this->getUri("db_dropSelectedTable");
        $frm->addDiv()->Content=$tab->RowCount;
        $table=$frm->addDiv()->setStyle("overflow:auto;")->addTable();
        $tr=$table->addTr();
        IGKHtmlUtils::AddToggleAllCheckboxTh($tr);
        $tr->add("th", array("class"=>"fitw"))->Content=__("lb.Name");
        $tr->add("th")->Content=IGK_HTML_SPACE;
        $tr->add("th")->Content=IGK_HTML_SPACE;
        igk_html_paginate($table, $frm, $tab->Rows, 10, function($table, $k, $v){
            $s=$v["Table"];
            $tr=$table->addTr();
            $tr->addTd()->addInput("tname[]", "checkbox", $s);
            $tr->addTd()->add("a", array("href"=>igk_js_post_frame($this->getUri("db_viewtableentries_ajx&n=".$s."&from=".$this->SelectedDb))))->Content=$s;
            $this->__addEditTable($tr, $s, $this->SelectedDb);
            $tr->addTd()->addLi()->add("a", array("href"=>igk_js_post_frame($this->getUri("db_droptable_ajx&n=".$s))))->add("img", array(
            "width"=>"16px",
            "height"=>"16px",
            "src"=>R::GetImgUri("drop_16x16"),
            "alt"=>__("tip.dropdatabase")
        ));
        }
        , igk_io_currenturi().'/'.$this->getUri("viewtable&v="), $selected);
        $div=$frm->addActionBar();
        $div->addAJXA($this->getUri("db_dropSelectedTable_ajx"))->Content=__("btn.droptableselection");
        $div->addAJXA($this->getUri("db_drop_alltable_ajx"))->Content=__("btn.dropall");
    }
    ///<summary></summary>
    ///<param name="reset"></param>
    /**
    * 
    * @param mixed $reset the default value is 0
    */
    private function _storeDbCache($reset=0){
   
        $f=$this->getCacheFile();
        $o="";
        $tables=& $this->getLoadTables();
        if($tables){
            $kt=0;
            foreach($tables as $k=>$v){
                if($kt){
                    $o .= IGK_LF;
                }
                $o .= $k.":".get_class($v);
                $kt=1;
            }
        }
        igk_io_w2file($f, $o, true);
        if(!$reset){
            array_slice($tables, count($tables));
        }
    }
    ///<summary></summary>
    ///<param name="zdiv"></param>
    /**
    * 
    * @param mixed $zdiv
    */
    private function _view_conf_backup($zdiv){
        $this->_showDataBaseBackup($zdiv->addPanelBox());
    }
    ///<summary>shows datas base </summary>
    /**
    * shows datas base
    */
    private function _view_conf_datas($zdiv){
        $zdiv->clearChilds(); 
        $frm=null;
        $mysql=igk_get_data_adapter(IGK_MYSQL_DATAADAPTER, true);
        $v_table=null;
        $pan=$zdiv->addPanelbox();
        $pan->h2()->Content=__("Datas");
        $h=$pan->addRow();
        $div1=$h->addCol("igk-col-3-3")->addDiv()->setClass("db_info");
        $conf_title=null;
        if($mysql){
            if($mysql->connect()){
                try {
                    $r=$mysql->sendQuery("SHOW DATABASES");
                    if($r && !$r->ResultTypeIsBoolean() && ($r->RowCount > 0)){
                        $this->_showDataBases($r, $div1, $conf_title);
                        $div1=$div1->addActionBar();
                        $div1->addABtn($this->getUri("pinitSDb"))->Content=__("btn.initDb");
                        $div1->addABtn($this->getUri("backupDb"))->Content=__("btn.backupdatabase");
                    }
                    else{
                        $row=$div1->addDiv()->addRow();
                        $row->addCol()->Content=__("msg.cantshowdatabase");
                        $this->_db_viewTables($h, $mysql);
                    }
                    if(!empty($this->SelectedDb)){
                        $div2=$h->addCol("igk-col-3-2")->addDiv();
                        $this->_showSelectedDbTables($div2, $conf_title);
                    }
                }
                catch(Exception $ex){
                    $this->_db_viewTables($h->addCol("igk-col-3-3"), $mysql);
                }
                $mysql->close();
            }
            else{
                $div1->addPanel()->Content=__("Failed to connect and select the base MySQL's Database");
            }
        }
    }
    ///<summary></summary>
    ///<param name="zdiv"></param>
    /**
    * 
    * @param mixed $zdiv
    */
    private function _view_conf_general($zdiv){
        $pan=$zdiv->addPanelBox();
        $pan->h3()->Content = __("Mysql Database Configuration");
        $pan->div()->p()->addArticle(null, "help/mysql.db.config.help");

        $frm=$pan->addForm();
        $frm->setStyle("max-width:300px;");
        $frm["method"]="POST";
        $frm["action"]=$this->getUri("updatedb");
        $cnf = igk_app()->Configs;
        $frm->addFields(
			[
				"dbServer"=>["attribs"=>["class"=>"igk-form-control required" , "placeholder"=>__("Server"), "value"=>$cnf->db_server]],
				"dbUser"=>["attribs"=>["class"=>"igk-form-control", "placeholder"=>__("user"), "value"=>$cnf->db_user]],
				"dbPasswd"=>["type"=>"password", "attribs"=>["class"=>"igk-form-control", "placeholder"=>__("password"), "value"=>null]],
				"dbName"=>["attribs"=>["class"=>"igk-form-control", "placeholder"=>__("dbname"), "value"=>$cnf->db_name]],
				"dbPort"=>["attribs"=>["class"=>"igk-form-control", "placeholder"=>__("dbport"), "value"=>$cnf->db_port]],
			]
        );
         
        $frm->addBr();
        $_cbar=$frm->addActionBar();
        $_cbar->addBtn("btn_update", __("Update"))->setClass("-clsubmit +igk-btn");
        $check_uri=$this->getUri("check_con_ajx");
        $_cbar->addBtn("btn_checkconnect", __("Check connection"))->setClass("-clsubmit +igk-btn")->setAttribute("onclick", "javascript: ns_igk.ajx.get('{$check_uri}'); return false");
    }
    ///<summary></summary>
    ///<param name="zdiv"></param>
    /**
    * 
    * @param mixed $zdiv
    */
    private function _view_conf_query($zdiv){
        ///TODO: query selector tool 
        $pan=$zdiv->addPanelBox();
        $pan->h2()->Content =  __("MySQL Query Tool");        
        $h=$pan->addDiv()->addRow();
        $dv=$h->addCol("fitw")->addDiv();
        $frm=$dv->addForm();
        $frm["action"]=$this->getUri("__db_query_r_ajx");
        $frm["igk-ajx-form"]=1;
        $frm["igk-ajx-form-no-autoreset"]=1;
        $frm["igk-ajx-form-target"]="#query-s-r";
        $row = $frm->row();
        $row->col("igk-col-12-9")->div()->addTextArea()->setId("clQuery")->setClass("igk-form-control fitw-i")->setStyle("height:150px")
        ->Content=igk_getr('clQuery') ?? "Select * From `table` ";

        $ul = $row->col("igk-col-12-3")->div()->setId("query_helper")->ul();
        if ($r = $this::db_query("SHOW TABLES")){
            $g = $r->getColumns();
            $clname = $g[0]->name; 
            $ul->loop($r->getRows())->host(function($n,$i)use($clname){                
                $n->li()->a("#")->on("click", "\$igk('#clQuery').first().o.value = 'SELECT * from `'+this.o.innerHTML+'`';")->content = $i->{$clname};
            });
            $ul->setStyle("max-height: 150px; overflow-x:clip; overflow-y:auto;");
        }

        $acb=$frm->addActionBar();
        $acb->addInput("btn.send", "submit", __("btn.send"))->setClass("-clsubmit +igk-btn igk-btn-default");
        $dv->addDiv()->setId("query-s-r")->setClass("fitw-i");
    }
    ///<summary></summary>
    ///<param name="zdiv"></param>
    /**
    * 
    * @param mixed $zdiv
    */
    private function _view_conf_tools($zdiv){
        $pan=$zdiv->addPanelBox(); 
        $pan->div()->h2()->Content= __("Tools");
        $pan->notifyhost("mysql:tools");
        $frm=$pan->form();
        $bar=$frm->addActionBar();
        if (igk_server_is_local() && ($h=igk_app()->Configs->phpmyadmin_uri)){
            $bar->addABtn($this->getUri("gotophpmyadmin"))->Content=__("btn.phpmyadmin");
        }
        
        $bar->addABtn($this->getUri("pinitSDb"))->Content=__("btn.initDb");
        $bar->addABtn($this->getUri("backupDb"))->Content=__("btn.backupdatabase");
        $bar->addABtn($this->getUri("pdropDb"))->Content = __("Drop database");
        $bar->addABtn($this->getUri("pMigrate"))->Content = __("Migrate");
        if (!igk_environment()->is("OPS"))
            $bar->addABtn($this->getUri("pSeed"))->Content = __("Seed");
        $bar->addAJXA($this->getUri("db_reload_sys_tables_ajx"))->setClass("igk-btn")->Content=__("btn.reloadsystables");
    }
    private function notifyctrl(){
        return igk_notifyctrl("mysql:tools");
    }
  
    public function pDropDb(){
        if (igk_is_conf_connected()){
            $c = new \IGK\System\Console\Commands\MysqlCommand();
            ob_start();
            $o = $c->exec((object)[
                "options"=>(object)["--action"=>"dropdb"]
                ]);
                $r = ob_get_clean();
                $this->notifyctrl()->success("database cleaned");
        }
        return igk_navtocurrent();
    }
    public function pMigrate(){
        if (igk_is_conf_connected()){
            $c = new \IGK\System\Console\Commands\MysqlCommand();
            ob_start();
            $o = $c->exec((object)[
                "options"=>(object)["--action"=>"migrate"]
            ]);
            $r = ob_get_clean();
            $this->notifyctrl()->success(__("database migrate"));
        }
        return igk_navtocurrent();
    }
    public function pSeed(){
        if (igk_is_conf_connected()){
            if (igk_environment()->is("OPS")){
                $this->notifyctrl()->error(__("database seeding is not for ops"));
                return igk_navtocurrent();
            }
            $c = new \IGK\System\Console\Commands\MysqlCommand();
            ob_start();
            $o = $c->exec((object)[
                "options"=>(object)["--action"=>"seed"]
            ]);
            $r = ob_get_clean();
            igk_notifyctrl("mysql:tools")->success(__("database seed") );
        }
        return igk_navtocurrent();
    }
    ///<summary>entry to backup mysql database</summary>
    /**
    * entry to backup mysql database
    */
    public function backupDb(){
        $mysql=igk_get_data_adapter($this, true);
        if(!$mysql){
            igk_notifyctrl()->addError("can't get ".IGK_MYSQL_DATAADAPTER. " data adapter");
            return;
        }
        $adapter=igk_get_data_adapter(IGK_CSV_DATAADAPTER);
        if(!$adapter){
            igk_notifyctrl()->addError("can't get csv adapter");
            return;
        }
        $v_date=igk_date_now("Ymd_his");
        $v_file= igk_io_applicationdir()."/".IGK_BACKUP_FOLDER."/backup_".$v_date.".csv";
        $out=IGK_STR_EMPTY;
        $db_table=$this->_getTables(null);
        $warn="";
        if($mysql->connect()){
            if($db_table){
                foreach($db_table->Rows as $t=>$v){
                    $v_tbname=igk_getv($v, "Table");
                    // $r=$mysql->sendQuery("SELECT * FROM `".$v_tbname."`;");
                    ///TODO: SELECT
                    $r= $mysql->getGrammar()->createSelectQuery($v_tbname);  // sendQuery("SELECT * FROM `".$v_tbname."`;");
                    if($r){
                        $out .= $v_tbname. IGK_LF;
                        $v_sep=false;
                        $out .= $adapter->toCSVLineEntry($r->Columns, "name").IGK_LF;
                        if($r->Rows){
                            foreach($r->Rows as $e){
                                $out .= $adapter->toCSVLineEntry($e).IGK_LF;
                            }
                        }
                        else{
                            $warn .= ("notice: no data row for [".$v_tbname."]\r\n");
                        }
                        $out .= "\0".IGK_LF;
                    }
                    else{
                        igk_debug_wln("error: mysql adapter failed");
                        igk_debug_wln($r);
                        igk_notifyctrl()->addMsgr("Error.Config");
                    }
                }
            }
            else{
                igk_debug_wln("error: no table");
                igk_notifyctrl()->addMsgr("Msg.NoTable");
            }
            $mysql->close();
        }
        if(!empty($warn) && !igk_sys_env_production()){
            igk_ilog($warn);
        }
        IGKIO::CreateDir(dirname($v_file));
        if(strlen($out) > 100000){
            igk_zip_content($v_file.".zip", basename($v_file), $out);
        }
        else{
            igk_io_save_file_as_utf8($v_file, $out, true, 0666);
        }
        $this->View();
        igk_notifyctrl()->addMsgr("msg.dataBackup");
        igk_navtocurrent();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function check_con_ajx(){
        igk_reset_db_dataadapter();
        $ad=igk_get_data_adapter("MYSQL");
        $con="failed";
        $type = "igk-danger";
        igk_set_env("sys://Db/NODBSELECT", 1);
        if($ad && $ad->connect()){
            $con="success";
            $type = "igk-success";
            $ad->close();
        }else {

        }
        igk_ajx_toast(implode(" ", [__("Connection:"), __($con)]), $type);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearBackup(){
        if(igk_qr_confirm()){
            $v_dir=igk_io_applicationdir()."/".IGK_BACKUP_FOLDER.
            IGKIO::RmDir($v_dir);
            $this->View();
            igk_navtocurrent();
        }
        else{
            $frame=igk_frame_add_confirm($this, "confirm_Clear_backup_file", $this->getUri("ClearBackup"));
            $frame->Form->Div->Content=__(IGK_MSG_DELETEALLDATABASEBACKUP_QUESTION);
        }
    }
    ///<summary>backup table associated to a controller</summary>
    /**
    * backup table associated to a controller
    */
    public function db_backup_tables($ctrl, $outtag, $dbname=null, $storetableinfo=true){
        $tables=$this->getTablesFor($ctrl);
        if(!is_array($tables))
            return;
        $ad=igk_get_data_adapter($this, true);
        if($ad && $ad->connect($dbname)){
            $e=$outtag->addNode(IGK_ENTRIES_TAGNAME);
            foreach($tables as  $v){
                $tabinfo=$this->getDataTableDefinition($v, false);
                if(!$tabinfo)
                    continue;
                if($storetableinfo){
                    $def=$outtag->addNode(IGK_DATA_DEF_TAGNAME);
                    $def["TableName"]=$v;
                    foreach($tabinfo as $ck=>$c){
                        switch($ck){
                            case "Description":
                            break;default:
                            foreach($c as  $nn){
                                $col=$def->add(IGK_COLUMN_TAGNAME);
                                $col->setAttributes($nn);
                            }
                            break;
                        }
                    }
                }
                $table=$e->addNode($v);
                $r=$ad->selectAll($v);
                foreach($r->Rows as $m=>$n){
                    $table->addNode(IGK_ROW_TAGNAME)->setAttributes($n);
                }
            }
            $ad->close();
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_Clearall_db_entry(){
        $r=$this->getParam("db:r");
        $dbname=$this->getParam("db:dbname");
        $tbname=$this->getParam("db:table");
        if(igk_qr_confirm()){
            $db=igk_get_data_adapter($this, true);
            if($db){
                $db->connect();
                $db->selectdb($dbname);
                $db->deleteAll($tbname);
                $db->close();
            }
            $this->db_viewtableentries($dbname, $tbname);
        }
        else{
            $frame=igk_frame_add_confirm($this, "frame_Clearall", $this->getUri("db_Clearall_db_entry"));
            $frame->Form->Div->Content=__("Q.WILLYOUDROPTHECONTENTOFTHISTABLE", $tbname);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_dbRestore(){
        $v_f=igk_getr("file");
        $v_mode=igk_getr("mode", 1);
        
     
        if(igk_qr_confirm() && $v_mode){
            $r= igk_io_applicationdir()."/".IGK_BACKUP_FOLDER."/".$v_f;
            $v_file=igk_io_basedir($r);
            if(!file_exists($v_file))
                igk_notifyctrl()->addErrorr("msg.nodatatorestore");
            else{
                $str=igk_io_read_allfile($v_file);
                $h=explode("\0", $str);
                $table=null;
                $definition=null;
                $header=null;
                $warn="";
                $syncdata=array();
                foreach($h as  $v){
                    $lines=explode(IGK_LF, trim($v));
                    $entries=array_slice($lines, 2);
                    $table=trim($lines[0]);
                    $header=igk_array_tokeys(IGKCSVDataAdapter::LoadString(trim($lines[1]))[0]);
                    $entriecs=array();
                    $definition=IGKCSVDataAdapter::LoadString(implode(IGK_LF, $entries), true, $header);
                    if(igk_count($definition) == 0)
                        continue;
                    $th=igk_count($header);
                    $ec=-1;
                    foreach($definition as $m=>$n){
                        $ec++;
                        if(igk_count($n) != $th){
                            $warn .= "column header doest not match the entries definition of : {$table} : {$ec} ".IGK_LF;
                            continue;
                        }
                        $row=igk_createobj($header);
                        $i=0;
                        foreach($header as $ss=>$tt){
                            $row->{$ss}=$n[$i];
                            $i++;
                        }
                        $entriecs[]=$row;
                    }
                    $syncdata[$table]=$entriecs;
                }
                $adapt=igk_get_data_adapter(IGK_MYSQL_DATAADAPTER);
                if(($adapt != null) && ($adapt->connect())){
                    $adapt->beginTransaction();
                    $adapt->sendQuery("SET foreign_key_checks = 0;");
                    $_syncfc=function($adapt, $t, $e, $m){
                        $ge=$adapt->sendQuery("SELECT * FROM `{$t}` LIMIT 0");
                        $tbinfo=igk_db_getdatatableinfokey($t);
                        $o=igk_createobj();
                        foreach($ge->Columns as $d){
                            if(!isset($tbinfo[$d->name])){
                                igk_set_error("sys", "data table columns not match", 0xDB0001);
                                return 0;
                            }
                            $o->{$d->name}=null;
                        }
                        $r=0;
                        switch($m){
                            case 1:
                            $r=$adapt->delete($t);
                            if($r){
                                if(is_array($e)){
                                    foreach($e as $v){
                                        $mm=igk_createobj_filter($v, $o);
                                        $r=$r && $adapt->insert($t, $mm);
                                    }
                                }
                                else
                                    $r=$r && $adapt->insert($t, $e);
                            }
                            break;
                        }
                        return $r;
                    };
                    $r=1;
                    foreach($syncdata as $t=>$e){
                        $e_x=$adapt->sendQuery("SELECT * FROM `{$t}` LIMIT 0");
                        if(!$e_x){
                            $warn .= "table [{$t}] does not exists".IGK_LF;
                            continue;
                        }
                        $r=$r && $_syncfc($adapt, $t, $e, $v_mode);
                        if(!$r)
                            break;
                    }
                    $adapt->sendQuery("SET foreign_key_checks = 1;");
                    if($r){
                        $adapt->commit();
                    }
                    else{
                        $adapt->rollback();
                    }
                    $adapt->close();
                }
            }
            $this->View();
        }
        else{
            // $frame=igk_frame_add_confirm($this, "confirm_restoration", $this->getUri("db_dbRestore"));
            // $frame->Form->Div->Content=__(IGK_MSG_RESTOREBACKUPFILE_QUESTION, $v_f);
            // $frame->Form->addInput("file", "hidden", $v_f);

            $form = igk_createnode("form");
            $form["action"] = $this->getUri("db_dbRestore");
            $form->div()->setStyle("max-width: 200px")->Content=__(IGK_MSG_RESTOREBACKUPFILE_QUESTION, $v_f);
            $form->addInput("file", "hidden", $v_f);
            $form->addInput("confirm", "hidden", "1");
            $form->actionbar(
                IGKHtmlUtils::ConfirmAction()
               );
            igk_ajx_panel_dialog(__("Confirm db restore"), $form);

        }
    }
    ///<summary>drop all tables</summary>
    /**
    * drop all tables
    */
    public function db_drop_alltable_ajx(){
        if(igk_qr_confirm()){
            $this->db_drop_sys_tables();
            igk_ajx_replace_ctrl_view($this);
            igk_exit();
        }
        $b=igk_createnode();
        $frm=$b->addForm();
        $frm["action"]=$this->getUri(__FUNCTION__);
        $frm["igk-ajx-form"]=1;
        $frm["igk-ajx-form-data"]="{complete: function(){ ns_igk.winui.notify.close(); }}";
        $frm->addConfirm(1);
        igk_html_binddata($this, $frm->addDiv(), "confirm.dialog.template", (object)array("clMessage"=>__("msg.confirmalltablesuppression")));
        $frmdial=igk_ajx_notify_dialog(__("title.confirmalltablesuppression"), $b);
        $frmdial->RenderAJX();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_drop_entry(){
        $n=igk_getr("n");
        $s=igk_getr("s");
        $dbname=$this->getParam("db:dbname");
        $table=$this->getParam("db:table");
        $db=igk_get_data_adapter($this, true);
        if($db && $db->connect()){
            $db->selectdb($dbname);
            $db->delete($table, array($s=>$n));
            $db->close();
        }
        $this->db_viewtableentries($dbname, $table);
    }

        ///<summary>drop all tables</summary>
    /**
    * drop all tables
    */
    public function db_drop_sys_tables(){
        // $tab=$this->getAllTableNames();
        $ldtables = $this->getLoadTables();
        $tab = array_keys($ldtables);
        $attrb=array();
        $ad=null;
        $adapter = igk_get_data_adapter($this);
        $cl= get_class($adapter);
        igk_ilog("START: drop system database");
        $t = igk_start_time(__METHOD__);
        foreach($tab as $v){
            $c=isset($ldtables[$v]) ? $ldtables[$v]: "";
            if (empty($c)){
                igk_ilog("info table not found : ".$v);
                continue;
            }
            $adapter = igk_get_data_adapter($c);
            $inf=null;
            if(isset($attrb[$cl])){
                $inf=$attrb[$cl];
            }
            else{
                $inf=(object)array('adapter'=>$adapter, 'tables'=>array());
            }
            $inf->tables[]=$v;
            $attrb[$cl]=$inf;
        }

        foreach($attrb as  $v){
            $ad=$v->adapter;
            if($ad && $ad->connect()){
                $ad->initForInitDb();
                $ad->dropTable($v->tables);
                $ad->flushForInitDb();
                $ad->close();
            } 
        }
        igk_ilog("END: drop system database ". igk_execute_time(__METHOD__));
    }

    ///<summary>drop table associated to a controller</summary>
    /**
    * drop table associated to a controller
    */
    public function db_drop_tables($ctrl, $dbname=null){
        $tables=$this->getTablesFor($ctrl);
        if(!is_array($tables))
            return;
        $ad=igk_get_data_adapter($ctrl, true);
        if($ad && $ad->connect($dbname)){
            $ad->dropTable($tables);
            $ad->close();
        }
    }
    ///<summary></summary>
    ///<param name="dbname" default="null"></param>
    /**
    * 
    * @param mixed $dbname the default value is null
    */
    public function db_dropSelectedTable_ajx($dbname=null){
        if(!$this->ConfigCtrl->IsConnected)
            return;
        $db=$dbname ? $dbname: igk_app()->Configs->db_name;
        if(igk_qr_confirm()){
            if($db == null){
                igk_wln("no db name selected");
                return;
            }
            $h=null;
            $adapter=igk_get_data_adapter($this, true);
            if(!$adapter)
                return;
            $query=$this->getParam("db:dropSelectedTable");
            $h=igk_getv($query, "tname");
            if($h && $adapter->connect($db)){
                if($adapter->dropTable($h)){
                    igk_notifyctrl()->addMsgr("msg.db.tabledelete_1", igk_count($h));
                }
                else{
                    igk_notifyctrl()->addErrorr("e.db.deletetableerror_1", igk_debuggerview()->getMessage());
                }
                $adapter->close();
            }
            else{
                igk_notifyctrl()->addError("Aucune table selectionner");
            }
            $this->setParam("frame:tname", null);
            $this->setParam("db:dropSelectedTable", null);
            igk_notifyctrl()->addMsgr("msg.databaseupdated");
            $c=igk_debuggerview()->getMessage();
            if(!empty($c))
                igk_notifyctrl()->addError($c);
            $this->View();
            igk_navtocurrent();
        }
        else{
            $tname=igk_getv($_REQUEST, "tname");
            if($tname && (count($tname) > 0)){
                $frame=igk_frame_add_confirm($this, "confirm_Clear_backup_file", $this->getUri("db_dropSelectedTable_ajx"));
                $frame->Form->Div->Content=__("q.deleteallselecteddbtable");
                $frame->Form->addInput("dbname", "hidden", $dbname);
                $this->setParam("db:dropSelectedTable", $_REQUEST);
                $frame->RenderAJX();
            }
        }
    }
    ///<summary></summary>
    ///<param name="dbname" default="null"></param>
    ///<param name="table" default="null"></param>
    ///<param name="navigate" default="false"></param>
    /**
    * 
    * @param mixed $dbname the default value is null
    * @param mixed $table the default value is null
    * @param mixed $navigate the default value is false
    */
    public function db_droptable($dbname=null, $table=null, $navigate=false){
        $n=igk_getr("n", $table);
        if(igk_qr_confirm()){
            $mysql=igk_get_data_adapter($this, true);
            if(!$mysql)
                return;
            $dbname=igk_getr("from", $dbname);
            if($mysql->connect($dbname)){
                $r=$mysql->dropTable($n);
                $mysql->close();
                if(igk_is_ajx_demand()){
                    if($r){
                        igk_ajx_toast('datatable : '.$n, 'success');
                    }
                    else{
                        igk_ajx_toast('failed : '.$n, 'danger');
                    }
                }
            }
            if($navigate){
                $this->View();
                igk_navtocurrent("./#igkdb_tablelist");
                igk_exit();
            }
        }
        else{
            $frame=igk_frame_add_confirm($this, "confirm_drop_table", $this->getUri("db_droptable"));
            $frame->Form->Div->Content=__(IGK_MSG_DELETESINGLETABLE_QUESTION, $n);
            $frame->Form->addInput("n", "hidden", $n);
            return $frame;
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_droptable_ajx(){
        $frame=$this->db_droptable();
        if($frame != null){
            $frame->RenderAJX();
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_edit_entry_ajx(){
        $n=igk_getr("n");
        $s=igk_getr("s");
        $dbname=$this->getParam("db:dbname");
        $table=$this->getParam("db:table");
        $this->db_edit_entry_frame($this, $dbname, $table, $n, $s, true);
    }
    public function getIgnoreList($table){
        static $ignoreList = null;
        if ($ignoreList === null){
            $tb = igk_db_get_table_name(IGK_TB_USERS);
            $ignoreList = [
                $tb=>["clPwd", "clDate", "clLastLogin", "clPicture", "clId" ]
            ];
        }
        return igk_getv($ignoreList, $table, ["clId"]);
    }
    public function getFieldHandler($table){
        $tb = igk_db_get_table_name(IGK_TB_USERS);
        $r = array_merge([$tb=>function($columninfo, $li, $value, $iparam){
            $v = $columninfo->name;
            switch($columninfo->name){
                case "clClassName":
                    $li->add("label")->Content= __("lb.{$v}");
                        $sl=$li->add("select");
                        $sl->setId($columninfo->name);
						$sl->setClass("igk-form-control");
                        $sl->add("option")->setAttributes(array("value"=>"0"));
                        $h=1;
                        $cllist=igk_sys_getuserctrls();
                        $tcllist=array();
                        foreach($cllist as $b=>$c){
                            $n = get_class($c);
                            $o=$sl->add("option");
                            $o['value']=$h;
                            $o->Content=$n;
                            $h++;
                            $tcllist[]=$c;
                            if($n == $value){
                                $o["selected"]="true";
                            }
                        }    
                        $iparam->setParam("update:cllist", array($v=>$tcllist));                    
                        return 1;
                    
                case "clParent_Id": 
                    return 1;
            }
        }], igk_environment()->{"db.fieldhandler"} ?? [] );
        return igk_getv($r, $table);
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    ///<param name="dbname"></param>
    ///<param name="table"></param>
    ///<param name="n"></param>
    ///<param name="s"></param>
    ///<param name="render" default="true"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $dbname
    * @param mixed $table
    * @param mixed $n
    * @param mixed $s
    * @param mixed $render the default value is true
    */
    public function db_edit_entry_frame($ctrl, $dbname, $table, $n, $s, $render=true){
        $this->setParam("update:n", $n);
        $this->setParam("update:s", $s);
        $frm=igk_createnode("form");
        $frm["action"]=$this->getUri("db_update_entry");
        $frm["class"] = ["db-update-entry-form"];
        $frm->setStyle("min-width: 360px");
        $frm->addInput("cltable", "hidden", $table);
        $frm->addInput("cldb", "hidden", $dbname);
        $frm->addInput("cln", "hidden", $n);
        $frm->addInput("cls", "hidden", $s);
        igk_html_form_initfield($frm);

        if($ctrl !== $this){
            $frm->addInput("clexternal", "hidden", 1);
            $frm->addInput("ctrl", "hidden", $ctrl->Name);
        }
        $table = igk_db_get_table_name($table);
        $mysql=igk_get_data_adapter($ctrl, true);
        if ( $mysql != null )
        {
            $mysql->connect($dbname);
            $e=$mysql->select($table, array($s=>$n));
            $ignore_list = $this->getIgnoreList($table);
            $field_handler = $this->getFieldHandler($table);

            if($e->RowCount == 1){
                $ul=$frm->add("ul");
                $l=$e->getRowAtIndex(0);
                foreach($e->Columns as $k){
                    $li=$ul->addLi();
                    $v=$k->name;
                    if (in_array($v, $ignore_list))
                        continue; 
                    if ($field_handler && $field_handler($k, $li, $l->$v, $this)){
                        continue;
                    }
                    igk_html_build_form_array_entry($v, $k->typeName, $li, $l->$v);
                }
            }
            
            $mysql->close();
        }
        $frm->addHSep();
        $frm->addInput("btn_update", "submit", __("Update"));
        if($render){
            igk_ajx_panel_dialog(__("Edition") . " : ". $table, $frm);
        }
        return $frm;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_insert_db_entry(){
        $r=$this->getParam("db:r");
        $dbname=$this->getParam("db:dbname");
        $n=$this->getParam("db:table");
        $b=igk_get_robj();
        $db=igk_get_data_adapter($this, true);
        if($db){
            $db->connect();
            $db->selectdb($dbname);
            $t=igk_db_getdatatableinfokey($n);
            $db->insert($n, (array)$b, $t);
            $db->close();
        }
        $this->db_viewtableentries($dbname, $n);
        igk_exit();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_insert_db_entry_frame_ajx(){
        $frame=igk_html_frame($this, __FUNCTION__);
        $frame->ClearChilds();
        $frame->Title=__("title.db.insertnewentry");
        $d=$frame->BoxContent;
        $frm=$d->addForm();
        $frm["action"]=$this->getUri("db_insert_db_entry");
        $ul=$frm->add("ul");
        $c=$this->getParam("db:columns");
        if($c){
            foreach($c as $k){
                $li=$ul->addLi();
                $pwd=$k->name == IGK_FD_PASSWORD;
                switch(strtolower($k->type)){
                    case "blob":
                    $li->addSLabelTextarea($k->name, "lb.".$k->name, array("class"=>"-cltextarea"));
                    break;
                    case "string":
                    case "text":default: $li->addSLabelInput($k->name, $pwd ? "password": "text", $pwd ? "": $k->def);
                    break;
                }
            }
        }
        $frm->addHSep();
        $frm->addBtn("btn_add", __("btn.add"));
        $frame->RenderAJX();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_reload_sys_tables_ajx(){
        if(igk_qr_confirm()){
            $this->db_drop_sys_tables();
            $this->pinitSDb(true);
            $this->View();
            igk_ajx_replace_node($this->ConfigNode);
            igk_createnode("Toast")->setContent("Operation Complete")->RenderAJX();
            igk_exit();
        }
        else{
            $frm = igk_createnode("form");
            $frm->div()->p()->Content = __("q.reloadsystables");            
            $frm->actionbar(IGKHtmlUtils::ConfirmAction()); 
            igk_ajx_panel_dialog(__("Confirm"), $frm);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function db_update_entry(){
        if(!$this->ConfigCtrl->getIsConnected())
            return;
 

        $dbname=igk_getr("cldb", $this->getParam("db:dbname"));
        $table= igk_db_get_table_name(igk_getr("cltable", $this->getParam("db:table")));
        $n=igk_getr("cln", $this->getParam("update:n"));
        $s=igk_getr("cls", $this->getParam("update:s"));
        $tclist=$this->getParam("update:cllist");
        $this->setParam("update:cllist", null);
        $adapter=igk_get_data_adapter($this, true);
        $ext=igk_getr('clexternal');
        if($adapter){
            
            $o=igk_get_robj();
            $properties = igk_array_filter((array)$o, array_fill_keys($v_list = ["cln","cldb", "cls","cltable","clexternal"], null));
            foreach($v_list as $m){
                unset($o->$m);
            }           
            foreach($tclist as $k=>$v){
                $ii=$o->$k - 1;
                if($ii>=0)
                    $o->$k=get_class($v[$ii]);
            }
            $adapter->connect($dbname);
            $adapter->update($table, $o, array($s=>$n), igk_db_getdatatableinfokey($table));
            $adapter->close(); 
        }
        if(!$ext){
            $this->db_viewtableentries($dbname, $table);
        }
        else{
            $ctrl=igk_getctrl(igk_getr("ctrl"));
            if($ctrl != null){
                $ctrl->View();
            }
        }
    }
    ///<summary></summary>
    ///<param name="dbname" default="null"></param>
    ///<param name="table" default="null"></param>
    ///<param name="navigate" default="true"></param>
    ///<param name="adapter" default="null"></param>
    /**
    * 
    * @param mixed $dbname the default value is null
    * @param mixed $table the default value is null
    * @param mixed $navigate the default value is true
    * @param mixed $adapter the default value is null
    */
    public function db_viewtableentries($dbname=null, $table=null, $navigate=true, $adapter=null){
        $tb=$table ? $table: igk_getr("n");
        $db=$dbname == null ? igk_getr("from"): $dbname;
        $mysql=$adapter == null ? igk_get_data_adapter($this, true): $adapter;
        if(!$mysql)
            return;
        $mysql->connect($db);
        $r=$mysql->selectAll($tb);
        $frame=igk_html_frame($this, "db_view_entries", "./#igkdb_tablelist");
        $frame->Title=__("title.db_viewtableentries_1", $tb);
        $frame->ClearChilds();
        $div=$frame->BoxContent->addDiv();
        $div["class"]="igk-db-tableentries";
        $title=$div->addDiv();
        $div->addHSep();
        $title->setClass("title");
        $content=$div->addDiv();
        $content["class"]="datas";
        $content["style"]="overflow:auto;";
        $title->Content=__("title.TableInfo", $db.".".$tb);
        $table=$content->addTable();
        $table["class"]="fitw";
        $table["style"]="border:1px solid black";
        $tr=$table->addTr();
        $tr->add("th")->Content=IGK_HTML_SPACE;
        $pkey=null;
        $pkname=null;
        foreach($r->Columns as $k){
            $td=$tr->add("th");
            $td->Content=__($k->name);
            if(($pkey == null) && $k->primary_key == 1){
                $pkey=$k->name;
            }
        }
        $tr->add("th")->Content=IGK_HTML_SPACE;
        $tr->add("th")->Content=IGK_HTML_SPACE;
        foreach($r->Rows as $k){
            $tr=$table->addTr();
            $tr->addTd()->Content=IGK_HTML_SPACE;
            foreach($k as $e=>$v){
                $tr->addTd()->Content=($e == IGK_FD_PASSWORD) ? IGK_HTML_SPACE: $v;
            }
            IGKHtmlUtils::AddImgLnk($tr->addTd(), igk_js_post_frame($this->getUri("db_edit_entry_ajx&n=".$k->$pkey."&s=".$pkey)), "edit_16x16");
            IGKHtmlUtils::AddImgLnk($tr->addTd(), $this->getUri("db_drop_entry&n=".$k->$pkey."&s=".$pkey), "drop_16x16");
        }
        $div->addHSep();
        $this->setParam("db:dbname", $db);
        $this->setParam("db:table", $tb);
        $this->setParam("db:r", $r);
        $this->setParam("db:columns", $r->Columns);
        $cdiv=$div->addDiv();
        IGKHtmlUtils::AddImgLnk($cdiv, igk_js_post_frame($this->getUri("db_insert_db_entry_frame_ajx")), "add_16x16");
        IGKHtmlUtils::AddImgLnk($cdiv, $this->getUri("db_Clearall_db_entry"), "drop_16x16");
        IGKHtmlUtils::AddBtnLnk($div, __("btn.close"), $frame->CloseUri)->setAttribute("onclick", "javascript: (function(q){ ns_igk.winui.framebox.close_currentframe(q); })(this); return false;");
        IGKHtmlUtils::ToggleTableClassColor($table, "tr");
        $mysql->Close();
        if($navigate){
            igk_navtocurrent();
            igk_exit();
        }
        return $frame;
    }
    ///<summary></summary>
    ///<param name="dbname" default="null"></param>
    ///<param name="table" default="null"></param>
    ///<param name="adapter" default="null"></param>
    /**
    * 
    * @param mixed $dbname the default value is null
    * @param mixed $table the default value is null
    * @param mixed $adapter the default value is null
    */
    public function db_viewtableentries_ajx($dbname=null, $table=null, $adapter=null){
        $frame=$this->db_viewtableentries($dbname, $table, false, $adapter);
        $frame->RenderAJX();
        igk_exit();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function dbChanged(){
        $this->resetDataTableDefinition();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function demandToShowDataBase_ajx(){
        $c=igk_createnode("div");
        $c["class"]="igk-dbview";
        $this->_showSelectedDbTables($c, "Database");
        $c->RenderAJX();
        igk_exit();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function downloadbackupfile(){
        $v_file=igk_getr("file");
        $v_f= igk_io_applicationdir()."/".IGK_BACKUP_FOLDER."/".$v_file;
        if(file_exists($v_f)){
            igk_download_file(basename($v_f), $v_f);
        }
        else{
            igk_notifyctrl()->addError("file not present");
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function dropBackup(){
        $v_file=igk_getr("file");
        if(igk_qr_confirm()){
            $v_f=igk_io_currentrelativepath(IGK_BACKUP_FOLDER."/".$v_file);
            unlink($v_f);
            $this->View();
            igk_navtocurrent();
        }
        else{
            $frame=igk_frame_add_confirm($this, "confirm_Clear_backup_file", $this->getUri("dropBackup"));
            $frame->Form->Div->Content=__(IGK_MSG_DELETEABACKUPFILE_QUESTION, $v_file);
            $frame->Form->addInput("file", "hidden", $v_file);
        }
    }
    
    ///<summary></summary>
    /**
    * 
    */
    public function dropsysdb(){
        if(igk_is_conf_connected()){
            $ad=igk_get_data_adapter($this);
            if($ad && $ad->connect()){
                $ad->sendQuery("drop database if exists ".igk_app()->Configs->db_name. " ");
                $ad->close();
            }
        }
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    ///<param name="exp"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $exp
    */
    public function dropTablesRegex($ctrl, $exp){
        $db=igk_get_data_adapter($ctrl, true);
        if($db->connect()){
            $r=$db->listTables();
            $n=$r->Columns[0]->name;
            $tab=array();
            foreach($r->Rows as $v){
                if(preg_match($exp, $v->$n)){
                    $tab[]=$v->$n;
                }
            }
            $db->dropTable($tab);
            $db->close();
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function editdb(){
        $this->View();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAllTableNames(){
        $this->_initTableInfo();
        $tab = & $this->getLoadTables();
        if($tab)
            return array_keys($tab);
        return null;
    }
    ///<summary></summary>
    /**
    * get the cache faile used to store table:controller definition
    */
    public function getCacheFile(){  
        return igk_io_cachedir().DIRECTORY_SEPARATOR.".mysql.db.cache";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigPage(){
        return "mysqldatabase";
    }
    ///<summary>return the controller that manage the table name</summary>
    /**
    * return the controller that manage the table name
    */
    public function getDataTableCtrl($tablename){
        $this->_initTableInfo();
        $tab=& $this->getLoadTables();
        if(isset($tab[$tablename])){
            return $tab[$tablename];
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="global" default="true"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $global the default value is true
    */
    public function getDataTableDefinition($tablename, $global=true){
       if (strpos($tablename, "%")!==false){
           igk_wln("table not resolved:". $tablename);
           igk_trace();
           igk_exit();
       }
        if(!is_string($tablename)){
            igk_die("/!\\ tablename not a string");
        }
        $h=igk_getv(self::$sm_tabinfo, $tablename);
        if($h)
            return $h;
        $this->_initTableInfo();
        $tables=& $this->getLoadTables();
        if(isset($tables[$tablename])){
            $ctrl=$tables[$tablename];
            $b=$ctrl->getDataTableInfo();
            if($b === null)
                igk_die("/!\\ Controller->getDataTableInfo() return null for table definition. [".$ctrl."] for [".$tablename."]");
            if(isset($b[$tablename])){
                $this->regInfo($tablename, $b[$tablename]);
                return $b[$tablename];
            }
            else if($global){
                $c=array("ColumnInfo"=>$b);
                $this->regInfo($tablename, $c);
                return $c;
            }
        }
        else{
            igk_ilog("[".__FUNCTION__."] - [".$tablename."] . tips: you must probably reset the db.cache files or load the data schema.");
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDbConstantFile(){
        return igk_sys_db_constant_cache();
    }
    ///<summary></summary>
    ///<return refout="true"></return>
    /**
    * 
    * @return *
    */
    public function & getLoadTables(){
        static $tables=null;
        if($tables == null){
            $tables=array();
        }
        return $tables;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getName(){
        return IGK_MYSQL_DB_CTRL;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSearhTable(){
        return $this->getFlag(self::SEARCH_DB);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSelectedDb(){
        return $this->getFlag(self::SELECTED_DB) ?? igk_app()->Configs->db_name;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTabInfo(){
        return $this->getFlag(self::TABINFO_DB);
    }
    public function getTablesFor($ctrl, $include_dependency=false){
        if(is_string($ctrl)){
            $h=igk_getctrl($ctrl);
            if($h == null)
            return false;
            $ctrl=$h;
        }
        $this->_initTableInfo();
        $t=array();
        $tab=& $this->getLoadTables();
        foreach($tab as $k=>$v){
            if($v == $ctrl){
                $t[$k]=$k;
            }
        }
        if($include_dependency){
            $s=array_merge($t, array());
            while($s && (igk_count($s) > 0)){
                $d=array_pop($s);
                $tabinfo=$this->getDataTableDefinition($d, false);
                if(!$tabinfo)
                    continue;
                $info=igk_array_object_refkey(igk_getv($tabinfo, "ColumnInfo"), IGK_FD_NAME);
                foreach($info as $m=>$n){
                    if(!isset($n->clLinkType) || isset($s[$n->clLinkType]) || isset($t[$n->clLinkType]))
                        continue;
                    $s[$n->clLinkType]=$n->clLinkType;
                    $t[$n->clLinkType]=$n->clLinkType;
                }
            }
        }
        return $t;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getViewMyAdmin(){
        return $this->getFlag(self::VIEWMYADMIN_DB);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function gotophpmyadmin(){
       // if($this->getViewMyAdmin()){
        if (igk_server_is_local() && $h=igk_app()->Configs->phpmyadmin_uri){
            igk_navto($h);
            igk_exit();
        }
        igk_exit(); 
    }
    ///<summary></summary>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $args
    */
    public function init_table($args){
        $e=$args->args;
        $o=igk_getv($e, 0);
        $tbn=igk_getv($e, 1);
        $data=igk_getv($e, 2);
        $this->_addTable($tbn, $o);
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function InitComplete(){
        parent::InitComplete();
        $this->_loadDbFromCache();
    }
    ///<summary></summary>
    ///<param name="ctrlid" default="null"></param>
    /**
    * 
    * @param mixed $ctrlid the default value is null
    */
    function initCtrlDb($ctrlid=null){
        $ctrl=igk_getctrl($ctrlid);
        if($ctrl){
            igk_set_env(IGK_ENV_DB_INIT_CTRL, $ctrl);
            $ctrl->initDb();
            igk_set_env(IGK_ENV_DB_INIT_CTRL, null);
        }
    } 
    ///<summary></summary>
    ///<param name="view" default="true"></param>
    ///<param name="nav" default="true"></param>
    /**
    * 
    * @param mixed $view the default value is true
    * @param mixed $nav the default value is true
    */
    public function initSDb($view=true, $nav=true){
        $ad=igk_get_data_adapter($this, true);
        if($ad){
            igk_set_env("sys://Db/NODBSELECT", 1);
            $ad->initForInitDb();
            if(!$ad->connect()){
                igk_wln("/!\\ connection failed");
                igk_exit();
            }
            ini_set("max_execution_time", 0);
            $ad->beginTransaction();
            $init_env=function(){
                igk_set_env("sys://Db/NODBSELECT", null);
                igk_set_env("sys://db_init", 1);
                igk_set_env("sys://db_init/error", 0);
                igk_set_env(IGK_ENV_DB_INIT_CTRL, null);
            };
            $init_env();
            $callable=array($this, "init_table");
            igk_reg_hook(IGK_NOTIFICATION_INITTABLE, $callable);
            $r=$ad->createdb(igk_app()->Configs->db_name);
            $ad->selectdb(igk_app()->Configs->db_name);
            $icomplete = [];
            if(function_exists($global_fc = "InitDb")){
                call_user_func_array($global_fc, []);
            }
            else{
                $ad_n=$this->getDataAdapterName();
                $v_ctab=igk_app()->getControllerManager()->getControllers();
                foreach($v_ctab as $k){
                    if(($k == $this) || ($k->getDataAdapterName() != $ad_n))
                        continue;
                    igk_set_env(IGK_ENV_DB_INIT_CTRL, $k);
                    $k->initDb();
                    if (method_exists($k, "initDataComplete")){
                        $icomplete[] = $k;
                    }
                }
            }
            igk_notifyctrl()->addMsgr("msg.db.initialized");
            igk_notification_unreg_event(IGK_NOTIFICATION_INITTABLE, $callable);
            $ad->flushForInitDb();
            if(igk_get_env("sys://db_init/error") > 0){
                $ad->rollback();
            }
            else{
                $ad->commit();
                
            
            igk_notification_push_event("sys://db/init_complete", $this);
            igk_hook(IGKEvents::HOOK_DB_INIT_ENTRIES, array($this));
            igk_hook(IGKEvents::HOOK_DB_INIT_COMPLETE, ["controller"=>$this]);
        }
            igk_set_env("sys://db_init", null);
            igk_set_env("sys://db_init/error", null);
            $ad->close();
            if($view){
                $this->View();
            }
        }
        else{
            igk_notifyctrl()->addError(__("E.DbNotConnected"));
            igk_ilog("/!\\ Connect FAILED");
        }
        if($nav){
            igk_navtocurrent();
        }
    }
    ///<summary></summary>
    ///<param name="f"></param>
    /**
    * 
    * @param mixed $f
    */
    public function IsFunctionExposed($f){
        return igk_is_conf_connected();
    }
    ///<summary>public function expose system init db . must be conf connected</summary>
    /**
    * public function expose system init db . must be conf connected
    */
    function pinitSDb($nav=true){
        if(!igk_is_conf_connected()){
            igk_set_header(403);
            igk_navtocurrent();
        }
        set_time_limit(0);
        igk_set_env(__FUNCTION__, 1);
        igk_notification_reset(IGKEvents::HOOK_DB_INIT_ENTRIES);
        IGKIO::RmDir(IGK_APP_DIR."/Caches/db");
        $this->resetDataTableDefinition();
        $ad=igk_get_data_adapter($this);
        $dbname=igk_app()->Configs->db_name;
        if($dbname && $ad->connect(null, 0)){
            if(!$ad->selectdb($dbname) && !$ad->createDb($dbname)){
                $ad->close();
                $ad=null;
                igk_wln_e("failed : create db");
            }
            else{
                $ad->setForeignKeyCheck(0);
                $fc=array($this, '__inittable_callback');
                $keye='sys://event/sdb/inittable';
                igk_reg_session_event($keye, $fc);
                $this->initSDb(true, false);
                igk_unreg_session_event($keye, $fc);
                igk_invoke_session_event("sys://event/sdb/finish", array($this, null));
                
                $ad->setForeignKeyCheck(1);
                $ad->close();
                igk_set_env(__FUNCTION__, null);
                $this->_storeDbCache();
                igk_getctrl(IGK_SESSION_CTRL)->forceview();
                $this->notifyctrl()->success(__("init system database"));
            }
        }
        else
            $ad=null;
        
        igk_close_session();
        if($nav && !igk_is_ajx_demand()){
            igk_navtocurrent();
        }
        igk_exit();
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="inf"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $inf
    */
    private function regInfo($tablename, $inf){
        if(self::$sm_tabinfo === null)
            self::$sm_tabinfo=array();
        self::$sm_tabinfo[$tablename]=$inf;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function registerHook(){
        igk_reg_hook(IGK_HOOK_DB_CHANGED, function($e){
            $this->dbChanged($e);
        });
        igk_reg_hook(IGK_NOTIFICATION_INITTABLE, function($e){
            // igk_trace(); 
            $this->_addTable($e->args[1], $e->args[0]);
        });
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    ///<param name="tbname"></param>
    ///<param name="inf"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $tbname
    * @param mixed $inf
    */
    private function regTableDefinition($ctrl, $tbname, $inf){
        $tab=& $this->getLoadTables();
        $tab[$tbname]=$ctrl;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function resetDataTableDefinition(){
        self::$sm_tabinfo=null;
        $tab=& $this->getLoadTables();
        array_slice($tab, count($tab));
        $f=$this->getCacheFile();
        if(file_exists($f))
            @unlink($f);
        igk_env_count(__FUNCTION__);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function searchtable(){
        $q=igk_getr("q");
        $this->setFlag('db:searchtable', $q);
        $this->View();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function selectdb(){
        $n=igk_getr("n");
        $this->setSelectedDb($n);
        $this->View();
        igk_navto(igk_server()->HTTP_REFERER."/#igkdb_tablelist");
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    private function setSelectedDb($v){
        $this->setFlag(self::SELECTED_DB, $v);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function updatedb(){
        $server=igk_getr("dbServer");
        $user=igk_getr("dbUser");
        $pwd=igk_getr("dbPasswd");
        $dbname=igk_getr("dbName");
        $dbPort=igk_getr("dbPort");
        $cnf = igk_app()->Configs;        
      
        $cnf->db_pwd = $pwd;
        $cnf->db_server = $server;
        $cnf->db_user = $user;        
        $cnf->db_name = $dbname;
        $cnf->db_port = $dbPort;

        igk_save_config();
        igk_resetr();
        igk_notifyctrl()->addSuccessr("msg.databaseinfoupdated");
        $db=igk_get_data_adapter($this);
        $db->Reset();
        $this->View();
        igk_navtocurrent();
        igk_exit();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function View(){

        if(igk_is_ajx_demand()){
            $p=igk_getr("v", null);
            if(empty($p))
                return;
            $t=igk_createnode("div");
            $f="_view_conf_{$p}";
            if(method_exists($this, $f)){
                $this->setFlag('tabview', $p);
                call_user_func_array(array($this, $f), array($t));
                igk_ajx_replace_uri(igk_io_request_uri_path()."#!p=".$p);
                $t->RenderAJX();
            }
            else{
                igk_set_header(404);
                igk_wln_e(__("no function to found!", $p));
            }
            return;
        }

        if(!$this->getIsVisible()){
            igk_html_rm($this->TargetNode);
            return;
        }

        $c=$this->TargetNode;
        igk_html_add($c, $this->ConfigNode);
        $c=$c->ClearChilds()->addPanelBox();
        igk_html_title($c, __("Configure MySQL Database"));
        $c->addNotifyHost();
        $h=$c->addRow();
        $div=$h->addDiv();
        $pan=$div->addPanelBox();
        $pan->addDiv()->Content="DataBase : MySQL";
        $pan->addDiv()->Content="Available : ". igk_parsebool(defined("IGK_MSQL_DB_Adapter"));
        $pan->addDiv()->Content="MySQL : ". igk_parsebool(IGK_MSQL_DB_AdapterFunc);
        $pan->addDiv()->Content="MySQLi : ". igk_parsebool(IGK_MSQLi_DB_AdapterFunc);
        $cview=$this->getFlag("tabview");
        $tab=$div->addComponent($this, "AJXTabControl", __METHOD__, 1);
        $tab->addTabPage(__("General"), $this->getUri("view&v=general"), empty($cview) || $cview == 'general');
        $tab->addTabPage(__("Datas"), $this->getUri("view&v=datas"), $cview == 'datas');
        $tab->addTabPage(__("Backup"), $this->getUri("view&v=backup"), $cview == 'backup');
        $tab->addTabPage(__("Tools"), $this->getUri("view&v=tools"), $cview == 'tools');
        $tab->addTabPage(__("Query"), $this->getUri("view&v=query"), $cview == 'query');
        $zdiv=$div->addDiv();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function viewtable(){
        $selected=igk_getr("v") ?? 1;
        $n=igk_createnode("div");
        $this->_showSelectedDbTables($n, null, $selected);
        $n->RenderAJX();
        igk_exit();
    }
}