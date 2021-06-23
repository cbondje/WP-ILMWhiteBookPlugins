<?php

namespace ILYEUM\Console\Database;


class migrate{

    protected $migrates;
    public function boot(){
        $this->migrates = [
            ILYEUM\WhiteBooks\Database\Migrates::class
        ];
        return $this;
    }
    public function run(){
        foreach($this->migrates as $k){
            // $c = new $k();
            // $c->run();
        }
        exit(0);
    }
}
require_once(dirname(__FILE__)."/../../../ilyeum_whitebook.php");

(new migrate())->boot()->run();