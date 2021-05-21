<?php

namespace IGK\System\Database\MySQL;
use \IGKObject;

///<summary>Represente class: IGKMySQLTimeManager</summary>
/**
* Represente IGKMySQLTimeManager class
*/
final class IGKMySQLTimeManager extends IGKObject{
    var $ad;
    ///<summary></summary>
    ///<param name="ad"></param>
    /**
    * 
    * @param mixed $ad
    */
    public function __construct($ad){
        $this->ad=$ad;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function Now(){
        return date($this->ad->getFormat("datetime"));
    }
}