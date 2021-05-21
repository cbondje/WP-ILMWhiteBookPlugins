<?php
/*
boot strap structure functions
*/

//used to add picture zone
function igk_bootstrap_pic_zone($n, $r, $c, $base=4,  $tab=null, $offset=0){
	$tr = $r;
	$ct = 0;
	while($r>0){
		$r--;
		$t = $n->add("div")->setClass("row");//Row();
		$j = $c;
		while($j>0){
			$j--;
			$cl = $t->add('div');
			$cl->setClass("col-lg-".$base);
			$cl->addDiv()->setClass("pic")->Content = igk_getv($tab,$ct, IGK_HTML_SPACE);
			$ct++;
		}
	}
}


?>