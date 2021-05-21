<?php
/*
controller to load inistialization script on document
*/
abstract class IGKJSScriptInitCtrl extends IGKCtrlTypeBase
{
	private $m_script;
	public static function SupportMultiple(){//return false to indicate that an element of this type must be unique
		return false;
	}
	public function getCanAddChild(){
		return false;
	}
	public function getCanEditDataBase()
	{
		return false;
	}
	public function getCanEditDataTableInfo(){
		return false;
	}
	protected function initTargetNode()
	{
		$n =  IGKHtmlItem::CreateWebNode("script");
		$this->m_script = $n;
		return null;
	}
	public function getIsVisible(){
		return !igk_is_confpagefolder();
	}
	public function pageFolderChanged()
	{
		$this->View();
	}
	public function View()
	{
		if ($this->IsVisible)
		{
			$this->m_script->Content  ="";
			$v = $this->getArticleContent("default.js");
			$this->m_script->Content =$v;
			igk_html_add($this->m_script, $this->App->doc->body,10000);
		}
		else {
			igk_html_rm($this->m_script);
		}
	}
}
?>