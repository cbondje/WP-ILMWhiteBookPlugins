<?php
use function igk_resources_gets as __;

///<summary>class used to manage database for a controller</summary>
/**
* class used to manage database for a controller
*/
class IGKDbUtility extends IGKObject implements IIGKDbUtility {
    ///note : it used clId as id by default if you don't want to used clId by default for row identification
    private $m_Ctrl;
    private $m_ad;
    private $m_errorcode;
    private $m_errorstr;
    // public function __debugInfo(){
    //     return [];
    // }
    protected function getHashKey($v){    
        if (is_bool($v)){
            return $v? "1" :"0";
        }
        if (is_object($v)){
            if (method_exists($v, "get_cachekey")){
                return $v->get_cachekey();
            }
            throw new Exception("Object not implement get_cachekey");
        }
        return $v;
    }
    protected function getCacheKey($n){
        if (is_array($n)){
            $o = [];
            foreach($n as $v){
                array_push($o, $this->getHashKey($n));
            }
            $n = implode("-", $n);
        }
        return $n;
    }
    public function cache($name, callable $callback){
        $key = "dbCache://".$this->getCacheKey($name);
        $args = array_slice(func_get_args(),2);
        if ($r = igk_environment()->get($key)){
            return $r;
        }
        $r =  call_user_func_array($callback, $args); 
        if ($r){
            igk_environment()->set($key, $r);
        }
        return $r;
    }
    public function last_error(){
        return $this->Ad->getError();
    }
    public function select_row($table, $conditions){
        return $this->selectSingleRow($table, $conditions);
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="arguments"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $arguments
    */
    public function __call($name, $arguments){
        if(preg_match("/Callback$/i", $name)){
            $fc=igk_getv($arguments, 0);
            $n=substr($name, 0, strlen($name)-8);
            if(!empty($n) && (strtolower($n) != "callback")){
                $s=call_user_func_array(array($this, $n), array_slice($arguments, 1));
                if($s !== null){
                    $fc($s);
                }
                return $s;
            }
        }
        $this->close(); 
        $msg="/!\\ DBUtility[".get_class($this)."] Action {$name} not implements";
        igk_ilog($msg, __CLASS__);
        igk_notifyctrl()->addError($msg);
        igk_assert_die(igk_server_is_local(), $msg);
        return null;
    }
    public function selectdb($dbname){
        if ($this->m_ad)
            return $this->m_ad->selectdb($dbname);
        return false;
    }
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    public function __construct($ctrl){
        if($ctrl == null){
            igk_die("variable ctrl can't be null");
        }
        $this->m_Ctrl=$ctrl;
        if($this->connect()){
            register_shutdown_function(function(){
                $this->close();
            });
        }
        else{
          //igk_wln_e( $this->getAd());
          // igk_die(__("Failed to select {0}'s databases", __CLASS__));
        }
    }
    ///<summary>get table prefix</summary>
    /**
    * get table prefix
    */
    protected function _getTablePrefix(){
        return "";
    }
	public function getTableName($table){
		return igk_db_get_table_name($table);
	}
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $value
    */
    protected function _syncValue($table, $value){
        $v_=$value;
        if(preg_match_all("#^@:/(?P<value>(.)+)$#", $value, $tab)){
            $v_=igk_getv($tab["value"], 0);
        }
        return $v_;
    }
    ///<summary>table fixture </summary>
    /**
    * table fixture
    */
    protected function _table($table){
        if(empty($table=trim($table))){
            igk_die("table name is empty");
        }
        return $this->_getTablePrefix().$table;
    }
    ///<summary></summary>
    ///<param name="callback"></param>
    /**
    * 
    * @param mixed $callback
    */
    public function adCallback(callable $callback){
        if(!$this->connect())
            return;
        $o=$callback($this);
        $this->close();
        return $o;
    }
    ///<summary>add direct object for table name</summary>
    /**
    * add direct object for table name
    */
    public function addObject($tablen, $mixed){
        $r=igk_db_create_row($tablen, $mixed);
        return $this->insertIfNotExists($tablen, $r);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function beginTransaction(){
        return $this->m_ad->beginTransaction();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function close(){
        if($this->m_ad){
            $this->m_ad->close();
            if($this->m_ad->OpenCount()<=0){
                $this->m_ad=null;
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function commit(){
        return $this->m_ad->commit();
    }
    ///<summary>connect adapter</summary>
    /**
    * connect adapter
    */
    public function connect(){
        if(!$this->m_ad)
            $this->m_ad=$this->initDataAdapter();

        return $this->m_ad && $this->m_ad->connect($this->m_Ctrl);
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="id" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $id the default value is null
    */
    public function delete($table, $id=null){
        if(is_numeric($id)){
            return igk_db_delete($this->m_Ctrl, $table, array(IGK_FD_ID=>$id));
        }
        return igk_db_delete($this->m_Ctrl, $table, $id);
    }
    ///<summary></summary>
    ///<param name="table"></param>
    /**
    * 
    * @param mixed $table
    */
    public final function dropTable($table){
        $this->connect();
        $r=null;
        if($this->m_ad){
            $r=$this->m_ad->dropTable($table);
        }
        $this->close();
        return $r;
    }
    ///<summary></summary>
    ///<param name="g"></param>
    /**
    * 
    * @param mixed $g
    */
    public function endTransaction($g){
        if($g){
            $this->m_ad->commit();
        }
        else{
            $this->m_ad->rollback();
        }
    }
    ///<summary>get the data adapter</summary>
    /**
    * get the data adapter
    */
    public final function getAd(){
        return $this->m_ad;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    /**
    * 
    * @param mixed $table
    */
    public function getCanSyncDataTable($table){
        return true;
    }
    ///<summary> get configs db function</summary>
    /**
    *  get configs db function
    */
    public function getConfigv($n, $default=null, $table=null, $comment=null){
        return igk_db_get_config($n, $default, $comment, 0);
    }
    ///<summary>get controller</summary>
    /**
    * get controller
    */
    public function getCtrl(){
        return $this->m_Ctrl;
    }
    ///END sync functions
    /**
    */
    public function getErrorCode(){
        return $this->m_errorcode;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getErrorString(){
        return $this->m_errorstr;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="condition"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $condition
    */
    public function getID($table, $condition){
        $r=$this->select($table, $condition)->getRowAtIndex(0);
        if($r)
            return $r->clId;
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getLastQuery(){
        $ad=$this->Ad;
        return $ad ? $ad->getLastQuery(): -1;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="condition"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $condition
    */
    public function getRow($table, $condition){
        $r=$this->select($table, $condition)->getRowAtIndex(0);
        return $r;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="id"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $id
    */
    public function getRowById($table, $id){
        return $this->select($table, array(IGK_FD_ID=>$id))->getRowAtIndex(0);
    }
    ///get default condition id
    ///<summary>return a sync data id</summary>
    /**
    * return a sync data id
    */
    public function getSyncDataID($table, $value, $properties=null){
        if($table == igk_db_get_table_name(IGK_TB_USERS)){
            if("+@id:/".$properties->User->clLogin === $value){
                return $properties->User->clId;
            }
            return $value;
        }
        $tab=array();
        $v_=$this->_syncValue($table, $value);
        if(!empty($v_)){
            $tb_row=igk_getv($properties->Rows[$table], $v_);
            $c=$this->selectSingleRow($table, $tb_row["row"]);
            if($c){
                return $c->clId;
            }
            if($this->connect()){
                $id=null;
                if($this->insertIfNotExists($table, $tb_row["row"])){
                    $id=$this->last_id();
                    unset($properties->Entries[$table][$tb_row["index"]]);
                }
                else{
                    igk_ilog("Failed to insert value : ".$this->Ad->getLastQuery(), __FUNCTION__);
                }
                $this->close();
                return $id;
            }
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="valueInTable"></param>
    ///<param name="info" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $valueInTable
    * @param mixed $info the default value is null
    */
    public function getSyncDataValueDisplay($table, $valueInTable, $info=null){
        $row=$this->selectSingleRow($table, $valueInTable);
        if(!$row){
            return "[row is null ". $valueInTable."]";
        }
        if (($table == igk_db_get_table_name(IGK_TB_USERS)) && $row){
            return "+@id:/".$row->clLogin;
        }
        return "@:/".$this->getSyncIdentificationId($table, $row);
    }
    ///SYNC functions
    ///override
    /**
    */
    public function getSyncIdentificationId($table, $syncrow){
        $r=igk_getv($syncrow, "clId");
        if($r){
            return $r;
        }
        return igk_getv($syncrow, "clName");
    }
    ///<summary></summary>
    ///<param name="id"></param>
    /**
    * 
    * @param mixed $id
    */
    public function getSystemUserById($id){
        return igk_get_user($id); 
    }
    ///<summary>get user id</summary>
    /**
    * get user id
    */
    public function getUID(){
        $u=$this->m_Ctrl->User;
        return $u ? $u->clId: 0;
    }
    ///<summary>get user by id</summary>
    /**
    * get user by id
    */
    public function getUser($uid){
        return igk_get_user($uid);
    }
    ///<summary></summary>
    ///<param name="uid" default="null"></param>
    /**
    * 
    * @param mixed $uid the default value is null
    */
    public function getUserId($uid=null){
        if(($uid == null) && ($u=$this->m_Ctrl->getUser())){
            $uid=$u->clId;
        }
        return $uid;
    }
    ///<summary>initialize data adapter</summary>
    /**
    * initialize data adapter
    */
    protected function initDataAdapter(){
        return igk_get_data_adapter($this->m_Ctrl);
    }
  
    ///<summary>insert data</summary>
    ///<param name="table">table where to insert</param>
    ///<param name="obj">object to insert</param>
    /**
    * 
    * @param mixed $table
    * @param mixed $obj
    */
    public function insert($table, $obj){
        $table=$this->_table($table);
        if($this->m_ad){
            return $this->m_ad->insert($table, $obj);
        }
        else
            return igk_db_insert($this->m_Ctrl, $table, $obj);
    }
	///<summary>insert array in items by building as semi-column separated query</summary>
	public function insert_array($tbname, $values, $throwex=true){
		 $tbname=$this->_table($tbname);
        if($this->m_ad){
            return $this->m_ad->insert_array($tbname, $values, $throwex);
        }
	}
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="obj"></param>
    ///<param name="id" default="'clId'"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $obj
    * @param mixed $id the default value is 'clId'
    */
    public function insertAndUpdate($table, $obj, $id='clId'){
        if($this->insert($table, $obj)){
            $obj->$id=$this->last_id();
            return 1;
        }
        return 0;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="obj"></param>
    ///<param name="leaveOpen" default="false"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $obj
    * @param mixed $leaveOpen the default value is false
    */
    public function insertIfNotExists($table, $obj, $leaveOpen=false){
        return igk_db_insert_if_not_exists($this->m_Ctrl, $table, $obj, null, null, $leaveOpen, "Or");
    }
    ///<summary>insert or update $obj</summary>
    /**
    * insert or update $obj
    */
    public function insertOrUpdate($table, $condition, $obj, callable $callback=null){
        $_invoke=function($r) use ($table, $condition, $obj, $callback){
            if($r->RowCount == 1){
                $row=$r->getRowAtIndex(0);
                if(is_callable($callback)){
                    if(!$callback($row, $obj))
                        return false;
                }
                $obj->clId=$row->clId;
                if($this->update($table, $obj, $condition)){
                    return 2;
                }
            }
            
            return igk_die("not implement ".$r->RowCount);
            
        };
        if($condition == null){
            $tab=null;
            if(igk_db_data_is_present($this->Ctrl, $table, $obj, null, $tab)){
                return $_invoke($tab);
            }
            if($this->insert($table, $obj))
                return 1;
        }
        else{
            $r=$this->select($table, $condition);
            if($r->RowCount > 0){
                return $_invoke($r);
            }
            else{
                if($this->insert($table, $obj))
                    return 1;
            }
        }
        return 0;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function last_id(){
        $ad=$this->Ad;
        return $ad ? $ad->last_id(): -1;
    }
    ///<summary></summary>
    ///<param name="tab"></param>
    ///<param name="callback" default="null"></param>
    ///<param name="tablen" default="products"></param>
    /**
    * 
    * @param mixed $tab
    * @param mixed $callback the default value is null
    * @param mixed $tablen the default value is "products"
    */
    public function loadCsvEntries($tab, $callback=null, $tablen="products"){
        $row=igk_db_create_row($tablen);
        if(!$row)
            return 0;
        return $this->adCallback(function($ad) use ($tab, $callback, $tablen, $row){
            $error=0;
            if($callback){
                foreach($tab as$v){
                    $error=!$callback($v, $tablen, $row, $ad) && !$error;
                }
            }
            else{
                foreach($tab as  $v){
                    $row->clName=igk_getv($v, 1);
                    $error=!$ad->insert($tablen, $row) && !$error;
                }
            }
            return !$error;
        });
    }
    public function model($modeltype, $name=null){
        return $this->Ctrl->loader->model($modeltype, $name);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function rollback(){
        return $this->m_ad->rollback();
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $condition the default value is null
    * @param mixed $options the default value is null
    */
    public function select($table, $condition=null, $options=null){
        $table=$this->_table($table);
        if($this->m_ad){
            return $this->m_ad->select($table, $condition, $options);
        }
        igk_die("/!\ no adapter created. tips. call connect function first ");
        return igk_db_table_select_where($table, $condition, $this->m_Ctrl, false, $options);
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="callback" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $condition the default value is null
    * @param mixed $callback the default value is null
    */
    public function selectCallback($table, $condition=null, $callback=null){
        $options=array("callback"=>$callback);
        $r=$this->select($table, $condition, $options);
        return $r;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $condition the default value is null
    * @param mixed $options the default value is null
    */
    public function selectFirstRow($table, $condition=null, $options=null){
        $r=$this->select($table, $condition, $options);
        if($r && $r->RowCount > 0)
            return $r->getRowAtIndex(0);
        return null;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $condition the default value is null
    * @param mixed $options the default value is null
    */
    public function selectLastRow($table, $condition=null, $options=null){
        $r=$this->select($table, $condition, $options);
        if($r && $r->RowCount > 0)
            return $r->getRowAtIndex($r->RowCount-1);
        return null;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $condition the default value is null
    * @param mixed $options the default value is null
    */
    public function selectSingleRow($table, $condition=null, $options=null){
        $r=$this->select($table, $condition, $options);
        if($r && $r->RowCount == 1){
            $g=$r->getRowAtIndex(0);
            $g->{"sys:table"}=$table;
            return $g;
        }
        return null;
    }
    ///send query string
    /**
    */
    public final function sendQuery($querystring){
        $this->connect(); 
        $r=null;
        if($this->m_ad){
            $r=$this->m_ad->sendQuery($querystring);
        }
        $this->close();
        return $r;
    }
    ///<summary> set the data adapter</summary>
    ///<remark>from connect set the dataapter to change it</remark>
    /**
    *  set the data adapter
    */
    protected final function setAd($ad){
        $this->m_ad=$ad;
    }
    ///<summary></summary>
    ///<param name="code"></param>
    /**
    * 
    * @param mixed $code
    */
    protected function setErrorCode($code){
        $this->m_errorcode=$code;
    }
    ///<summary></summary>
    ///<param name="s"></param>
    /**
    * 
    * @param mixed $s
    */
    protected function setErrorString($s){
        $this->m_errorstr=$s;
    }
    ///<summary></summary>
    ///<param name="table"></param>
    /**
    * 
    * @param mixed $table
    */
    public final function tableExists($table){
        return $this->getAd()->tableExists($table); 
    }
   
    
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="entrie"></param>
    ///<param name="condition" default="null"></param>
    ///<param name="tabinfo" default="null"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $entrie
    * @param mixed $condition the default value is null
    * @param mixed $tabinfo the default value is null
    */
    public final function update($table, $entrie, $condition=null, $tabinfo=null){
        $table=$this->_table($table);
        if ( $_ad=$this->getAd()){  
            return $_ad->update($table, $entrie, $condition, $tabinfo);
        }
        $r=null;
        if ($this->connect()){
            $_ad=$this->m_ad;
            if($_ad){            
                $r=$_ad->update($table, $entrie, $condition, $tabinfo);
            }
            $this->close();
        }
        return $r;
    }
	///<summary> Update row table </summary>
	public function update_row($row, $table=null, $condition=null){
		($table == null) && !($table = $this->getTable()) && igk_die(__("table name not define"));
		return $this->update($table, $row, $condition, null);
	}
	public function select_rows($table=null, $condition=null, $options=null){
		($table == null) && !($table = $this->getTable()) && igk_die(__("table name not define"));
		if ($g = $this->select($table, $condition, $options)){
			return $g->getRows();
		}
		return null;
	}
	public function drop($table, $condition=null){
		$this->connect();
        $_ad=$this->Ad;
        if($_ad){ 
            $r=$_ad->drop($table, $condition);
        }
        $this->close();
    }
    public function select_count($condition=null, $table=null){
        if (!($table = $table ?? $this->getTable())){ 
            igk_die("table not found");
        } 
        if ($r = $this->ad->selectCount($table, $condition)->Rows[0]){
            return $r->count;  
        }
        // igk_wln_e("table :::::".$table, $this->ad->getLastQuery());
        return -1;
    }
}