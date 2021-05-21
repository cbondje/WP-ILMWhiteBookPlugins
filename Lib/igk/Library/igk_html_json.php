<?php
// @file: test_jsondata.phtml
// @author: C.A.D. BONDJE DOUE
// @copyright: igkdev © 2019
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>json html node</summary>
/**
* json html node
*/
function igk_html_json($n){
    $d=array();
    $cp=array(array("s"=>& $d, "q"=>$n));
    $t=& $n;
    while($g=array_pop($cp)){
        $q=$g["q"];
        $m=& $g["s"];
        $r=$q->getTagName();
        $view=array();
		//igk_wln("tag : ", $r , get_class($q));

		if ( get_class($q) != IGKHtmlText::class){

			if($ctrib=$q->getAttributes()){
				$attribs=array();
				foreach($ctrib->ToArray() as $k=>$attrv){
					$attribs[$k]=IGKHtmlUtils::GetAttributeValue($attrv);
				}
				if(count($attribs) > 0){
					$view[0]=$attribs;
				}
			}
			if(($_cc=$q->getChilds()) && ($childs=$_cc->toArray()) && (count($childs) > 0)){
				$view[1]=array();
				foreach($childs as $ch){
					array_unshift($cp, array("q"=>$ch, "s"=>& $view[1]));
				}
			}
			if(!empty($s=$q->getContent())){
				$view[2]=$s;
			}
			$m[]=array($r=>$view);
		} else{
			$m[] = array("c"=>$q->getContent());
		}
    }
    return json_encode($d);
}
///<summary></summary>
///<param name="n"></param>
/**
* 
* @param string $n string to decode
*/
function igk_html_json_decode($n){
    $tab=json_decode($n);
    $out=array();
    foreach($tab as $i){
        $cp=array(array("i"=>$i, "n"=>null));
        $_c=0;
        while($q=array_pop($cp)){
            $i=$q["i"];
            $n=$q["n"];
            if($_c > 0){}
            $_c++;
            foreach($i as $k=>$v){
                $n=$n == null ? igk_createnode($k): $n->add($k);
                if(is_object($v)){
                    $v=(array)$v;
                }
                if(isset($v[2])){
                    $n->setContent($v[2]);
                }
                if(isset($v[0])){
                    $n->setAttributes($v[0]);
                }
                if(isset($v[1])){
                    foreach($v[1] as $rr){
                        array_unshift($cp, array("i"=>(array)$rr, "n"=>$n));
                    }
                }
            }
            $out[]=$n;
        }
    }
    return $out;
}
