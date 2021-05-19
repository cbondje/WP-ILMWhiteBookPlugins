<?php
namespace ILYEUM\database; 
use IGKDbExpression;
use ILYEUM\WhiteBooks\Models\ModelBase;

class QueryBuilder{
    private $m_conditions;
    private $m_options;
    private $model;

    const LeftJoin="LeftJoin";
    const InnerJoin="InnerJoin";

    public function __construct(ModelBase $model)
    {
        if (!$model)
            die("not allowed");
        $this->m_conditions = null;
        $this->m_options = [];
        $this->model = $model;
    }
    /**
     * help left join
     * @param mixed $condition 
     * @return array 
     */
    public static function LeftJoin($condition){
        return ["type"=>self::LeftJoin, $condition];
    }
    public static function InnerJoin($condition){
        return ["type"=>self::InnerJoin, $condition];
    }
    public static function Or(array $conditions){
        return (object)["operand"=>"OR", "conditions"=>$conditions];
    }
    /**
     * return a db expression 
     * @param mixed $string 
     * @return IGKDbExpression 
     */
    public static function Expression($string){
        return new QueryExpression($string);
    }

    public function conditions(array $condition){
        $this->m_conditions = $condition;
        return $this;
    }

    public function join(array $join){
        $this->m_options["Joins"][] = $join;
        return $this;
    }
    public function limit(array $limit_raw){
        $this->m_options["Limit"] = $limit_raw;
        return $this;
    }
    public function orderBy($order){
        $this->m_options["OrderBy"] = $order;
        return $this;
    } 
    public function distinct(bool $distinct=true){
        if ($distinct)
            $this->m_options["Distinct"]  = 1;
        else 
            unset($this->m_options["Distinct"]);
        return $this;
    } 
    public function query(){
        return $this->model->select_query($this->m_conditions, $this->m_options);
    }
    public function query_rows(){
        if ($r = $this->query()){
            return $r->getRows();
        }
        return null;
    }
}