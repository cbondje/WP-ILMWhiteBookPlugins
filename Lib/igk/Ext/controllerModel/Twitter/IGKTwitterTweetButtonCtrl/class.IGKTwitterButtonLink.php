<?php
/*
description: use to share a link to twitter
*/
abstract class IGKTwitterButtonLinkCtrl  extends IGKCtrlTypeBase
{
	public function getcanAddChild(){
		return false;
	}
	public static function GetAdditionalConfigInfo()
	{
		return array("clTwitterUri");
	}
	public static function SetAdditionalConfigInfo(& $t)
	{
		$t["clTwitterUri"] = igk_getr("clTwitterUri");
	}
	protected function getConfigFile()
	{
		$s = dirname(__FILE__)."/".IGK_DATA_FOLDER."/".IGK_CTRL_CONF_FILE;
		return igk_io_dir($s);
	}
	public static function GetCtrlCategory(){
		return "COMMUNITY";
	}
	protected function getDBConfigFile()
	{
		return igk_io_dir(dirname(__FILE__)."/".IGK_DATA_FOLDER."/".IGK_CTRL_DBCONF_FILE);
	}

	public function View()
	{

		extract($this->getSystemVars());
		$t->ClearChilds();
		$c = $t->Add("div");
		$tweet = R::ngets("lb.tweet");
		$l = R::GetCurrentLang();
$c->Content = <<<EOF
<a href="https://twitter.com/share" class="twitter-share-button" data-url="{$this->Configs->clTwitterUri}" data-lang="{$l}" >{$tweet->getValue()}</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
EOF;

	}
}


?>