<?php

namespace IGK\System\Html\Dom; 

use IGKHtmlItem;

///<summary>Represente class: IGKHtmlMeta</summary>
/**
* Represente IGKHtmlMeta class
*/
class IGKHtmlMeta extends IGKHtmlItem  {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("meta");
    }
    ///<summary></summary>
    ///<param name="item"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $item
    * @param mixed $index the default value is null
    */
    protected function _AddChild($item, $index=null){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function serialize(){
        return "{'a>>>>>>>>':'b>>>>>>>>>>>>}";
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setContent($v){
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function unserialize($v){}
}