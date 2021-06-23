<?php

namespace ILYEUM;

class RouteHandler{
    public static function GetRoutes(){
        $routes = [];
        include(ILM_WHITE_BOOK_DIR."/Configs/routes.php");
        return $routes;
    }
}