<?php

namespace ILYEUM\WhiteBooks\Pages;

use ILYEUM\RouteHandler;
use ILYEUM\WhiteBooks\Controller;
use ILYEUM\wp\filters;
use function ilm_server as server;
use function ilm_getctrl as getctrl;

class Init{
   

    public function __construct()
    {
        // | Parse request to access the page view process
        add_filter(filters::DO_PARSE_REQUEST, function($x, $wp=null, $extra_args=null){	
            $routes = RouteHandler::GetRoutes();            
            foreach($routes as $p){
                if ($p->match()){
                    return false;
                }
            };
            if (strpos(server()->REQUEST_URI, Controller::ENTRY_URL) === 0){
                getctrl()->view("page");
                return 0;
            }
            return $x;
        });

    }
}