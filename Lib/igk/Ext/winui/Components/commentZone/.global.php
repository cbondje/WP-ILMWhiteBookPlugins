<?php

//comment zone control
igk_ctrl_zone_init(__FILE__);

function igk_html_node_CommentZone(){
$n = igk_createNode("div");
$n["class"] = "igk-comment-z";
$n->addOnRenderCallback(igk_create_node_callback('igk_comment_init', array($n)));
return $n;
}




function igk_comment_time($data){
	$s = $data[0]>1;
	return R::ngets("btv.comment_time_2", $data[0], R::ngets('time.i.'.$data[1].($s?'s':'')
	)); //"Il Y a 3 Jour";
}
///<summary>a comment zone template</summary>
function igk_comment_zone($ctrl, $n, $title="", $msg=null, $since=null, $id_reply=null,
	$likes=1,
	$have_child=false,
	$can_drop=0
){
	$c = $n->addDiv();
	$c["class"]  = "i";
	$h = $c->addDiv();
	$h["class"]="t";
	$dtc = igk_get_env(__FUNCTION__."://time", function(){
		return igk_time_span("Ymd His",date("Ymd His"));
	});

	// igk_wln(date("Ymd His"));
	// igk_wln(ini_get('date.timezone'));
	// igk_wln(date("Ymd His", $since));
	// igk_wln(gmdate("Ymd His", $since));
	//igk_set_env(__FUNCTION__."://time", $dtc);

	$p = $h->addDiv()->setClass("p  igk-bg-comment");
	$p->addSpan()->Content = "logo picture";
	$p->addDiv()->setClass("dispib posab fit loc_l loc_t")->Content= igk_svg_use("comment");
	$ctn = $h->addDiv();
	$hd = $ctn->addDiv()->setClass("cm-header");
	$hd->addSpan()->setClass("dispib")->addSectionTitle(6)->Content= $title;
	// $since = igk_time_span("Ymd","20181020");
	if ($since){

		// igk_wln($since);
		$data = igk_time_max_info($dtc, $since);//, igk_time_span("Ymd",date("Ymd")));
		$hd->addSpan()->Content = igk_comment_time($data);
	}
	$ctn->setClass("c")->addDiv()->setClass("m")->Content = $msg!=null?$msg:<<<EOF
--------------------- Nothing to Comment ----------------
EOF;
	$a = $ctn->addDiv();
	//action
	if ($id_reply){
		$a =   $a->addDiv()->setClass("a");

		$a->addA($ctrl->getAppUri("comment_add_ajx"))
		->setAttribute('options', "{id:'{$id_reply}'}")
		->setClass("igk-cm-btn")->Content = R::ngets("btn.answer");
	}

	if ($likes){
		$a->addSpan()->setClass("cm-btn lk")->Content  = igk_svg_use("like");//file_get_contents(dirname(__FILE__)."/like.svg");
		$a->addSpan()->setClass("cm-btn ulk")->Content  = igk_svg_use("unlike"); //file_get_contents(dirname(__FILE__)."/unlike.svg");// ->Content = " Dislike ";
	}
	if ($have_child){
		$a->addSpan()->setClass("cm-btn more")->Content  = igk_svg_use("morev");
	}
	if ($can_drop)
	$a->addSpan()->setClass("cm-btn drop")->Content  = igk_svg_use("drop");
	return $c;
}

///<suummary>init comment zone</summary>
function igk_comment_init($a,$b,$c){
	//@c: rendering options
	//@a: no-tag-node
	//$b: current node
	$CF = igk_ctrl_zone(__FILE__);
	if (igk_is_ajx_demand()){
		igk_close_session();
	}

	igk_css_bind_wuistyle_file($c->Document, $CF->getStylesDir()."/default.pcss");
	igk_js_bind_wuiscript($c->Document, $CF, ".commentZone.js", $a);
	return 1;
}
function igk_comment_zone_callback($n, $callback, $params=null){
	$c = $n->addDiv();
	$c["class"]  = "i";
	$c["style"] = "margin: 2em 0px; padding:10px; ";
	return call_user_func_array($callback, $params);
}




interface IIGKCommentZoneListener {
	function comment_add_ajx($i);
	function comment_drop_ajx($i);
	function comment_viewmore_ajx($id);
}

?>