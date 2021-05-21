<?php
//author: C.A.D BONDJE DOUE
//file : IGKApplicationManager.php
//desc: controller of template

///<summary>Represente class: IGKApplicationManager</summary>

use IGK\Controllers\BaseController;

/**
* Represente IGKApplicationManager class
*/
class IGKApplicationManager extends BaseController{
    const IDENTIFIER=IGK_CTRL_IDENTIFIER + 11;
    const MAIN_FILE="Application.php";
    const MANIFEST_TAGNAME="balafon_manifest";
    const TEMPLATE_DIR="Templates";

    public function getDataTableName()
    {
        return null;
    }
    public function getCanInitDb()
    {
        return false;
    }
    ///<summary>.ctr</summary>
    /**
    * .ctr
    */
    function __construct(){
        parent::__construct();
        $this->m_createdCtrls=array();
    }
    ///<summary></summary>
    /**
    * 
    */
    private function __renderConfigPage_ajx(){
        $s=igk_getr("ajx-lnk") ? "^": "";
        $t=$this->getReplacementNode();
        $t->clearChilds();
        $t->setTarget($s.".".strtolower(igk_css_str2class_name($this->Name)));
        $this->showConfig($t->addNoTagNode());
        $t->renderAJX();
    }
    ///<summary></summary>
    ///<param name="fname"></param>
    /**
    * 
    * @param mixed $fname
    */
    private function _get_manifest_obj($fname){
        $e=igk_zip_unzip_filecontent($fname, ".manifest");
        if($e == null){
            igk_ilog("no manisfest file found in the package {$fname}");
            return null;
        }
        $d=igk_createXMLNode("dom");
        $d->load($e);
        $t=igk_html_select($d, IGKApplicationManager::MANIFEST_TAGNAME);
        if(igk_count($t) != 1){
            igk_ilog("invalid manifest .");
            return;
        }
        $manifest=$t[0];
        $m_obj=igk_xml_to_obj($manifest);
        return $m_obj;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function addNewTemplate_ajx(){
        if(igk_qr_confirm()){
            $obj=igk_get_robj();
            $ns="";
            $obj->clPackageName=trim($obj->clPackageName);
            if(!empty($obj->clPackageName) && !igk_template_package_exists($obj->clPackageName) && !empty($ns=igk_io_basenamewithoutext($obj->clPackageName)) && !igk_template_package_exists($ns)){
                if($this->genTemplates($obj)){
                    igk_notifyctrl("template://addnew")->addMsgr("template.msg.templateadded");
                }
                else{
                    igk_notifyctrl("template://addnew")->addErrorr("error.template.addfailed");
                }
                igk_resetr();
                $this->__renderConfigPage_ajx();
                igk_exit();
            }
            else
                igk_notifyctrl("template://addnew")->addError("/!\\ no name defined or packagename name already exists");
        }
        $frame=igk_createNode("frameDialog", null, array(__METHOD__, $this));
        $frame->Title=R::ngets("title.newTemplate");
        $d=$frame->BoxContent;
        $d->ClearChilds();
        $frm=$d->addForm();
        $frm["action"]=$this->getUri(__FUNCTION__);
        $frm["igk-ajx-form"]=1;
        $frm->addNotifyHost("template://addnew");
        $dv=$frm->addDiv();
        $dv->setStyle("padding:32px 24px; min-width:400px;");
        $tab=igk_db_form_data(IGK_TB_TEMPLATES, function($n, & $t){
            if($n == "clId")
                return 1;
            switch($n){
                case "clPackageName":
                case "clTitle":
                $t[$n]=array("required"=>1);
                return 1;
                case "clVersion":
                $t[$n]=array("required"=>1, "attribs"=>array("value"=>"1.0"));
                return 1;
                case "clInstallDate":
                case "clPath":
                case "clDescription":
                return 1;default:
                break;
            }
            return false;
        });
        $tab["clDefaultLang"]=array(
            "type"=>"select",
            "attribs"=>array(
                "options"=>array("fr"=>"Français(Belgique)", "en"=>"English(England)"),
                "options-select-attribs"=>array("useArrayIndex"=>1)
            )
        );
        $tab["confirm"]=array("type"=>"hidden", "attribs"=>array("value"=>"1"));
        igk_html_build_form($dv, $tab, "div");
        $dv=$frm->addDiv();
        $dv->addInput("btn.add", "submit", R::ngets("btn.create"));
        $frame->RenderAJX();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function assets(){
        $q=igk_getr("q");
        $w=igk_getr("w");
        $h=igk_getr("h");
        if($q == 'none'){
            $s=file_get_contents($this->getDataDir()."/R/DefaultAssets/template100x100.png");
            ob_clean();
            header("Content-type: image/png");
            igk_wl($s);
            igk_exit();
        }
        $c=$this->getPackageManifestFile($q);
        if(file_exists($c)){
            $dc=igk_zip_unzip_entry($c, "r/Assets/logo.{$w}x{$h}.png");
            if($dc){
                header("Content-type: image/png");
                igk_wl($dc);
                igk_exit();
            }
        }
        igk_navto($this->getUri("assets&q=none"));
        igk_exit();
    }
    ///<summary>available templates</summary>
    /**
    * available templates
    */
    public function avail_t_ajx(){
        $r=igk_curl_post_uri("http://local.com/balafon/templates/list/xml");
        $n=igk_createNode();
        $n->addDiv()->Content="Result";
        $d=$n->addDiv()->setStyle("max-height:500px; min-height:200px;");
        $v=igk_createNode("d");
        $v->add(IGKHtmlReader::LoadXML($r));
        $tobj=array();
        foreach($v->getElementsByTagName("item") as $v){
            $obj=igk_xml_to_obj($v);
            $tobj[]=$obj;
        }
        if (count($tobj)>0)
            $d->addDiv()->load($r);
        $n->RenderAJX();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function back(){
        $this->setParam("template://row_id", null);
        $this->m_config_view="config";
        igk_navtocurrent();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function back_ajx(){
        $this->setParam("template://row_id", null);
        $this->resetCurrentView(null);
        $this->m_config_view="config";
        $this->__renderConfigPage_ajx();
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function created($n){
        return isset($this->m_createdCtrls[$n]);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function e_manifest(){
        $this->setParam("template://row_id", igk_getr("id"));
        $this->m_config_view="editmanifest";
        igk_navtocurrent();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function e_manifest_ajx(){
        $this->setParam("template://row_id", igk_getr("id"));
        $this->m_config_view="editmanifest";
        $this->__renderConfigPage_ajx();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function eventMessage(){
        igk_wln("event message ....");
    }
    ///<summary></summary>
    ///<param name="obj" default="null"></param>
    ///<param name="renderxml"></param>
    /**
    * 
    * @param mixed $obj the default value is null
    * @param mixed $renderxml the default value is 0
    */
    public function genTemplates($obj=null, $renderxml=0){
        $m=igk_createNode(IGKApplicationManager::MANIFEST_TAGNAME);
        $m->setAttribute("xmlns:balafon", "http://schemas.igkdev.com/templates/manifest");
        $obj=igk_obj_binddata(igk_template_createTemplateInfo(), $obj);
        $t=igk_xml_to_node($obj, "template");
        $m->add($t);
        $f=$this->getPackageStoreDir()."/".$obj->clPackageName.".xtbl";
        if(file_exists($f))
            @unlink($f);
        IGKIO::CreateDir(dirname($f));
        $zip=igk_zip_content($f, ".manifest", $m->Render(), 0);
        $zip->addEmptyDir("lib");
        $zip->addEmptyDir("r");
        $zip->addEmptyDir("src");
        $zip->addFromString(".init.php", "");
        $zip->addFromString(".erase.php", "");
        $zip->addFromString(".data.json", "");
        igk_init_controller(new igk_template_init_listener($zip));
        $zip->addFromString("src/".IGK_VIEW_FOLDER."/default.phtml", igk_template_default_content('view/default'));
        $zip->addFromString("src/".IGKApplicationManager::MAIN_FILE, igk_template_default_script_content());
        $zip->addFromString("r/assets/logo100x100.png", file_get_contents($this->getDataDir()."/R/DefaultAssets/template100x100.png"));
        $zip->close();
        $this->install_template_package_ajx($f);
        if($renderxml)
            $m->RenderXML();
        return 1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function get_css_class_ajx(){
        igk_wln(igk_css_str2class_name($this->Name));
    }
    ///<summary></summary>
    ///<param name="row"></param>
    /**
    * 
    * @param mixed $row
    */
    public function getAssetsUri($row){
        return $this->getUri("assets&q={$row->clPackageName}");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigView(){
        if(empty($this->m_config_view)){
            $this->m_config_view="config";
        }
        return $this->m_config_view;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function getCreatedCtrl($n){
        return $this->created($n) ? $this->m_createdCtrls[$n]: null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDataAdapterName(){
        return IGK_MYSQL_DATAADAPTER;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDb(){
        static $dbu=null;
        if($dbu == null){
            $dbu=new IGKApplicationManagerDbUtility($this);
        }
        return $dbu;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsVisible(){
        return parent::getIsVisible() && igk_is_conf_connected();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getName(){
        return IGK_CTRL_IDENTIFIER + 11;
    }
    ///<summary></summary>
    ///<param name="packagename"></param>
    /**
    * 
    * @param mixed $packagename
    */
    public function getPackageManifest($packagename){
        $v_fname=$this->getPackageManifestFile($packagename);
        if(file_exists($v_fname)){
            $obj=$this->_get_manifest_obj($v_fname);
            return $obj;
        }
        return 0;
    }
    ///<summary></summary>
    ///<param name="packagename"></param>
    /**
    * 
    * @param mixed $packagename
    */
    public function getPackageManifestFile($packagename){
        return IGKIO::GetDir($this->getPackageStoreDir()."/{$packagename}.xtbl");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getPackageStoreDir(){
        return $this->getDataDir()."/packages";
    }
    ///<summary></summary>
    /**
    * 
    */
    private function getReplacementNode(){
        if(!$this->m_replacementNode){
            $this->m_replacementNode=igk_createNode("ajxLnkReplace");
        }
        return $this->m_replacementNode;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function getUseDataSchema(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUser(){
        return $this->App->Session->User;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function InitComplete(){
        parent::InitComplete();
        $cb=igk_create_func_callback('igk_view_render_if_visible', array($this));
        igk_reg_hook(IGK_CONF_USER_CHANGE_EVENT, $cb);
    }
  
    ///<summary></summary>
    /**
    * 
    */
    public function install(){}
    ///<summary></summary>
    ///<param name="f" default="null"></param>
    /**
    * 
    * @param mixed $f the default value is null
    */
    public function install_template_package_ajx($f=null){
        igk_flush_data();
        $f=$f ?? dirname(__FILE__)."/".IGK_PACKAGES_FOLDER."/igkdev.com.rcard.xtbl";
        $e=igk_zip_unzip_filecontent($f, ".manifest");
        if($e == null){
            igk_ilog("no manisfest file found in the package");
            return;
        }
        $d=igk_createXMLNode("dom");
        $d->load($e);
        $t=igk_html_select($d, IGKApplicationManager::MANIFEST_TAGNAME);
        if(igk_count($t) != 1){
            igk_ilog("invalid manifest .".$f);
            return;
        }
        $manifest=$t[0];
        $m_obj=igk_xml_to_obj($manifest);
        $dir=igk_templage_get_dir($m_obj->template->clPackageName);
        if(!IGKIO::CreateDir($dir))
            igk_die("failed to create a dir");
        igk_zip_unzip_to($f, $dir, "src/");
        $app_file=$dir."/".IGKApplicationManager::MAIN_FILE;
        if(!file_exists($app_file))
            igk_die("no app file exists");
        $str=file_get_contents($app_file);
        $ns=str_replace(".", "\\", $m_obj->template->clPackageName);
        $str=str_replace('%{$packagenamespace}', "namespace {$ns}; \nuse \\IGKTemplateBase as IGKTemplateBase;", $str);
        igk_io_save_file_as_utf8($app_file, $str);
        ///TODO: ADD TO DB

        $this->Db->addInstalledTemplate($m_obj->template, igk_io_unix_path(igk_io_basePath(dirname($app_file))));
    }
    ///<summary></summary>
    ///<param name="p"></param>
    /**
    * 
    * @param mixed $p
    */
    public function is_set_as_default($p){
        if($p){
            return ($p->clPackageName == igk_app()->Configs->web_pagectrl);
        }
        return 0;
    }
    ///<summary> load and install a template manually</summary>
    /**
    *  load and install a template manually
    */
    public function loadtemplate_package_ajx(){
        $hb=igk_get_header_obj();
        if(igk_getv($hb, "UPLOADFILE")){
            $n=$hb->FILE_NAME;
            $s=$hb->UP_FILE_SIZE;
            $v_c=file_get_contents("php://input", false, null, -1, $s);
            if((igk_io_path_ext($n) == 'xtbl') && substr($v_c, 0, 2) === "PK"){
                $usr=$this->getUser();
                $u=$usr ? $usr->clLogin."/": "";
                $v_fname=$this->getPackageStoreDir()."/{$u}{$n}";
                igk_io_save_file_as_utf8_wbom($v_fname, $v_c);
                igk_notifyctrl("template://msg")->addMsg("File uploaded");
                $this->install_template_package_ajx($v_fname);
            }
            else{
                igk_notifyctrl("template://msg")->addError("uploaded file not a valid balafon template package");
            }
        }
        $this->__renderConfigPage_ajx();
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function LoadTemplates(){}
    ///<summary></summary>
    ///<param name="id" default="null"></param>
    /**
    * 
    * @param mixed $id the default value is null
    */
    public function make_default_ajx($id=null){
        $id=$id ?? igk_getr("id");
        $table = igk_db_get_table_name(IGK_TB_TEMPLATES);
        if($this->Db->connect()){
            $row=$this->Db->selectSingleRow($table, $id);
            if($row){
                igk_app()->Configs->web_pagectrl=$row->clPackageName;
                igk_save_config();
            }
            $this->Db->close();
        }
        $this->__renderConfigPage_ajx();
    }
    ///<summary>create a new package</summary>
    /**
    * create a new package
    */
    public function make_package(){
        $id=igk_getr("id");
        $row=$this->Db->getPackageRow($id);
        if(!$row){
            return;}
        $m=igk_createNode(IGKApplicationManager::MANIFEST_TAGNAME);
        $m->setAttribute("xmlns:balafon", "http://schemas.igkdev.com/templates/manifest");
        $obj=igk_obj_binddata(igk_template_createTemplateInfo(), $row);
        $t=igk_xml_to_node($obj, "template");
        $m->add($t);
        $pfile=$this->getPackageManifestFile($row->clPackageName);
        $f=null;
        $temp=0;
        if(!file_exists($pfile)){
            $temp=1;
            $f=igk_io_tempfile('xtb');
            IGKIO::CreateDir(dirname($f));
            if(file_exists($f))
                @unlink($f);
        }
        else{
            $f=$pfile;
        }
        $zip=igk_zip_content($f, ".manifest", $m->Render(), 0);
        $zip->addEmptyDir("lib");
        $zip->addEmptyDir("r");
        $zip->addEmptyDir("src");
        $zip->addFromString(".init.php", "");
        $zip->addFromString(".erase.php", "");
        $zip->addFromString(".data.json", "");
        $bpath= igk_io_dir(igk_io_basedir()."/".$row->clPath);
        $tf=igk_io_getfiles($bpath);
        foreach($tf as  $v){
            $p=igk_io_unix_path(substr($v, strlen($bpath) + 1));
            if($p == ".manifest")
                continue;
            $zip->addFromString("src/{$p}", file_get_contents($v));
        }
        $zip->close();
        igk_download_file($row->clPackageName.".xtbl", $f, "binary", 0);
        if($temp)
            unlink($f);
        igk_exit();
    }
    ///<summary>register  template controller</summary>
    ///<param name="obj">controller template application</summary>
    ///<param name="n">name of the global namespace uri</summary>
    /**
    * register template controller
    * @param mixed $obj controller template application
    * @param mixed $n name of the global namespace uri
    */
    public function register($obj, $n){
        if($obj === null){
            if(isset($this->m_createdCtrls[$n])){
                unset($this->m_createdCtrls[$n]);
                return 1;
            }
        }
        else{
            if(igk_reflection_class_extends(get_class($obj), "IGKTemplateBase")){
                $this->m_createdCtrls[$n]=$obj;
                BaseController::InvokeInitCompleteOn($obj);
                return 1;
            }
        }
        return 0;
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function showConfig($t){
        igk_ctrl_view($this, $t, $this->ConfigView);
    }
    ///<summary></summary>
    ///<param name="id" default="null"></param>
    /**
    * 
    * @param mixed $id the default value is null
    */
    public function uninstall_ajx($id=null){
        $id=$id ?? igk_getr("id");
        if($this->Db->connect()){
            $row=$this->Db->selectSingleRow(IGK_TB_TEMPLATES, $id);
            if($row){
                $n=igk_template_name($row->clPackageName);
                $ctrl=$this->getCreatedCtrl($n);
                if($ctrl){
                    $ctrl->uninstall();
                }
                unset($this->m_createdCtrls[$n]);
                $dir=igk_templage_get_dir($row->clPackageName);
                if(is_dir($dir))
                    IGKIO::RmDir($dir);
                if(igk_app()->Configs->web_pagectrl == $row->clPackageName){
                    igk_app()->Configs->web_pagectrl=null;
                    igk_save_config();
                }
                $this->Db->dropTemplate($id);
            }
            $this->Db->close();
        }
        $this->showConfig($this->TargetNode);
        $sc=igk_createNode("script");
        $sc->Content=<<<EOF
ns_igk.winui.ajx.lnk.getLink().select("^.igk-roll-owner").setCss({ opacity:0.0}).transEnd('remove');
EOF;
        $sc->RenderAJX();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function update_manifest(){
        $obj=igk_get_component_by_id(igk_getr("com_id"));
        if($obj != null){
            $obj->update();
        }
        else{
            igk_wln("not parameter with id ".igk_getr("com_id"));
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function visit(){
        igk_navtobase(IGK_STR_EMPTY);
    }
}
