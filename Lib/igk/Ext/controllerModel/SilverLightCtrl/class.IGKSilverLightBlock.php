<?php

abstract class IGKSilverLightBlockCtrl extends IGKCtrlTypeBase
{


public static function GetAdditionalConfigInfo()
	{
		return array(
		"clXabUri"=>new IGKAdditionCtrlInfo("text", "file.xap"),
		"clPrimaryWidth"=>new IGKAdditionCtrlInfo("text", "400px"),
		"clPrimaryHeight"=>new IGKAdditionCtrlInfo("text", "300px")
		);
	}
public function getCanAddChild(){
	return false;
}
protected function initTargetNode()
{
	$n = parent::initTargetNode();
	return $n;
}
public function View(){

$s = igk_html_uri(igk_io_baseuri()."/".$this->Configs->clXabUri);
$w  = $this->Configs->clPrimaryWidth;
$h  = $this->Configs->clPrimaryHeight;
$t  = IGKHTMLReader::Load(
<<<OEF
<object id="SilverlightPlugin1" width="{$w}" height="{$h}"
    data="data:application/x-silverlight-2,"
    type="application/x-silverlight-2" >
    <param name="source" value="{$s}"/>

    <!-- Display installation image. -->
    <a href="http://go.microsoft.com/fwlink/?LinkID=149156&v=4.0.60310.0"
        style="text-decoration: none;">
        <img src="http://go.microsoft.com/fwlink/?LinkId=161376"
            alt="Get Microsoft Silverlight"
            style="border-style: none"/>
    </a>
</object>
OEF
);
$this->TargetNode->ClearChilds();
$this->TargetNode->Add($t);
if (!$this->IsVisible)
{
	igk_html_rm($this->TargetNode);
}

}

protected  function _showChild($targetnode=null){
//no target
}
}
?>