<?php

$configs["plugin_uri"] = 'ilm:/white_book'; 
$configs["plugin_name"] = 'WhiteBook'; 
$configs["plugin_title"] = 'ILM - WhiteBook';
$configs["plugin_entry_ns"] = "ILYEUM";

$configs["plugin_shortname"] = 'ILM - WhiteBook';
$configs["db_prefix"] = "ilm_";
$configs["config_dir"] = __DIR__;//"/data.ilm_";
$configs["book_dir"] = __DIR__."/books";

$configs["wp_widgets"] = [
    \ILYEUM\WhiteBooks\Widgets\BookDetails::class,
    \ILYEUM\WhiteBooks\Widgets\BookDownload::class,
    \ILYEUM\WhiteBooks\Widgets\Books::class,
];


$configs["js_version"] = "2.4";
$configs["css_version"] = "2.3";