<?php
///<summary>represent used to store common primary articles and templates</summary>
abstract class IGKAtriclesCtrlBase extends IGKCtrlTypeBase
{
	public function __construct(){
		parent::__construct();
	}
	///<summary>bind primary articles</summary>
	public function bindArticle($ctrl, $name, $target, $row){
		$f = $this->getArticle($name);
		if (file_exists($f)){
			igk_html_binddata($ctrl, $target, $f, $row);
		}
	}
}
?>