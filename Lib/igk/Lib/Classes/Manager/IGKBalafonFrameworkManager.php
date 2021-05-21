<?php
// @file: IGKBalafonFrameworkManager.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

namespace IGK\Manager;

///<summary>Framework manager</summary>
/**
* Framework manager
*/
class IGKBalafonFrameworkManager{
    var $handleAllAction;
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $args
    */
    public function __call($name, $args){
        $f="igk_".$name;
        if(function_exists($f)){
            igk_wl(call_user_func_array($f, $args));
        }
        else{
            echo "command [{$name}] not found";
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        $this->handleAllAction=1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function clear_cache(){
        igk_clear_cache();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function help(){
        echo "help ";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function install(){
        echo "running install";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function test(){
        echo "run test";
    }
}
