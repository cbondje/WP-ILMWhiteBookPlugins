<?php
// @author: C.A.D BONDJE DOUE
// @file: ConfigHandler.php
// @desc: 
// @date: 20210517 11:08:31
namespace ILYEUM;

/**
 * configuration handler
 * @package ILYEUM
 */
class ConfigHandler{
    private $m_items;
    public function __construct(array $item)
    {
        $this->m_items = $item;
    }
    public function __get($n){
        return ilm_getv($this->m_items, $n);
    }
    public function __set($n, $v){
        $this->m_items[$n] = $v;
    }
}