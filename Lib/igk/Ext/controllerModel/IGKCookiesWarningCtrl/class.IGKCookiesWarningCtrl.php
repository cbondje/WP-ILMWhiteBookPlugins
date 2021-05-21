<?php
//cookies browser
abstract class IGKCookiesWarningCtrl  extends IGKCtrlTypeBase
{
	public function getCanAddChild(){return false;}
	protected function InitComplete()
	{
		parent::InitComplete();
		igk_js_load_script($this->App->Doc, dirname(__FILE__)."/".IGK_SCRIPT_FOLDER);
		$clname = ".".strtolower($this->Name);
		igk_css_regclass($clname, "{sys:posab,loct,fitw} overflow:hidden; min-height:32px; line-height:32px; text-align:center; vertical-align:middle; z-index:100; background-color:white;");
		igk_css_regclass($clname." span","line-height:1; font-size:0.8em;  display:inline-block; vertical-align:middle; ");
		igk_css_regclass($clname." .btn_close","[res:btn_close_warning]{sys:dispb,posab} top:50%; right:4px;margin-top:-12px; width:24px; height:24px;");
	}
	public function View()
	{
		if (!isset($_COOKIE["igk-app-cookieswarning-inform"]) && $this->IsVisible)
		{
			$this->TargetNode->ClearChilds();
			$t = $this->TargetNode;
			$t->add("span")->Content = R::ngets("msg.cookies.requirement");
			$d = $t->add("div", array("class"=>"btn_close"));
			$d->add("a", array("class"=>"fitw fith dispb", "onclick"=>"window.igk.ctrl.cookieswarning.close('".$this->TargetNode["id"]."'); return false;"))->Content = IGK_HTML_SPACE;

		}
		else{
			igk_html_rm($this->TargetNode);
		}
	}
	public function getIsVisible(){
		return parent::getIsVisible();
	}
}
?>