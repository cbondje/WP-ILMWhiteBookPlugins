<?php
namespace IGK\System\Database\MySQL;

use IGKDBSingleValueResult;
use IGKMYSQLDataAdapter;
use IGKQueryResult;
use IGKQueryRowObj;
use IGKSorter;
use IIGKQueryResult;

///<summary>Represent MySQL Query result wrapper</summary>
/**
* Represent MySQL Query result wrapper
*/
final class IGKMySQLQueryResult extends IGKQueryResult implements IIGKQueryResult {
    private $m_adapterName;
    private $m_columns;
    private $m_dbname;
    private $m_fieldcount;
    private $m_irows;
    private $m_primarykey;
    private $m_query;
    private $m_rows;
    private $m_rowsEntity;
    private $m_tables;
    private $m_type;
    private $m_value;
    private $m_multitable; 
    public function success(){
        return $this->m_rows !== null;
    }
    ///<summary></summary>
    /**
    * 
    */
    private function __construct(){
        $this->m_columns=array();
        $this->m_tables=array();
        $this->m_rows=array();
        $this->m_rowsEntity=array();
        $this->m_irows=array();
        $this->m_adapterName="MYSQL";
        $this->m_type="none";
        $this->m_result=0;
        $this->m_multitable = false;
    }
    ///retult of the query  uses for boolean data
    /**
    */
    public function __toString(){
        return "IGKMySQLQueryResult [RowCount: ".$this->RowCount."]";
    }
    ///be aware: don't make call to !== it make bit exhausted
    ///<summary>add a row to query result</summary>
    ///<remark>if build in query result . that will be a copy of the rows</remark>
    /**
    * add a row to query result
    */
    public function addRow($row){
        if(($this->m_type == "igk_db_query_result") && ($this->m_query == ":igk_build_in_query_result")){
            if(is_object($row)){
                $tab=array();
                foreach($this->m_columns as  $v){
                    $tab[$v->name]=igk_getv($row, $v->name);
                }
                $this->m_rows[]=(object)$tab;
            }
            return;
        }
        if(is_object($row) || (is_array($row) && (count($row) == $this->m_fieldcount))){
            $this->m_rows[]=(object)$row;
            $this->m_rowcount=count($this->m_rows);
        }
        else{
            igk_wln_e("row not added ".$this->m_fieldcount. " isarray?".is_array($row). " || iscount? :: ".count($row)." == ".$this->m_fieldcount);
        }
    }
    ///<summary>create a empty result from result type</summary>
    /**
    * create a empty result from result type
    */
    public static function CreateEmptyResult($result, $seacharray=null){
        $out=new IGKMySQLQueryResult();
        $out->m_dbname=$result->m_dbname;
        $out->m_fieldcount=$result->m_fieldcount;
        $out->m_rows=array();
        $out->m_rowsEntity=array();
        $out->m_query='Empty';
        return $out;
    }
    ///<summary>create a result data</summary>
    ///<param name="options">callback or igk_db_create_opt_obj()</param>
    /**
    * create a result data
    * @param mixed options callback or igk_db_create_opt_obj()
    */
    public static function CreateResult($dbresult, $query=null, $options=null){
        $_handle=$options && igk_getv($options, 'handle');
        if(!$_handle){
            if(is_bool($dbresult) || is_numeric($dbresult) || ($dbresult === null)){
                $out = new IGKDBSingleValueResult();
                $out->type = is_numeric($dbresult) ? "numeric": "boolean";
                $out->value = $dbresult;
                $out->query = $query;
                return $out;
            }
            if(!$dbresult){
                igk_set_error(__METHOD__, "CreateResult - > dbresult  not Define");
                return null;
            } 
            if(is_object($dbresult)){
                $cl=strtolower(get_class($dbresult));
                
                if(!preg_match("/mysql(i)?_result/", $cl)){
                    $out=new IGKMySQLQueryResult();
                    $out->m_rowcount=1;
                    $out->m_rows[]=$dbresult;
                    $tab=(array)$dbresult;
                    $out->m_fieldcount=igk_count($tab);
                    $out->m_type="igk_db_query_result";
                    $out->m_query=":igk_build_in_query_result";
                    $i=0;
                    foreach($tab as $k=>$v){
                        $out->m_columns[$i]=(object)array("name"=>$k, "typeName"=>"php", "index"=>$i);
                        $i++;
                    } 
                    return $out;
                }
            }
        }
        if(!igk_db_is_resource($dbresult) || ($dbresult instanceof IGKQueryResult )){
            return $dbresult;
        }
        
        $c=igk_db_num_rows($dbresult);
        $out=new IGKMySQLQueryResult();
        if($_handle){
            $out->m_adapterName=$options->adapterName ?? $out->m_adapterName;
        }
        $out->m_rowcount=$c;
        $out->m_fieldcount=igk_db_num_fields($dbresult);
        $out->m_type="igk_db_query_result";
        $out->m_query=$query;
        $index=0;
        $prim_key=array();
        while($d=igk_db_fetch_field($dbresult)){
            $d->index=$index;
            $d->typeName=igk_mysql_db_gettypename($d->type);
            if((isset($d->primary_key) && $d->primary_key) || (igk_mysql_db_is_primary_key($d->flags))){
                $d->primary_key=1;
                $prim_key[]=(object)(array("name"=>$d->name, "index"=>$index));
            }
            else{
                $d->primary_key=0;
            }
            $out->m_columns[$index]=$d;
            $out->m_tables[$d->table]=$d->table;
            $index++;
        }
        $v_primkey=count($prim_key) == 1 ? $prim_key[0]->name: null;
        $v_primkeyindex=count($prim_key) == 1 ? $prim_key[0]->index: null;
        $callback=is_callable($options) ? $options: igk_getv($options, self::CALLBACK_OPTS );
        $_nn=(igk_count($out->m_tables) > 1);
        $c=0;
        while($d=igk_db_fetch_row($dbresult)){
            $t=array();
            foreach($out->m_columns as $k=>$s){
                if(!isset($t[$s->name])){
                    $t[$s->name]=$d[$s->index];
                } else { 
                if ($_nn)
                    $t[$s->table.".". $s->name]=$d[$s->index];
                else 
                    $t[$s->name]=$d[$s->index];
                }

            }
            $obj=  IGKQueryRowObj::Create($t);
            if($callback && !$callback($obj)){
                continue;
            }
            $c++;
            if($v_primkey){
                $out->m_rows[$d[$v_primkeyindex]]=$obj;
            }
            else if(count($prim_key) > 1)
                $out->m_rows[]=$obj;
            else
                $out->m_rows[]=$obj;
            $out->m_irows[]=$obj;
        }
        $out->m_rowcount=$c;
        $out->m_primarykey=$v_primkey;
        $out->m_multitable = $_nn;

 

        return $out;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getColumnCount(){
        return igk_count($this->m_columns);
    }
    ///<summary></summary>
    ///<param name="columnname"></param>
    /**
    * 
    * @param mixed $columnname
    */
    public function getColumnIndex($columnname){
        if(isset($this->m_columns[$columnname])){
            return $this->m_columns[$columnname]->index;
        }
        return -1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getColumns(){
        return $this->m_columns;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getHasRow(){
        return ($this->getRowCount() > 0);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getQuery(){
        return $this->m_query;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getResult(){
        if(strtolower($this->m_type) == 'boolean'){
            return igk_getv(igk_getv($this->m_irows, 0), 'clResult');
        }
        return false;
    }
    ///<summary>get the type of result. boolean|numeric|db_result</summary>
    /**
    * get the type of result. boolean|numeric|db_result
    */
    public function getResultType(){
        return $this->m_type;
    }
    ///<summary></summary>
    ///<param name="index"></param>
    /**
    * 
    * @param mixed $index
    */
    public function getRowArray($index){
        if(($index < 0) && ($index>=$this->RowCount)){
            return null;
        }
        $f=array();
        $c=0;
        foreach($this->m_columns as $k){
            $f[$k->name]=$this->m_rows[$index][$c];
            $c++;
        }
        $f["info"]=array();
        if(isset($this->m_columns[0]))
            $f["info"]["sourcetable"]=$this->m_columns[0]->table;
        return $f;
    }
    ///<summary></summary>
    ///<param name="index"></param>
    /**
    * 
    * @param mixed $index
    */
    public function getRowAtIndex($index){
        if(strtolower($this->m_type) == 'igk_db_query_result'){
            return igk_getv($this->m_irows, $index);
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getRowCount(){
        return igk_count($this->m_rows);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getRows(){
        return $this->m_rows;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSuccess(){
        return ($this->ResultTypeIsBoolean() && $this->getValue()) || ($this->RowCount > 0);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTables(){
        return $this->m_tables;
    }
    ///<summary>get the request value</summary>
    /**
    * get the request value
    */
    public function getValue(){
        return $this->m_value;
    }
    ///<param name="equalsTab">array for searching </param>
    /**
    * @param mixed $equalsTab array for searching
    */
    public function searchEqual($equalsTab){
        if(!is_array($equalsTab))
            return null;
        $t=array();
        foreach($this->Rows as  $v){
            $found=true;
            foreach($equalsTab as $m=>$n){
                if($v->$m != $n){
                    $found=false;
                    break;
                }
            }
            if($found)
                $t[]=$v;
        }
        if(igk_count($t) == 1)
            return $t[0];
        if(igk_count($t) == 0)
            return null;
        return $t;
    }
    ///<summary></summary>
    ///<param name="callback"></param>
    /**
    * 
    * @param mixed $callback
    */
    public function select($callback){
        $result=new IGKMySQLQueryResult();
        $result->m_columns=$this->m_columns;
        $result->m_type="igk_db_query_filter_result";
        $result->m_fieldcount=$this->m_fieldcount;
        foreach($this->m_rows as  $v){
            if($callback($v)){
                $result->m_rows[]=$v;
            }
        }
        return $result;
    }
    ///<summary></summary>
    ///<param name="key"></param>
    ///<param name="asc" default="true"></param>
    ///<param name="preserveid" default="true"></param>
    /**
    * 
    * @param mixed $key
    * @param mixed $asc the default value is true
    * @param mixed $preserveid the default value is true
    */
    public function SortBy($key, $asc=true, $preserveid=true){
        return $this->SortValueBy($key, $asc, null, $preserveid);
    }
    ///<summary>sort result </summary>
    ///<param name="key">mixed. callback | key to sor </param>
    /**
    * sort result
    */
    public function SortValueBy($key, $asc=true, $param=null, $preserveid=false){
        if(is_callable($key))
            $param=$key;
        else{
            if($param == null){
                $t=new IGKSorter();
                $t->key=$key;
                $t->asc=$asc;
                $param=array($t, "SortValue");
            }
        }
        $tab=$this->m_rows;
        usort($tab, $param);
        $pm=$this->m_primarykey ?? IGK_FD_ID;
        if($preserveid){
            $t=array();
            foreach($tab as $k){
                $t[$k->$pm]=$k;
            }
            $this->m_irows=$tab;
            $this->m_rows=$t;
        }
        else{
            $this->m_irows=$tab;
            $this->m_rows=$tab;
        }
        return $this;
    }
    public function toAssocArray($name){
        $o = null;
        foreach($this->Rows as $r){
            if ($o===null) $o = [];
            $o[$r->$name] = $r;
        }
        return $o;
    }
}