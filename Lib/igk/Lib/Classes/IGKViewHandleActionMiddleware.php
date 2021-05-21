<?php
// @file: IGKViewHandleActionMiddleware.php
// @author: C.A.D. BONDJE DOUE
// @copyright: igkdev © 2019
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKViewHandleActionMiddleware</summary>
/**
* Represente IGKViewHandleActionMiddleware class
*/
class IGKViewHandleActionMiddleware extends IGKRunCallbackMiddleware{
    ///<summary></summary>
    ///<param name="fname"></param>
    ///<param name="action"></param>
    ///<param name="params"></param>
    /**
    * 
    * @param mixed $fname
    * @param mixed $action
    * @param mixed $params
    */
    public function __construct($fname, $action, $params){
        parent::__construct(function($service) use ($fname, $action, $params){
            $r=igk_view_handle_actions($fname, $action, $params, 0);
            return $r;
        });
    }
}
