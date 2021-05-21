<?php

$configs["plugin_uri"] = 'ilm://white_book'; 
$configs["plugin_name"] = 'WhiteBook'; 
$configs["plugin_title"] = 'ILM - WhiteBook';
$configs["plugin_entry_ns"] = "ILYEUM";

$configs["plugin_shortname"] = 'ILM - WhiteBook';
$configs["db_prefix"] = "ilm_";

$configs["wp_widgets"] = [
    \ILYEUM\WhiteBooks\Widgets\BookDetails::class,
    \ILYEUM\WhiteBooks\Widgets\BookDownload::class,
    \ILYEUM\WhiteBooks\Widgets\Books::class,
];