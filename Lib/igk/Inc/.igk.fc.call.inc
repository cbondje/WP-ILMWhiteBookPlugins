<?php
//requires parameters
//$params : array that will contain the function add argument to pass
//$fclist: array of callable function
//$fc_result : the result of call to that function


$fc = isset($params) ?igk_getv($params, 0) : null; 
$fc_result = null;
if ($fc && isset($fclist[$fc])){
	$ht = array_slice($params, 1);
	if (igk_count($ht)>0){
		$fc_result = call_user_func_array($fclist[$fc], $ht);
	}
	else
		$fc_result = $fclist[$fc]();
	unset($ht);
}
unset($fc);
?>