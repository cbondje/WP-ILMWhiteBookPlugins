<?php

namespace IGK\System\Process;

use IGK\Controllers\BaseController;
use IGK\Models\Crons;

class CronJobProcess{
    const mail_script = "process.mail";
    public static function Register($name, $script, $options, ?BaseController $ctrl=null){
        if ($provider = self::GetJobProcessProvider($script)){
            $options = $provider->treat($options);
        }

        return Crons::create([
            "crons_name"=>$name,
            "crons_script"=>$script,
            "crons_class"=>$ctrl ? get_class($ctrl):null,
            "crons_options"=>json_encode($options)
        ]) !==null;
    }
    public static function GetJobProcessProvider($script_file){
        return new CronJobProcessMailProvider();
    }
}