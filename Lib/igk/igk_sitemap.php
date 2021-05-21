<?php
// desc: sitemap.xml management script
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// TODO: site map function

define("IGK_SITEMAP_REQUEST", 1);
define("IGK_REDIRECTION", 1);
require_once(dirname(__FILE__)."/igk_framework.php");
igk_set_timeout(10);
if(!preg_match("/\/sitemap(\.xml)?/", igk_getv($_SERVER, "REDIRECT_URL"))){
    igk_set_header(404);
    igk_show_error_doc(null, 404, false);
    igk_exit();
}
$dir=realpath(dirname(__FILE__)."/../../");
chdir($dir);
if(file_exists("index.php")){
    include_once("index.php");
    $s=igk_io_subdomain_uri_name();
    $defctrl=igk_get_defaultwebpagectrl();
    if($defctrl){
        $defctrl->handle_redirection_uri('/sitemap');
    }
}
igk_exit();