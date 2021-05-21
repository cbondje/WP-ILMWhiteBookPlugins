<?php
//author: C.A.D. BONDJE DOUE
//desc: global templates function
//date: 116/01/2017

///<summary></summary>
///<param name="prefix" default="'tmp'"></param>
/**
* 
* @param mixed $prefix the default value is 'tmp'
*/
function igk_io_tempfile($prefix='tmp'){
    return tempnam(sys_get_temp_dir(), $prefix);
}
///<summary></summary>
///<param name="ns"></param>
/**
* 
* @param mixed $ns
*/
function igk_templage_get_dir($ns){
    return IGKIO::GetDir(igk_io_baseDir()."/".IGKApplicationManager::TEMPLATE_DIR."/".str_replace(".", "/", $ns));
}
///<summary></summary>
/**
* 
*/
function igk_template_class_uri(){
    return igk_base_uri_name(__FILE__);
}
///<summary>create template application instance</summary>
/**
* create template application instance
*/
function igk_template_create($n){
    $n=str_replace(".", "\\", $n);
    if(class_exists($n)){
        return new $n();
    }
    return null;
}
///<summary>create and init application template controller</summary>
/**
* create and init application template controller
*/
function igk_template_create_ctrl($n){
    $tc=igk_template_mananer_ctrl();
    igk_assert_die(($tc == null) && !igk_sys_env_production(),
    ["code"=>1201,
    "message"=>"TemplateManager: controller [{$n}] not found or session data not valid. Failed to create [{$n}]",
    ]);
    if($tc===null)
        return null;
    $n=str_replace(".", "\\", $n);
    $cl=$n."\\Application";
    if($tc && $tc->created($n)){
        if(!class_exists($cl, false)){
            igk_template_load_ns($n);
        }
        return $tc->getCreatedCtrl($n);
    }
    $o=null;
    if(class_exists($n, false)){
        $o=new $n();
    }
    else{
        $s=IGKIO::GetDir(IGKApplicationManager::TEMPLATE_DIR."/".str_replace(".", "/", $n)."/".IGKApplicationManager::MAIN_FILE);
        if(igk_template_load($s)){
            if(class_exists($cl)){
                $o=new $cl();
            }
        }
    }
    if($o && $tc->register($o, $n)){
        return $o;
    }
    return null;
}
///<summary>create a template setting</summary>
/**
* create a template setting
*/
function igk_template_createtemplateinfo(){
    return (object)array(
            "clPackageName"=>"com.igkdev.nothing",
            "clTitle"=>"Demo template",
            "clAuthor"=>"C.A.D BONDJE DOUE",
            "clDate"=>igk_date_now(),
            "clVersion"=>"1.0",
            "installClass"=>"init_php",
            "IsConfigurable"=>1,
            "IsDesignable"=>1,
            "required-permission"=>array(),
            "required"=>array(),
            "uses-permission"=>array(
                array("name"=>"resolv.INTERNET"),
                array("name"=>"resolv.STORAGE")
            ),
            "category"=>array((object)array("name"=>"portfolio"))
        );
}
///<summary></summary>
///<param name="name"></param>
/**
* 
* @param mixed $name
*/
function igk_template_default_content($name){
    $c=dirname(__FILE__)."/Data/scripts/".$name.".default";
    if(file_exists($c))
        return file_get_contents($c);
    return 0;
}
///<summary></summary>
/**
* 
*/
function igk_template_default_script_content(){
    return file_get_contents(dirname(__FILE__)."/Data/scripts/application.default");
}
///<summary>function that return a list of avaible controller</summary>
/**
* function that return a list of avaible controller
*/
function igk_template_get_ctrls(){
    $t=array();
    if($g=igk_getctrl(igk_template_class_uri(), false)){
        $db=$g->Db;
        if($db){
            $q=$g->Db->getTemplateNames();
            if($q) foreach($q->Rows as $v){
                $t[]=$v->clPackageName;
            }
        }
    }
    return $t;
}
///<summary></summary>
/**
* 
*/
function igk_template_init_env(){
    $dir=IGKIO::GetDir(igk_io_applicationdir()."/".IGKApplicationManager::TEMPLATE_DIR);
    if(IGKIO::CreateDir($dir)){
        igk_io_w2file($dir."/.htaccess", "deny from all");
    }
    else{
        igk_die("failed to create directory");
    }
}
///<summary></summary>
///<param name="ctrl"></param>
/**
* 
* @param mixed $ctrl
*/
function igk_template_is_template_class($ctrl){
    return igk_reflection_class_extends($ctrl, 'IGKApplicationBase');
}
///<summary></summary>
///<param name="f"></param>
/**
* 
* @param mixed $f
*/
function igk_template_load($f){
    $tns=igk_get_env("sys://templates/loaded", array());
    $d=igk_io_basedir();
    $f=IGKIO::GetDir($d."/".$f);
    if(!file_exists($f))
        return 0;
    if(strstr($f, igk_ob_get(get_included_files()), $f) == $f)
        return 1;
    $ns=str_replace(".", "\\", igk_base_uri_name(dirname($f), igk_io_dir($d."/Templates")));
    if(isset($tns[$ns]))
        return 1;
    $o=0;
    try {
        $g=igk_io_getfiles(dirname($f), "/\.php/i");
        igk_include_script($g, $ns);
        $o=1;
        $tns[$ns]=1;
        igk_set_env("sys://templates/loaded", $tns);
    }
    catch(Exception $ex){
        igk_ilog("Inclusion of file failed");
        $o=0;
    }
    return 1;
}
///<summary>load template from namespace</summary>
/**
* load template from namespace
*/
function igk_template_load_ns($ns){
    $s=IGKIO::GetDir(IGKApplicationManager::TEMPLATE_DIR."/".str_replace(".", "/", $ns)."/".IGKApplicationManager::MAIN_FILE);
    if(igk_template_load($s)){
        return 1;
    }
    return 0;
}
///<summary></summary>
/**
* 
*/
function igk_template_mananer_ctrl(){
    $n=igk_template_class_uri();
    return igk_getctrl($n, false);
}
///<summary></summary>
///<param name="packagename"></param>
/**
* 
* @param mixed $packagename
*/
function igk_template_name($packagename){
    return str_replace(".", "\\", $packagename);
}
///<summary></summary>
///<param name="ns"></param>
/**
* 
* @param mixed $ns
*/
function igk_template_package_exists($ns){
    $ctrl=igk_template_mananer_ctrl();
    if($ctrl->Db->connect()){
        $r=$ctrl->Db->select("tbigk_templates", array("clPackageName"=>$ns))->RowCount == 1;
        $ctrl->Db->close();
        return $r;
    }
    return 0;
}
///<summary></summary>
///<param name="n"></param>
///<param name="ctrl"></param>
/**
* 
* @param mixed $n
* @param mixed $ctrl
*/
function igk_template_view_assets_favicon($n, $ctrl){
    $n->addDiv()->setContent("set the default favicon");
    $row=$n->addDiv()->addRow();
}
///<summary></summary>
///<param name="n"></param>
///<param name="ctrl"></param>
/**
* 
* @param mixed $n
* @param mixed $ctrl
*/
function igk_template_view_badge($n, $ctrl){
    $n->addDiv()->setContent("set here bagde for many resolution");
    $row=$n->addDiv()->addRow();
}
///<summary></summary>
///<param name="t"></param>
///<param name="ctrl"></param>
/**
* 
* @param mixed $t
* @param mixed $ctrl
*/
function igk_template_view_btn_loadandinstall($t, $ctrl){
    $dv=$t->addDiv();
    $dv->addSectionTitle("5")->setFont("arial")->Content=R::ngets("title.loadTemplate");
    $frm=$dv->addForm();
    $frm->addNotifyHost("template://msg");
    $frm->addajxPickFile($ctrl->getUri("loadtemplate_package_ajx"), "{complete:igk.ajx.fn.complete_ready('^form')}")->setClass("igk-btn igk-btn-default")->Content=R::ngets("btn.loadandinstall.templates");
}
///<summary></summary>
///<param name="ctrl"></param>
/**
* 
* @param mixed $ctrl
*/
function igk_view_render_if_visible($ctrl){
    if($ctrl->IsVisible)
        $ctrl->View();
} 


include_once(dirname(__FILE__)."/Views/uri.listener.pinc");
igk_reg_initenv_callback("igk_template_init_env");
spl_autoload_register(function($n){
    if(preg_match(IGK_IS_NS_IDENTIFIER_REGEX, $n)){        
        igk_template_load_ns(str_replace("\\", ".", dirname($n)));
    }
});