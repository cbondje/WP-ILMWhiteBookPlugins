<?php
define("IGK_GKDS_LAYERDOCUMENT", "LayerDocument");

final class IGKGkdsFile extends IGKObject
{
	private $m_source;
	private $m_gd;
	private $m_document;

	public function getGD(){return $this->m_gd; }
	public function getDocument(){return $this->m_document; }
	private function __construct(){
	}
	public static function ParseToGD($filename, $index=0){
		if (!defined("IGK_GD_SUPPORT") || !file_exists($filename))
			return null;

		$doc = IGKHtmlReader::LoadFile($filename);
		if ($doc == null)
			return null;
		$t = igk_getv($doc->getElementsByTagName(IGK_GKDS_LAYERDOCUMENT) , $index);
		if ($t == null)
			return null;
		$f = new IGKGkdsFile();
		$f->m_document = $t;
		$f->m_gd = IGKGD::Create($t["Width"], $t["Height"]);
		$f->m_gd->Clearf("white");
		$f->_visit();

		return $f;
	}
	private function _restore(){
	}
	private function _save(){
	}
	private function _visit(){
		foreach($this->m_document->Childs as  $v){
			$m = "Visit".$v->TagName;
			if (method_exists(__CLASS__, $m))
				$this->$m($v);
		}
	}
	public function VisitLayer($layer){
		foreach($layer->Childs as $v){
			$m = "Visit".$v->TagName;
			if (method_exists(__CLASS__, $m))
				$this->$m($v);
		}
	}
	public function VisitCircle($i){
		$c = IGKVector2f::FromString($i["Center"]);
		$t = explode(" ", $i["Radius"]);
		$r = 0;

		if (count($t) == 1)
		{
			$r = IGKVector2f::FromString($i["Radius"]);
		}
		else {
			$r = IGKVector2f::FromString($t[0]);
		}
		$this->GD->FillEllipse(IGKColorf::FromString("red")->toByte(),  $c, $r);
		$this->GD->DrawEllipse(IGKColorf::FromString("black"),  $c, $r);
	}
	public function RenderPicture(){
		header("Content-Type: image/png");
		$this->GD->Render();
	}
	public function Dispose(){
		$this->GD->Dispose();
		unset($this->m_gd);
	}
}