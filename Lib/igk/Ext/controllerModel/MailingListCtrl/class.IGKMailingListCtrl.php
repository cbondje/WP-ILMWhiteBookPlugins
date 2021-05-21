<?php
abstract class IGKMailingListCtrl  extends IGKCtrlTypeBase
{
	protected function InitComplete(){
		parent::InitComplete();
		igk_getctrl("IGKMailCtrl")->addMailSendEvent($this, "mailinglist_send_mail");

	}
	public function mailinglist_send_mail($sender, $args)
	{
		if ($args){
			$s = igk_db_objentries($this, $args);
			$this->insert((array)$s);
		}
	}
	protected function getConfigFile()
	{
		$s = dirname(__FILE__)."/".IGK_DATA_FOLDER."/".IGK_CTRL_CONF_FILE;
		return igk_io_dir($s);
	}
	protected function getDBConfigFile()
	{
		return igk_io_dir(dirname(__FILE__)."/".IGK_DATA_FOLDER."/".IGK_CTRL_DBCONF_FILE);
	}
	public function getcanAddChild(){
		return false;
	}
	public function getisVisible(){
		return false;
	}
}
?>