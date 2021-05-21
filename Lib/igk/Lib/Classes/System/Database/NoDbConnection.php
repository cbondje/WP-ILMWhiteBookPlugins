<?php

namespace IGK\System\Database;

///<summary>Represente class: IGKNoDbConnection</summary>
/**
* Represente IGKNoDbConnection class
*/
class NoDbConnection{
    ///<summary></summary>
    /**
    * 
    */
    function close(){}
    ///<summary></summary>
    /**
    * 
    */
    function closeAll(){}
    ///<summary></summary>
    /**
    * 
    */
    function connect(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    function initForInitDb(){}
    ///<summary></summary>
    /**
    * 
    */
    function insert(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    function OpenCount(){
        return -1;
    }
    ///<summary></summary>
    ///<param name="query"></param>
    /**
    * 
    * @param mixed $query
    */
    function sendQuery($query){
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    function setCloseCallback(){}
    ///<summary></summary>
    /**
    * 
    */
    function setOpenCallback(){}
    /** */
    function flushForInitDb(){}

    public function __call($n, $name){
        return null;
    }
    public static function __callStatic($name, $arguments)
    { 
        return null;        
    }
}
