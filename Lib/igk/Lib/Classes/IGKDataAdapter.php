<?php

use function igk_ilog as _log;
use function igk_getv as getv;


///<summary>IGKDataAdapter driver</summary>
/**
* Represente IGKDataAdapter class
*/
abstract class IGKDataAdapter extends IGKObject {
    private static $sm_regAdapter=null;
    protected $m_name;
    private $m_relations;

    public function pushRelations($table, $columninfo){
        
        if (!isset($this->m_relations->relations[$table])){
            $this->m_relations->relations[$table] = [];
        }
        $this->m_relations->relations[$table][] = [
            "target"=>$columninfo->clLinkType, 
            "column"=>$columninfo, 
            "ctrl"=> $this->m_relations->ctrl,
            "info"=>[]
        ];
    }
    public function pushEntries($table, $entries, $tableInfo){
        $this->m_relations->info[$table] = $tableInfo;
        if (!isset($this->m_relations->entries[$table])){
            $this->m_relations->entries[$table] = [];
        }
        $this->m_relations->entries[$table][] = $entries; 
    }
    public function beginInitDb($ctrl=null){
        $this->m_relations = (object)["relations"=>[], "entries"=>[], "ctrl"=>$ctrl];
    }
    public function endInitDb(){
        $_grammar = $this->getGrammar();
        $links = [];
        if ($this->m_relations->relations){
            foreach($this->m_relations->relations as $tbname=>$r){
                foreach($r as $m=>$p){
                    $c = clone($p["column"]);
                    $c->clLinkType = igk_db_get_table_name($c->clLinkType, $p["ctrl"]);
                    if (! $this->sendQuery($query = $_grammar->add_foreign_key( $tbname, $c))){
                        _log(implode("\n", ["query failed: ",$query, $this->last_error()]));
                    }
                    $links[$tbname][$c->clLinkType]++;
                }
            }
        }
        if ($this->m_relations->entries){
            //sort links data
            uksort($this->m_relations->entries, function($a, $b)use($links){
                if ($a == $b){
                    return 0;
                }
                if (isset($links[$a])){
                    if (isset($links[$b])){
                       if (!in_array($b, $links[$a])){
                           return -1;
                       } 
                    }
                    return 1;
                }
                return -1;
            });
            foreach($this->m_relations->entries as $tbname=>$r){
                $info = getv($this->m_relations->info, $tbname);
               //init entries
               foreach($r as $b){
                   foreach($b as $row){
                        $query = $_grammar->createInsertQuery($tbname, $row, $info);
                        if (!$this->sendQuery($query)){
                            _log(implode("\n", ["query failed: ",$query, $this->last_error()]));
                        }
                   }
               } 
            }
        }
        $this->m_relation = null;
    }

    ///<summary>retrieve the adapter name</summary>
    public function getName(){
        return $this->m_name;
    }
	public abstract function escape_string($s);
    public function last_error(){}
    public function sendQuery($query, $throwex=true, $options=null){}
    /**
     * 
     * @return object grammar object
     */
    public function getGrammar(){}

    public function GetExpressQuery($express, $tinf){
        $b = explode(".", $express);
        $sl = [$b[0]=>$b[1]]; 
        if ($b= $this->getGrammar()->createSelectQuery($tinf->clLinkType, $sl, ["Columns"=>[$tinf->clLinkColumn ?? IGK_FD_ID]])){
            return $b = "(".rtrim(trim($b),";").")";
        }
        return null; 
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    /**
    * 
    * @param mixed $tablename
    */
    public function ClearTable($tablename){}
    ///<summary></summary>
    /**
    * 
    */
    abstract public function close();
    ///<summary></summary>
    ///<param name="params"></param>
    /**
    * 
    * @param mixed $params
    */
    protected function configure($params){}
    ///<summary></summary>
    ///<param name="ctrl" default="null"></param>
    /**
    * 
    * @param mixed $ctrl the default value is null
    */
    abstract public function connect($ctrl=null);
    ///<summary></summary>
    ///<param name="ctrl"></param>
    ///<param name="throwexception"></param>
    ///<param name="newAdapter"></param>
    ///<param name="params" default="null"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $throwexception
    * @param mixed $newAdapter the default value is 0
    * @param mixed $params the default value is null
    */
    public static function CreateDataAdapter($ctrl, $throwexception=true, $newAdapter=0, $params=null){
        if(empty($ctrl)){
            igk_wln_e("can't create empty data adapter");
            return null;
        }
        $adapt=self::GetAdapters();
        $n=IGK_STR_EMPTY;
        $key=IGK_STR_EMPTY;
        if(is_string($ctrl)){
            $key=strtoupper($ctrl);
            $n="IGK".$ctrl."DataAdapter";
        }
        else{
            $key=strtoupper($ctrl->getDataAdapterName());
            $n="IGK".$key."DataAdapter";
        } 
        if(!$newAdapter && isset($adapt[$key])){
            
            return $adapt[$key];
        } 
        if(class_exists($n) && !igk_reflection_class_isabstract($n)){
            $out=igk_create_adapter_from_classname($n);
            if($out){
                $adapt[$key]=$out;
                $out->m_name = $key;
                return $out;

            }
        }
        else{
            $c=igk_get_env("sys://dataadapter");
            if($c && isset($c[$key])){
                $o=$c[$key];
                $out=new $o();
                $adapt[$key]=$out;
                $out->m_name = $key;
                return $out;
            }
        }
        if($throwexception){
            igk_die("DataAdapter: ".$ctrl. " not found. Driver class expected : ".$n);
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    abstract public function CreateEmptyResult();
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="columninfoArray"></param>
    ///<param name="entries" default="null"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $columninfoArray
    * @param mixed $entries the default value is null
    */
    public function createTable($tablename, $columninfoArray, $entries=null){}
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="entries"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $entries
    */
    public function delete($tablename, $condition=null){
        igk_die("function ".__FUNCTION__." not implements");
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    /**
    * 
    * @param mixed $tablename
    */
    public function deleteAll($tablename, $condition=null){
        igk_die("function ".__FUNCTION__." not implements");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function drop($tablename, $condition=null){
        $this->deleteAll($tablename, $condition);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function flushForInitDb($complete=null){}
    ///<summary></summary>
    /**
    * 
    */
    public static function GetAdapters(){
        if(self::$sm_regAdapter == null){
            self::$sm_regAdapter=array();
            self::LoadAdapter();
        }
        return self::$sm_regAdapter;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getApp(){
        return igk_app();
    }
    ///<summary>get the db identifier</summary>
    /**
    * get the db identifier
    */
    public function getDbIdentifier(){
        return "db";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsAvailable(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initForInitDb(){}

	///<summary>insert </summary>
    public function insert($table, $entries){
        igk_ilog(__CLASS__. " - [warning] ::::must override insert");
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function Load(){
        if(defined("IGK_INIT"))
            return;
        self::LoadAdapter();
    }
    ///<summary></summary>
    /**
    * 
    */
    private static function LoadAdapter(){
        $fc=igk_io_syspath(IGK_ADAPTER_CACHE);
        $n="/^IGK(?P<name>(.)+)DataAdapter$/i";
        if(empty($fc)){
            return false;
        }
        if(file_exists($fc)){
            foreach(explode(IGK_LF, igk_io_read_allfile($fc)) as $k){
                if(empty(trim($k)))
                    continue;
                $key=strtoupper($k);
                if(preg_match_all($n, $key, $tab)){
                    $key=$tab["name"][0];
                }
                if (class_exists($k, false)){
                    self::$sm_regAdapter[$key]=new $k();
                    self::$sm_regAdapter[$key]->m_name = $key;
                }
                    
            }
        }
        else{
            self::$sm_regAdapter=array();
            $m="";
            foreach(get_declared_classes() as $k=>$v){
                if(preg_match($n, $v)){
                    $t=array();
                    preg_match_all($n, $v, $t);
                    $s=$t["name"][0];
                    if(!igk_reflection_class_isabstract($v) && igk_reflection_class_extends($v, "IGKDataAdapter")){
                        self::$sm_regAdapter[strtoupper($s)
                        ]=new $v();
                        $m .= $v.IGK_LF;
                    }
                }
            }
            igk_io_w2file($fc, $m);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function makeCurrent(){
        IGKDBQueryDriver::$Config["db"]=$this->getDbIdentifier();
    }
    ///<summary> override to manage the open connexion counter</summary>
    /**
    *  override to manage the open connexion counter
    */
    public function OpenCount(){
        return 0;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function ResetDataAdapter(){
        self::$sm_regAdapter=array();
        self::LoadAdapter();
    }
    ///<summary></summary>
    ///<param name="tablename">tablename</param>
    ///<param name="condition">condition for select</param>
    ///<param name="options">options</param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $condition
    * @param mixed $options
    */
    public function select($tablename, $condition=null, $options=null){}
    ///<summary></summary>
    ///<param name="tbname"></param>
    /**
    * 
    * @param mixed $tbname
    */
    public function selectAll($tbname){
        return null;
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="conditions" default="null"></param>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $conditions the default value is null
    * @param mixed $options the default value is null
    */
    public function selectAndWhere($tablename, $conditions=null, $options=null){
        igk_die("function ".__FUNCTION__." not implements");
    }
    ///<summary></summary>
    ///<param name="dbname"></param>
    /**
    * 
    * @param mixed $dbname
    */
    public function selectdb($dbname){}
    ///<summary></summary>
    /**
    * 
    */
    public function selectLastId(){
        return null;
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="entrie"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $entrie
    */
    public function update($tablename, $entries, $condition=null){
        return false;
    }
}