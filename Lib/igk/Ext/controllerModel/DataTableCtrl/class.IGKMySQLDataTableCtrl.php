<?php
/*datatable controller
*/
abstract class IGKMySqlDataTableCtrl extends IGKCtrlTypeBase
{
	// public static function GetAdditionalConfigInfo()
	// {
		// return array(
			// "clDataTableName"
		// );
	// }
	public static function GetNonConfigurableConfigInfo(){
		return array(
			"clDataAdapterName",
			"clVisible",
			"clVisiblePages",
			"clTargetNodeIndex",
			"clParentCtrl"
		);
	}
	public function getIsVisible(){
		return false;
	}

	public function getDataAdapterName(){
		return IGK_MYSQL_DATAADAPTER;
	}
	public function getName(){
		return parent::getName();
	}
	public function getDataAdapterTableName(){
		return igk_getv($this->Configs , "clDataTableName", $this->getName());
	}
}
?>