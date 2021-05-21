<?php
namespace IGK\System\Database;

use IGKDbSchemas;

class SchemaBuilder{
    private $_output;
    private $_migrations;
    public function __construct(){
        $this->_output = igk_createxmlnode(IGK_SCHEMA_TAGNAME);
    }
    public function render(){
        return $this->_output->render();
    }
    public function createTable(string $table, $desc=null){
        $n = $this->_output->add(IGKDbSchemas::DATA_DEFINITION);
        $n["TableName"] = $table;
        $n["Description"] = $desc;
        return SchemaTableBuilder::Create($n, $this);
    }
    public function migrations(){
        if ($this->_migrations==null){

            $n =  $this->_output->add(IGKDbSchemas::MIGRATIONS_TAG);
            $this->_migrations = SchemaMigrationBuilder::Create($n , $this);
            
        }
        return $this->_migrations;
    }
}