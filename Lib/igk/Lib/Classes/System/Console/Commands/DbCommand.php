<?php

namespace IGK\System\Console\Commands;


class DbCommand{
    public static function Init($command){ 
        $app = igk_app();    
        foreach([
            "-db"=>"db_name",
            "-db_user"=>"db_user",
            "-db_pwd"=>"db_pwd",
            "-db_host"=>"db_server",
            "-db_prefix"=>"db_prefix",
        ] as $k=>$v){
            if (property_exists($command->options, $k)){   
                igk_app()->configs->$v = $command->options->{$k};
            }    
        }  
       
        if (property_exists($command->options, "--querydebug")){
            igk_environment()->querydebug = 1;
        }
    }
}