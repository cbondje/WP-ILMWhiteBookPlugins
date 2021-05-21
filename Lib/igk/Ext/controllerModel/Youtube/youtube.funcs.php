<?php
///global utility fonctions used to manage youtube fonction
///02-10-2015

function igk_html_node_youtubevideo($uri, $param=null){
//test with lean on
//<iframe width="854" height="480" src="https://www.youtube.com/embed/YqeW9_5kURI" frameborder="0" allowfullscreen></iframe>
//integrate video : exemple : https://www.youtube.com/embed/YqeW9_5kURI
//integrate video list : exemple : https://www.youtube.com/embed/2JaTztqeUDs?list=PL3A7BF1733573B10A

	$n = igk_createNode("iframe");
	$n["src"] = $uri;
	$n["allowFullScreen"]="1";
	$n["title"]= igk_getv($param, "title", "YouTube video player" );
	$n["class"]= igk_getv($param, "class", "youtube-player");
	$n["frameborder"]= 0;
	$n["type"]="text/html";
	return $n;

}

//<summary>demonstration of you tube video</summary>
function igk_html_demo_youtubevideo($tg){
	$n = igk_createNode();
	//major lazer
	$n->addyoutubeVideo("https://www.youtube.com/embed/YqeW9_5kURI");
	$tg->addDiv()->Content = "You tube demonstration";
	$tg->add($n); 
}
//<summary>description of you tube video</summary>
function igk_html_desc_youtubeVideo($tg){
	$n = igk_createNode();
	//major lazer
	// $s = "test";
	$n->addDiv()->Content = "Usage in PHP Script";
	$n->addCode()->Content = (<<<EOF
\$node->addyoutubeVideo("https://www.youtube.com/embed/YqeW9_5kURI");
EOF
);
	$tg->add($n);

}