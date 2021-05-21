<?php
//community controller global functions
//author: C.A.D BONDJE DOUE
//version : 1.0


function igk_community_init_node_callback($t){
	// igk_wln("call init node  . ".$t);
	$ctrl = igk_db_sys_ctrl("community");
	if ($ctrl){
		$ctrl->loadCommunityNode($t);
	}
}
function igk_community_init_ShareWith_callback($t){
	$ctrl = igk_db_sys_ctrl("community");
	if ($ctrl){
		$ctrl->loadCommunityNode($t);
	}
}

function igk_html_node_CommunityNode(){
	$n = igk_createNode("div");
	$n["class"]="igk-community-node";
	igk_community_init_node_callback($n);
	return $n;
}

function igk_html_node_SharedWithCommunity($tab=null){
	$n = igk_createNode("div");
	$n["class"]="igk-shared-comm";
	if ($tab!=null){
		foreach($tab as $k=>$v){
				$n->addSpan()->addA('#')->Content = $k;
		}
	}else
	igk_community_init_ShareWith_callback($n);
	return $n;
}




function igk_html_node_FollowUsButton($name, $uid){
	$srv = igk_community_get_followus_service();
	$fc = igk_getv($srv, $name);
	if ($fc){
		$n = igk_createnotagnode();
		$args = array_merge(array("view", $n, $uid), array_slice(func_get_args(),2));
		call_user_func_array($fc, $args);//call$fc("googleplus", "view", $t->addDiv(), "110019067739683958923");
		return $n;
	}
	return null;
}
///<summary>get follows entries list</summary>
function igk_community_get_follow_entries($cnf){
	$tab = igk_community_get_followus_service();
	if (!$tab){
		return null;
	}
	$otab=array();
	$cnf = igk_getv($cnf,"app.Followus");
	foreach($tab as $k=>$fc){
		$u = $fc("getlink", null, $cnf);
		if ($u)
		$otab[] = (object)array("u"=>$u, "t"=>$k);
	}
	return $otab;
}






igk_community_register_followus_service("twitter", function($cmd, $t,$v=null, $name=null){
	$targs = [];
	switch($cmd){
		case "edit":
			$t->addInput("cl".$name,"text", igk_conf_get($v,'twitter'));
		break;
		case "getlink":
			if (isset($v->twitter))
			return "https://twitter.com/".$v->twitter;
		default: //view
			$t->addTwitterFollowUsButton(igk_getv($targs,0));
		break;
	}
	return null;
});



?>