<?php

//<summary> utility bootstrap build form </summary>
final class IGKHtmlBootstrapFormBuilder extends IGKFormBuilderEngine
 implements IIGKFormBuilderEngine
{
	// var $frm ;
	// private $m_groups;
	public function __construct($frm){
		$this->frm = $frm;
	}
	// public function getView(){

		// $c = null;
		// if ($this->m_groups){
			// $c = $this->m_groups;
		// }
		// else
			// $c = $this->frm;
		// return $c;
	// }
	public function addGroup(){
		$this->m_groups = $this->frm->addDiv();
		$this->m_groups["class"]="form-group";
		return $this;
	}
	public function addLabel($id, $class=null , $text=null){
		$c = $this->getView();
		$t = $c->addLabel($id, $id);
		if ($text)
			$t->Content = text;
		$t["class"] = $class;
		return $this;
	}
	public function addControl($id, $type="text", $style=null, $attribs=null){
		$c = $this->getView();
		$t = $c->addInput($id, $type);
		$t["class"] = "-cltext form-control ".$style;
		return $this;
	}
	public function addButton($id, $type="button", $text=null, $style=null){
		$c = $this->getView();
		$t = $c->addInput($id, $type, $text);
		$t["class"] = "btn btn-default igk-btn igk-btn-default ".$style;
		return $this;
	}
	public function addTextarea($id, $style=null){
		$c = $this->getView();
		$t = $c->add("textarea");
		$t["id"] = $t["name"] = $id;
		$t["class"] = "form-control ".$style;
		return $this;
	}
	public function addLabelControl($id, $value=null,  $type="text", $style=null)
	{
		return $this->addLabel($id)
		->addControl($id,$type, $style, $value);
	}
	public function addLabelTextarea($id, $style="")
	{
		return $this->addLabel($id)
		->addTextarea($id, $style);
	}
	public function addLabelSelect($id, $data, $filter=null){
		$this->addLabel($id);
		$c = $this->getView()->addSelect($id);
		$c["class"]="form-control -clselect";
		if ($data){
			$fobj = ["selected"=>0];
			if ($filter){
				$fobj["value"] = igk_getv($filter, "value", "clId");
				$fobj["key"] = igk_getv($filter, "key", "clName");

			}

			foreach($data->Rows as $k=>$v){
				$op = $c->add("option");
				$tv = 0;
				if ($filter){
					$tv = igk_getv($v,$fobj["value"]);
					$op["value"] = $tv;
					$op->Content = igk_getv($v,$fobj["key"]);



				}else{
					$tv = $k;
					$op["value"] = $k;
					$op->Content = $v;

				}

				if ($tv == $fobj["selected"]){
						$op["selected"]=1;
				}
			}
		}
		return $this;

	}

}



igk_reg_form_builder_engine("bootstrap", IGKHtmlBootstrapFormBuilder::class);