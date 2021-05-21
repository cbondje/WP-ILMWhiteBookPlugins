<?php

function igk_html_node_twitterFollowUs($id, $showcount=0){
	//followus
	//<a class="twitter-timeline" href="https://twitter.com/IGKDEV?ref_src=twsrc%5Etfw">Tweets by IGKDEV</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
	//<a href="https://twitter.com/IGKDEV?ref_src=twsrc%5Etfw" class="twitter-follow-button" data-show-count="false">Follow @IGKDEV</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
	$n = igk_createXmlNode("a");
	$n["class"]="twitter-follow-button";
	$n["data-show-count"]=$showcount?"true":"false";
	$n["href"] = "https://twitter.com/".$id;



	$b = igk_html_node_onrendercallback(igk_create_expression_callback(<<<EOF
\$doc = igk_getv(\$extra[0], "Document");
if (\$doc){
	\$d = \$doc->addTempScript('https://platform.twitter.com/widgets.js',1);
	\$d["charset"]="utf-8";
	\$d->activate("async");
	return 1;
}
return 0;
EOF
,array("n"=>$n)));

	$n->add($b);
	return $n;
}


///<summary>twitter time line zone</summary>
function igk_html_node_twitterTimeLine($id, $theme=null, $color=null){
	//<a class="twitter-timeline" data-theme="dark" data-link-color="#E81C4F" href="https://twitter.com/IGKDEV?ref_src=twsrc%5Etfw">Tweets by IGKDEV</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
	$n = igk_createXmlNode("a");
	$n["class"]="twitter-timeline";
	$n["href"] = "https://twitter.com/".$id."?ref_src=twsrc%5Etfw";
	$n["data-theme"] = $theme;//"https://twitter.com/".$id;
	$n["data-link-color"] = $color;//"https://twitter.com/".$id;


	$js = dirname(__FILE__)."/Scripts/.twitter.loader.js";
	$lib= "";
	$b = igk_html_node_onrendercallback(igk_create_expression_callback(<<<EOF
\$doc = igk_getv(\$extra[0], "Document");
if (\$doc){
	igk_doc_add_tempscript(\$doc,'{$js}', 1 , array('charset'=>'utf-8'));
	return 1;
}else{
	igk_doc_add_tempscript(null,'{$js}', 1 , array('charset'=>'utf-8','data-lib'=>'{$lib}'));
}
return 0;
EOF
,array("n"=>$n)));

	$n->add($b);
	return $n;
}

?>