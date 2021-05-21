<?php
namespace IGK\System\Models;

use IGKDbUtility;

class UsersUtility extends IGKDbUtility{
    public function __construct(){
        parent::__construct(igk_getctrl(IGK_USER_CTRL));
    }
}