<?php
class IGKProcessDocument extends IGKNonVisibleControllerBase
{
	public function getIsSystemController(){
		return true;
	}
	public function getName(){
		return "process_script";
	}
	protected function InitComplete(){
		parent::InitComplete();
	}
	public function __construct(){
		parent::__construct();
	}
	public function processFile($file=null)
	{
		$file = $file == null? base64_decode(igk_getr("file")): $file;
		if (file_exists($file))
		{
			//
			$str = IGKIO::ReadAllText($file);

			$out = preg_replace("/^\s*\/\/\/@@@(?P<value>(.)+)$/i", '///<summary>${1}</summary>', $str);

			igk_io_save_file_as_utf8(dirname(__FILE__)."/out.php_t", $out, true);

			$doc  = $this->ProcessDoc($str);
			$doc->RenderAJX();
		}
		igk_exit();
	}
	public function processDoc($text){

		$v_tab = array();
		//$v_c = preg_match_all("/\s*\/\/\/\<summary\>(?P<value>(.)+)\<\/summary\>/im", $text, $v_tab);
		$v_c = preg_match_all("/^\s*\/\/\/(?P<value>(.)+)$/im", $text, $v_tab);

		$v_d =  IGKHtmlItem::CreateWebNode("div");
		$v_n =  IGKHtmlItem::CreateWebNode("div");
		if ($v_c > 0){
		for($i = 0; $i < $v_c; $i++)
		{
			$p = $v_d->add("p");
			$p->add("span")->Content = $i;
			$p->add("li")->Content = $v_tab["value"][$i];
			$v_n->Content .= $v_tab["value"][$i];
		}
		}
		else{
			$v_d->Content = "no matched document";
		}
		return $v_n;
	}

}
?>