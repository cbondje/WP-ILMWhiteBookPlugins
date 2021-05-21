<?php
// @file: IGKHrefListValue.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente namespace: IGK\Core\Ext\Google</summary>
/**
* Represente IGK\Core\Ext\Google namespace
*/
namespace IGK\Core\Ext\Google;
// DIRECT RENDERING///<summary>represent list get value storage</summary>
/**
* represent list get value storage
*/
final class IGKHrefListValue{
    var $values;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        $this->values=func_get_args();
    }
    ///<summary>display value</summary>
    /**
    * display value
    */
    public function __toString(){
        return $this->getValue();
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function getValue($options=null){
        $o=0;
        if(isset($options->Document)){
            if(is_array($this->values)){
                $o=array_shift($this->values);
                if(count($this->values) == 1)
                    $this->values=$this->values[0];
            }
            else
                $o=$this->values;
        }
        else{
            if(is_array($this->values) && (count($this->values) > 0))
                $o=$this->values[0];
            else
                $o=$this->values;
        }
        if(is_object($o)){
            $o=$o->getValue();
        }
        return $o;
    }
}
