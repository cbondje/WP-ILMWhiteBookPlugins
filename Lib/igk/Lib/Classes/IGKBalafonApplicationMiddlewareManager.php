<?php
// @file: IGKBalafonApplicationMiddlewareManager.php
// @author: C.A.D. BONDJE DOUE
// @copyright: igkdev © 2019
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKBalafonApplicationMiddlewareManager</summary>
/**
* Represente IGKBalafonApplicationMiddlewareManager class
*/
class IGKBalafonApplicationMiddlewareManager implements IIGKBalafonApplicationMiddlewareService{
    private $_properties;
    private $_whereList;
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $args
    */
    public function __call($n, $args){
        if(strpos(strtolower($n), "use") === 0){
            ($middle=IGKBalafonMiddleware::CreateMiddleware($t=substr($n, 3), $args, $this)) || igk_die("failed to get middleware $t");
            return $this;
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    function __construct(){
        $this->_whereList=array();
        $this->_properties=array();
    }
    ///<summary></summary>
    ///<param name="middleware"></param>
    /**
    * 
    * @param mixed $middleware
    */
    public function Attach($middleware){
        $w=& $this->_whereList;
        $w[]=$middleware;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function GetLastMiddleware(){
        $w=& $this->_whereList;
        if(($c=count($w)) > 0){
            return $w[$c-1];
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function offsetExists($i){
        return isset($this->_properties[$i]);
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function offsetGet($i){
        return isset($this->_properties[$i]) ? $this->_properties[$i]: null;
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
        if($v == null)
            unset($this->_properties[$i]);
        else
            $this->_properties[$i]=$v;
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function offsetUnset($i){
        unset($this->_properties[$i]);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function Process(){
        IGKBalafonMiddleware::Process($this, $this->_whereList);
    }
    ///<summary></summary>
    ///<param name="callback"></param>
    /**
    * 
    * @param mixed $closurecallback
    */
    public function Run($callback){
        IGKBalafonMiddleware::Attach(new IGKRunCallbackMiddleware($callback), $this);
        return $this;
    }
    ///<summary></summary>
    ///<param name="middle"></param>
    /**
    * 
    * @param mixed $middle
    */
    public function UseMiddleWare($middle){
        if(is_object($middle) && is_subclass_of(get_class($middle), IGKBalafonMiddleware::class))
            IGKBalafonMiddleware::Attach($middle, $this);
        return $this;
    }
}
