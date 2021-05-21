<?php

final class IGKBillingEntry
{
	var $clId;
	var $clUId;
	var $clDate;
	var $clTotalAmount;

	public function __toString(){
		return "BillingEntry";
	}
}

final class IGKBillingDetailsEntry
{
	var $clId;
	var $clUId;
	var $clBillId;
	var $clRefId;
	var $clQte;
	var $clAmount;

	public function __toString(){
		return "IGKBillingDetailsEntry";
	}
}
final class IGKBillingDetailsCtrl extends IGKCtrlNonAtomicTypeBase //first non atomic data
{
	public function getBilling()
	{
		throw new Exception("getBilling");
	}
	public function __construct()
	{
		parent::__construct();
	}
	protected function getDBConfigFile()
	{
		return null;
	}
	protected function getConfigFile()
	{
		return null;
	}
	protected function InitComplete()
	{
		parent::InitComplete();
	}
	public function getDataAdapterName(){
		if ($this->getBilling())
			return igk_getv($this->Billing, "DataAdapterName");
		return parent::getDataAdapterName();
	}
	public function getDataTableName()
	{
		return igk_getv($this->getBilling(), "DataTableName")."_details";
	}
	public function getDataTableInfo()
	{
	return array(
		new IGKDbColumnInfo(array(IGK_FD_NAME=>IGK_FD_ID, IGK_FD_TYPE=>"Int","clAutoIncrement"=>true,IGK_FD_TYPELEN=>10, "clIsUnique"=>true, "clIsPrimary"=>true)),
		new IGKDbColumnInfo(array(IGK_FD_NAME=>"clBillId", IGK_FD_TYPE=>"Int", IGK_FD_TYPELEN=>10)),
		new IGKDbColumnInfo(array(IGK_FD_NAME=>"clUId", IGK_FD_TYPE=>"Int", IGK_FD_TYPELEN=>10)),
		new IGKDbColumnInfo(array(IGK_FD_NAME=>"clRefId", IGK_FD_TYPE=>"VarChar", IGK_FD_TYPELEN=>30)),
		new IGKDbColumnInfo(array(IGK_FD_NAME=>"clQte", IGK_FD_TYPE=>"Int", IGK_FD_TYPELEN=>10)),
		new IGKDbColumnInfo(array(IGK_FD_NAME=>"clAmount", IGK_FD_TYPE=>"Float", IGK_FD_TYPELEN=>10)),
		);
	}

}
abstract class IGKBillingCtrl extends IGKCtrlTypeBase
{
	private $m_billingDetails;

	protected function getDBConfigFile()
	{
		return igk_io_getdbconf_file(dirname(__FILE__));
	}
	protected function getConfigFile()
	{
		return igk_io_getconf_file(dirname(__FILE__));
	}
	protected function InitComplete()
	{
		parent::InitComplete();
		//register a billing
		$this->app->getControllerManager()->register("Billing", $this);
		$this->app->getControllerManager()->register("BillingDetails", new IGKBillingDetailsCtrl());
	}
	public function store($caddyInfo)
	{
		$u = $this->app->Session->User;
		if ( ($u==null) || ($caddyInfo==null) || igk_count($caddyInfo)==0)
			return;

		$e = new IGKBillingEntry ();

		$e->clUId = $u->clId;
		$e->clDate = igk_mysql_datetime_now();
		$m = 0.0;
		$d = igk_getctrl("igkbillingdetailsctrl");
		$tk = array();
		foreach($caddyInfo as  $v)
		{

			$m += $v->getAmount();
			$h = new IGKBillingDetailsEntry();
			$h->clAmount = $v->getAmount();
			$h->clQte = $v->clQte;
			$h->clUId = $u->clId;
			$h->clRefId = $v->clRef;
			$tk[] = $h;
		}
		$e->clTotalAmount = $m;
		if ($this->insert($e)){
			foreach($tk as $v)
			{
				$v->clBillId = $e->clId;
				$d->insert($v);
			}
		}

		$this->getBillingPDF();
	}
	public function getBillingPDF(){
		$u = $this->app->Session->User;
		if ($u == null)return;

		$pdf = new IGKPDF();

		$pdf->Render();
		igk_exit();
	}
}

//mark the class as a system controller
igk_sys_regSysController("IGKBillingDetailsCtrl");
?>