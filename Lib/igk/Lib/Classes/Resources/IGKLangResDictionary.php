<?php

namespace IGK\Resources;

use ArrayAccess; 

///<summary> use for key's language operation</summary>
/**
*  use for key's language operation
*/
final class IGKLangResDictionary implements ArrayAccess{
    private $_f;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){}
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function OffsetExists($i){
        return isset($this->_f[strtolower($i)]);
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function offsetGet($i){
        return $this->_f[strtolower($i)];
    }
    ///<summary></summary>
    ///<param name="i"></param>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $i
    * @param mixed $v
    */
    public function offsetSet($i, $v){
        $this->_f[strtolower($i)]=$v;
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function offsetUnset($i){
        unset($this->_f[strtolower($i)]);
    }
    ///<summary> get sorted keys</summary>
    /**
    *  get sorted keys
    */
    public function sortKeys(){
        if(($this->_f == null) || (igk_count($this->_f) == 0)){
            return false;
        }
        $keys=array_keys($this->_f);
        igk_usort($keys, "igk_key_sort");
        return $keys;
    }
}