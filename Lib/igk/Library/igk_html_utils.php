<?php
function igk_html_attribvalue($n){
	if (!$n){
		if (is_numeric($n)){
			$n = "0";
		}
	}
	return "\"".$n."\"";
}