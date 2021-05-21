<?php
/*
file: class.IGKNavigationMenuBar

*/
///<summary>represent a navigation menu bar</summary>
abstract class IGKNavigationMenuBarCtrl extends IGKCtrlTypeBase
{
	protected function InitComplete(){
		parent::InitComplete();
	}

	public static function GetAdditionalConfigInfo()
	{
		return array("clSpeed"=>new IGKAdditionCtrlInfo("text", 1000));
	}
	public function View(){
		if ($this->IsVisible)
		{
			$this->TargetNode->ClearChilds();
			extract($this->getSystemVars());
			$ul =$t->add("ul");
			$v_dummy =  IGKHtmlItem::CreateWebNode("dummy");
			$v_output = igk_html_article($this, "default", $v_dummy);

			foreach($v_dummy->getElementsByTagName("a") as  $v)
			{
				$ul->add("li")->add($v);
			}
			if ($v_output)
			{
				igk_html_article_options($this, $this->TargetNode, $this->getArticle("default"));
			}
			$script = $t->addScript();
			$script->Content = "window.igk.ctrl.bindPreloadDocument('navigation_menubar', function(){igk.winui.navigationbar.init(window.igk.getParentScript(),window.igk.ctrl.getCtrlById('".igk_get_defaultwebpagectrl()->Name."'),null);});";
			if ($this->TargetNode->HtmlItemParent == null)
			{
				igk_html_add($this->TargetNode, $this->App->Doc->body);
			}
		}
		else{
			igk_html_rm($this->TargetNode);
		}
	}
}
?>