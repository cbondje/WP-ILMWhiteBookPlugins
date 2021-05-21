<?php
// @author: C.A.D BONDJE DOUE
// @file: SchemaMigrationBuilder.php
// @desc: the migration schema builder
// @date: 20210422 06:39:05
namespace IGK\System\DataBase;

use IGKDbSchemas;

class SchemaMigrationBuilder extends SchemaBuilderHelper{
  
    private $is_migration;
    public static function Create($node, $schema){
        $c = new static();
        $c->_output = $node;
        $c->_schema = $schema;
        $c->is_migration = 0;
        return $c;
    }
    public function migration(){
        if ($this->is_migration){
            return $this;
        }
        $n = $this->_output->add(IGKDbSchemas::MIGRATION_TAG);        
        $d = self::Create($n,$this->_schema);
        $d->is_migration = 1;
        return $d;
    }
    public function addColumn($table, ?array $options=null, $after=null){
        if ($this->is_migration){
            $b = $this->_output->add("addColumn");
            $b["table"] = $table;
            $b["after"] = $after;
            if (!empty($options)){
                $this->_addcolumnAttributes($options, $b);
            } 
            return $this;
        }
        $this->migration()->addColumn($table, $options, $after);
        return $this;
    }
    public function changeColumn($table, $column, array $options){
        if ($this->is_migration){
            $b = $this->_output->add("changeColumn");
            $b["table"] = $table; 
            $b["column"] = $column; 
            if (!empty($options)){
                $this->_addcolumnAttributes($options, $b);
            } 
            return $this;
        }
        $this->migration()->changeColumn($table, $column, $options);
    }
    public function renameColumn($table, $colname, $newname){
        if ($this->is_migration){
            $b = $this->_output->add("renameColumn");
            $b["table"] = $table;
            $b["column"] = $colname;
            $b["new_name"] = $newname;
            return $this;
        }
        $this->migration()->renameColumn($table, $colname, $newname);
        return $this;
    }
    public function removeColumn($table, $colname){
        if ($this->is_migration){
            $b = $this->_output->add("removeColumn");
            $b["table"] = $table;
            $b["column"] = $colname; 
            return $this;
        }
        $this->migration()->removeColumn($table, $colname);
        return $this;
    }
}