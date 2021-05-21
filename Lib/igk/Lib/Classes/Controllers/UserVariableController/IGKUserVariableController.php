<?php
// @file: IGKUserVariableController.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKUserVarsCtrl</summary>

use IGK\Resources\R;

/**
* Represente IGKUserVarsCtrl class
*/
final class IGKUserVarsCtrl extends IGKConfigCtrlBase {
    private $m_searchkey;
    private $m_vars;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct();
        $this->__loadVars();
    }
    ///<summary></summary>
    /**
    * 
    */
    private function __loadVars(){
        $this->m_vars=array();
        $e=IGKCSVDataAdapter::LoadData($this->dataFileName);
        if($e){
            foreach($e as  $v){
                $this->m_vars[$v[0]]=array("value"=>igk_getv($v, 1), "comment"=>igk_getv($v, 2));
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __storeVars(){
        if(defined("IGK_FRAMEWORK_ATOMIC"))
            return;
        $f=$this->getdataFileName();
        $out=IGK_STR_EMPTY;
        foreach($this->m_vars as $k=>$v){
            $v_cv=igk_getv($v, "value");
            $v_cc=igk_getv($v, "comment");
            if(!empty($out)){
                $out .= IGK_LF;
            }
            $out .= $k.",".igk_csv_getvalue($v_cv).",".igk_csv_getvalue($v_cc);
        }
        if(igk_io_save_file_as_utf8($f, $out)){
            igk_notifyctrl()->addMsgr("msg.uservarsctrl.varsaved");
        }
        else{
            igk_wln($out);
            igk_exit();
        }
        $this->View();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getConfigPage(){
        return "uservarctrl";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getdataFileName(){
        return igk_io_syspath(IGK_DATA_FOLDER.DIRECTORY_SEPARATOR."usersvar.csv");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getName(){
        return IGK_USERVARS_CTRL;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getVars(){
        return $this->m_vars;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function InitComplete(){
        parent::InitComplete();
        $file=igk_io_applicationdir(IGK_PROJECTS_FOLDER."/register_uvar.phtml");
        if(file_exists($file)){
            include($file);
        }
    }
    ///<summary>register user variable</summary>
    /**
    * register user variable
    */
    public function regVars($name, $value, $comment){
        if(empty($name))
            return;
        $this->m_vars[$name]=array("value"=>$value, "comment"=>$comment);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function search_var(){
        $this->m_searchkey=igk_getr("q");
        $this->View();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function vc_addvarframe_ajx(){
        $frame=igk_html_frame($this, __FUNCTION__);
        $frame->Title=R::ngets("title.addsystemvariable");
        $d=$frame->BoxContent;
        $d->ClearChilds();
        $frm=$d->addForm();
        $frm["action"]=$this->getUri("vc_addvars");
        $ul=$frm->add("ul");
        $ul->addLi()->addSLabelInput(IGK_FD_NAME);
        $ul->addLi()->addSLabelInput("clValue");
        $ul->addLi()->addSLabelInput("clComment");
        $frm->addHSep();
        $frm->addInput("btn_savevar", "submit", R::ngets("btn.save"));
        igk_wl($frame->Render());
    }
    ///<summary></summary>
    /**
    * 
    */
    public function vc_addvars(){
        $obj=igk_get_robj();
        $this->m_vars[$obj->clName]=array("value"=>$obj->clValue, "comment"=>$obj->clComment);
        $this->__storeVars();
    }
    ///<summary></summary>
    ///<param name="store" default="true"></param>
    /**
    * 
    * @param mixed $store the default value is true
    */
    public function vc_Clearvars($store=true){
        $this->m_vars=array();
        if($store)
            $this->__storeVars();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function vc_dropvars(){
        $obj=igk_getr("n");
        if(isset($this->m_vars[$obj])){
            unset($this->m_vars[$obj]);
            $this->__storeVars();
            $this->View();
        }
        igk_navtocurrent();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function vc_rm_selection(){
        $n=igk_getr(IGK_FD_NAME);
        if(is_array($n)){
            foreach($n as  $v){
                unset($this->m_vars[$v]);
            }
            $this->__storeVars();
        }
        $this->View();
        igk_navtocurrent();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function vc_saveAllVars(){
        $tn=igk_getr(IGK_FD_NAME);
        $tv=igk_getr("clValue");
        $tc=igk_getr("clComment");
        if(igk_getr("btn_save")){
            $tn=igk_getr("clHName");
        }
        for($i=0; $i < igk_count($tn); $i++){
            $n=$tn[$i];
            $v=igk_getv($tv, $i);
            $c=igk_getv($tc, $i);
            $this->regVars($n, $v, $c);
        }
        $this->__storeVars();
        igk_navtocurrent();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function View(){
        if(!$this->getIsVisible()){
            igk_html_rm($this->TargetNode);
            return;
        }
        $t=$this->ConfigNode;
        $t->ClearChilds();
        igk_html_add_title($t, "title.uservariables");
        $t->addHSep();
        igk_html_article($this, "uservariableinfo", $t->addDiv());
        $t->addHSep();
        $t->add(new IGKHtmlSearchItem($this->getUri("search_var"), $this->m_searchkey));
        $frm=$t->addForm();
        $frm["action"]=$this->getUri("vc_saveAllVars");
        igk_notify_sethost($frm->addDiv());
        $table=$frm->addTable();
        $tr=$table->addTr();
        IGKHtmlUtils::AddToggleAllCheckboxTh($tr);
        $tr->add("th")->Content=R::ngets(IGK_FD_NAME);
        $tr->add("th")->Content=R::ngets("clValue");
        $tr->add("th")->Content=R::ngets("clComment");
        $tr->add("th")->Content=IGK_HTML_SPACE;
        if(is_array($this->m_vars)){
            foreach($this->m_vars as $k=>$v){
                $obj=(object)$v;
                if($this->m_searchkey && !strstr(strtolower($k), strtolower($this->m_searchkey)))
                    continue;
                $tr=$table->addTr();
                $td=$tr->addTd();
                $td->addInput("clName[]", "checkbox", $k);
                $td->addInput("clHName[]", "hidden", $k);
                $tr->addTd()->Content=$k;
                $tr->addTd()->addInput("clValue[]", "text", $obj->value);
                $tr->addTd()->addInput("clComment[]", "text", $obj->comment);
                IGKHtmlUtils::AddImgLnk($tr->addTd(), $this->getUri("vc_dropvars&n=".$k), "drop_16x16");
            }
        }
        $frm->addHSep();
        $frm->addInput("btn_save", "submit", R::ngets("btn.save"));
        IGKHtmlUtils::AddBtnLnk($frm, R::ngets("btn.addvars"), igk_js_post_frame($this->getUri("vc_addvarframe_ajx")));
        IGKHtmlUtils::AddBtnLnk($frm, R::ngets("btn.rmSelection"), "#", array("onclick"=>igk_js_a_postform($this->getUri("vc_rm_selection"))));
        igk_html_toggle_class($table);
    }
}
