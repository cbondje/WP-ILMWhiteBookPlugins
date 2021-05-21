<?php

namespace IGK\Resources;

use IGKHtmlUtils;
use IGKViewMode;
use IIGKHtmlGetValue;
use function igk_resources_gets as __;

///<summary>represent a language key entries. it support IIGKHtmlGetValue for getting and setting the values</summary>
/**
* represent a language key entries. it support IIGKHtmlGetValue for getting and setting the values
*/
final class IGKLangKey implements IIGKHtmlGetValue {
    var $args;
    var $def;
    var $key;
    ///<summary></summary>
    ///<param name="key"></param>
    ///<param name="default"></param>
    ///<param name="args" default="null"></param>
    /**
    * 
    * @param mixed $key
    * @param mixed $default
    * @param mixed $args the default value is null
    */
    public function __construct($key, $default, $args=null){
        if(empty($key))
            igk_die("key is null or empty");
        $this->key=strtolower($key);
        $this->def=$default;
        $this->args=$args;
    }
    ///<summary>display value</summary>
    /**
    * display value
    */
    public function __toString(){
        return $this->key;
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function getValue($options=null){
        $s=__($this->key);
        $c=0;
        if($this->args != null){
            $s=self::GetValueKeys($s, $this->args);
        }
        if($s == strtolower($this->key) && igk_app()->IsSupportViewMode(IGKViewMode::WEBMASTER)){
            $v_langctrl=igk_getctrl(IGK_LANG_CTRL);
            if(!igk_get_env("::".__METHOD__)){
                $vs=igk_createnode("script");
                $vs->Content=$v_langctrl->sourceScript();
                if($options && $options->Document && ($body=$options->Document->body)){
                    $body->AppendContent->addSingleNodeViewer(IGK_HTML_NOTAG_ELEMENT)->targetNode->add($vs);
                }
                else{
                    $vs->RenderAJX();
                }
                igk_set_env("::".__METHOD__, 1);
            }
            $v_index=null;
            if($this->args != null){
                $v_index=$v_langctrl->RegKeyLang($this->key, $this->args);
            }
            $n=igk_createnode("span");
            $n["class"]="igk-new-lang-key";
            $n["igk-new-lang-key"]="1";
            $n["igk:data"]=$v_index;
            $n->Content=$s;
            $s=$n->Render();
        }
        return html_entity_decode($s);
    }
    ///<summary></summary>
    ///<param name="s"></param>
    ///<param name="args"></param>
    /**
    * 
    * @param mixed $s
    * @param mixed $args
    */
    public static function GetValueKeys($s, $args){
        $macth=array();
        $c=preg_match_all("/\{(?P<value>[0-9]+)\}/i", $s, $match);
        for($i=0; $i < $c; $i++){
            $index=$match["value"][$i];
            if(is_numeric($index)){
                if(isset($args[$index])){
                    $s=str_replace($match[0][$i], IGKHtmlUtils::GetValue($args[$index]), $s);
                }
            }
        }
        return $s;
    }
}