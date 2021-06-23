<?php
// @author: C.A.D BONDJE DOUE
// @file: wp/database/driver.php
// @desc: 
// @date: 20210517 09:27:52
namespace ILYEUM\wp\database;

use ILYEUM\database\Grammar;
use ILYEUM\database\QueryRow;
use ILYEUM\Utility;

use function ilm_getv as getv;
use function ilm_log as _log;
/**
 * represent model driver
 * @package ILYEUM\wp\database
 */
class driver{
    private $con;
    /**
     * 
     * @var mixed
     */
    private $m_grammar;

    /**
     * store info on init database loading
     * @var mixed
     */
    private $m_relations;

    /**
     * filter query
     * @var mixed
     */
    var $filter;

    const SELECT_DATA_TYPE_QUERY = 'SELECT distinct data_type as type FROM INFORMATION_SCHEMA.COLUMNS';
    const SELECT_VERSION_QUERY = "SHOW VARIABLES where Variable_name='version'";
 
    public function getDbName(){
        return constant('DB_NAME');
    }

    public function escape_string($s){
        return mysqli_real_escape_string($this->con, $s);
    }

    public function __get($name){
        return ilm_environment()->get("db:".$name);
    }
    public function __construct()
    {
        register_shutdown_function(function(){
            $this->on_exit();
        });
        if (!($connect = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME))){
            ilm_wln_e(mysqli_connect_error());
        }
        $this->con = $connect;
        mysqli_set_charset($connect, "UTF8");
    }
    public function beginInitDb(){
        $this->m_relations = [];
    }
    public function endInitDb(){
        if ($this->m_relations){
            foreach($this->m_relations as $tbname=>$r){
                foreach($r as $m=>$p){
                    $c = $p["column"];
                    $c->clLinkType = Utility::GetTableName( $c->clLinkType ); 
                    if (! $this->sendQuery($query = $this->getGrammar()->add_foreign_key( $tbname, $c))){
                        _log(implode("\n", ["query failed: ",$query, $this->last_error()]));
                    }
                }
            }
        }
        $this->m_relations = [];
    }
    private function on_exit(){
        if ($this->con){
            mysqli_close($this->con);
            $this->con = null;
        }
    }
    public function pushRelations($table, $columninfo){
        if (!isset($this->m_relations[$table])){
            $this->m_relations[$table] = [];
        }
        $this->m_relations[$table][] = ["target"=>$columninfo->clLinkType, "column"=>$columninfo];
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
    public function GetExpressQuery(){
        
    }
    protected static function ResolvType($type){
        switch(strtolower($type)){
            case "int":
            return "Int";
            case "varchar":
            return "VARCHAR";
        }
        return strtoupper($type);
    }
    public function getColumnInfo($table){
        $data =  $this->getGrammar()->get_column_info($table);
        $outdata = [];
        array_map(function($v)use($table, & $outdata){            
            $cl = [];
            $ctype = trim($v->Type);
            $tab=array();
            $ctype = trim($v->Type);
            preg_match_all("/^((?P<type>([^\(\))]+)))\\s*((\((?P<length>([0-9]+))\)){0,1}|(.+)?)$/i", trim($v->Type), $tab);

            $cl["clType"]= static::ResolvType(getv($tab["type"], 0, "Int"));
            if (strtolower($cl["clType"]) =="enum"){
                $cl["clEnumValues"] = substr($ctype, strpos($ctype, "(")+1,-1); 
            }else{
                $cl["clTypeLength"]=getv($tab["length"], 0, 0);
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
                $rel= $this->getGrammar()->get_relation($table, $v->Field, $this->getDbName());
                if($rel){
                    $cl["clLinkType"]=$rel->REFERENCED_TABLE_NAME;
                }
            }
            if (!empty($v->Extra) && (($cpos = strpos($v->Extra, "on update "))!==false)){
                $c = trim(substr($v->Extra, $cpos+10));
                if (in_array($c, ["CURRENT_TIMESTAMP"]))
                    $cl["clUpdateFunction"] = "Now()";
            }
            $outdata[$v->Field] = (object)$cl;
        }, $data);
        return $outdata;
    }
    public function sendQuery($query, $options=null){
        if (ilm_environment()->querydebug){
            if (function_exists("igk_wln")){
                igk_wln("query: $query");       
            } 
        }
        if ($g = mysqli_query($this->con, $query)){           
            if (is_bool($g)){
                return $g;
            }
            $fc = getv($options, "@callback");
            $tab = [];
            $cols = null; 
            while($row = mysqli_fetch_assoc($g)){   
                if ($cols === null){
                    $fields = mysqli_fetch_fields($g);
                    foreach($fields as $k){
                        $cols[$k->name] = $k;
                    } 
                }
                $tr = QueryRow::Create($cols, $row);
                if ($fc &&  ($m = $fc($tr))){
                    $tab[] = $m;
                }else{
                    $tab[] = $tr;
                }
            }
            return $tab;
        }        
    }
    public function foreignCheck(bool $check){
        $s = $check ? "1" : "0";
        $this->sendQuery("SET foreign_key_checks=$s"); 
    }
    public function dropTable($tablename){     
        $this->sendQuery("DROP TABLE ".$this->escape_string($tablename)); 
    }
    /**
     * get driver grammar
     * @return Grammar 
     */
    public function getGrammar(){
        if ($this->m_grammar === null){
            $this->m_grammar = new Grammar($this);
        }
        return $this->m_grammar;
    }
    public function createTable($tablename, $columnInfo, $desc=null){
        $query = $this->getGrammar()->createTableQuery($tablename, $columnInfo, $desc);
        if (!$this->sendQuery($query)){
            _log("query error: ".$query);
        }
        return true;
    }
    public function insert($tablename, $values, $tabInfo=null){        
        $query = $this->getGrammar()->createInsertQuery($tablename, $values, $tabInfo);
        return $this->sendQuery($query);
    }
    public function update($tablename, $values, $conditions=null, $tableInfo=null){
        $query = $this->getGrammar()->createUpdateQuery($tablename, $values, $conditions, $tableInfo);
        return $this->sendQuery($query);
    }
    public function select($tablename, $conditions=null, $options=null){
        $query = $this->getGrammar()->createSelectQuery($tablename, $conditions, $options);
        $g =  $this->sendQuery($query, $options);
        return $g;
    }
    public function isTypeSupported($type){
        static $supportedList;
        if ($supportedList===null){
            $supportedList = [];
            if ($g = $this->sendQuery(self::SELECT_DATA_TYPE_QUERY)){
                foreach($g as $r){
                    $supportedList[] = strtolower($r->type);
                } 
            }
        }       
        return in_array(strtolower($type), $supportedList); 
    }
    public function tableExist($table){
        if ($s=$this->sendQuery("SELECT Count(*) FROM `".$this->escape_string($table)."`", 
                [
                    "throw"=>false,
                    "no_log"=>true
                ]
            )){ 
                return true;
          }
         
        return false;           
    }
    public function getFuncValue($type, $value){
        //
        // if ($type == "IGK_PASSWD_ENCRYPT"){
        //     return "'".$driver->escape_string(IGKSysUtil::Encrypt($value))."'";
        // }
        return "'".$this->m_driver->escape_string($value)."'";
    }
    public function getObjValue($value){
        // if(igk_reflection_class_implement($value, 'IIGKHtmlGetValue')){
        //     return $value->getValue(
        //         (object)[
        //             "grammar"=>null,
        //             "type"=>"insert"
        //         ]
        //     );
        // }
        return null;
    }
    public function getObExpression($value, $throwex=false){
        // if ($s instanceof IGKDbExpression){
        //     $columns.= $s->getValue();
        // } else {
        //     throw new IGKException(__("objet not a DB Expression"));
        // }
        return null;
    }
    public function last_id(){
        return mysqli_insert_id($this->con);
    }
    public function last_error(){
        return mysqli_error($this->con);
    }

    public function createLinkExpression(){
        //new IGKDbLinkExpression($model::table(), $express[0], $express[1], $model->getPrimaryKey()); 
        return null;
    }
    public function delete($tbname, $condition){
        $query = $this->getGrammar()->createDeleteQuery($tbname, $condition);
        return $this->sendQuery($query);
    }
    public function listTables(){
        return $this->getGrammar()->listTables();
    }
}