<?php
 
namespace IGK\System\Database;

use IGKDbColumnInfo;
use IGKDbSchemas;
use IGKException;

class SchemaChangeColumnMigration extends SchemaMigrationItemBase{
    protected $fill_properties = ["table", "column"];
    private $column_info;
    // source column to restore
    var $columnInfo;
    public function up(){
        
        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        if (empty($this->column_info->clName))
            $this->column_info->clName = $this->column;
        $ctrl::db_change_column($tb, $this->column_info); 
    }
    public function down()
    {
        if (!$this->columnInfo)
            return; 
        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        $ctrl::db_change_column($tb, $this->columnInfo);
    }

    protected function loadChilds($childs){
        $this->columns = [];
        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        foreach($childs as $c){
            $cl = IGKDbColumnInfo::CreateWithRelation(igk_to_array($c->Attributes), $tb, $ctrl, $tbrelation);           
            $this->columns[]=$cl; 
            $this->column_info = $cl;
            break;
        }  
        // TODO: To remove
        // $this->up();   
    }
}