<?php
namespace IGK\System\Database;

use IGKDbSchemas;
use IGKException;

class SchemaBuilderMigration{
    var $controller;
    private $items; 
    public function __call($name, $arguments)
    {
        $cl = __NAMESPACE__."\\Schema".ucfirst($name)."Migration";
        if (class_exists($cl) && is_subclass_of($cl, SchemaMigrationItemBase::class)){
            if(!$this->items){
                $this->items = [];
            }
            $c = new $cl($this);
            $this->items[] = $c;
            return $c;
        }
        throw new IGKException("not allowed: $cl::".$name);
    } 
    public function upgrade(){
        if (!$this->items)return false;
        foreach($this->items as $c){
            $c->up();
        }
        return true;
    }
    public function downgrade(){
        if (!$this->items)
            return false;
        foreach($this->items as $c){
            $c->down();
        }
        return true;
    }
     
}