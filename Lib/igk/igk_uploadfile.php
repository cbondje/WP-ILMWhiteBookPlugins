<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: updload file

require_once("igk_framework.php");
$file=dirname(__FILE__)."/../../index.php";
IGKApp::
$BASEDIR=dirname($file);
$d=igk_getr("d");
file_put_contents(igk_io_basepath($d), file_get_contents("php://input"));