<?php

namespace IGK\System\Process;

use IGK\Controllers\BaseController;

class CronJobProcessMailProvider extends CronJobProcessProviderBase{
    public function treat($options){
        return igk_get_robjs('to|subject|message', 0, $options);
    }
   
}