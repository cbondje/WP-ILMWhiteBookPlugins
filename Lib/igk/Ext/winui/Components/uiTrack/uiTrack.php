<?php
$CF = igk_ctrl_zone_init(__FILE__);

function igk_html_node_uiTrack($type='default'){
	$CF = igk_ctrl_zone(__FILE__);
	$dv = igk_createNode("div");
	$dv["class"]="igk-winui-uitrack ".$type;

	$callback["_trackoption.func"]=<<<EOF
\$this["igk:uitrack-options"]=igk_getv(\$param,0);
return \$this;
EOF;

	$dv->setCallback("setOption", $callback["_trackoption.func"]);

	$dv->addOnRenderCallback(igk_create_expression_callback(
	igk_io_read_allfile(dirname(__FILE__)."/.style.func"),
	array(
	"node"=>$dv,
	"CF"=>$CF,
	"type"=>$type))
	);
	return $dv;

}
function igk_html_demo_uiTrack($t){
	$frm = $t->addDiv()->addForm();

	$frm->setStyle("width:300px; padding:2em;");

	$frm->addUiTrack()->setId("red");//->RenderAJX();
	// session_destroy();
	
	// $frm->addLabel()->Content = "Jesdld qmlsdkfj mqlskjd mfqklsjd mfkjqsdmlkfj mqsldkjfm qlsdkjf qlsdkjvvn";
	$frm->addUiTrack()->setId("sepia")->setOption("{min:-128, max:128, update:function(x){return parseInt(((this.max-this.min) * x) + this.min); }}");
	$frm->addUiTrack()->setId("blur")->setOption("{min:0, max:255, update:function(x){return parseInt(((this.max-this.min) * x) + this.min); }}");
}
?>