<?php
// @author: C.A.D BONDJE DOUE
// @file: SchemaRenameColumnMigration.php
// @desc: schema builder helper
// @date: 20210422 09:09:36
namespace IGK\System\DataBase;


class SchemaRemoveColumnMigration extends SchemaMigrationItemBase{
    protected $fill_properties = ["table", "column"];
    public $columnInfo;

    public function up(){
        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        $ctrl::db_rm_column($tb, $this->column);
    }
    public function down(){ 
        $ctrl = $this->getMigration()->controller;
        $tb = igk_db_get_table_name($this->table, $ctrl);
        $ctrl::db_add_column($tb, $this->columnInfo, null); 
    }
   

}