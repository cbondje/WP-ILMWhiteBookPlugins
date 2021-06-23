<?php
// @author: C.A.D BONDJE DOUE
// @file: ConfigHandler.php
// @desc: 
// @date: 20210517 11:08:31
namespace ILYEUM;

use ArrayAccess;
use Iterator;
use function ilm_getv as getv;

/**
 * configuration handler
 * @package ILYEUM
 */
class ConfigHandler implements Iterator, ArrayAccess{
    private $m_items;
    private $_it;
    public function __construct(array $item)
    {
        $this->m_items = $item;
    }
    public function __empty($i){ 
        return empty($this->m_items);
    }
    public function __isset($i){        
        return isset($this->m_items[$i]);
    }
    public function offsetExists($offset) {
        return key_exists($offset, $this->m_items);
     }

    public function offsetGet($offset) { 
        if ($this->offsetExists($offset))
            return $this->m_items[$offset];
        return null;
    }

    public function offsetSet($offset, $value) { 
        $this->m_items[$offset] = $value;
    }

    public function offsetUnset($offset) { 
        unset($this->m_items[$offset]);
    }

    public function current() { 
        if ($this->_it){
            $t= $this->m_items[$this->_it->keys[$this->_it->index]];
            // convert child to ConfigHandler
            // ------------------------------
            if (get_class($t) !== __CLASS__){
                $t = new ConfigHandler((array)$t);
                $this->m_items[$this->_it->keys[$this->_it->index]] = $t;
            }
            return $t;
        }
    }

    public function next() { 
        if ($this->_it){
            $this->_it->index ++;            
        }
    }

    public function key() { 
        if ($this->_it){
            return $this->_it->keys[$this->_it->index];
        }
    }

    public function valid() { 
        if ($this->_it){
            return ($this->_it->index>=0) && ($this->_it->index < count($this->_it->keys));
        }
    }

    public function rewind() {         
        $this->_it = (object)["keys"=>array_keys($this->m_items), "index"=>0];
     }
    public function __get($n){
        return getv($this->m_items, $n);
    }
    public function __set($n, $v){
        $this->m_items[$n] = $v;
    }
    public function toArray(){
        return $this->m_items;
    } 
    public function get($n, $default=null){
        return getv($this->m_items, $n, $default);
    }
}