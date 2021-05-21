<?php
///<summary>represent blog platform </summary>
abstract class IGKBlogCtrl extends IGKCtrlTypeBase
{
	public function __construct(){
		parent::__construct();
	}
	//
	public function getDataTableInfo(){
	}
	protected function InitComplete(){
		parent::InitComplete();
		$k = "blog(/:function(/:params+)?)?";
		igk_sys_ac_register($k, $this->getUri("evaluateUri"));
	}
	public function evaluateUri(){
		$inf = igk_sys_ac_getpatterninfo();
		$p = $inf->getParams();
		$c = igk_getv($p, "function");
		$p = igk_getv($p, "params");
		if (empty($c))
		{
			$this->renderDefaultDoc();
		}
		else{
			if (method_exists($this, $c))
			{
				if (is_array($p) == false)
					$p = array($p);
				call_user_func_array(array($this, $c), $p);
			}
			else{
				$this->renderError($c);
			}
		}
		igk_exit();
	}
	public function getcanAddChild(){
		return false;
	}
	public function renderError($c){
			//render error
			$d = new IGKHtmlDoc($this->App, true);
			$d->Title = "Blog Error";
			$div = $d->Body->add("div");
			$div->add("div", array("class"=>"igk-title"))->Content = R::ngets("Title.Error");
			$div->add("div", array("class"=>"igk-notify igk-notify-danger"))->Content = "No function $c found";
			$d->RenderAJX();
			unset($d);
	}
} 