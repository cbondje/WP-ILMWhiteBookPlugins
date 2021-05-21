<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// file: igk_run_cron.php
// desc: definition of run cron script
//date: 20-11-2017

igk_ilog("run cron ".date('Y:m:d H:i:s'));
include_once(".igk_include_header.pinc");
igk_getctrl(IGK_SESSION_CTRL)->RunCron();