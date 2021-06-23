<?php


function igk_html_node_wp_a_get($url='#'){
    $n  = igk_createnode("a");

    $n["href"]="#";    
    return $n;
}