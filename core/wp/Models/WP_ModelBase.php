<?php
namespace ILYEUM\wp\Models;

use ILYEUM\WhiteBooks\Models\ModelBase;


abstract class WP_ModelBase extends ModelBase{

    private static $sm_colmn_info;
    public function create_row(){
        if(!self::$sm_colmn_info){
            self::$sm_colmn_info = [];
        }
        $table = $this->getTable();
        if (!isset(self::$sm_colmn_info[$table])){
            $data = $this->getDataAdapter()->getColumnInfo($table);       
            self::$sm_colmn_info[$table] = $data;   
        }         
        return array_fill_keys(array_keys(self::$sm_colmn_info[$table]), null);
    }
}