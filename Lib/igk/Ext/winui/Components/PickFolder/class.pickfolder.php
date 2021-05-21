<?php

///<summary>used to configure sytem application folder</summary>
class IGKPickFolderCtrl extends IGKNonVisibleControllerBase
{
	public function getcanModify(){return false;}
	public function getcanDelete(){return false;}
	public function getIsSystemController(){
		return true;
	}

}

//define a pick folder item
class IGKHtmlPickFolderItem extends IGKHtmlItem
{
	public $m_folder;

	public function getFolder(){return $this->m_folder;}
	public function setFolder($value){ $this->m_folder = $value;}

	public function __construct(){
		parent::__construct("div");
	}

}
?>