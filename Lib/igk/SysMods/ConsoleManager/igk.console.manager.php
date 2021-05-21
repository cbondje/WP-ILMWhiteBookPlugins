<?php
// @file: igk.console.manager.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com
use function igk_resources_gets as __;

///<summary>Represente class: IGKConsoleToolManager</summary>
/**
* Represente IGKConsoleToolManager class
*/
final class IGKConsoleToolManager extends IGKConfigCtrlBase{
    ///<summary></summary>
    /**
    * 
    */
    public function getCanConfigure(){
        return 1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigGroup(){
        return "administration";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigImageKey(){
        return "";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigIndex(){
        return 10;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigPage(){
        return "console";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsConfigPageAvailable(){
        return !igk_environment()->is("production");
    }


	public function View(){
		if(!$this->getIsVisible()){
            igk_html_rm($this->TargetNode);
            return;
        }
		$cnf = $this->getConfigNode();
		$t = $this->getTargetNode();
		// IGKHtmlUtils::AddItem($t, $cnf);
		$t->clearChilds();
		$t = $this->viewConfig($t, __("Admin Console"), ".help/console.manager.desc");

		$frm = $t->addDiv()->addPanelBox()->addForm();
		$frm->addDiv()->Content = __("On Development");
	}
}
