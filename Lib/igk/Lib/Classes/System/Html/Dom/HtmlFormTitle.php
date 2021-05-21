<?php

namespace IGK\System\Html\Dom;

use IGKHtmlItem;

///<summary>Represente class: IGKHTmlFormTitle</summary>
/**
* Represente IGKHTmlFormTitle class
*/
final class HtmlFormTitle extends IGKHtmlItem{
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this["class"]="title";
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function AcceptRender($options=null){
        if(!$this->IsVisible){
            return 0;
        }
        $c=$this->Content;
        if($c)
            return 1;
        return 0;
    }
}