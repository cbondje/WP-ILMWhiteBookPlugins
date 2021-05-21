<?php
/// controller de changement. permet à un controlleur de signaler un changement de configuration
// final class IGKChangeManagerCtrl extends IGKConfigCtrlBase
// {
	// // private $m_datas;
	// // private $m_propertyChangedEvent;
	// // private $m_loadConfigEvent;
	// // private $m_vChange;
	// // private $m_ConfigNode;


	// const DATE_CONST = 'Y-m-d H:i:s';
	// public function getName(){return IGK_CHANGE_MAN_CTRL;	}

	// //ajout et suppression d'évènement
	// public function addPropertyChangedEvent($obj, $method){
		// // $this->m_propertyChangedEvent->add($obj, $method);
	// }
	// public function removePropertyChangedEvent($obj, $method)
	// {
		// // $this->m_propertyChangedEvent->remove($obj, $method);
	// }
	// //register to config event
	// public function addLoadConfigEvent($obj, $method)
	// {
		// // $this->m_loadConfigEvent->add($obj, $method);
	// }
	// public function removeLoadConfigEvent($obj, $method)
	// {
		// // $this->m_loadConfigEvent->remove($obj, $method);
	// }
	// //donné sauvegarder par le controlleur
	// public function getData(){
		// return $this->m_datas;
	// }
	// public function __construct(){
		// parent::__construct();
		// // $this->m_propertyChangedEvent = new IGKEvents($this, get_class($this)."::PropertyChanged");
		// // $this->m_loadConfigEvent =  new IGKEvents($this, get_class($this)."::loadConfigEvent");
		// $this->loadConfig();
	// }
	// public function initEvent(){

	// }
	// public function getConfigPage(){
		// return "changectrl";
	// }



	// //enregistrer les changements sur un paramètre de configuration.
	// //cela a pour effet de mettre a jour la ligne de temps.
	// public function registerChange($itemname, & $entrynewdata)
	// {
		// // if (!$this->App->Configs->changectrl_checkchange)
			// // return;
		// $d = date(self::DATE_CONST);
		// $data = $this->getData();
		// if ($data){
		// $data->$itemname = $d;
		// $data->LastChange = $this->m_datas->$itemname ;
		// $data->saveData();
		// $entrynewdata = $d;
		// }
	// }
	// //supprimer la sauvegarde sur un paramètre de configuration
	// public function unregisterChange($itemname){
		// if (!$this->App->Configs->changectrl_checkchange)
			// return;
		// $d = date(self::DATE_CONST);
		// $this->m_datas->$itemname = null;
		// $this->m_datas->LastChange = $d;
		// $this->m_datas->Save();
	// }
	// //detecter si la propriété a changer au cour du temp. retourne true si besoin de recharge sinon false
	// public function isChanged($entryname, & $entrynewdata =null ){
		// $n = $entryname;
		// if ($this->Data->$n ==null)
			// return;
		// $data = $this->Data->$n ;
		// $m = (($data != null) && (!empty($entrynewdata))) ;
		// $reload = $m && ($data != $entrynewdata);
		// if ($this->Data->$n != $entrynewdata)
		// {
			// $entrynewdata = $data;
		// }
		// return $reload;
	// }
	// protected function InitComplete()
	// {
		// parent::InitComplete();
	// }
	// public function getIsVisible(){
		// return false;
	// }
	// public function IsFunctionExposed($function){//make all function available
		// return true;
	// }
	// public function View(){
		// //not visible
		// $this->TargetNode->ClearChilds();
		// if ($this->App->Configs->changectrl_checkchange)
		// {
			// $i = $this->App->Configs->changectrl_interval? $this->App->Configs->changectrl_interval : 'null';
			// igk_html_add($this->TargetNode, $this->App->Doc->Body);
			// $this->TargetNode->addScript()->Content = "igk.ctrl.changement.init('".$this->getUri("checkforupdate_ajx")."', ".$i.");";
		// }
		// else{
			// igk_html_rm($this->TargetNode);
		// }

	// }
	// public function checkforupdate_ajx()
	// {
		// $this->loadConfig();
		// if ($this->isChanged("LastChange", $this->m_vChange))
		// {
			// igk_wl(R::ngets("MSG.DATACHANGED"));
		// }
	// }


	// public function update_change()
	// {
		// $i = igk_getr("clCheckChange", false);

		// $this->App->Configs->changectrl_checkchange = $i;
		// $this->App->Configs->changectrl_interval = igk_getr("clChangeInterval");
		// igk_save_config();
		// igk_resetr();

		// igk_notifyctrl()->addMsgr("msg.configs.updated");
		// $this->showConfig();
		// $this->View();
	// }
	// //configuration view
	// public function showConfig(){

		// $c = $this->getConfigNode();
		// if (!$c)
			// return;

		// $c->ClearChilds();
		// $c = $c->addDiv();
		// //IGKHtmlUtils::AddItem($c, $this->ConfigNode);
		// igk_html_add_title($c, "title.ChangeCtrlManager");
		// $c->addHSep();
		// igk_notify_sethost($c->addDiv());
		// $frm = $c->addForm();
		// $frm["action"] = $this->getUri("update_change");

		// $ul = $frm->add("ul");
		// $t = array();
		// if ($this->App->Configs->changectrl_checkchange)
			// $t["checked"] = "true";

		// $ul->addLi()->addSLabelInput("clCheckChange", "checkbox", $this->App->Configs->changectrl_checkchange, $t);
		// $ul->addLi()->addSLabelInput("clChangeInterval",  "text", $this->App->Configs->changectrl_interval);
		// $frm->addBtn("btn_save", R::ngets("btn.save"));
	// }
	// public function loadConfig()
	// {
		// $fullpath = igk_io_syspath(IGK_CHANGE_CONF_DATA);
		// $f = IGKCSVDataAdapter::LoadData($fullpath);
		// $d = $this->getData();
		// if ($d == null)
			// $d= new IGKConfigData($fullpath, $this, array());
		// if ($f != null)
		// {
			// foreach($f as $k=>$v)
			// {
				// $c  = igk_getv($v,0);
				// if ($c){
					// $d->$c = igk_getv($v, 1);
				// }
			// }
		// }
		// //raise onLoadConfigEvent
		// $this->__onLoadConfigEvent();
	// }
	// protected function __onLoadConfigEvent()
	// {
		// igk_invoke_session_event(__CLASS__."::ConfigLoaded", array($this, null));
		// //$this->m_loadConfigEvent->Call($this, null);
	// }

	// public function showinfo()
	// {
		// $this->TargetNode->ClearChilds();
		// IGKHtmlUtils::AddItem($this->TargetNode, $this->App->Doc->body);
		// $ul = $this->TargetNode->add("ul");
		// foreach($this->m_datas->getEntries() as $k=>$v)
		// {
			// $ul->addLi()->Content = $k.":".$v;
		// }
	// }

	// private static function CompareDate($date1, $date2)
	// {
		// return igk_date_compare($date1, $date2);
	// }
	// protected function initTargetNode()
	// {
		// $this->m_ConfigNode =  igk_createNode("div");
		// return parent::initTargetNode();
	// }
// }

?>