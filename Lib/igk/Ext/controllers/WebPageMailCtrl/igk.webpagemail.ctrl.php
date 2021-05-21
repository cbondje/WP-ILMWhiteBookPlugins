<?php


//TODO : MAIL MANAGEMENENT
/*

final class IGKWebPageMailCtrl extends IGKConfigCtrlBase
{
	public function getConfigPage(){ return "mailpages"; }
	public function getMailDir(){
		return $this->getDeclaredDir()."/Mails";
	}
	public function getMailAtricles(){
		return IGKIO::GetFiles($this->getMailDir(), "/\.phtml$/i",false);
	}
	public function getMailArticle($n=null)
	{
		$n = !$n? igk_getr("n"): $n;

		return $this->getArticleInDir($n, $this->getMailDir());
	}
	public function View(){
		parent::View();
		if (!$this->IsVisible)return;

		$t = $this->TargetNode;
		$t->ClearChilds();
		igk_html_add_title($t, "title.mailsystem");
		$t->addHSep();

		$frm = $this->TargetNode->addDiv()->addForm();
		$tab = $frm->addDiv()->addTable();
		$tab["class"]  = "igk-table igk-table-hover";
		$tr = $tab->add("tr");
		IGKHtmlUtils::AddToggleAllCheckboxTh($tr);
		$tr->add("th")->setClass("fitw")->Content = R::ngets("clName");
		$tr->add("th",array("class"=>"tab_btn"))->Content = IGK_HTML_SPACE;
		$tr->add("th",array("class"=>"tab_btn"))->Content = IGK_HTML_SPACE;
		$tr->add("th",array("class"=>"tab_btn"))->Content = IGK_HTML_SPACE;
		$tr->add("th",array("class"=>"tab_btn"))->Content = IGK_HTML_SPACE;

		$ctrl = igk_getctrl(IGK_CA_CTRL);

		$t = $this->getMailAtricles();
		if ($t)
		foreach($t as $k=>$v)
		{
			$tr = $tab->add("tr");
			$tr->add("td")->addInput("clArticles[]", "checkbox", basename($v));
			$tr->add("td")->Content = basename($v);
			IGKHtmlUtils::AddImgLnk($tr->add("td"), igk_js_post_frame($ctrl->getUri("ca_edit_article_ajx&ctrlid=".$this->Name."&n=".basename($v)), $this), "edit");
			IGKHtmlUtils::AddImgLnk($tr->add("td"), igk_js_post_frame($ctrl->getUri("ca_edit_articlewtiny_f_ajx&ctrlid=".$this->Name."&n=".basename($v)), $this), "tiny");
			IGKHtmlUtils::AddImgLnk($tr->add("td"), igk_js_post_frame($this->getUri("mail_preview_ajx&n=".basename($v)), $this), "view");
			IGKHtmlUtils::AddImgLnk($tr->add("td"), igk_js_post_frame($ctrl->getUri("ca_edit_articlewtiny_f_ajx&ctrlid=".$this->Name."&n=".basename($v)), $this), "drop");
		}
		igk_html_toggle_class($tab);
		$frm->addHSep();
		$d = $frm->addDiv();
		IGKHtmlUtils::AddImgLnk($d, igk_js_post_frame($this->getUri("add_mail_article_ajx")), "add");
	}
	public function add_mail_article_ajx(){
		if(igk_qr_confirm())
		{
			$n = igk_getr("clName");
			$a = $this->getMailDir();
			$f = $n;
			$lang = R::GetCurrentLang();
			$file = igk_io_get_article_file($n, $this->getMailDir(), R::GetCurrentLang());


		}
		else{
			$frame = igk_add_new_frame($this, __FUNCTION__);
			$frame->Title = R::ngets("title.addmail");
			$frame->Content->ClearChilds();
			$frm = $frame->Content->addForm();
			$frm["action"] = $this->getUri(__FUNCTION__);;
			$frm->addSLabelInput("clName");
			$d = $frm->addDiv();
			$d->addInput("confirm", "hidden", 1);
			$d->addInput("btn_confirm", "submit", R::ngets("btn.confirm"));
			$frame->RenderAJX();
		}
	}
	public function send_mail(){
	}
	public function mail_preview_ajx(){
		$frame = igk_add_new_frame($this, __FUNCTION__);
		$frame->Title = R::ngets("title.mailpreview");
		$frame->Height = 400;
		$frame->Width = 600;
		$frame->Content->ClearChilds();
		$frm = $frame->Content->addForm();
		$frm["action"] = "";
		$s = $this->getArticle(igk_getr("n"));
		$frm->addDiv()->LoadFile($s);
		$frame->RenderAJX();
	}
	public function showConfig(){
		parent::showConfig();
	}

	public function getConfirmationMail(){
	}

	public function getIsSystemController(){return true;}
}
*/
?>