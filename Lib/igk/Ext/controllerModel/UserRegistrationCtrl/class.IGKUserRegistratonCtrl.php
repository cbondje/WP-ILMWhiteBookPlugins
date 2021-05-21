<?php
abstract class IGKUserRegistrationCtrl extends IGKCtrlTypeBase
{

	public function register()
	{

	}

	public function subscribe(){
		extract($this->getSystemVars());

		$obj = igk_get_robj();
		igk_val_init();
		igk_val_check(IGKValidator::IsStringNullOrEmpty($obj->clEmail) ||
			!IGKValidator::IsEmail($obj->clEmail)
		, $obj, "clEmail", "email is Null or not valid");
		igk_val_check("IsStringNullOrEmpty", $obj, "clPwd", "password not define");
		igk_val_check(($obj->clPwd != $obj->clRePwd), $obj, "clRePwd,clPwd", "password don't match");

		if (igk_val_haserror())
		{
			igk_val_regParam($this, "subscribe");
			$this->View();
		}
		else{
			igk_val_unregParam($this, "subscribe");
			$this->getView("subscribemailresponse", true);
		}
		igk_val_unregParam($this, "subscribe");
	}

	public function InitEnvironment()
	{
		igk_io_save_file_as_utf8($this->_getViewFile("registration_mail"), <<<EOF
<?php
?>
EOF
);
igk_io_save_file_as_utf8($this->_getViewFile("confirmation_mail"), <<<EOF
<?php
?>
EOF
);
		igk_io_save_file_as_utf8($this->_getViewFile("subscribeform"), <<<EOF
		\$this->TargetNode->ClearChilds();
 igk_html_article(\$this , "default", \$this->TargetNode);

 \$e = \$this->getParam("subscribe:error");
 \$cb = \$this->getParam("subscribe:errorcibling");

 \$frm = \$t->addForm();
 \$frm["action"] = \$this->getUri("subscribe");
 if (\$e!=null)
 {

 \$div = \$frm->addDiv();
 \$div["class"] = "error";
 \$div->add(\$e);
 }

 \$div = \$frm->addDiv();
 \$li = \$div->addSLabelInput("clType","lb.particulier", "radio", "1");
 \$li["checked"]="checked";
 \$li = \$div->addSLabelInput("clType","lb.enterprise", "radio", "2");
 \$ul = \$frm->addDiv()->add("ul");

 \$ul->add("li", array("class"=>igk_val_cbcss(\$cb, "clEmail") ))->addSLabelInput("clEmail", "lb.Email");
 \$ul->add("li", array("class"=>igk_val_cbcss(\$cb, "clPwd").""))->addSLabelInput("clPwd",  "lb.Pwd" ,"password");
 \$ul->add("li", array("class"=>igk_val_cbcss(\$cb, "clRePwd").""))->addSLabelInput("clRePwd",  "lb.RePwd" ,"password");
 \$ul->add("li", array("class"=>""))->addInput("btn_submit", "submit", R::ngets("btn.register"));
EOF
);

	}
}
?>