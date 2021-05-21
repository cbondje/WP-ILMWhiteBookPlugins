<?php


 
///<summary>Represent the base IGK object class </summary>
/**
* Represent the base IGK object class
*/
class IGKObject {
    ///<summary></summary>
    ///<param name="key"></param>
    /**
    * 
    * @param mixed $key
    */
    public function __get($key){
        if(method_exists($this, "get".$key)){ 
            return call_user_func(array($this, "get".$key), array_slice(func_get_args(), 1));
        }
        return null;
    }
    // public function __isset($key){
    //     igk_trace(); 
    //     return method_exists($this, "get".$key);
    // }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $value
    */
    public function __set($name, $value){
        $this->_setIn($name, $value);
    }
    ///<summary>display value</summary>
    /**
    * display value
    */
    public function __toString(){
        return get_class($this);
    }
    ///get object osed to compare
    /**
    */
    public function __wakeup(){
        if(method_exists($this, 'registerHook')){
            $this->registerHook();
        }
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="value" ref="true"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed * $value
    */
    protected function _setIn($name, & $value){
        if(method_exists($this, "set".$name)){
            call_user_func(array($this, "set".$name), $value);
            return true;
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="event"></param>
    ///<param name="method"></param>
    /**
    * 
    * @param mixed $event
    * @param mixed $method
    */
    public function callEvent($event, $method){
        throw new Exception(__METHOD__." Not implement");
    }
    ///<summary></summary>
    ///<param name="obj"></param>
    /**
    * 
    * @param mixed $obj
    */
    public function CompareTo($obj){
        $g=$this->getCmpObj();
        $s=$obj->getCmpObj();
        $r=($g == $s);
        return $r;
    }
    ///<summary>used to dispose and release element</summary>
    /**
    * used to dispose and release element
    */
    public function Dispose(){}
    ///<summary></summary>
    /**
    * 
    */
    protected function getCmpObj(){}
    ///<summary>override this method to filter call of global method used to call internal function (protected)</summary>
    /**
    * override this method to filter call of global method used to call internal function (protected)
    */
    public static function Invoke($ctrl, $method, $args=null){
        if(method_exists($ctrl, $method)){
            if($args == null){
                return $ctrl->$method();
            }
            else{
                return $ctrl->$method(...$args); 
            }
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $value
    */
    public function regEvent($name, $value){
        throw new Exception(__METHOD__." not implement");
    }
}