<?php

/*
file : class.IGKPDFViewerCtrl.php
author:C.A.D BONDJE DOUE
*/


igk_bind_attribute("class","IGKPDFViewerCtrl", new IGKControllerTypeAttribute());
/*
represent a IGKPDFViewerCtrl
*/

final class IGKHtmlPdfViewNode extends IGKHtmlItem
{
	private $m_ctrl;
	public function __construct($ctrl)
	{
		parent::__construct("iframe");
		$this->m_ctrl = $ctrl;
		$this["class"]="noborder dispb fitw fith cliframe";

	}
	public function render($xmloption=null)
	{
		$uri = $this->m_ctrl->getUri("render_pdf_ajx");
		$this["src"] = igk_io_baseuri().$uri;
		return parent::Render($xmloption);
	}
	public function innerHTML (& $xmloption =null)
	{

			$o = parent::innerHTML($xmloption);
			//$c  =  IGKHtmlItem::CreateWebNode("script");


			// $c->Content = <<<EOF
// (function(p){ var q = \$igk(p).add('div'); window.igk.ajx.aget('{$uri}', null, new igk.ajx.targetResponse(q).update);})(window.igk.getParentScript());
// EOF;
			// $o .= $c->Render();
			return $o;
	}
}
abstract class IGKPDFViewerCtrl extends IGKCtrlTypeBase
{
	private $m_pdf;
	public function __construct(){
		parent::__construct();
	}
	protected function InitComplete(){
		parent::InitComplete();
	}
	public function getCanAddChild(){
		return false;
	}
	protected function initTargetNode(){
		$n = parent::initTargetNode();
		$pdf = new IGKHtmlPdfViewNode($this);
		$n->add(	$pdf);
		$this->m_pdf = $pdf;

		return $n;
	}
	public function View(){
		if (!$this->IsVisible)
		{
			igk_html_rm($this->TargetNode);
		}
	}
	public function render_pdf_ajx()
	{
		$pdf = new IGKPdf();
		include(dirname(__FILE__)."/".IGK_DATA_FOLDER."/temp.iwpdfsrc");

		igk_wl($pdf->Render());
	}
}
?>