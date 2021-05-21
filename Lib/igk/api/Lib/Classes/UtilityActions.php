<?php
// @file: UtilityActions.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente namespace: IGKApi</summary>
/**
* Represente IGKApi namespace
*/
namespace IGKApi;
// DIRECT RENDERINGuse IGKIO;
///<summary>Represente class: UtilityActions</summary>
/**
* Represente UtilityActions class
*/
class UtilityActions{
    var $ctrl;
    var $target;
    ///<summary></summary>
    ///<param name="ctrl"></param>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $ctrl
    * @param mixed $t
    */
    public function __construct($ctrl, $t){
        $this->ctrl=$ctrl;
        $this->target=$t;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function rmDir(){
        $n=igk_getr("clName");
        if(!empty($n)){
            $f=igk_io_dir(igk_io_basedir());
            $d=igk_io_basedir()."/__temp_dir";
            rename($f."/".$n, $d);
            \IGKIO::RmDir($d);
            igk_wln("ok");
        }
    }
}
