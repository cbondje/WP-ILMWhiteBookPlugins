<?php
// @file: class.IGKCommunicationServerTypeCtrl.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>represent a communication controller base</summary>
/**
* represent a communication controller base
*/
abstract class IGKCommunicationServerCtrl extends IGKApplicationController{
    const HTTP_ACCEPT="text/event-stream";
    ///<summary></summary>
    /**
    * 
    */
    private function getSocketFile(){
        return $this->getDataDir()."/server.socket";
    }
    ///<summary>override this to handle server</summary>
    /**
    * override this to handle server
    */
    abstract public function handle();
    ///<summary></summary>
    /**
    * 
    */
    abstract public function sendmsg();
}