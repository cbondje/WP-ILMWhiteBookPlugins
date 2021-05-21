<?php

//file: winui.calendar.phhp
//author: C.A.D. BONDJE DOUE
//version:1.0
//release:19/12/2017

///<summary>represent the calendar item</summary>
function igk_html_node_Calendar(){
	$d = igk_createNode("div");
	$d["class"] = "igk-winui-calendar";
	return $d;
}

function igk_html_demo_Calendar($t){
	die("not implement");
	// $t->addObData(function(){
	// 	igk_wln("call demo");
	// 	// igk_wln(igk_get_env("sys://html/components/demos"));

	// 	// igk_wln(igk_get_env_all("sys://html"));
	// 	// igk_wln(igk_get_env_obj("sys://"));

	// 	igk_wln(igk_str_glue(",", "information", "test " ,"", " la vie de l'etre sample"));
	// 	igk_wln(igk_str_glue(function($v, &$s ){
	// 		return "<div> Data : ".$v."</div>";
	// 	}, "information", "test " ,"", " la vie de l'etre sample"));
	// });
}
// register calendar component
igk_html_reg_component_demo("Calendar", function($n){
});