<?php


///<summary> use to host template as a default page controller</summary>
abstract class IGKTemplateHostCtrl extends IGKDefaultPageController
implements IIGKUriActionRegistrableController
{
	public function LoadTemplate(){
		throw new Exception(__METHOD__. " Not Implement");
	}
}