<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: manage webservices

define("IGK_SERVICES_REQUEST", 1);
defined("IGK_SERVICES_ENTRY_URL") || define("IGK_SERVICES_ENTRY_URL", "/services");
///<summary>get ctrl from request uri</summary>
/**
* get ctrl from request uri
*/
function igk_get_ctrl_ruri($uri){
    if(preg_match_all("/(\?|\&)c=(?P<name>(([a-z]|[_])([a-z0-9_]*))+)/i", $uri, $tab))
        return igk_getctrl(igk_getv($tab["name"], 0));
    return null;
}
require_once(dirname(__FILE__)."/igk_framework.php");
$_SERVER["REQUEST_URI"]=IGK_SERVICES_ENTRY_URL;
$dir=realpath(dirname(__FILE__)."/../../");
chdir($dir);
include_once("index.php");
$doc=igk_get_document("sys://documents/services");
$doc->Title="Services - ".igk_app()->Configs->website_domain;
$doc->Favicon=new IGKHtmlRelativeUriValueAttribute("Lib/igk/Default/R/Img/cfavicon.ico");
$bbox=$doc->body->addBodyBox();
$bbox->ClearChilds();
$t=$bbox->addDiv();
$t->addSectionTitle("title.ListOfServices");
$ctn=$t->addContainer();
$ac=igk_getctrl(IGK_SYSACTION_CTRL);
$srv=0;
$actions=$ac->getActions();
foreach($actions as $k=>$v){
    if(preg_match_all("/^\^".IGK_SERVICES_ENTRY_URL."\/(?P<name>([^\/\(])+)(\/)?/i", $k, $tab)){
        $ctrl=igk_get_ctrl_ruri($v);
        $r=$ctn->addRow();
        $dv=$r->addCol()->addDiv();
        $nn=igk_getv($tab["name"], 0);
        $dv->addDiv()->addA(igk_io_baseUri().IGK_SERVICES_ENTRY_URL."/".$nn)->setStyle("font-size:2.1em")->Content=$nn;
        $dv->addDiv()->Content=$ctrl->getServiceDescription();
        $srv++;
    }
}
if($srv == 0){
    $ctn->addRow()->addCol()->Content="NoServices found";
}
$doc->RenderAJX();