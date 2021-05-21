<?php
// @file: IGKGooglePackage.php
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
// DIRECT RENDERING///<summary>use for google package</summary>
/**
* use for google package
*/
class IGKGooglePackage{
    ///<summary></summary>
    /**
    * 
    */
    public function Button(){
        $n=igk_createNode("div");
        $n["class"]="google-button";
        $n["curx"]="10px";
        $n["cury"]="10px";
        $n->Content="Google Button";
        return $n;
    }
    ///<summary></summary>
    ///<param name="name"></param>
    /**
    * 
    * @param mixed $name
    */
    public function CreateNode($name){
        if(method_exists($this, $name)){
            return call_user_func_array([$this, $name], array_slice(func_get_args(), 1));
        }
        return igk_createNode("div");
    }
}
