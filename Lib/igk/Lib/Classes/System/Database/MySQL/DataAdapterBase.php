<?php


namespace IGK\System\Database\MySQL;

use Exception;
use IGKDBQueryDriver;
use IGKException;
use IGKMySQLDataCtrl;
use IGKNotImplementException;
use IGKSQLDataAdapter;
use IGKSQLQueryUtils;

///<summary>Represente class: DataAdapterBase</summary>
/**
* Represente DataAdapterBase class
*/
abstract class DataAdapterBase extends IGKSQLDataAdapter{
    private $m_controller;
    private $m_dbname;
    private $m_error;
    private $m_errormsg;
    private $m_time;
    private static $sm_emptyResult;
    protected $m_dbManager;

    

    ///<summary></summary>
    ///<param name="ctrl" default="null"></param>
    /**
    * 
    * @param mixed $ctrl the default value is null
    */
    public function __construct($ctrl=null){
        $this->m_controller=$ctrl;
        $this->m_dbManager=$this->__createManager();
        if($this->m_dbManager == null){
            if(defined('IGK_DEBUG')){
                throw new Exception("/!\\ Manager not created.");
            }
            else{
                igk_ilog(__METHOD__."::".__LINE__, "/!\\ Failed to create database manager.");
            }
            igk_die("failed to create MySQL database manager. msqli or mysql not present. please install it");
        }
        else{
            $this->m_dbManager->setCloseCallback(array($this, 'closeCallback'));
            $this->m_dbManager->setOpenCallback(array($this, 'openCallback'));
        }
        if($this->connect()){
            register_shutdown_function(function(){
                $c = $this->OpenCount();
                while($this->OpenCount()>0){
                    $this->close();
                    if ($c == $this->OpenCount()){
                        new IGKException("failed to close connection");
                    }
                }  
            });
        }
    }

    ///<summary></summary>
    /**
    * 
    */
    protected function __createManager(){}
    ///<summary></summary>
    /**
    * 
    */
    public function beginTransaction(){
        $this->sendQuery("START TRANSACTION");
    }
    ///<summary></summary>
    ///<param name="leaveOpen" default="false"></param>
    /**
    * 
    * @param mixed $leaveOpen the default value is false
    */
    public function close($leaveOpen=false){
        if($this->m_dbManager != null){
            $this->m_dbManager->close($leaveOpen);
            if($this->m_dbManager->OpenCount()<=0){
                $this->m_dbname=null;
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function closeAll(){
        if($this->m_dbManager){
            $this->m_dbManager->closeAll();
        }
        $this->m_dbname=null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function closeCallback(){
        $this->m_dbname=null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function commit(){
        $this->sendQuery("COMMIT");
    }
    ///<summary></summary>
    ///<param name="array"></param>
    /**
    * 
    * @param mixed $array
    */
    public function configure($array){
        $this->m_dbManager->configure($array);
    }
    ///<summary></summary>
    ///<param name="dbnamemix" default="null"></param>
    ///<param name="selectdb" default="true"></param>
    /**
    * 
    * @param mixed $dbnamemix the default value is null
    * @param mixed $selectdb the default value is true
    */
    public function connect($dbnamemix=null, $selectdb=true){    
        $this->makeCurrent();        
        if(($this->m_dbManager == null) || (!$this->m_dbManager->connect())){
            igk_ilog_assert(!igk_sys_env_production(), $this->m_dbManager ? "can't connect with DBManager: ".get_class($this->m_dbManager): "dbManager is null");
            return false;
        }
        $dbs=igk_get_env("sys://Db/NODBSELECT");
        $dbname = $this->m_dbname;
        if(is_string($dbnamemix))
            $dbname=$dbnamemix;

        if(!$dbs && $selectdb){
            $dbname=$dbname == null ? $this->app->Configs->db_name: $dbname;
            if(!$this->selectdb($dbname)){
                $this->close();
                return false;
            }
            $this->m_dbname=$dbname;
        }
        return true;
    }
    ///<summary></summary>
    ///<param name="dbserver"></param>
    ///<param name="dbname"></param>
    ///<param name="dbuser"></param>
    ///<param name="dbpwd"></param>
    /**
    * 
    * @param mixed $dbserver
    * @param mixed $dbname
    * @param mixed $dbuser
    * @param mixed $dbpwd
    */
    public function connectTo($dbserver, $dbname, $dbuser, $dbpwd){
        return $this->m_dbManager->connectTo($dbserver, $dbname, $dbuser, $dbpwd);
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="whereTab" default="null"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $whereTab the default value is null
    */
    public function selectCount($tbname, $where=null, $options=null){
        if (!$options)
            $options = [];

        $options["Columns"] = [
            "Count(*) as count"
        ];
        $query = $this->getGrammar()->createSelectQuery($tbname, $where, $options);
        // $o="";
        // $s=0;
        // $flag = "";
        // $extra = null;
        // if ($options){
        //     $extra = IGKSQLQueryUtils::GetExtraOptions($options, $this);
        //     $flag = igk_getv($extra, "flag");
        // }               
        // $q="SELECT ";
        // if (!empty($flag))
        //     $q.=$flag;
        // $q.= "Count(*) as count FROM `".igk_mysql_db_tbname($tbname)."`";
       
        // if ($extra && ($joints = igk_getv($extra, "join"))){            
        //     $q.= $joints.PHP_EOL;            
        // }

        // if(is_array($whereTab) && igk_count($whereTab) > 0){
        //     $q .= " WHERE ".IGKSQLQueryUtils::GetCondString($whereTab);
        // }
        // else{
        //     if(is_string($whereTab)){
        //         $q .= " WHERE ".igk_db_escape_string($whereTab);
        //     }
        // } 
        // $q .= ";";
        try {
            $g=$this->sendQuery($query, false);
            return $g;
        }
        catch(Exception $ex){
            igk_ilog("Exception: ".$ex->getMessage());
        }
        return 0;
    }
    ///<summary></summary>
    ///<param name="result" default="false"></param>
    /**
    * 
    * @param mixed $result the default value is false
    */
    public function CreateEmptyResult($result=false){
        return IGKMySQLQueryResult::CreateResult($result);
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="entry"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $conditions
    * @return mixed
    */
    public function delete($tablename, $conditions=null){
        $r = null;
        if($this->m_dbManager != null){
            $r = $this->m_dbManager->delete($tablename, $conditions);
        }
        return $r;
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    /**
    * 
    * @param mixed $tablename
    * @return mixed
    */
    public function deleteAll($tablename, $condition=null){
        $r = null;
        if($this->m_dbManager != null)
            $r= $this->m_dbManager->deleteAll($tablename, $condition);
        return $r;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function dropAllRelations(){
        return IGKMySQLDataCtrl::DropAllRelations($this, $this->m_dbname);
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    /**
    * 
    * @param mixed $tbname
    */
    public function dropTable($tbname){
        if($this->m_dbManager != null)
            return IGKMySQLDataCtrl::DropTable($this, $tbname, $this->DbName);
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function flushForInitDb($complete=null){
        if($this->m_dbManager)
            $this->m_dbManager->flushForInitDb($complete);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAllRelations(){
        return IGKMySQLDataCtrl::GetAllRelations($this, $this->m_dbname);
    }
    ///<summary></summary>
    ///<param name="s"></param>
    /**
    * 
    * @param mixed $s
    */
    public function getConstraint_Index($s){
        if($this->m_dbManager != null)
            return IGKMySQLDataCtrl::GetConstraint_Index($this, $s, $this->DbName);
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDbName(){
        return $this->m_dbname;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getError(){
        return $this->m_error;
    }
    ///<summary></summary>
    ///<param name="type"></param>
    /**
    * 
    * @param mixed $type
    */
    public function getFormat($type){
        switch(strtolower($type)){
            case 'time':
            return IGK_MYSQL_TIME_FORMAT;
            case 'datetime':
            return IGK_MYSQL_DATETIME_FORMAT;
            case 'date':
            return IGK_MYSQL_DATE_FORMAT;
        }
        return "";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsAvailable(){
        return ($this->m_dbManager != null);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsConnect(){
        return $this->m_dbManager->getIsConnect();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getLastQuery(){
        return $this->m_dbManager->getLastQuery();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getResId(){
        return IGKDBQueryDriver::GetResId();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getStored(){
        return $this->m_dbManager ? $this->m_dbManager->getStored(): null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getStoredRequired(){
        return $this->m_dbManager ? $this->m_dbManager->getStoredRequired(): null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTabInitInfo(){
        return $this->m_dbManager->getTabInitInfo();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTime(){
        $this->m_time=new IGKMySQLTimeManager($this);
        return $this->m_time;
    }
     
    
    ///<summary></summary>
    /**
    * 
    */
    public function initForInitDb(){
        if($this->m_dbManager)
            $this->m_dbManager->initForInitDb();
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="callback"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $callback
    */
    public function initSystablePushInitItem($tablename, $callback){
        return $this->m_dbManager && $this->m_dbManager->initSystablePushInitItem($tablename, $callback);
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    /**
    * 
    * @param mixed $tablename
    */
    public function initSystableRequired($tablename){
        return $this->m_dbManager && $this->m_dbManager->initSystableRequired($tablename);
    }
    ///<summary></summary>
    ///<param name="tbN"></param>
    /**
    * 
    * @param mixed $tbN
    */
    public function IsStoredTable($tbN){
        $g=$this->getStored();
        return isset($g[$tbN]);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function last_id(){
        return $this->m_dbManager->lastId();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function listTables(){
        return $this->getGrammar()->listTables(); 
    }
    ///<summary></summary>
    /**
    * 
    */
    public function openCallback(){
        igk_log_write_i(__CLASS__, "open connection");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function OpenCount(){
        if($this->m_dbManager)
            return $this->m_dbManager->OpenCount();
        return 0;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function Reset(){
        if($this->m_dbManager != null)
            $this->m_dbManager->closeAll();
        $this->m_dbManager=$this->__createManager() ?? igk_die("failed to recreate db connection");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function rollback(){
        $this->sendQuery("ROLLBACK");
    }
    ///<summary></summary>
    ///<param name="dbname"></param>
    /**
    * 
    * @param mixed $dbname
    */
    public function selectdb($dbname){
        if(($this->m_dbManager != null) && !empty($dbname)){
            $r=$this->m_dbManager->selectdb($dbname);
            if($r){
                $this->m_dbname=$dbname;
            }
            else{
                if(!igk_sys_env_production()){
                    igk_ilog(["can't select database \"{$dbname}\". Database not found.", __FILE__.":".__LINE__]);
                }
            }
            return $r;
        }
        return false;
    }
    ///<summary></summary>
    /**
    * 
    * @return mixed
    */
    public function selectLastId(){
        $r = null;
        if($this->m_dbManager != null)
            $r= $this->m_dbManager->selectLastId();
        return $r;
    }
    ///<summary></summary>
    ///<param name="d"></param>
    /**
    * 
    * @param mixed $d
    */
    public function setForeignKeyCheck($d){
        if(is_integer($d))
            $this->sendQuery("SET foreign_key_checks=".igk_db_escape_string($d).";");
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    protected function setLastQuery($v){
        throw new IGKNotImplementException(__FUNCTION__);
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="entries" default="null"></param>
    ///<param name="where" default="null"></param>
    ///<param name="querytabinfo" default="null"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $entries the default value is null
    * @param mixed $where the default value is null
    * @param mixed $querytabinfo the default value is null
    */
    public function update($tbname, $entries, $where=null, $querytabinfo=null){
        if(($entries == null) || ($this->m_dbManager == null)){
           
            return false;
        }
        return $this->m_dbManager->update($tbname, $entries, $where, $querytabinfo);
    }
}