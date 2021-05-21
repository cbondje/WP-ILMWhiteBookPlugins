<?php
//controller code class declaration
//file is a part of the controller tab list
abstract class DragDropZoneCtrl extends IGKCtrlTypeBase
{

	public function getCanAddChild(){
		return false;
	}
	public function View()
	{
		parent::View();
	}
	protected function initTargetNode()
	{
		$t = new DragDropZoneItem();
		$t["id"]="dropzone";
		return $t;
	}
}

class DragDropZoneItem extends IGKHtmlItem
{
	private $m_scriptNode;
	public function __construct()
	{
		parent::__construct("div");
		$this->m_scriptNode =  IGKHtmlItem::CreateWebNode("script");
	}
	public function render($xmloption=null)
	{
		$this->m_scriptNode->Content = <<<EOF
igk.winui.dragdrop.init(IGK.getParentScript());
EOF;
		igk_html_add($this->m_scriptNode, $this);
		$o = parent::Render($xmloption);
		return $o;

	}

}
?>