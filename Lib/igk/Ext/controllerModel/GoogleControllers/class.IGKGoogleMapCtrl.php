<?php
// @file: class.IGKGoogleMapCtrl.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKGoogleMapCtrl</summary>
/**
* Represente IGKGoogleMapCtrl class
*/
abstract class IGKGoogleMapCtrl extends IGKCtrlTypeBase {
    ///<summary></summary>
    /**
    * 
    */
    public static function GetAdditionalConfigInfo(){
        return array(
            "clGoogleMapUrl",
            igk_createAdditionalConfigInfo(array("clRequire"=>1))
        );
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getcanAddChild(){
        return false;
    }
    ///<summary></summary>
    ///<param name="t" ref="true"></param>
    /**
    * 
    * @param  * $t
    */
    public static function SetAdditionalConfigInfo(& $t){
        $t["clGoogleMapUrl"]=igk_getr("clGoogleMapUrl");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function View(){
        $t=$this->TargetNode;
        $t->ClearChilds();
        $lnk=igk_getv($this->Configs, "clGoogleMapUrl", "http://www.google.fr");
        $s=<<<EOF
<iframe class="noborder googlemap_map" src="{$lnk}"></iframe>
EOF;
        $t->Load($s);
    }
}
///<summary>Represente class: IGKHtmlGoogleMapNodeItem</summary>
/**
* Represente IGKHtmlGoogleMapNodeItem class
*/
final class IGKHtmlGoogleMapNodeItem extends IGKHtmlItem{
    private $m_key;
    private $m_location;
    private $m_query;
    private $m_type;
    ///<summary></summary>
    ///<param name="opt" default="null"></param>
    /**
    * 
    * @param mixed $opt the default value is null
    */
    protected function __acceptRender($opt=null){
        $this->initView();
        return parent::__AcceptRender($opt);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this["class"]="igk-winui-google-map";
        $this->m_type="place";
        $this->m_key="AIzaSyDDOfGXjfMVZOFoAESJ3ON0bZyiJpnXBqc";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getKey(){
        return $this->m_key;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getLocation(){
        return $this->m_location;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getQuery(){
        return $this->m_query;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getType(){
        return $this->m_type;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        $this->ClearChilds();
        $key=$this->getKey();
        $t=$this->getType();
        $q=$this->Location;
        $lnk="https://www.google.com/maps/embed/v1/{$t}?key={$key}&q={$q}";
        if(igk_sys_env_production() && $lnk){
            $s=<<<EOF
<iframe class="no-border1 googlemap_map fitw" src="{$lnk}" frameborder="0" ></iframe>
EOF;
            $this->Load($s);
        }
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setKey($v){
        $this->m_key=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setLocation($v){
        $this->m_location=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setQuery($v){
        $this->m_query=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setType($v){
        $this->m_type=$v;
        return $this;
    }
}
?>