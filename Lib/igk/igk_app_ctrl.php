<?php
// @file: igk_app_ctrl.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com 
// @url: https://www.igkdev.com

use IGK\Resources\R;

define("IGK_INC_APP_INITDB", IGK_LIB_DIR."/".IGK_INC_FOLDER."/igk_initapp_db.pinc");

use function igk_resources_gets as __; 


///<summary></summary>
///<param name="ctrl"></param>
///<param name="n"></param>
/**
* 
* @param mixed $ctrl
* @param mixed $n
*/
function igk_app_ctrl_dropped_callback($ctrl, $n){
    $c=IGKApplicationController::GetApps();
    $c=array();
}

///<summary></summary>
///<param name="app"></param>
///<param name="node"></param>
///<param name="fname"></param>
///<param name="goodUri" default="null"></param>
/**
* 
* @param mixed $app
* @param mixed $node
* @param mixed $fname
* @param mixed $goodUri the default value is null
*/
function igk_app_load_login_form($app, $node, $fname, $goodUri=null){
    $u=$goodUri;
    if($u == null){
        $q=igk_getr("q");
        $u=$app->getAppUri();
        if(!empty($q)){
            $u=base64_decode($q);
        }
    }
    $frm=$node->addAppLoginForm($app, $app->getAppUri($fname), $u);
}
///<summary></summary>
///<param name="app"></param>
///<param name="div"></param>
///<param name="badUri" default="null"></param>
///<param name="goodUri" default="null"></param>
/**
* 
* @param mixed $app
* @param mixed $div
* @param mixed $badUri the default value is null
* @param mixed $goodUri the default value is null
*/
function igk_app_login_form($app, $div, $badUri=null, $goodUri=null){
    $frm=$div->addForm();
    $frm["action"]=$app->getUri("login");
    $frm["class"]="igk-login-form";
    $frm["igk-form-validate"]=1;
    igk_notify_sethost($frm->addDiv(), "notify/app/login");
    $cd=$frm->addDiv()->setClass("form-group");
    $cd->addRow()->addCol()->addDiv()->addInput("clLogin", "text")->setClass("igk-form-control")->setAttribute("placeholder", R::ngets("tip.login"))->setAttribute("igk-input-focus", 1)->setAttribute("autocomplete", 'off')->setAttribute("autocorrect", "off")->setAttribute("autocapitalize", "none");
    $row=$cd->addRow();
    $row->addCol()->addDiv()->addInput("clPwd", "password")->setClass("igk-form-control")->setAttribute("placeholder", R::ngets("tip.pwd"))->setAttribute("autocomplete", 'current-password');
    $row=$cd->addRow();
    $row->addCol()->addDiv()->setClass("alignc")->addInput("btn_connect", "submit", R::ngets("btn.connect"))->setClass("igk-btn igk-btn-default igk-form-control igk-btn-connect");
    $row=$cd->addRow();
    $dv=$row->addCol()->addDiv();
    $dv->Content=igk_get_string_format(<<<EOF
<input type="checkbox" name="remember_me" id="remember_me" value="1" checked="1" /><span>{1}</span>
<span class="separator" >&middot;</span>
<a href="#" >{0}</a>
EOF
    , R::ngets("lb.q.forgotpwd"), R::ngets("lb.remember_me"));
    $t=array();
    $t["badUri"]=array("type"=>"hidden", "attribs"=>array("value"=>$badUri));
    if($goodUri){
        $t["goodUri"]=array("type"=>"hidden", "attribs"=>array("value"=>$goodUri));
    }
    igk_html_build_form($frm, $t);
}
///<summary>get application authorization key</summary>
/**
* get application authorization key
*/
function igk_get_app_auth($app, $name){
    return $app->Name.":/".$name;
}
///<summary>get all application controller</summary>
/**
* get all application controller
*/
function igk_get_app_ctrl(){
    $v_ruri=igk_io_base_request_uri();
    $tab=explode('?', $v_ruri);
    $uri=igk_getv($tab, 0);
    $page="/".$uri;
    $actionctrl=igk_getctrl(IGK_SYSACTION_CTRL,false);
    if($actionctrl && ($e=$actionctrl->matche($page))){
        $n=igk_getv(igk_getquery_args($e->value), "c");
        $ctrl=igk_getctrl($n, false);
        if($ctrl){
            if(igk_reflection_class_extends($ctrl, "IGKApplicationController")){
                return $ctrl;
            }
        }
    }
    return null;
}

