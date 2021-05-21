<?php
abstract class IGKPageViewCtrl extends IGKCtrlTypeBase
{

	/** @var IGKHtmlItem $m_viewZone */
	private $m_viewZone;
	public function getName(){return get_class($this);}
	public function getViewZone(){return $this->m_viewZone;}

	protected function InitComplete(){
		parent::InitComplete();
		//please enter your controller declaration complete here

	}
	public static function GetAdditionalConfigInfo()
	{
		return null;
	}
	//@@@ init target node
	protected function initTargetNode(){
		$node =  parent::initTargetNode();
		$this->m_viewZone = $node->addDiv();
		igk_css_regclass(".pageview", "{sys:dispib,alignl,alignt,fitw} max-width:1024px; padding:4px;");
		$this->m_viewZone["class"]="pageview";
		return $node;
	}
	public function getCanAddChild(){
		return true;
	}
	public function View()
	{
		if ($this->IsVisible)
		{
			 $n = $this->TargetNode;
			 $t = $this->m_viewZone;
			 if ($t !== null)
			 {
			 $this->setTargetNode($t);

			 $t->ClearChilds();
			//view article
			//---------------------------------
			$this->_showViewFile();
			$this->setTargetNode($n); //restore
			$this->_showChild();
			}
		}
		else{
			igk_html_rm($this->TargetNode);
		}
	}
	protected function _showChild($targetnode=null)
	{
		//maintain the view
		$t = $targetnode? $targetnode: $this->TargetNode;
		igk_html_add($this->m_viewZone,$t);

		if ($this->hasChild)
		{
			foreach($this->getChilds() as  $v)
			{
				if ($v->isVisible)
				{
					igk_html_add($v->TargetNode, $this->m_viewZone);
					$v->View();
				}
				else {
					igk_html_rm($v->TargetNode);
				}
			}
		}
	}
}
?>