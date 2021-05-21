<?php
/*
*file: class.IGKArticleViewerCtrl.php
*author: C.A.D. Bondje doue
*script :
*/

igk_js_bind_script_folder(dirname(__FILE__)."/".IGK_SCRIPT_FOLDER);

abstract class IGKArticleViewerCtrl extends IGKCtrlTypeBase
{

	protected function InitComplete(){
		parent::InitComplete();
	}
	public function View(){
		$t = $this->TargetNode;
		$t->ClearChilds();
		if ($this->isVisible)
		{
			$o = $this->getAllArticlesByCurrentLang();
			$i = 0;
			foreach($o as $k)
			{
				igk_html_article($this, basename($k), $t->addDiv(array("class"=>"igk-article-viewer-box node_".$i)));
				$i++;
			}
			$t->addScript()->Content = "window.igk.winui.articleviewer.init();";
		}
		else{
			$t->TargetNode->addDiv()->Content = "No target item";
		}
	}

}

//article viewer extension function

function igk_js_av_bind_initarticle($classname, $updatesize=true, $initanimate=true){//article viewer
	$s =  IGKHtmlItem::CreateWebNode("script");
	$r  = igk_parsebool($updatesize);
	$h = igk_parsebool($initanimate);
	$s->Content = <<<EOF
window.igk.winui.articleviewer.initViewBox(window.igk.getParentScript(), '{$classname}', {$r},{$h});
EOF;
	return $s->Render();
}

?>