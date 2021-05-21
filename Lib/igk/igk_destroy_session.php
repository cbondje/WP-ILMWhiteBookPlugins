<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// file: igk_destroy_session.php
// desc: just destroy current session

session_start();
session_destroy();
exit();