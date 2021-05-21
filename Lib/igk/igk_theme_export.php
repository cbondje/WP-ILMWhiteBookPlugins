<?php
// file: igk_theme_export.php
// desc: create a primary static css theme
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019

ini_set("display_errors", "1");
error_reporting(E_ALL);
include("igk_framework.php");
header("Content-Type: text/css");
$app=IGKApp::getInstance();
igk_wl("/*default igk core system style */");
igk_wl(igk_css_getbasedef(true, true));