<?php

//adding function
function igk_mail_get_mailinfo($i)
{
	$b = new StdClass();
	$m = $i;
	$n = $i;
	$tab = array();
	if (preg_match_all("/(?P<name>(.)+)\<(?P<mail>([^\<\>]+))\>/i", $i,  $tab))
	{
		$m = $tab["mail"][0];
		$n = $tab["name"][0];
	}
	$b->clEmail = $m;
	$b->clDisplayName = $n;

	return $b;
}




class IGKHtmlMailboxNodeItem extends IGKHtmlComponentNodeItem{
	private $m_users; //list of user attached to this mail box

	private $m_cuser; //current user;
	private $m_imap;
	private $m_error; //rerror node;

	public function __construct(){
		parent::__construct("div");
		$this->m_users = array();
	}
	public function addUser($server, $port, $options,  $login, $pwd){

		$d = new StdClass();
		$d->clPort = $port;
		$d->clLogin = $login;
		$d->clServer = $server;
		$d->clPwd = $pwd;
		$d->clOptions = $options;
		$this->m_users[$login] = $d;
	}

	public function initView(){
		$this->ClearChilds();

		$r = $this->addRow();
		$dv = $r->addCol()->addDiv();
	    $dv->addTitleLevel(4)->Content = "Mailbox";

		$r = $this->addRow();
		$r->addMenuBar();

		$r = $this->addRow();
		$dv  = $r->addCol()->setClass("igk-col msg-bc posab")->setStyle("width: 300px;")->addDiv()->setClass("msgb-b");

		$rd = $dv->addDiv()->addAccordeon();
		foreach($this->m_users as $k=>$v){
			$ul = igk_createNode("ul");
			$rd->addPanel($k, $ul, true);

			$d = $this->getFolders($v);
			$ul->add("li")->Content = "Count: <span class=\"badge\">".igk_count($d)."</span>";
			foreach($d as $m=>$n){
				$ul->add("li")->addAJXA($this->getComponentUri("mbx_vmsg&u=".$k."&q=".base64_encode($n->clDisplay)))->Content = $n->clDisplay;
			}
		}
		$dv = $r->addCol()->setClass("igk-col msg-zc")->setStyle("margin-left: 300px")->addDiv();
		$dv->setClass("msg-z");
		$dv->addDiv()->Content = "Well done your mail box is correctly configured";

		$dv = $r->addCol()->addDiv();
	}

	private function connect($i, $link = null){
		$host = $i->clServer.":".$i->clPort;
		$proto = $i->clOptions;
		if ($link ==null)
		{
			$link = "{".$host."/$proto}INBOX";
		}else{
			$link = "{".$host."/$proto}".trim($link);
		}
		$imap = @imap_open($link,$i->clLogin, $i->clPwd);
		if (!$imap){
			$this->m_error = igk_createNode("div");
			$this->m_error->addDiv()->Content = "Error";
			$this->m_error->addDiv()->Content = $link;
			$this->m_error->addDiv()->Content = imap_errors();
		}
		return $imap;
	}
	private function close ($imap=null){
		if ($imap==null)
		{
			$imap = $this->m_imap;
			imap_close($imap);
			$this->m_imap = null;
		}
		else
			imap_close($imap);
	}
	private function getFolders($u){
		$o = array();
			$imap = $this->connect($u);
			if ($imap){
			$f = imap_listmailbox($imap, "{".$u->clServer.":".$u->clPort."}", "*");
			// $MC = imap_check($imap);
			// $result = imap_fetch_overview($imap,"1:{$MC->Nmsgs}",0);

			foreach($f as  $v){
				$o[] = (object)array("clDisplay"=>preg_replace("/\{(.)+\}/i", "", $v), "clLink"=>$v);
			}

			$this->close($imap);
			}
		return $o;
	}
	private function getMessage($u, $link){
		$o = array();
		$imap = $this->connect($u, $link);
		if ($imap){
			//$f = imap_listmailbox($imap, "{".$u->clServer.":".$u->clPort."}", "*");
			$MC = imap_check($imap);
			if ($MC->Nmsgs>0)
			{
				$f = imap_fetch_overview($imap,"1:{$MC->Nmsgs}",0);

				foreach($f as $v){
					$o[] = (object)array("clMessage"=>$v);
				}
			}
			$this->close($imap);
		}
		return $o;
	}

	///------------------------------------------------------
	///mail function
	///------------------------------------------------------

	public function mbx_rm(){
	}
	public function mbx_vmsg($u=null,$q=null){

		if ($u==null){
		$tab = igk_getquery_args(base64_decode(igk_getr("q")));

		$q = base64_decode(igk_getv($tab, "q"));
		$u = igk_getv($this->m_users, igk_getv($tab, "u"));
		}

		if (!$q || !$u)
		{
			igk_die("attribute not defined");
		}

		//igk_wln($u);
		$msg = $this->getMessage($u, $q);
		//igk_wln("done ");
		
		$d = igk_createNode("div");
		$d->setClass("msg-z");
		if (igk_count($msg)==0)
		{
			$d->addDiv()->Content = R::ngets("msmg.mailbox.boxisempty_1", $q);
		}
		else{
			$p = igk_createNode("div");
			$p->setClass("msg");
			$row = $p->addRow();
			//$p->loadExpression("[func:igk_wln_ob_get(\$row)]<div>{\$row->clSubject}</div>");
			$row->loadExpression("<div class=\"igk-col igk-col-5-1\">{\$row->clSubject}</div>");
			$row->addCol()->setClass("igk-col-5-1")->Content = "{\$row->clFrom->clEmail}";
			$row->addCol()->setClass("igk-col-5-1")->Content = "t";
			$row->addCol()->setClass("igk-col-5-1")->Content = "t";
			$row->addCol()->setClass("igk-col-5-1")->Content = "{\$row->clUDate}";

			foreach($msg as  $v){

				//igk_log_write_i("date", igk_wln_ob_get($v->clMessage));
				$v = $v->clMessage;
				$m = new StdClass();
				$m->clId = igk_getv($v, "msgno", 0);
				$m->clSubject = igk_getv($v, "subject", R::gets("NotDefine"));
				$m->clFrom = igk_mail_get_mailinfo( igk_getv($v, "from", R::gets("NotDefine")));
				$m->clDate = igk_getv($v, "date", R::gets("NotDefine"));

				$m->clUDate = date("Y-d-m_h:i:s", igk_getv($v, "udate", 0));
				$m->clIsSeen = igk_getv($v, "seen", 0);
				$m->clIsDraft = igk_getv($v, "draft", 0);
				$m->clIsReply = igk_getv($v, "answered", 0);

				$dv = $d->addDiv();
				igk_html_bind_node($this->Ctrl,
				$p,
				$dv->addDiv(),
				$m
				);
				$dv->addDiv()->setClass("info-bx");
			}
		}
		igk_ajx_replace_node($d, ".msg-z" , "#!\mailbox=".$q);
		igk_exit();
	}
} 