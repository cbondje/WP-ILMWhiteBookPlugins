<?php
use function igk_getv as getv;

///<summary>Represente class: IGKSQLDataAdapter</summary>
/**
* Represente IGKSQLDataAdapter class
*/
abstract class IGKSQLDataAdapter extends IGKDataAdapter{
    /**
     * 
     * @param mixed $t 
     * @return mixed 
     * @throws IGKException 
     * @deprecated since 11.7.05.19 use SQLGrammar insteed
     */
    public static function ResolvType($t){        
        return IGKSQLQueryUtils::ResolvType($t);
    }
    public function GetValue($k, $rowInfo=null, & $tinfo=null){
        static $configs;
        if ($configs===null){
            $configs['auto_increment_word'] = "AUTO_INCREMENT";
        }
        $sys = $configs;
        if(empty($sys))
            return null;
        $m= getv($configs, $k);
        if(is_callable($m)){
            return $m($rowInfo, $tinfo);
        }
        return $m;
    }

     /**
     * create link expression
     * @param string $table table name
     * @param array $column 
     * @param array $value 
     * @param mixed $columnkey 
     * @return IGKDbLinkExpression 
     */
    public function createLinkExpression($table, $column, $value, $columnkey){
        return new IGKDbLinkExpression($table, $column, $value, $columnkey);      
    }

    public function getGrammar(){
        return $this->create_grammar() ?? die("grammar can't be found");
    }
    protected function create_grammar(){        
        $grammar = new IGK\System\Database\SQLGrammar($this);
        // $grammar->driver = $this;
        return $grammar;
    }
    public function escape($str){
        return igk_db_escape_string($str);
    }
    /**
     * get relation attached to table
     * @param mixed $adapter 
     * @param mixed $tname 
     * @return mixed 
     * @throws Exception 
     */
    protected static function GetRelation($adapter, $tname, $clname){
        $r = $adapter->getDbname();        
        $adapter->selectdb("information_schema");
        $h=$adapter->sendQuery("SELECT * FROM `KEY_COLUMN_USAGE` WHERE `TABLE_NAME`='".igk_db_escape_string($tname)."' AND `TABLE_SCHEMA`='".igk_db_escape_string($r)."' AND `COLUMN_NAME`='".igk_db_escape_string($clname)."' AND `REFERENCED_TABLE_NAME`!=''");
        $adapter->selectdb($r);
        return $h->getRowAtIndex(0);
    }
    public static function ResolvColumnInfo($adapter, $table, $columninfo){
        $v = $columninfo;
        $table_n = $table;
        $mysql = $adapter;
        $cl= []; // $row->addNode("Column");
        $cl["clName"]=$v->Field;
        $tab=array();
        preg_match_all("/^((?P<type>([^\(\))]+)))\\s*(\((?P<length>([0-9]+))\)){0,1}( (?P<option>(unsigned)))?$/i", trim($v->Type), $tab);
        igk_ilog("name: ".$v->Field. " ".$v->Type);
        $cl["clType"]= static::ResolvType(igk_getv($tab["type"], 0, "Int"));
        $cl["clTypeLength"]=igk_getv($tab["length"], 0, 0);
        if (!empty($tab["option"][0])){
            switch(strtolower(trim($tab["option"][0]))){
                case "unsigned":
                    $cl["clType"] = "U".$cl["clType"];
                break;
            }
        }
        if($v->Default)
            $cl["clDefault"]=$v->Default;
        if($v->Comment){
            $cl["clDescription"]=$v->Comment;
        }
        $cl["clAutoIncrement"]=preg_match("/auto_increment/i", $v->Extra) ? "True": null;
        $cl["clNotNull"]=preg_match("/NO/i", $v->Null) ? "True": null;
        $cl["clIsPrimary"]=preg_match("/PRI/i", $v->Key) ? "True": null;
        $cl["clIsUnique"]=preg_match("/UNI/i", $v->Key) ? "True": null;
        if(preg_match("/(MUL|UNI)/i", $v->Key)){
            $rel= static::GetRelation($mysql, $table_n, $v->Field);
            if($rel){
                $cl["clLinkType"]=$rel->REFERENCED_TABLE_NAME;
            }
        }
        if (!empty($v->Extra) && (($cpos = strpos($v->Extra, "on update "))!==false)){
            $c = trim(substr($v->Extra, $cpos+10));
            if (in_array($c, ["CURRENT_TIMESTAMP"]))
                $cl["clUpdateFunction"] = "Now()";
        }
        //+ insert and update function ignored
        return $cl; 
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="entry"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $condition
    */
    public function delete($tbname, $conditions=null){
        $this->dieNotConnect();
        $query=IGKSQLQueryUtils::GetDeleteQuery($tbname, $conditions);
        $t=$this->sendQuery($query, $tbname);
        if($t){
            return true;
        }
        return false;
    }
    ///<summary>delete all from table</summary>
    /**
    * delete all from table
    */
    public function deleteAll($tbname, $condition=null){
        $query = $this->getGrammar()->createDeleteQuery($tbname, $condition);		
        return $this->sendQuery($query); 
    }
    ///<summary>setup manager config for next operation</summary>
    /**
    * setup manager config for next operation
    */
    protected function initConfig(){}
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="values"></param>
    ///<param name="tableinfo" default="null"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $values
    * @param mixed $tableinfo the default value is null
    */
    public function insert($tbname, $values, $tableinfo=null){
        $this->dieNotConnect();
        $this->initConfig();
        $query=IGKSQLQueryUtils::GetInsertQuery($tbname, $values, $tableinfo);
        $t=$this->sendQuery($query, $tbname);
        if($t){
            if(is_object($values)){
                if(igk_getv($values, "clId") == null)
                    $values->clId=$this->last_id();
            }
            return true;
        }
        else{
            $error="Insertion Query Error : ".igk_mysql_db_error(). " : ".$query;
            igk_debug_wln($error);
            igk_db_error($error);
        }
        return false;
    }


   ///<summary></summary>
    /**
    * 
    */
    public function last_id(){}
    ///<summary>build and send a mysql select query</summary>
    ///<param name="options">callback or igk_db_create_opt_obj()</param>
    /**
    * build and send a mysql select query
    * @param mixed $options callback or igk_db_create_opt_obj()
    */
    public function select($tbname, $where=null, $options=null){
        if(empty($tbname))
            igk_die("table name is empty");
        $q=IGKSQLQueryUtils::GetSelectQuery($this, $tbname, $where, $options);
        return $this->sendQuery($q, $tbname, $options);
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    /**
    * 
    * @param mixed $tbname
    */
    public function selectAll($tbname){
        $tbname=igk_mysql_db_tbname($tbname);
        $q="SELECT * FROM `".($tbname)."` ";
        return $this->sendQuery($q, $tbname);
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $condition the default value is null
    * @param mixed $options the default value is null
    */
    public function selectAndWhere($tbname, $condition=null, $options=null){
        // $o="";
        // $q="";
        // $s=0;
        // $s="";
        // $column= "*"; //IGKSQLQueryUtils::GetColumnList($options, $tbname);
        // $tlist = "";
        // if(is_string($tbname)){
        //     $tbname=igk_mysql_db_tbname($tbname);
        //     $tlist="`".$tbname."`";
        // }
        // else if(is_array($tbname)){
        //     $r=0;
        //     foreach($tbname as $k){
        //         $_n=igk_mysql_db_tbname($k);
        //         if($r)
        //             $q .= ",";
        //         $q .= "`".$_n."`";
        //         $r=1;
        //     }
        //     $tlist = $q;
        //     $q = "";
        // } 
        // if($condition){
        //     if(is_object($condition) || (is_array($condition) && (igk_count($condition) > 0))){
        //         if ($c = IGKSQLQueryUtils::GetCondString($condition, "AND", $this))
        //             $q .= "WHERE ".$c;
        //     }
        //     else if(is_numeric($condition) || !is_string($condition)){
        //         $q .= "WHERE `".IGK_FD_ID."`='".igk_db_escape_string($condition)."' ";
        //     }
        //     else if(is_string($condition)){
        //         $q .= "WHERE ".igk_db_escape_string($condition)." ";
        //     }
        // }
        // $q = " FROM ".$tlist .$q;
        // $columns = "*";
        // if ($options && ($tq= IGKSQLQueryUtils::GetExtraOptions($options, $this))){
        //     if (!empty($tq->join))
        //         $q = $tq->join . " ".$q;
        //     $q.= " ".$tq->extra;
        //     $columns = $tq->columns;
        // }
        // $q = "SELECT {$columns} ".$q.";";
        if ($query = $this->getGrammar()->createSelectQuery($tbname, $condition, $options)){
            return $this->sendQuery($query, $tbname, $options);
        }
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="entry"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="tabinfo" default="null"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $entry
    * @param mixed $condition the default value is null
    * @param mixed $tabinfo the default value is null
    */
    public function update($tablename, $entry, $condition=null, $tabinfo=null){
        $this->dieNotConnect();
        $query = $this->getGrammar()->createUpdateQuery($tablename, $entry, $condition, $tabinfo);
        // $query=IGKSQLQueryUtils::GetUpdateQuery($tablename, $entry, $condition, $tabinfo);
        $s=$this->sendQuery($query, $tablename);
        return $s;
    }

    public function getFuncValue($type, $value){
        //
        // if ($pos == "IGK_PASSWD_ENCRYPT"){
        //     return "'".$driver->escape_string(IGKSysUtil::Encrypt($value))."'";
        // }
        //
        return null;
    }
    public function getObjValue($value){
        if(igk_reflection_class_implement($value, IIGKHtmlGetValue::class)){
            return $value->getValue(
                (object)[
                    "grammar"=>null,
                    "type"=>"insert"
                ]
            );
        }
        return null;
    }
    public function getObExpression($value, $throwex=false){
        if ($value instanceof IGKDbExpression){
            return $value->getValue();
        } else {
            if ($throwex){
                throw new IGKException(__("objet not a DB Expression"));
            }
        }
        return null;
    }
}