<?php
// @file: IGKRunCallbackMiddleware.php
// @author: C.A.D. BONDJE DOUE
// @copyright: igkdev © 2019
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKRunCallbackMiddleware</summary>
/**
* Represente IGKRunCallbackMiddleware class
*/
class IGKRunCallbackMiddleware extends IGKBalafonMiddleware{
    private $callback;
    ///<summary></summary>
    ///<param name="callback"></param>
    /**
    * 
    * @param closure callback
    */
    public function __construct($callback){
        $this->callback=$callback;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function invoke(){
        $r=call_user_func_array($this->callback, array($this->getService()));
        if(!$r)
            $this->next();
    }
}
