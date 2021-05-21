<?php

use IGK\Helper\Utility;

class IGKQueryRowObj implements ArrayAccess, Iterator{
	private $m_rows;
	private $it_current;
	private $it_keys;
	private $it_key;
    private function __construct(){}
    public function __toString(){
        return "[".__CLASS__."]";
    }
	public function __debugInfo()
	{
		return $this->m_rows; //["m_rows"=>$this->m_rows];
	}
    public function to_json(){
        return Utility::To_JSON($this->m_rows, null);
    }
 
	public static function Create($tab){
		if (!$tab || !is_array($tab))
			return null;
		$g = new IGKQueryRowObj();
		$g->m_rows = $tab;
		return $g;
	}
	public function toArray($filter=false){
		$tab = $this->m_rows;
		if ($filter){
			$tab = array_filter($tab, function($k, $m){
				if (strpos($m, ":") === false){
					return 1;
				}
				return 0;
			},  ARRAY_FILTER_USE_BOTH  );
		}
		return $tab;
	}
	public function OffsetExists($i){ 
		return isset($this->m_rows[$i]);
	}
	public function OffsetSet($i, $v){
		$this->m_rows[$i] = $v;
	}
	public function OffsetGet($i){
		if ($this->OffsetExists($i)){
			return $this->m_rows[$i];
		}
		return null;
	}
	public function OffsetUnset($i){
		 unset( $this->m_rows[$i]);
	}
	public function __isset($i){ 
		return $this->OffsetExists($i);
	}
	public function __get($i){ 
		return $this[$i];
	}
	public function __set($i,$v){
		$this[$i] = $v;
	}
    public function __unset($n){
        $this->OffsetUnset($n);
    }

	public function current (){
		return $this->it_current;
	}
	public function key (){
		return $this->it_keys[$this->it_key];
	}
	public function next (){
		$this->it_key++;
		if (isset($this->it_keys[$this->it_key])){
			$s =  $this->it_keys[$this->it_key];
			$this->it_current = $this[$s];
		}else
			$this->it_current = null;
	}
	public function rewind (){
		$this->it_keys = array_keys($this->m_rows);
		$this->it_key = 0;
		$s =  $this->it_keys[$this->it_key];
		$this->it_current = $this[$s];
	}
	public function valid (){
		return $this->it_key < count($this->it_keys);
	}
   
}
