<?php

namespace IGK\System\Console;

class ConsoleLogger{
    var $app; 
    public function __construct($app)
    {
        $this->app = $app;
    }
    public function warn($msg){
        $this->app->print($this->app::gets(App::PURLPLE, $msg));
    }
    public function danger($msg){
        $this->app->print($this->app::gets(App::RED, $msg));
    }
    public function success($msg){
        $this->app->print($this->app::gets(App::GREEN, $msg));
    }
    public function info($msg){
        $this->app->print($this->app::gets(App::YELLOW, $msg));
    }
    public function log($msg){
        $this->app->print($msg); //this->app::gets(App::PURLPLE, $msg));
    }
    public function print($msg){
        $this->app->print($msg);
    }

}