<?php
// @file: igk_html_ob.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary></summary>
///<param name="callback"></param>
/**
* 
* @param mixed $callback
*/
function igk_html_ob($callback){
    ob_start();
    $callback();
    $s=ob_get_contents();
    ob_end_clean();
    igk_wl($s);
}
///<summary></summary>
///<param name="id"></param>
///<param name="options"></param>
///<param name="selected" default="null"></param>
///<param name="display" default="null"></param>
///<param name="attribs" default="null"></param>
///<param name="render" default="1"></param>
/**
* 
* @param mixed $id
* @param mixed $options
* @param mixed $selected the default value is null
* @param mixed $display the default value is null
* @param mixed $attribs the default value is null
* @param mixed $render the default value is 1
*/
function igk_html_ob_select($id, $options, $selected=null, $display=null, $attribs=null, $render=1){
    $o="<select name=\"{$id}\" ";
    if($attribs){
        foreach($attribs as $k=>$v){
            $o .= $k."=".igk_html_attribvalue($v)." ";
        }
    }
    $o .= ">";
    if($options){
        foreach($options as $k=>$v){
            $o .= "<option value=".igk_html_attribvalue($k)." ";
            if($k == $selected){
                $o .= "selected=\"true\" ";
            }
            $o .= ">";
            if($display == null){
                $o .= $v;
            }
            else{
                $o .= $v->$display;
            }
            $o .= "</option>";
        }
    }
    $o .= "</select>";
    if($render)
        igk_wl($o);
    return $o;
}
///<summary></summary>
///<param name="id"></param>
///<param name="text"></param>
///<param name="attribs" default="null"></param>
///<param name="render" default="1"></param>
/**
* 
* @param mixed $id
* @param mixed $text
* @param mixed $attribs the default value is null
* @param mixed $render the default value is 1
*/
function igk_html_ob_submit($id, $text, $attribs=null, $render=1){
    $o="<input ";
    $g=array(
            "type"=>"submit",
            "id"=>$id,
            "name"=>$id,
            "value"=>$text
        );
    if(!$attribs){
        $attribs=$g;
    }
    else{
        $attribs=array_merge($g, $attribs);
    }
    if($attribs){
        foreach($attribs as $k=>$v){
            $o .= $k."=".igk_html_attribvalue($v)." ";
        }
    }
    $o .= "/>";
    if($render)
        igk_wl($o);
    return $o;
}
