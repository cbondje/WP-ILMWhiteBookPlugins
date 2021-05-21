<?php

class IGKArticleController extends IGKAtriclesCtrlBase
{

	public function getIsSystemController()
	{
		return true;
	}

	public function __construct(){
		parent::__construct();
	}
	public function getInfoCondition(){
		return $this->getArticle("condition");
	}
	public function getCookiesWarning(){
		return $this->getArticle("cookieswarning");
	}
	public function getConfidentiality(){
		return $this->getArticle("confidentiality");
	}

	public function initComplete(){
		parent::initComplete();
		igk_reg_ctrl("system/articleviewer", $this);
	}
}

// igk_reg_sys("IGKArticleController");
igk_sys_regSysController("IGKArticleController");
?>