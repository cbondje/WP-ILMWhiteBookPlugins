<?php
///</summary>controller used to a a like on page à the target points</summary>
abstract class IGKFacebookLikeCtrl  extends IGKCtrlTypeBase
{
	public function getcanAddChild(){
		return false;
	}
	public static function GetAdditionalConfigInfo()
	{
		return array("clFacebookUri"=>igk_createAdditionalConfigInfo(array("clRequire"=>1)));
	}
	public static function SetAdditionalConfigInfo(& $t)
	{
		$t["clFacebookUri"] = igk_getr("clFacebookUri");
	}
	public static function GetCtrlCategory(){
		return "COMMUNITY";
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

	public function View()
	{

		extract($this->getSystemVars());
		$t->ClearChilds();
		$c = $t->Add("div");
$c->Content = <<<EOF
<iframe src="http://www.facebook.com/plugins/like.php?href={$this->Configs->clFacebookUri}&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
EOF;

	}
}

final class IGKHtmlFacebookLikeItem extends IGKHtmlItem
implements IIGKHtmlUriItem
{
	private $m_uri;

	public function getUri(){ return $this->m_uri; }
	public function setUri($v) { $this->m_uri = $v; return $this;}

	public function View(){
		$this->ClearChilds();
        //$uri = $this->m_uri;
		$c = $this->Add("div");
// $c->Content = <<<EOF
// <iframe src="http://www.facebook.com/plugins/like.php?href={$uri}&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
// EOF;
$href = htmlentities($this->m_uri);
$c->Content = <<<EOF
<iframe src="//www.facebook.com/plugins/like.php?href={$href}&layout=button_count&action=like" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:65px;" allowTransparency="true"></iframe>
EOF;


	}
	protected function loadingComplete(){
		parent::loadingComplete();
	}
	public function __construct()
	{
		parent::__construct("div");
		$this->m_uri = "https://www.facebook.com/IGKDEV";
	}
}
?>