<?php

$CF = igk_ctrl_zone_init(__FILE__);


function igk_html_node_ToggleStateButton($id,$value='on', $checked=0,$type="window10"){
	// static $src_expression = null;
	// if ($src_expression===null)
		$src_expression = igk_io_read_allfile(dirname(__FILE__)."/.statebtn.func");
	$CF = igk_ctrl_zone(__FILE__);
	$n = igk_createNode("div");
	$n["class"] = "igk-winui-btn-toggle-state";
	// $checked = 0;
	$n->addOnRenderCallback(igk_create_expression_callback($src_expression
	,
	array("node"=>$n,
	"CF"=>$CF,
	"type"=>$type,
	"name"=>$id,
	"i_value"=>array("v"=>$value,"c"=>$checked)
	)
	));
	//on render bind css style to document
	return $n;
}

function igk_html_demo_ToggleStateButton($tg){
	$tg->addDiv()->Content = "<b>window10</b> style state button";
	$n = igk_html_node_ToggleStateButton('marche',"window10");
	$tg->add($n);
}