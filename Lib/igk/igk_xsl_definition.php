<?php
// file: igk_xsl_definition.php
// Desc: use to call dxsl definitoin data
// author : C.A.D BONDJE DOUE
// license: Balafon @ copyright 2019

///<summary>xsl function creator</summary>
/**
* xsl function creator
*/
function igk_xsl_creator_callback($name, $args){
    if(function_exists($fc="igk_xsl_node_".$name) || function_exists($fc="igk_html_node_".$name)){
        return call_user_func_array($fc, $args);
    }
    return new IGKXmlNode($name);
}
