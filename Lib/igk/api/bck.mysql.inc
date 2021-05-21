<?php
//mysql utility 
function igk_api_mysql__doc($api, $f=null){
	$doc = igk_get_document("sysapp:mysql", true);
	return $doc;
}
function igk_api_mysql_help($api){

	$doc = igk_get_document("sysapp:mysql", true);
	$doc->Title = "MysQL Utility - [BALFAFON]";	
	
	$bbox = $doc->body->addBodyBox();
	$dv = $bbox->addDiv()->addContainer();
	
	$ul = $dv->addUl();
	
	$c = file_get_contents(__FILE__);
	$tab = array();
	
	// preg_match_all("/function\\s+igk_api_mysql_(?P<name>([a-z]|[_])([a-z0-9_]*))\\s*/i",$c, $tab);
	preg_match_all("/function\\s+igk_api_mysql_(?P<name>([a-z])([a-z0-9_]*))\\s*/i",$c, $tab);
	
	foreach($tab["name"] as $k=>$v){
		$ul->addLi()->add("a")->setAttribute("href",$api->getAppUri("mysql/".$v))->Content = $v;
	}
	
	$mi = $doc->body->addmenu()->setAttributes(array(
		"type"=>"context",
		"id"=>"action"
	))->addmenuitem();
	
	$mi["accesskey"] = 1; 
	$mi["label"] = "Aller à la ligne…BONJE" ;
	$mi["type"]="checkbox";
	
	// addObData($tab);
	// igk_wln($tab); 
	
	$doc->RenderAJX();
}
function igk_api_mysql_about($api){
	$doc = igk_api_mysql__doc($api);
	$doc->Title = "MySQL Utility - About - [BALAFON]";
	$bbox = $doc->body->addBodyBox();
	$dv = $bbox->addDiv()->addContainer();
	$dv->addDiv()->setClass("igk-title-4")->Content = "About";
	$ul = $dv->addDiv()->addUl();
	$ul->addLi()->Content = IGK_AUTHOR;
	$ul->addLi()->Content = "Version : 1.0";
	
	$doc->RenderAJX();
}
function igk_balafon_mysql_datatype($t){
	switch(strtolower($t)){
		case "int":
			return "Int";
		case "varchar":
			return "VARCHAR";
	}
	return strtoupper($t);
}
function igk_mysql_rename_table($api, $tbname, $ntbname){

	$mysql = igk_get_data_adapter(IGK_MYSQL_DATAADAPTER, true);
		if($mysql->connect()){	
	$tbname = igk_db_escape_string($tbname);
	$ntbname = igk_db_escape_string($ntbname);
	$mysql->sendQuery("RENAME TABLE `{$tbname}` TO `{$ntbname}`");
	$mysql->close();
	}
}
function igk_api_mysql_init_db($api){
	//igk_wln("initdb");
	if (igk_is_conf_connected()){		
		igk_getctrl(IGK_DB_CTRL)->initSDb(false, false);
	}
	else{
		igk_createNode("response")->setContent("You must be logged as administrator")->RenderXml();
	}		
	igk_exit();
}
///used to get relation </summary>
function get_relation($mysql, $tname, $clname,  $dbname=null){

	$r = $dbname==null? igk_mysql_db_selected_db($mysql) : $dbname;
	$n = $mysql->selectdb("information_schema");
	
	// $h = $mysql->sendQuery("SELECT * FROM `TABLE_CONSTRAINTS` WHERE `TABLE_NAME`='".igk_db_escape_string($tname)
		// ."' AND `TABLE_SCHEMA`='".igk_db_escape_string($r)."'");
		
	$h = $mysql->sendQuery("SELECT * FROM `KEY_COLUMN_USAGE` WHERE `TABLE_NAME`='".igk_db_escape_string($tname)
		."' AND `TABLE_SCHEMA`='".igk_db_escape_string($r)."' AND `COLUMN_NAME`='".igk_db_escape_string($clname)."' AND `REFERENCED_TABLE_NAME`!=''");
	
	//restore db
	$n = $mysql->selectdb($r);
	// if ($tname == "tbigk_users_reference_models")
		// igk_wln($h);
	return $h->getRowAtIndex(0);	
}
function igk_api_get_columninfo($row, $v, $table_n, $mysql){
	$cl = $row->addNode("Column");
	$cl["clName"] = $v->Field;
	$tab = array();
	preg_match_all("/^((?P<type>([^\(\))]+)))\\s*(\((?P<length>([0-9]+))\)){0,1}$/i", 
	trim($v->Type)
	//"Text"
	, $tab);
	
	// igk_wln($tab); 
	// if ($table_n == "tbigk_humans"){
		// igk_wln($tab["type"]);
		// igk_wln("for : ".$v->Type);
		// igk_wln(igk_balafon_mysql_datatype( igk_getv($tab["type"],0, "Int")));
		// igk_wln(igk_getv($tab["length"],0, 0));
		// $e = true;
	// }
	
	
	$cl["clType"] = igk_balafon_mysql_datatype( igk_getv($tab["type"],0, "Int"));
	$cl["clTypeLength"] = igk_getv($tab["length"],0, 0);
	if ($v->Default)
		$cl["clDefault"] = $v->Default;
	if ($v->Comment){
		$cl["clDescription"] = $v->Comment;
	}
	$cl["clAutoIncrement"] = preg_match("/auto_increment/i", $v->Extra) ? "True" : null;
	$cl["clNotNull"] = preg_match("/NO/i", $v->Null) ? "True": null;
	$cl["clIsPrimary"] = preg_match("/PRI/i", $v->Key) ? "True": null;
	
	if (preg_match("/(MUL|UNI)/i", $v->Key)){
		//link type type relation
		
		$rel = get_relation($mysql, $table_n,$v->Field);
		if ($rel){
			$cl["clLinkType"] = $rel->REFERENCED_TABLE_NAME;
		}
		// else{
			// igk_wln($v);
			// igk_wln("rel is null [{$t->$n}::{$v->Field}]");
 
		// }
	}
	return $cl;
}
function igk_api_mysql_get_data_schema($api, $render=1 , $param=null){
	$rep  = igk_createxmlnode(IGK_SCHEMA_TAGNAME);
	$rep["Date"] = date('Y-m-d');
	$rep["Version"]="1.0";
	$mysql = igk_get_data_adapter(IGK_MYSQL_DATAADAPTER, true);
	$tables = array();
	if($mysql->connect()){	
		$mysql->selectdb($api->App->Configs->db_name);
		$r = $mysql->listTables();//("Show Tables;");		
		if ($r){
				$n = $r->Columns[0]->name;
				$e = false;
				foreach($r->Rows as $t){
					if ($e)
					igk_exit();
					$table_n = $t->$n;
					$row = $rep->addNode(IGKDbSchemas::DATA_DEFINITION)->setAttributes(array(
						"TableName"=>$table_n
					));
					$tinfo = array();
					
					
					
					
					
					$tt = $mysql->sendQuery("SELECT Count(*) FROM `".$table_n."`;");
					$b = $tt->Columns[0]->name;
					$row["Entries"] = $tt->Rows[0]->$b;
					
					//$tt = $mysql->sendQuery("DESCRIBE `".$t->$n."`;");
					$tt = $mysql->sendQuery("SHOW FULL COLUMNS FROM `".$table_n."`;");
					
					foreach($tt->Rows as $m=>$v){	
						$cl = igk_api_get_columninfo($row, $v,$table_n, $mysql);						
						$tinfo[$v->Field] =new IGKDbColumnInfo( $cl->Attributes->ToArray());
						// if($table_n == "tbigk_humans"){
							//igk_wln($tables[$table_n]->tinfo[$v->Field]);
							//$e = true;
						// }
					}
			
					$tables[$table_n] = (object)array("tinfo"=>$tinfo, 'ctrl'=>"sys://mysql_db");
					// igk_wln("for table : ".$table_n);
					// igk_wln($tables[$table_n]);
					// igk_wln($tinfo);
				}
		}
		$mysql->close();
	}
	if ($render)
		igk_wl($rep->RenderXML());
		
	$check  = igk_getv($param, "check");
	if ($check)
		return $tables;
	return $rep;
}

function igk_api_mysql_get_table_definition($api, $row, $table_n, $mysql, $tinfo=null, $entries=null){ // Note can't pass item call by ref in call_array_function callback
	// igk_wln(func_get_args()); 
	$tt = $mysql->sendQuery("SELECT Count(*) FROM `".$table_n."`;");
	$b = $tt->Columns[0]->name;
	$count = $tt->Rows[0]->$b;
	$row["Entries"] = $count;
	
	//$tt = $mysql->sendQuery("DESCRIBE `".$t->$n."`;");
	$tt = $mysql->sendQuery("SHOW FULL COLUMNS FROM `".$table_n."`;");
	
	foreach($tt->Rows as $m=>$v){
		$cl = igk_api_get_columninfo($row, $v, $table_n, $mysql);						
		
		if ($tinfo)
		$tinfo[$v->Field] =new IGKDbColumnInfo( $cl->Attributes->ToArray());
		
	}

	if($entries && $count>0){
		//<Rows For="tbadman_userjobs">
		$n = $entries->addNode("Rows")->setAttribute("For", $table_n);
		foreach($mysql->select($table_n)->Rows as $k=>$v){
			$n->addNode("Row")->setAttributes($v);
		}
	}
}

function igk_api_mysql_get_sync_definition($api, $row, $table_n, $u, $mysql, $db=null, $entries=null, $tables=null){
					$tt = $mysql->sendQuery("SELECT Count(*) FROM `".$table_n."`;");
					$b = $tt->Columns[0]->name;
					$count = $tt->Rows[0]->$b;
					$row["Entries"] = $count;
					
					//$tt = $mysql->sendQuery("DESCRIBE `".$t->$n."`;");
					$tt = $mysql->sendQuery("SHOW FULL COLUMNS FROM `".$table_n."`;");
					$links = array();//links in tables
					$auto = null;
					foreach($tt->Rows as $m=>$v){	
					
						$cl = igk_api_get_columninfo($row, $v,$table_n, $mysql);	
						
						if ($cl["clLinkType"] ){
							$lnk =$cl["clLinkType"];
							$g = igk_getv($links, $lnk, null);								
							if ($g==null){
								$g = array();
							}								
							$g[] = array("Column"=>$v->Field, "Table"=> $lnk);
							$links[$lnk] = $g;
						}
					
						if ($cl["clAutoIncrement"]){
							$auto = $v->Field;
						}
					}
					
					//treat entries
					$tables->list[$table_n] = $links;
					if($entries && $count>0){
						//TODO: request for entries	
					
						//<Rows For="tbadman_userjobs">
						$n = $entries->addNode("Rows")->setAttribute("For", $table_n);
						
						if (igk_count($links)>0){
							$rows = $mysql->select($table_n)->Rows;
							//igk_wln("other link ".$lnk);
							foreach($rows as $k=>$v){
								$tn = $n->addNode("Row")->setAttributes($v);
								$continu = false;
								foreach($links as $lnk=>$vlnk){
									//treat each column
									foreach($vlnk as $g){
										$cn = $g["Column"];
										// igk_wln($cn);
										if ($g["Table"] == "tbigk_users"){
											if ($tn[$cn] != $u->clId){
												$tn->remove();
												$continu = true;
												break;
											}
											
										}
										$tn[$cn] = $db->getSyncDataValueDisplay($g["Table"] ,$tn[$cn]);
									}
									if ($continu)break;
								}
								if ($auto){
									$tn[$auto]=null;
								}
							
						}
						}
						else{
							foreach($mysql->select($table_n)->Rows as $k=>$v){
								$tn = $n->addNode("Row")->setAttributes($v);
								if ($auto){
									$tn[$auto]=null;
								}
							}
						}
					}
	
}
function igk_api_mysql_get_config_data_schema($api, $render=1, $param=null){
	$ctrl = $api->App->getControllerManager()->getControllers();
	$rep = igk_createxmlnode(IGK_SCHEMA_TAGNAME);
	$uri = igk_getv(igk_getv(igk_get_ns(), "ns"), "igk");
	$rep->setAttribute("xmlns:igk", $uri);
	$msg = $rep->add("warning-message");
	$err = $rep->add("error-message");
	$not = $rep->add("notice-message");
	$msg->setCallback("getIsVisible", "return \$this->ChildCount>0;");
	$not->setCallback("getIsVisible", "return \$this->ChildCount>0;");
	$err->setCallback("getIsVisible", "return \$this->ChildCount>0;");	
	$tables = array();
	$count = 0;
	
	foreach($ctrl as $k=>$v){
	
		if ($v->getDataAdapterName() == IGK_MYSQL_DATAADAPTER){
		
			if (!$v->UseDataSchema){
				//fonction list
				$tname = $v->getDataTableName();
				$tinfo = $v->getDataTableInfo();
				
				if (!empty($tname) && ($tinfo!==null))
				{
					if (isset($tables[$tname]))
					{
						$msg->add("warning")->Content = "Table $tname already found. [".$v->Name. "] get from ".$tables[$tname]->ctrl->Name. " no schema";						
						continue;
					}
					$tables[$tname] = (object)array("info"=>$tinfo, "ctrl"=>$v);
					$row = $rep->addNode(IGKDbSchemas::DATA_DEFINITION)->setAttributes(array(
						"TableName"=>$tname,
						"igk:ctrlName"=>$v->Name
					));
					
					foreach($tinfo as $kk=>$vv){
						$cl = $row->addNode("Column");
						$cl->setAttributes($vv);
					}
					$count ++;
				}
			}
			else{	
				
			
				$tschema =igk_db_load_data_schemas($v->getDataSchemaFile());
				if ($tschema){ 
				foreach($tschema as $ck=>$cv){
				
					if (isset($tables[$ck]))
					{
						$msg->add("msg")->Content = "Table $ck already found. [".$v->Name. "] get from ".$tables[$ck]->ctrl->Name." with schema";
						
						continue;
					}
					
						$row = $rep->addNode(IGKDbSchemas::DATA_DEFINITION)->setAttributes(array(
						"TableName"=>$ck,
						"igk:ctrlName"=>$v->Name
					));
					$row["clDescription"] = igk_getv($cv, "clDescription");
					$cinfo =  igk_getv($cv, "ColumnInfo");
					
					$tables[$ck] =(object) array("info"=>$cinfo, "ctrl"=>$v);
					
					foreach($cinfo as $ckk=>$ckv){
					$cl = $row->addNode("Column");
					$cl->setAttributes($ckv);
					}
					$count++;
				}
				}
			}
		
		}
	}
	$rep["tableCount"] = $count;
	$rep["Version"] = "1.0";
	$rep["Warning"] = $msg->ChildCount >0?$msg->ChildCount:null;
	$rep["Notice"] = $not->ChildCount >0?$not->ChildCount:null;
	$rep["Error"] = $err->ChildCount >0?$error->ChildCount:null;
	
	if ($render){
		igk_wl($rep->RenderXML());
	}
	$check  = igk_getv($param, "check");
	if ($check)
		return $tables;
	return $rep;
}
function igk_api_mysql_who_use($api, $tablename=null){
	if (!isset($tablename))
	{
		igk_navto($api->getAppUri("mysql"));
	}
	$c = igk_db_getdatatable_owner($tablename);
	if ($c){
		$n = igk_createNode("response");
		$n->addNode("info")->setContent("".$c);
		$n->addNode("class")->Content = get_class($c);
		if (igk_server_is_local()){
			$n->addNode("file")->Content = (new ReflectionClass(get_class($c)))->getFileName();
		}
		$n->RenderXML();
	}
}

function igk_api_mysql_get_table_data_schema($api, $tablename=null){
	$rep  = igk_createxmlnode(IGK_SCHEMA_TAGNAME);
	$rep->RenderAJX();
}
function igk_api_mysql_check_data_structure($api){

	//get configuration data schema
	// igk_wln("configuration data schema");
	$tconfig = igk_api_mysql_get_config_data_schema($api, false, (object)array("check"=>1));
	//get mysql data schema
	
	// igk_wln("mysql >  data schema");
	$tdb     = igk_api_mysql_get_data_schema($api, false, (object)array("check"=>1));
	// igk_wln($tdb["tbigk_humans"]); 
	$rep = igk_createNode("data_rep");
	$err = $rep->add("error-message");
	$err->setCallback("getIsVisible", "return \$this->ChildCount>0;");	
	
	$notused = $rep->add("TableNotUsed")->setCallback("getIsVisible", "return \$this->ChildCount>0;");	
	 // igk_wln(array_keys($tconfig));
	$ubind = (array_keys($tdb));
	
	foreach($tconfig as $k=>$v){
		$tbname = $k;
		if (!isset($tdb[$k]))
		{
			$err->add("Error")->add("Message")->Content = "Table $k doesn't exists";
			continue;
		}
		$v_b = $v->info;
		$v_testb  = $tdb[$k]->tinfo; 
		$v_err = 1;
		foreach($v_b as $kk=>$vv){		
			if (!isset($v_testb[$vv->clName])){
				$err->add("msg")->Content = "Column [{$tbname}::{$vv->clName}] Not found";
				$v_err=0;
				continue;
			}
			$v_btest = $v_testb[$vv->clName];
			if (strtolower($vv->clType) != strtolower($v_btest->clType)){
				$err->add("msg")->Content = "Column [{$tbname}::{$vv->clName}] Type Not match. Expected {$vv->clType} but {$v_btest->clType} found";
				$v_err=0;
			}else if (igk_db_is_typelength($vv->clType) &&  ($vv->clTypeLength != $v_btest->clTypeLength)){
				$err->add("msg")->Content = "Column [{$tbname}::{$vv->clName}] Type length not match. Expected {$vv->clTypeLength} but {$v_btest->clTypeLength} found";
				$v_err=0;
			}
			
			if (($vv->clLinkType) != strtolower($v_btest->clLinkType)){
				$err->add("msg")->Content = "/!\\ Column [{$tbname}::{$vv->clName}] Linktype not match. Expected {$vv->clLinkType} but {$v_btest->clLinkType} found!!!";
				$v_err=0;
			}
		}
		if ($v_err)
		unset($ubind[$k]);
	}
	
	foreach($ubind as $k=>$v){
		$notused->addNode("Item")->Content = $v;
	}
	
	if ($err->ChildCount>0)
		$rep["HasError"] = 1;
	
	igk_wl($rep->RenderXML());	 
}
 