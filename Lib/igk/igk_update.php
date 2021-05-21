<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc : update framework utility testing

require_once(dirname(__FILE__)."/igk_framework.php");
ob_start();
igk_flush_data("check for update");
igk_flush_data("you are up to date");
igk_flush_data("you are not up to date... please update");