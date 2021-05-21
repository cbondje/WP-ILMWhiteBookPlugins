<?php

use function igk_resources_gets as __; 

///<summary>represent a igk not implement exception</summary>
/**
* represent a igk not implement exception
*/
class IGKNotImplementException extends IGKException{
    ///<summary></summary>
    ///<param name="func"></param>
    /**
    * 
    * @param mixed $func
    */
    public function __construct($func){
        parent::__construct(__("Not Implement [0]", $func));
    }
}