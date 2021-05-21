<?php

class IGKRawDataBinding implements ArrayAccess {
    private $m_data;
    private function __construct(){}

     public function offsetExists (  $offset ) {
        if (is_object($this->m_data)){
            return property_exists($this->m_data, $offset);
        }
        return array_key_exists($offset, $this->m_data);
    }
     public function offsetGet (  $offset ) {
        return $this->__get($offset);
    }
    public function offsetSet (  $offset ,  $value ) {
        $this->__set($offset, $value);
    }
     public function offsetUnset (  $offset ) {
        if (is_object($this->m_data)){
            unset($this->m_data->$offset);
            return;
        }
        unset($this->m_data[$offset]);
    }

    public static function Create($row){
        
        if (($row == null)|| ((is_array($row)==false) &&  (is_object($row)==false))){
            return null;
        }

        $o = new self();
        $o->m_data = $row;
        return $o;        
    }
    public function __get($n){
        if (igk_environment()->is("DEV") && !$this->offsetExists($n)){   
            igk_die(__FILE__.":".__LINE__." : offset \"$n\" not present");
        }
        return igk_getv($this->m_data, $n);
    }
    public function __set($n, $v){
        $this->m_data[$n] = $v;
    }
    public function __toString(){
        return "[".__CLASS__."]";
    }
}