<?php

namespace IGK\System\Database;

///<summary> represent a query condition</summary>
class QueryCondition{
    /**
     * operant type
     * @var mixed
     */
    var $operand;
    /**
     * array of conditions
     */
    var $conditions;

    protected function __construct()
    {        
    }

    public static function Create(array $conditions, $operand="AND"){
        if (!in_array($operand, ["OR", "AND"])){
            die("not a valid operand");
        }
        $c = new static;
        $c->operand = $operand;
        $c->$conditions = $conditions;
        return $c;
    }
}