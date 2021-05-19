<?php
namespace ILYEUM\database;


class QueryExpression{
    var $expression;
    
    public function __construct($expression=null)
    {
        $this->expression = $expression;
    }
}