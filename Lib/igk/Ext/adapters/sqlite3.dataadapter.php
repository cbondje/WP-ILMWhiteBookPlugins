<?php
// @file: sqlite3.dataadapter.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

use IGK\System\Database\MySQL\IGKMySQLQueryResult;

define("IGK_SQL3LITE_KN", "sql3lite");
define("IGK_SQL3LITE_KN_TABLE_KEY", IGK_SQL3LITE_KN."::/tableName");
define("IGK_SQL3LITE_KN_QUERY_KEY", IGK_SQL3LITE_KN."::/query");
define("IGK_SQL3LITE_TYPE_NAME_INDEX", 2);
define("IGK_SQL3LITE_NAME_INDEX", 1);
///<summary></summary>
///<param name="r"></param>
///<param name="info" default="null" ref="true"></param>
/**
* 
* @param mixed $r
* @param  * $info the default value is null
*/
function igk_sql3lite_autoincrement($r, & $info=null){
    if(preg_match("/^(int(eger)?)$/i", (strtolower($r->clType))) && ($r->clIsPrimary)){
        if($info !== null){
            $primkey="noprimkey://".$r->clName;
            $info[$primkey]=1;
        }
        return "primary key autoincrement";
    }
    return null;
}
///<summary></summary>
/**
* 
*/
function igk_sql3lite_close(){
    $sq=IGKSQLite3DataAdapter::GetCurrent();
    return $sq && $sq->close();
}
///<summary></summary>
/**
* 
*/
function igk_sql3lite_connect(){
    igk_wln(func_get_args());
    throw new Exception("not permitted");
}
///<summary></summary>
/**
* 
*/
function igk_sql3lite_error(){
    $c=IGKSQLite3DataAdapter::GetCurrent();
    if($c)
        return $c->sql->lastErrorMsg();
    return 0;
}
///<summary></summary>
/**
* 
*/
function igk_sql3lite_error_code(){
    $c=IGKSQLite3DataAdapter::GetCurrent();
    if($c)
        return $c->sql->lastErrorCode();
    return 0;
}
///<summary></summary>
///<param name="str"></param>
/**
* 
* @param mixed $str
*/
function igk_sql3lite_escapestring($str){
    $sq=IGKSQLite3DataAdapter::GetCurrent();
    igk_assert_die($sq->sql === null, 'SQLite3 Error : Sql is null');
    return $sq->sql->escapeString($str);
}
///<summary></summary>
///<param name="r"></param>
///<param name="requiretable" default="1"></param>
/**
* 
* @param mixed $r
* @param mixed $requiretable the default value is 1
*/
function igk_sql3lite_fetch_field($r, $requiretable=1){
    $index=0;
    $v_k="field::/index";
    $v_inf_k="field::/index_info";
    $index=igk_getv($r, $v_k, 0);
    $v_inf=igk_getv($r, $v_inf_k, 0);
    $tb=igk_getv($r, IGK_SQL3LITE_KN_TABLE_KEY);
    $ctx=IGKSQLite3DataAdapter::GetCurrent();
    $k=null;
    if($index < $r->numColumns()){
        if(!$v_inf){
            $h=$r->fetchArray(SQLITE3_NUM);
            $fieldnames=[];
            $fieldtypes=[];
            for($colnum=0; $colnum < $r->numColumns(); $colnum++){
                $fieldnames[]=$r->columnName($colnum);
                $fieldtypes[]=$r->columnType($colnum);
            }
            $r->reset();
            if(!empty($tb)){
                $q="pragma table_info('{$tb}')";
                $v_inf=$ctx->sql->query($q);
                igk_wln($q);
                $r->$v_inf_k=$v_inf;
                while($d=$r->$v_inf_k->fetchArray(SQLITE3_NUM)){
                    igk_wln("i ");
                    igk_wln($d);
                }
            }
            else{}
        }
        $tab=[];
        $k=(object)array(
                "name"=>$r->columnName($index),
                "type"=>igk_sql3lite_tosql_data(igk_getv($tab,
                IGK_SQL3LITE_TYPE_NAME_INDEX)),
                "flags"=>igk_getv($tab,
                5),
                "table"=>$tb,
                "primary_key"=>igk_getv($tab,
                5),

            );
        $index++;
        $r->$v_k=$index;
    }
    else{
        $r->reset();
        unset($r->$v_k);
    }
    return $k;
}
///<summary></summary>
///<param name="r"></param>
/**
* 
* @param mixed $r
*/
function igk_sql3lite_fetch_row($r){
    return $r->fetchArray(SQLITE3_NUM);
}
///<summary></summary>
/**
* 
*/
function igk_sql3lite_lastid(){
    return -1;
}
///<summary></summary>
///<param name="r"></param>
/**
* 
* @param mixed $r
*/
function igk_sql3lite_num_fields($r){
    return $r->numColumns();
}
///<summary></summary>
///<param name="t"></param>
/**
* 
* @param mixed $t
*/
function igk_sql3lite_num_rows($t){
    igk_sql3lite_fetch_row($t);
    if($t->numColumns() && $t->columnType(0) != SQLITE3_NULL){
        return 1;
    }
    else{
        return 0;
    }
}
///<summary></summary>
///<param name="d"></param>
/**
* 
* @param mixed $d
*/
function igk_sql3lite_tosql_data($d){
    if(!preg_match_all("/(?P<type>([^\(\)])+)(\((?P<number>[0-9]+)\))?/i", $d, $tab))
        return "unknown";
    $d=igk_getv($tab["type"], 0);
    switch(strtolower($d)){
        case "integer":
        case "int":
        return MYSQLI_TYPE_SHORT;
        case "text":
        case "string":
        return MYSQLI_TYPE_STRING;

    }
    throw new Exception("error : ".$d);
}
///<summary> represent SQLite3 database adapter</summary>
/**
*  represent SQLite3 database adapter
*/
class IGKSQLite3DataAdapter extends IGKSQLDataAdapter implements IIGKDataAdapter{
    private $fname;
    private $m_creator;
    private $m_current;
    private static $LENGTHDATA=array("int"=>"Int", "varchar"=>"VarChar");
    private static $sm_connexions;
    private static $sm_list;
    private static $sm_sql;

	public function escape_string($v){
		    return "from sqlite3: ". $this->sql->escapeString($v);
	}
    ///<summary></summary>
    /**
    * 
    */
    public function beginTransaction(){
        $this->sql->exec("BEGIN TRANSACTION");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function close(){
        $sql=$this->getConnectionManager();
        if($sql){
            $sql->count--;
            if(($sql->count<=0) && ($sql->Sql)){
                $sql->Sql->close();
                $sql=null;
                unset(self::$sm_sql[$this->fname]);
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function commit(){
        $this->sql->exec("COMMIT");
    }
    ///<summary>mixed. controller| filename |data value<summary>
    /**
    * mixed. controller| filename |data value
    *
    */
    public function connect($dbname=null){
        if(func_num_args() > 0){
            if(igk_is_controller($dbname)){
                $f=igk_getv($dbname->Configs, "clSQLite3DataFile");
                $fulln=IGKIO::GetDir(igk_io_ctrl_db_dir($dbname)."/{$f}");
                $ctn=$this->getConnexion($fulln);
                if(($ctn == null) || ($ctn->openCount<=0)){
                    $this->fname=$fulln;
                    $this->initConfig();
                    return 1;
                }
                return 1;
            }
            else if(is_string($dbname)){
                $this->fname=$dbname;
            }
        }
        $fulln="";
        $sql=$this->fname ? $this->getSql(): null;
        $c=0;
        if(!$sql){
            if($this->m_creator){
                $o=$this->m_creator;
                if(igk_is_callback_obj($o)){
                    $sql=igk_invoke_callback_obj(null, $o);
                }
                else if(is_object($o))
                    $sql=$o->createDb($this);
            }
            else{
                if(igk_is_controller($dbname)){
                    $f=igk_getv($dbname->Configs, "clSQLite3DataFile");
                    if(empty($f)){
                        igk_die("no file setup");
                    }
                    $fulln=IGKIO::GetDir(igk_io_ctrl_db_dir($dbname)."/{$f}");
                }
                else if(is_string($dbname)){
                    $fulln=$dbname;
                }
                else{
                    if($dbname === null){
                        $fulln=igk_io_sys_datadir()."/databases/".igk_sys_getconfig("sqlite3.database", ".database.sqlite3");
                        IGKIO::CreateDir(dirname($fulln));
                    }
                    else
                        igk_die("SQLite3: failed initiate data".$dbname);
                }
                $this->fname=$fulln;
                $this->initConfig();
                $this->storeConnexion($fulln, $sql);
                $sql=$this->sql;
                $c=1;
            }
            if(!$c){
                if($sql == null){
                    igk_die("/!\\No creator set to init the sql connection");
                }
                $this->initConfig();
                $this->storeConnexion("", $sql);
                $this->fname="";
                return 1;
            }
        }
        $man=$this->getConnectionManager();
        $man->count++;
        $this->initConfig();
        if($man->count == 1)
            $this->enableForeignKey(1);
        return 1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function selectCount(){
        return 0;
    }
    ///<summary></summary>
    ///<param name="tb"></param>
    /**
    * 
    * @param mixed $tb
    */
    public function countTable($tb){
        return $this->sendQuery("SELECT Count(*) as count FROM `".$tb."`;", $tb);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function createDb(){
        return true;
    }
    ///<summary></summary>
    ///<param name="result" default="null"></param>
    ///<param name="query" default="null"></param>
    ///<param name="info" default="null"></param>
    /**
    * 
    * @param mixed $result the default value is null
    * @param mixed $query the default value is null
    * @param mixed $info the default value is null
    */
    public function CreateEmptyResult($result=null, $query=null, $info=null){
        $r=IGKMySQLQueryResult::CreateResult($result, $query, $info);
        return $r;
    }
    ///<summary></summary>
    ///<param name="r"></param>
    ///<param name="query" default="null"></param>
    ///<param name="obj" default="null"></param>
    /**
    * 
    * @param mixed $r
    * @param mixed $query the default value is null
    * @param mixed $obj the default value is null
    */
    public function createResult($r, $query=null, $obj=null){
        $inf=(object)array_merge(array(
            "source"=>$this,
            "handle"=>true,
            "adapterName"=>IGK_SQL3LITE_KN,
            "query"=>$query
        ), (array)$obj ?? []);
        return IGKMySQLQueryResult::CreateResult(new sql3literesult($r), $query, $inf);
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="columninfo"></param>
    ///<param name="entries" default="null"></param>
    ///<param name="desc" default="null"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $columninfo
    * @param mixed $entries the default value is null
    * @param mixed $desc the default value is null
    */
    public function createTable($tbname, $columninfo, $entries=null, $desc=null){
         $query=self::CreateTableQuery($tbname, $columninfo, $entries, $desc);
        $r=$this->sendQuery($query, $tbname);
        if($entries){
            igk_db_inserts($this, $tbname, $entries);
        }
        return $r;
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="columninfo"></param>
    ///<param name="entries" default="null"></param>
    ///<param name="desc" default="null"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $columninfo
    * @param mixed $entries the default value is null
    * @param mixed $desc the default value is null
    */
    public static function CreateTableQuery($tbname, $columninfo, $entries=null, $desc=null){
        $query="CREATE TABLE IF NOT EXISTS `".igk_mysql_db_tbname($tbname)."`(";
        $tb=false;
        $primary="";
        $unique="";
        $funique="";
        $findex="";
        $uniques=array();
        $primkey="";
        $tinf=array();
        $foreignkey="";
        foreach($columninfo as  $v){
            if(($v == null) || !is_object($v)){
                igk_die(__CLASS__." ::: Error table column info is not an object error for ".$tbname);
            }
            $primkey="noprimkey://".$v->clName;
            if($tb)
                $query .= ",";
            $v_name=igk_db_escape_string($v->clName);
            $query .= "`".$v_name."` ";
            $type=igk_getev($v->clType, "Int");
            if(($type == "Int") && ($v->clLinkType || $v->clAutoIncrement))
                $type="INTEGER";
            $query .= igk_db_escape_string($type);
            $s=strtolower($type);
            $number=false;
            if(isset(self::$LENGTHDATA[$s])){
                if($v->clTypeLength > 0){
                    $number=true;
                    $query .= "(".igk_db_escape_string($v->clTypeLength).") ";
                }
                else
                    $query .= " ";
            }
            else
                $query .= " ";
            if(!$number){
                if(($v->clNotNull) || ($v->clAutoIncrement))
                    $query .= "NOT NULL ";
                else
                    $query .= "NULL ";
            }
            else if($v->clNotNull){
                $query .= "NOT NULL ";
            }
            if($v->clAutoIncrement){
                $query .= IGKDBQueryDriver::GetValue("auto_increment_word", $v, $tinf)." ";
            }
            $tb=true;
            if($v->clDefault || $v->clDefault === '0'){
                $query .= "DEFAULT '".igk_db_escape_string($v->clDefault)."' ";
            }
            if($v->clIsUnique){
                if(!empty($unique))
                    $unique .= ",";
                $unique .= "CONSTRAINT ".strtolower("constraint_".$v_name)." UNIQUE (`".$v_name."`)";
            }
            if($v->clIsUniqueColumnMember){
                if(isset($v->clColumnMemberIndex)){
                    $tindex=explode("-", $v->clColumnMemberIndex);
                    $indexes=array();
                    foreach($tindex as $kindex){
                        if(!is_numeric($kindex) || isset($indexes[$kindex]))
                            continue;
                        $indexes[$kindex]=1;
                        $ck='unique_'. $kindex;
                        $bf="";
                        if(!isset($uniques[$ck])){
                            $bf .= "CONSTRAINT  `clUC_".$ck."_index` UNIQUE(`".$v_name."`";
                        }
                        else{
                            $bf=$uniques[$ck];
                            $bf .= ", `".$v_name."`";
                        }
                        $uniques[$ck]=$bf;
                    }
                }
                else{
                    if(empty($funique)){
                        $funique="CONSTRAINT `clUnique_Column_".$v_name."_idx` UNIQUE (`".$v_name."`";
                    }
                    else
                        $funique .= ", `".$v_name."`";
                }
            }
            if($v->clIsPrimary && !isset($tinf[$primkey])){
                if(!empty($primary))
                    $primary .= ",";
                $primary .= "`".$v_name."`";
            }
            if(($v->clIsIndex || $v->clLinkType) && !$v->clIsUnique && !$v->clIsUniqueColumnMember && $v->clIsPrimary){
                if(!empty($findex))
                    $findex .= ",";
                $findex .= "KEY `".$v_name."_index` (`".$v_name."`)";
            }
            unset($tinf[$primkey]);
            if($v->clLinkType){
                $cl=igk_getv($v, "clLinkColumn", "clId");
                if(!empty($foreignkey))
                    $foreignkey .= ",";
                $foreignkey .= "FOREIGN KEY ({$v_name}) REFERENCES {$v->clLinkType}({$cl})";
            }
        }
        if(!empty($primary)){
            $query .= ", PRIMARY KEY  (".$primary.") ";
        }
        if(!empty($unique)){
            $query .= ", ".$unique." ";
        }
        if(!empty($funique)){
            $funique .= ")";
            $query .= ", ".$funique." ";
        }
        if(igk_count($uniques) > 0){
            foreach($uniques as  $v){
                $v .= ")";
                $query .= ", ".$v." ";
            }
        }
        if(!empty($findex))
            $query .= ", ".$findex;
        if(!empty($foreignkey)){
            $query .= ", ".$foreignkey;
        }
        $query .= ")";
        $query .= ";";
        return $query;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function dieNotConnect(){
        if($this->sql == null)
            throw new Exception("sql3lite no connection available ");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function dropAllTables(){
        $r=$this->listTables();
        if($r){
            $this->sql->exec('PRAGMA foreign_keys=OFF');
            foreach($r->Rows as  $v){
                if($v->name == "sqlite_sequence"){
                    $this->deleteAll("sqlite_sequence");
                    continue;
                }
                $this->dropTable($v->name);
            }
        }
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    /**
    * 
    * @param mixed $tbname
    */
    public function dropTable($tbname){
        return $this->sendQuery("Drop Table IF EXISTS   `{$tbname}`", $tbname);
    }
    ///<summary></summary>
    ///<param name="b"></param>
    /**
    * 
    * @param mixed $b
    */
    public function enableForeignKey($b){
        $s=$b ? 'ON': 'OFF';
        $this->sql->exec('PRAGMA foreign_keys='.$s);
    }
    ///<summary></summary>
    ///<param name="str"></param>
    /**
    * 
    * @param mixed $str
    */
    public function escapeString($str){
        return $this->sql->escapeString($str);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConnectionManager(){
        if(self::$sm_sql)
            return igk_getv(self::$sm_sql, $this->fname);
        return null;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function getConnexion($n){
        $r=igk_getv(self::$sm_connexions, $n);
        return $r;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetCurrent(){
        if(self::$sm_list == null)
            self::
        $sm_list=array();
        return igk_getv(self::$sm_list, 0);
    }
    ///<summary>get entry database name</symmary>
    /**
    * get entry database name
    */
    public function getDatabaseFileName(){
        $r=$this->sendQuery('PRAGMA database_list', ':global:');
        $f=null;
        if($r && ($r->RowCount == 1)){
            $f=$r->getRowAtIndex(0)->file;
        }
        return $f;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDatabaseVersion(){
        $r=$this->sendQuery('PRAGMA user_version;', ':global:');
        $f=null;
        if($r && ($r->RowCount == 1)){
            $f=$r->getRowAtIndex(0)->user_version;
        }
        return $f;
    }
    ///<summary>get data schema</summary>
    ///<param name="fmt">format of the request dataschema. default is xml. acceptable is xml|obj</param>
    /**
    * get data schema
    * @param string $fmt format of the request dataschema. default is xml. acceptable is xml|obj
    */
    public function getDataSchema($entries=0, $fmt='xml'){
        $rep=null;
        switch($fmt){
            case 'xml':
            default:
            $rep=igk_createxmlnode(IGK_SCHEMA_TAGNAME);
            $rep["Date"]=date('Y-m-d');
            $rep["Version"]=$this->getDatabaseVersion();
            $rep["Name"]=basename($this->getDatabaseFileName());
            $r=$this->listTables();
            $entry_node=null;
            if($entry_node == null)
                $entry_node=$rep->addXMLNode("Entries");
            if($r){
                $n=$r->Columns[0]->name;
                $e=false;
                foreach($r->Rows as $t){
                    if($e)
                        break 2;
                    $table_n=$t->$n;
                    if(($table_n == null) || ($table_n == "sqlite_sequence"))
                        continue;
                    $row=$rep->addNode(IGKDbSchemas::DATA_DEFINITION)->setAttributes(array("TableName"=>$table_n));
                    $tinfo=array();
                    $tt=$this->countTable($table_n);
                    $b=$tt->Columns[0]->name;
                    if($entries){
                        $row["Entries"]=$tt->Rows[0]->$b;
                        $b=$this->select($table_n);
                        $trow=$entry_node->addXMLNode("Rows")->setAttribute("For", $table_n);
                        foreach($b->Rows as $kk=>$vv){
                            $trow->addXMLNode("Row")->setAttributes((array)$vv);
                        }
                    }
                    $tt=$this->sql->query("pragma table_info('{$table_n}')");
                    $itt=$this->sql->query("pragma index_list('{$table_n}')");
                    $ift=$this->sql->query("pragma foreign_key_list('{$table_n}')");
                    $tinfo=array();
                    $clinfo=(object)array();
                    $uc_index=1;
                    while($d=$itt->fetchArray(SQLITE3_NUM)){
                        $info=(object)array();
                        $cn=igk_getv($d, 1);
                        $clt=igk_getv($d, 3);
                        $por=$this->sql->query("PRAGMA index_info('{$cn}')");
                        $cl_count=0;
                        $cln=null;
                        while($rc=$por->fetchArray(SQLITE3_NUM)){
                            $cln=igk_getv($rc, 2);
                            if(!empty($info->$cln))
                                $info->$cln .= "|".$clt;
                            else
                                $info->$cln=$clt;
                            $cl_count++;
                            if(!isset($clinfo->$cln))
                                $clinfo->$cln=(object)array(
                                "is_Unique"=>0,
                                "is_UniqueColumnMember"=>0,
                                "cl_member_index"=>0,
                                "clRefType"=>null,
                                "clRefColumn"=>null,
                                "clInfo"=>$info
                            );
                            else{
                                if(is_array($clinfo->$cln->clInfo))
                                    $clinfo->$cln->clInfo[]=$info;
                                else{
                                    $clinfo->$cln->clInfo=array($clinfo->$cln->clInfo, $info);
                                }
                            }
                        }
                        if($cl_count > 1){
                            $info->clUniqueColumnMember=$uc_index++;
                            foreach(array_keys((array)$info) as $rmm=>$rtt){
                                if($rtt == "clUniqueColumnMember")
                                    continue;
                                $clinfo->$rtt->is_UniqueColumnMember=1;
                                $clinfo->$rtt->cl_member_index=(empty($clinfo->$rtt->cl_member_index) ? $info->clUniqueColumnMember: $clinfo->$rtt->cl_member_index."-".$info->clUniqueColumnMember);
                            }
                        }
                        else{
                            if($clt == "u"){
                                $clinfo->$cln->is_Unique=1;
                            }
                        }
                        $tinfo[]=$info;
                    }
                    $fields=array();
                    if($tt){
                        $tbrelations=[];
                        while($relations=$ift->fetchArray(SQLITE3_NUM)){
                            $tbrelations[igk_getv($relations, 3)
                            ]=(object)["table"=>igk_getv($relations, 2), "sourceColumn"=>igk_getv($relations, 3), "targetColumn"=>igk_getv($relations, 4)];
                        }
                        while($d=$tt->fetchArray(SQLITE3_NUM)){
                            $fi=(object)array(
                                "clName"=>igk_getv($d,
                                1),
                                "clType"=>igk_getv($d,
                                IGK_SQL3LITE_TYPE_NAME_INDEX),
                                "clComment"=>"",
                                "clAutoIncrement"=>igk_getv($d,
                                5),
                                "clDefault"=>igk_getv($d,
                                4),
                                "clNotNull"=>igk_getv($d,
                                3)
                            );
                            if(isset($tbrelations[$fi->clName])){
                                $fi->clLinkType=$tbrelations[$fi->clName]->table;
                                if($tbrelations[$fi->clName]->targetColumn != IGK_FD_ID)
                                    $fi->clLinkTypeColumn=$tbrelations[$fi->clName]->targetColumn;
                            }
                            $v=$fi;
                            $cl= $row->addNode("Column");
                            $cl["clName"]=$fi->clName;
                            $tab=array();
                            preg_match_all("/^((?P<type>([^\(\))]+)))\\s*(\((?P<length>([0-9]+))\)){0,1}$/i", trim($fi->clType), $tab);
                            $cl["clType"]=igk_sql_data_type(igk_getv($tab["type"], 0, "Int"));
                            $cl["clTypeLength"]=igk_getv($tab["length"], 0, 0);
                            $cl["clAutoIncrement"]=$v->clAutoIncrement ? $v->clAutoIncrement: null;
                            $cl["clNotNull"]=$v->clNotNull ? $v->clNotNull: null;
                            $cl["clIsPrimary"]=$v->clAutoIncrement ? $v->clAutoIncrement: null;
                            $rinf=igk_getv($clinfo, $fi->clName);
                            $cl["clColumnMemberIndex"]=$rinf && $rinf->is_UniqueColumnMember ? $rinf->cl_member_index: null;
                            $cl["clIsUnique"]=$rinf && $rinf->is_Unique ? 1: null;
                            $cl["clIsUniqueColumnMember"]=$rinf && $rinf->is_UniqueColumnMember ? 1: null;
                            $cl["clLinkType"]=igk_getv($fi, "clLinkType");
                            $cl["clLinkTypeColumn"]=igk_getv($fi, "clLinkTypeColumn");
                        }
                    }
                    $tinfo[$fi->clName]=new IGKDbColumnInfo((array)$fi);
                    $fields[]=$fi;
                    $tables[$table_n]=(object)array("tinfo"=>$tinfo, 'ctrl'=>"sys://mysql_db");
                }
            }
            break;
        }
        return $rep;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDbIdentifier(){
        return IGK_SQL3LITE_KN;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public static function GetSchemaOptions($n){
        $ul=$n->addUL();
        $li=$ul->add("li");
        $li->addLabel("clsqlite3_file")->setContent(null)->addSpan()->Content="SQLite3 Db Filename";
        $li->addInput("clsqlite3_file", "text");
        return $n;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSql(){
        if(self::$sm_sql == null){
            self::
            $sm_sql=[];
        }
        return igk_getv(self::$sm_sql, $this->fname, function(){
            $s=new SQLite3($this->fname);
            $man=new IGKSQLiteConnectionManager();
            $man->Sql=$s;
            $man->count=0;
            self::$sm_sql[$this->fname]=$man;
            return $man;
        })->Sql;
    }
    ///<summary>get sql version</summary>
    /**
    * get sql version
    */
    public function getVersion(){
        return $this->sql->version();
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function initConfig(){
        $this->makeCurrent();
        self::StoreStack($this);
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    /**
    * 
    * @param mixed $tablename
    */
    public function initSystableRequired($tablename){}
    ///<summary></summary>
    /**
    * 
    */
    public function IsForeignKeyEnable(){
        $r=$this->sendQuery('PRAGMA foreign_keys;', ':global:');
        $f=0;
        if($r && ($r->RowCount == 1)){
            $f=$r->getRowAtIndex(0)->foreign_keys;
        }
        return $f;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function last_id(){
        return $this->sql->lastInsertRowID();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function listTables(){
        return $this->sendQuery("SELECT name FROM sqlite_master WHERE type='table';", "sqlite_master");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function numRows(){
        return -1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function OpenCount(){
        $r=$this->getConnectionManager();
        return $r ? $r->count: 0;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function restoreRelationChecking(){
        $this->enableForeignKey(1);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function rollback(){
        $this->sql->exec("ROLLBACK");
    }
    ///<summary></summary>
    ///<param name="query"></param>
    ///<param name="tbname" default="null"></param>
    /**
    * 
    * @param mixed $query
    * @param mixed $tbname the default value is null
    */
    public function sendQuery($query, $tbname=null){
        $sql=$this->getSql();
        $r=null;
        if(preg_match("/^(INSERT|UPDATE|DELETE|CREATE|DROP)/i", $query)){
            !($r=@$sql->exec($query)) && igk_die("query failed . ".$query. " error ".$this->sql->lastErrorMsg());
            return $r;
        }
        else{
            !($r=@$sql->query($query)) && igk_debug_or_local_die($this->sql->lastErrorMsg());
        }
        if($r && is_object($r)){
            $obj=igk_createObj();
            igk_setv($obj, IGK_SQL3LITE_KN_QUERY_KEY, $query);
            igk_setv($obj, IGK_SQL3LITE_KN_TABLE_KEY, $tbname);
            return $this->createResult($r, $query, $obj);
        }
        if($this->sql->lastErrorCode() == 0)
            return null;
        $obj= $this->CreateEmptyResult(false, $query, array("error"=>1, "errormsg"=>$this->sql->lastErrorMsg()));
        return $obj;
    }
    ///<summary></summary>
    ///<param name="listener"></param>
    /**
    * 
    * @param mixed $listener
    */
    public function setCreatorListener($listener){
        $this->m_creator=$listener;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setDatabaseVersion($v){
        $this->sql->exec('PRAGMA user_version='.$v);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function setForeignKeyCheck(){}
    ///<summary></summary>
    /**
    * 
    */
    public function stopRelationChecking(){
        $this->enableForeignKey(0);
    }
    ///<summary></summary>
    ///<param name="fname"></param>
    ///<param name="sql"></param>
    /**
    * 
    * @param mixed $fname
    * @param mixed $sql
    */
    public function storeConnexion($fname, $sql){
        if(self::$sm_connexions == null)
            self::
        $sm_connexions=array();
        $this->m_current=(object)array("filename"=>$fname, "openCount"=>1, "sql"=>$sql);
        self::
        $sm_connexions[$fname]=$this->m_current;
    }
    ///<summary></summary>
    ///<param name="l"></param>
    /**
    * 
    * @param mixed $l
    */
    public static function StoreStack($l){
        self::GetCurrent();
        array_unshift(self::$sm_list, $l);
    }
}

///<summary>Represente class: IGKSQl3DbConfigCtrl</summary>
/**
* Represente IGKSQl3DbConfigCtrl class
*/
class IGKSQl3DbConfigCtrl extends IGKConfigCtrlBase{
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigPage(){
        return "sqlite3database";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getName(){
        return "SQLite3";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function showConfig(){
        parent::showConfig();
        $box=$this->getConfigNode()->clearChilds()->addPanelBox();
        $this->loader->view("sql3lite.config", ["t"=>$box]);
    }
}
///<summary>Represente class: sql3literesult</summary>
/**
* Represente sql3literesult class
*/
class sql3literesult{
    var $Columns;
    var $Rows;
    var $res;
    ///<summary></summary>
    ///<param name="res" type="SQLite3Result"></param>
    /**
    * 
    * @param SQLite3Result $res
    */
    public function __construct(SQLite3Result $res){
        $this->res=$res;
        $this->Columns=array();
        $this->Rows=array();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function CreateEmptyResult(){
        return null;
    }
}
IGKDBQueryDriver::Init(function(& $conf){
    $n=IGK_SQL3LITE_KN;
    $conf["db"]=IGK_SQL3LITE_KN;
    $conf[$n]["func"]=array();
    $conf[$n]["auto_increment_word"]="igk_sql3lite_autoincrement";
    $conf[$n]["data_adapter"]="SQL3Lite";
    $t=array();
    $t["connect"]="igk_sql3lite_connect";
    $t["selectdb"]="";
    $t["check_connect"]="";
    $t["query"]="igk_sql3lite_fetch_query";
    $t["escapestring"]="igk_sql3lite_escapestring";
    $t["num_rows"]="igk_sql3lite_num_rows";
    $t["num_fields"]="igk_sql3lite_num_fields";
    $t["fetch_field"]="igk_sql3lite_fetch_field";
    $t["fetch_row"]="igk_sql3lite_fetch_row";
    $t["close"]="igk_sql3lite_close";
    $t["error"]="igk_sql3lite_error";
    $t["errorc"]="igk_sql3lite_error_code";
    $t["lastid"]="igk_sql3lite_lastid";
    $conf[$n]["func"]=$t;
});