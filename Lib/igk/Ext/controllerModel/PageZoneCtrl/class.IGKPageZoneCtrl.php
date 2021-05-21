<?php
// @file: class.IGKPageZoneCtrl.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKPageZoneCtrl</summary>
/**
* Represente IGKPageZoneCtrl class
*/
abstract class IGKPageZoneCtrl extends IGKCtrlTypeBase {
    private $m_viewZone;
    ///<summary></summary>
    ///<param name="targetnode" default="null"></param>
    /**
    * 
    * @param mixed $targetnode the default value is null
    */
    protected function _showChild($targetnode=null){
        $t=$targetnode ? $targetnode: $this->TargetNode;
        igk_html_add($this->m_viewZone, $t, 1000);
        if($this->hasChild){
            foreach($this->getChilds() as  $v){
                if($v->isVisible){
                    igk_html_add($v->TargetNode, $this->m_viewZone);
                    $v->View();
                }
                else{
                    igk_html_rm($v->TargetNode);
                }
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function _showViewFile(){
        parent::_showViewFile();
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetAdditionalConfigInfo(){
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCanAddChild(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getName(){
        return get_class($this);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getViewZone(){
        return $this->m_viewZone;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function InitComplete(){
        parent::InitComplete();
    }
    ///<summary></summary>
    //@@@ init target node
    /**
    * 
    */
    protected function initTargetNode(){
        $node=parent::initTargetNode();
        $node["class"]="alignc alignt dispb";
        $this->m_viewZone=$node->addDiv();
        $this->m_viewZone["class"]="page_zone";
        return $node;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function View(){
        parent::View();
    }
}
?>