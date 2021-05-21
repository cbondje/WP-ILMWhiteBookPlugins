<?php
// @file: IGKApplicationBase.class.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>represent a template application base class</summary>
/**
* represent a template application base class
*/
abstract class IGKApplicationBase extends IGKApplicationController{
    private $m_manifest;
    ///<summary>display value</summary>
    /**
    * display value
    */
    public function __toString(){
        return strtolower("IGKTemplateApplication://".$this->getName());
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDataDir(){
        return igk_io_dir(dirname($this->getDeclaredFileName()).DIRECTORY_SEPARATOR.IGK_DATA_FOLDER);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDeclaredDir(){
        return igk_io_dir(dirname($this->getDeclaredFileName()));
    }
    ///template entries folder
    /**
    */
    public function getDeclaredFileName(){
        return igk_get_reg_class_file(get_class($this));
    }
    ///<summary></summary>
    /**
    *
    */
    public final function getManifest(){
        if($this->m_manifest == null){
            $this->m_manifest=igk_createOBJ();
            $this->m_manifest->clTitle="";
            $f=$this->getDataDir()."/.manifest";
            if(file_exists($f)){
                $o=IGKHtmlReader::LoadXmlFile($f);
                igk_wln($o);
                igk_exit();
            }
        }
        return $this->m_manifest;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getScriptDir(){
        return igk_io_dir(dirname($this->getDeclaredFileName()).DIRECTORY_SEPARATOR.IGK_SCRIPT_FOLDER);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getStylesDir(){
        return igk_io_dir(dirname($this->getDeclaredFileName()).DIRECTORY_SEPARATOR.IGK_STYLE_FOLDER);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getViewDir(){
        return igk_io_dir(dirname($this->getDeclaredFileName()).DIRECTORY_SEPARATOR.IGK_VIEW_FOLDER);
    }
    ///<summary>return an array of templates view</summary>
    /**
    * return an array of templates view
    */
    public function getViews(){
        return igk_io_getfiles($this->getViewDir(), "/\.xtphtml$/");
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function InitComplete(){
        parent::InitComplete();
        igk_notification_reg_event(IGK_FORCEVIEW_EVENT, array($this, "notify_view"));
    }
    ///<summary></summary>
    /**
    * 
    */
    public function notify_view(){
        if($this->isVisible){
            $this->View();
        }
    }
    ///<summary></summary>
    ///<param name="context" default="null"></param>
    /**
    * 
    * @param mixed $context the default value is null
    */
    protected function onInstall($context=null){
        igk_die(__METHOD__." not implement");
    }
    ///<summary></summary>
    ///<param name="context"></param>
    /**
    * 
    * @param mixed $context
    */
    protected function onUninstal($context){
        igk_die(__METHOD__." not implement");
    }
    ///<summary>override this method to uninstall this package</summary>
    /**
    * override this method to uninstall this package
    */
    public function uninstall(){
        $this->_unregisterEvents();
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