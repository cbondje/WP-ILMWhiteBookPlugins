<?php

final class IGKCaddyInfo
{
	var $clId; //current id of the caddy
	var $clRef;	//product ref
	var $clCaddId; //
	var $clUId; //
	var $clTitle; //title of the product
	var $clDescription; //title of product
	var $clUnitPrice; //title of
	var $clTva;
	var $clQte;

	public function getAmount(){
		return ($this->clUnitPrice * $this->clQte ) * (1 + $this->clTva/100.0);
	}
	public function getTvaAmount(){
		return ($this->clUnitPrice * $this->clQte ) * (1 + $this->clTva/100.0);
	}
	public function Copy($e)
	{
		foreach($e as $k=>$v)
		{
			$this->$k = $v;
		}
	}
	public function __toString(){
		return "caddy_info";
	}
}


abstract class IGKCaddyCtrl extends IGKCtrlTypeBase
{
	var $m_cadid; //caddy id
	var $m_caddyinfo;

	public function getCaddyInfo(){
		return $this->m_caddyinfo;
	}
	public static function GetAdditionalConfigInfo()
	{
		return null;
	}
	public function getDataAdapterName(){
		return "MYSQL";
	}
	public function getDBEntries()
	{
		return $this->selectAndWhere( array(
			"clCaddId"=>$this->m_cadid,
		));
	}
	public function __updateCaddyInfo()
	{//used to load caddy from current cad id
		$s = $this->getDBEntries();

		$this->m_caddyinfo = array();
		if ($s!=null){
			foreach($s->Rows as  $v)
			{
				$e = new IGKCaddyInfo();
				$e->Copy($v);
				$this->m_caddyinfo[] = $e;
			}
		}
	}


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

		$this->m_cadid =($this->m_cadid) ?  $this->m_cadid : igk_new_id();
	// 	igk_sys_uri_regpage("showcaddy");
		$this->app->ControllerManager->register("Caddy", $this);
		/*
		$this->caddy_addproduct();
		$this->__updateCaddyInfo();

		$this->app->Session->addUserChangedEvent($this, "__userChanged");

		igk_getctrl("igkusersctrl")->connect("bondje.doue@gmail.com", "test123");
		//$this->caddy_clear();
		$this->caddy_addproduct();
		$this->caddy_addproduct();
		$this->caddy_addproduct();


		igk_wln($this->caddy_render())*/
	}
	private function __reloadCaddy()
	{
		$u = $this->getUser();
		$e =  $this->selectAndWhere( array(
			"clUId"=>$u->clId
			));
		$this->m_caddyinfo = array();
		foreach($e->Rows as  $v)
		{
			$r = new IGKCaddyInfo();
			$r->Copy($v);
			$this->m_caddyinfo[] = $r;
		}
	}
	public function __userChanged()
	{
		// if use haven't a caddy associate the current caddy to the connected users
		$u = $this->app->Session->User;
		if ($u !=null)
		{
			$e =  $this->selectAndWhere( array(
			"clUId"=>$u->clId
			));

				if (($e==null) || ($e->RowCount == 0))
				{
					//no previous caddy for this $user
					$this->__attachCaddyInfoToUser($u);

				}
				else{
					$this->m_cadid = igk_new_id();
					$this->__attachCaddyInfoToUser($u, false);
					foreach($e->Rows as $v)
					{
						$r = new IGKCaddyInfo();
						$r->Copy($v);
						$r->clCaddId = $this->m_cadid ;
						$this->m_caddyinfo[] = $r;
					}
					$this->update($this->m_caddyinfo);
				}
		}

	}
	private function __attachCaddyInfoToUser($u, $update = true)
	{
		foreach($this->m_caddyinfo as  $v)
		{
			$v->clCaddId = $this->m_cadid;
			$v->clUId = $u->clId;
		}
		//update caddy info
		if ($update)
		$this->update($this->m_caddyinfo);
	}
	public function View()
	{
		parent::View();

	}
	public function getIsVisible(){
		return true;
	}

	public function getCanAddChild() {
		return false;
	}

	public function cadd_initusercaddy()
	{//used to initialize or retreive a user caddy

	}

	//add product to caddy.
	public function caddy_addproduct($obj=null){

		$obj = ($obj==null)? igk_get_robj() :$obj;


		$s =  $this->selectAndWhere( array(
			"clRef"=>$obj->clRef
			));

		if ($s && $s->RowCount>0)
		{
			foreach($s->Rows as  $v)
			{
				$v->clQte += igk_getv($obj, "clQte",0);
				$this->update($v);
			}
		}
		else{
			$e = new  IGKCaddyInfo();
			$e->clId = null;//"NULL";
			$e->clCaddId = $this->m_cadid;
			$e->clUId  = ($this->app->Session->User !=null) ? $this->app->Session->User->clId: 0;
			$e->clQte = igk_getv($obj, "clQte",12);
			$e->clTva = igk_getv($obj, "clTva",21);
			$e->clUnitPrice = igk_getv($obj, "clUnitPrice",12);
			$e->clTitle = igk_getv($obj, "clTitle", "Produit #");
			$e->clRef = igk_getv($obj, "clRef", "XXXREF");
			$e->clDescription = igk_getv($obj, "clDescription", "Descriptioin du produit");
			$this->insert(
				$e
			);
		}
		$this->__updateCaddyInfo();
		return true;

	}
	//clear caddy
	public function caddy_clear_ajx(){

		$this->caddy_clear();
		igk_wl($this->caddy_render());
	}
	public function caddy_clear()
	{
		$this->delete($this->m_caddyinfo);
		$this->m_caddyinfo = array();
	}

	public function caddy_totalamout()
	{
		$amount = 0.0;
			if ($this->m_caddyinfo)
			{
		foreach($this->m_caddyinfo as  $v)
		{
			$amount += $v->getAmount();
		}}
		return $amount;
	}
	public function caddy_validate()
	{
		if (igk_count($this->m_caddyinfo )> 0)
		{
			$billing = $this->app->ControllerManager->getRegCtrl("Billing");

			if ($billing)
			{
				$billing->store($this->m_caddyinfo);
				$this->caddy_clear();
				$this->caddy_render();
				igk_notifyctrl()->addMsg("Caddy validated");
			}
			else {
				igk_notifyctrl()->addErrorr("no billing in this system");

			}
		}
		igk_notifyctrl()->NotifyHost = $this->TargetNode;
		igk_navtocurrent($this->CurrentPage);

	}
	public function caddy_validate_ajx()
	{

		$this->caddy_validate();
		$t = $this->TargetNode;
		$this->caddy_render();
		$script =  IGKHtmlItem::CreateWebNode("script");
		$script->Content = "alert(IGK.getParentScript().innerHTML); ";

		igk_html_add($script , $t);
		$t->RenderAJX();
	}
	public function caddy_remove()
	{
		$n = igk_getr("n");
		$this->delete((object)array("clId"=>$n ));
	}
	public function caddy_store()
	{
		$this->update($this->m_caddyinfo);
	}
	//render the current caddy to user
	public function caddy_render(){
		$t = $this->TargetNode;
		$t->ClearChilds();
		$t = $t->addDiv();

		$tab = $t->add("table");
		$tab["class"] = "fitw caddy_table";
		$tr = $tab->add("tr");
		IGKHtmlUtils::AddToggleAllCheckboxTh($tr);
		$tr->add("th")->Content = R::ngets("title.caddy.ref");
		$tr->add("th")->Content = R::ngets("title.caddy.title");
		$tr->add("th")->Content = R::ngets("title.caddy.desc");
		$tr->add("th")->Content = R::ngets("title.caddy.unitprice");
		$tr->add("th")->Content = R::ngets("title.caddy.qte");
		$tr->add("th")->Content = R::ngets("title.caddy.tva");
		$tr->add("th")->Content = R::ngets("title.caddy.totalamount");


		$tr->add("th")->Content = IGK_HTML_SPACE;
		$qte = 0;
		if ($this->m_caddyinfo)
		foreach($this->m_caddyinfo as $v)
		{
			$tr = $tab->add("tr");
			$tr->add("td")->addInput("", "checkbox");
			$tr->add("td")->Content = $v->clRef;
			$tr->add("td")->Content = $v->clTitle;
			$tr->add("td")->Content = $v->clDescription;
			$tr->add("td", array("class"=>"alignr"))->Content = $v->clUnitPrice;
			$tr->add("td", array("class"=>"alignr"))->Content = $v->clQte;
			$tr->add("td", array("class"=>"alignr"))->Content = $v->clTva;
			$tr->add("td", array("class"=>"alignr") )->Content = $v->getAmount();


			IGKHtmlUtils::AddImgLnk($tr->add("td"), $this->getUri("caddy_remove&n=".$v->clId), "drop");

			$qte += $v->clQte;
		}
		$tr = $tab->add("tr");
		$tr->add("td", array("colspan"=>5))->Content = R::ngets("lb.TotalAmount");
		$tr->add("td", array("class"=>"alignr"))->Content = $qte;
		$tr->add("td")->Content = IGK_HTML_SPACE;
		$tr->add("td", array("class"=>"alignr"))->Content = $this->caddy_totalamout();
		$tr->add("td")->Content = IGK_HTML_SPACE;

		igk_html_toggle_class($tab);

		IGKHtmlUtils::AddBtnLnk($t,R::ngets("btn.clear"), igk_js_ajx_aposturi( $this->getUri("caddy_clear_ajx"), $t["id"]));
		IGKHtmlUtils::AddBtnLnk($t,R::ngets("btn.validate"),  $this->getUri("caddy_validate"));
		return $this->TargetNode->Render();
	}
}
?>