<?php
// @file: IGKExceptionHandleMiddleware.php
// @author: C.A.D. BONDJE DOUE
// @copyright: igkdev © 2019
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKExceptionHandleMiddleware</summary>
/**
* Represente IGKExceptionHandleMiddleware class
*/
class IGKExceptionHandleMiddleware extends IGKBalafonMiddleware{
    ///<summary></summary>
    /**
    * 
    */
    public function invoke(){
        try {
            $this->next();
        }
        catch(Exception $ex){
            igk_show_exception($ex);
        }
    }
}
