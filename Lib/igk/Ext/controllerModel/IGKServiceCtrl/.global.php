<?php

//BALAFON SERVICE FUNCTION
//Author: C.A.D BONDJE DOUE
//copyright: see balafon.copyright


function igk_srv_bind_cookie($lie, $s){
	preg_replace_callback("/(?P<name>([^;=])+)=(?P<value>([^;])+)/i", function($m)use($lie){
		$n= igk_getv($m, "name");
		$v= igk_getv($m, "value");
		$lie->__setCookie($n,$v);
	}, $s);
}
function igk_srv_soap_call($u, $name, $args){
	$lie = new SoapClient(null, array(
	"uri"=>$u,
	"location"=>$u,
	"trace"=>1,
	"exceptions"=>0));
	if (igk_get_env(__FUNCTION__."://prevent_session")){
		$tab = igk_srv_soap_LastHeader();
		if ($tab){
			$cook = $tab["Set-Cookie"];
			if (is_array($cook)){

				foreach($cook as $k){
					// igk_ilog("setting cookie".$k);
					// preg_match_replace("/(?name=(?P<value>([^;])+)/i"
					igk_srv_bind_cookie($lie, $k);
					// $lie->__setCookie($k);
				}
			}
		}
	}
	$e =  $lie->__call($name,$args);
	igk_set_env(__FUNCTION__, $lie);
	return $e;
}
function igk_srv_soap_session(){
	igk_set_env("igk_srv_soap_call://prevent_session", 1);
}
function igk_srv_soap_LastHeader(){
	$e = igk_get_env("igk_srv_soap_call");
	if (!$e)
		return null;
	$tab = array();
	$rtab  = explode("\n", $e->__getLastResponseHeaders());
	$tab["Status"]  = $rtab[0];
	for($i = 1; $i < igk_count($rtab) ; $i++){
		$h = explode(":", $rtab[$i]);
		$n = igk_getv($h, 0);
		$v = substr($rtab[$i], strpos($rtab[$i], ':')+1);

		if (isset($tab[$n])){
			$rk = $tab[$n];
			if (!is_array($rk)){
				$rk = array($rk);
			}
			$rk[] = $v;
			$tab[$n] = $rk;

		}else
		$tab[$n] = $v;
	}
	return $tab;
}


///<summary>used to mark a function or the class to not been exposed</summary>
function igk_srv_notexposed_attr($classname, $method){

		$key = "sys://services/".$classname."/notexposed";
		$tab = igk_get_env($key, array());
		$g = explode(",", strtolower($method));
		foreach($g as $k){
			$tab[trim($k)]=1;
		}
		igk_set_env($key, $tab);
		return $tab;
}

?>