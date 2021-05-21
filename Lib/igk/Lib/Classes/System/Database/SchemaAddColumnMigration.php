<?php

namespace IGK\System\Database;

use IGKDbColumnInfo;

class SchemaAddColumnMigration extends SchemaMigrationItemBase{
    protected $fill_properties = ["table", "after"];

    /**
     * list of column info
     */
    protected $columns;
    protected function loadChilds($childs){
        $this->columns = [];
        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        foreach($childs as $c){
            $cl = IGKDbColumnInfo::CreateWithRelation(igk_to_array($c->Attributes), $tb, $ctrl, $tbrelation);           
            $this->columns[]=$cl; 
        } 
        // TODO: To remove
        // $this->up();   
    }
    public function up(){ 

        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        foreach($this->columns as $cl){
            $ctrl::db_add_column($tb, $cl, $this->after);
        }
    }
    public function down(){
        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        foreach($this->columns as $cl){
            $ctrl::db_rm_column($tb, $cl);
        }
    }
}