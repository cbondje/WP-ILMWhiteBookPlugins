<?php

///<summary>Represente class: IGKConfigCtrlBase</summary>

use IGK\Controllers\BaseController;
use function igk_resources_gets as __; 

/**
* Represente IGKConfigCtrlBase class
*/
abstract class IGKConfigCtrlBase extends BaseController implements IIGKConfigController {

     
    ///<summary></summary>
    ///<param name="node"></param>
    ///<param name="title"></param>
    /**
    * 
    * @param mixed $node
    * @param mixed $title
    */
    protected function addTitle($node, $title){
        $d=$node->addDiv();
        $d["class"]="igk-cnf-title";
        $d->Content=__($title);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigCtrl(){
        return igk_getctrl(IGK_CONF_CTRL, false);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigNode(){
        return $this->getConfigCtrl()->getConfigNode();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigPage(){
        return "default";
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function getGlobalHelpArticle(){
        return "./help/help.".$this->Name;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsConfigPageAvailable(){
        return igk_is_conf_connected();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsVisible(){
        $app=igk_app();
        $cnf=$this->ConfigCtrl;
        $v=(($app->CurrentPageFolder == IGK_CONFIG_MODE) && ($cnf) && ($cnf->getSelectedConfigCtrl() === $this) && ($cnf->getIsConnected()));
        return $v;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function InitComplete(){
        parent::InitComplete();
        if($c=$this->getConfigCtrl())
            $c->registerConfig($this);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initConfigMenu(){
        $c=$this->getConfigPage();
        $error_msg="No config menu found for : ".$c. " = ".$this->Name. " : ".get_class($this). " ".$this->getDeclaredFileName();
        $conf=igk_get_configs_menu_settings();
        if(isset($conf->$c)){
            $cp=$conf->$c;
            if($cp){
                return array(
                    new IGKMenuItem($cp->menuname,
                    $cp->pagename,
                    $this->getUri("showConfig"),
                    $cp->menuindex,
                    $cp->imagekey,
                    null,
                    $cp->group)
                );
            }
            else{
                igk_ilog($error_msg, __METHOD__);
            }
        }
        else{
            return array(
                new IGKMenuItem($c,
                $c,
                $this->getUri("showConfig"),
                $this->ConfigIndex ?? -1,
                $this->ConfigImageKey,
                null,
                $this->ConfigGroup)
            );
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="funcName"></param>
    /**
    * 
    * @param mixed $funcName
    */
    public function IsFunctionExposed($funcName){
        return igk_is_conf_connected();
    }
    ///<summary>base show Configuration of the controller</summary>
    /**
    * base show Configuration of the controller
    */
    public function showConfig(){
        $_t=$this->getTargetNode();
		$e_key  = "sys://config/selectedview";
        $this->ConfigCtrl->setSelectedConfigCtrl($this, get_class($this)."::showConfig");
        

        if(!$this->getIsVisible()){
            igk_html_rm($_t);
            igk_set_env($e_key , null);
        }
        else{
            $this->View();
            if($_cnf_node=$this->getConfigNode()){
                $_cnf_node->clearChilds();
                igk_html_add($_t, $_cnf_node);
                igk_set_env($e_key, $this); 
            }
        }
    }
    ///<summary>used to initialize the config view node</summary>
    /**
    * used to initialize the config view node
    */
    protected function viewConfig($target, $titlekey, $descfile){
        return igk_html_ctrl_view_config($this, $target, $titlekey, $descfile);
    }
}