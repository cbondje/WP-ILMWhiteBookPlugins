<?php
// @author: C.A.D BONDJE DOUE
// @file: database/QueryRow.php
// @desc: 
// @date: 20210517 13:05:19
namespace ILYEUM\database;

use \ArrayAccess;
use ILYEUM\Utility;

class QueryRow implements ArrayAccess{
    private $_entries;
    public function toArray(){
        return $this->_entries;
    }
    public function offsetExists($offset) {
        return key_exists($offset, $this->_entries);
     }

    public function offsetGet($offset) {
        return $this->$offset;
     }

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
     }

    public function offsetUnset($offset) { 

    }
    public static function Create($cols, $row){        
        $tab = array_fill_keys($keys=array_keys($cols), null);
        $r = new static;
        foreach($keys as $k){
            $tab[$k]= $row[$k];
        }
        $r->_entries = $tab;
        return $r;
    }
    public function __get($n){
        return ilm_getv($this->_entries, $n);
    }
    public function set($n, $v){ 
        $this->m_items[$n] = $v;
    }
    public function to_json(){
        return Utility::To_JSON($this->_entries);
    }
}