<?php
// @file: IGKHtmlExtension.php
// @author: C.A.D. BONDJE DOUE
// @description: 
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKHtmlExtension</summary>
class IGKHtmlExtension{
    ///<summary></summary>
    ///<param name="p"></param>
    ///<param name="options" type="array"></param>
    public function loadItems($p, array $options){
        $items=$options["items"];
        $selected=igk_getv($options, "selected");
        $callback=igk_getv($options, "callback");
        $allowempty=igk_getv($options, "allowempty");
        $emptyvalue=igk_getv($options, "emptyvalue");

        if ($allowempty){
            $o=$p->add("option"); 
            if($emptyvalue == $selected){
                $o->setAttribute("selected", true);
            }
            $o["value"]= $emptyvalue;
            if ($callback)
                $callback($o);
        }

        foreach($items as $k){
            if(is_array($k))
                $k=(object)$k;
            $o=$p->add("option");
            $o->Content=$k->text;
            if($k->value == $selected){
                $o->setAttribute("selected", true);
            }
            $o["value"]=$k->value;
            if($callback)
                $callback($o);
        }
        return $p;
    }
}
