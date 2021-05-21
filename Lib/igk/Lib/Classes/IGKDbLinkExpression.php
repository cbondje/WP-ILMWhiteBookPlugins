<?php

class IGKDbLinkExpression extends IGKDbExpression{

    public $linkTable;
    public $columnName;
    public $columnValue;
    public $primaryColumn;

    public function __construct($linkTable, $columnName, $columnValue, $primaryColumn="clId"){
        parent::__construct("link.expression");
        $this->linkTable = $linkTable;
        $this->columnName = $columnName;
        $this->columnValue = $columnValue; 
        $this->primaryColumn = $primaryColumn;
    }
    public function getValue($grammar=null){
        //link value 
        if ($grammar == null){ 
            return null;
        } 
        switch($grammar->type){
            case "where":
                return "`{$grammar->column}`=(SELECT {$this->primaryColumn} FROM {$this->linkTable} where {$this->columnName}='{$this->columnValue}')";
            case "insert":
                return "(SELECT {$this->primaryColumn}  FROM {$this->linkTable} where {$this->columnName}='{$this->columnValue}')";
        }
        // if ($grammar->type=="where"){ 
        // }

        // igk_wln_e($grammar);
        // return "(SELECT clId FROM tbigk_groups where clName='tbn_ctrl/Administrators')";

        // $b = explode(".", $this->m_v);
        // $sl = [$b[0]=>$b[1]]; 
        // if ($b=$grammar::GetSelectQuery( 
        //     $tinf->clLinkType, $sl, ["Columns"=>[$tinf->clLinkColumn ?? "clId"]])){
        //     return $b = "(".rtrim(trim($b),";").")";
        // }

           

    }
}