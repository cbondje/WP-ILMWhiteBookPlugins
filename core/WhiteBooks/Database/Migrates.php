<?php

namespace ILYEUM\WhiteBooks\Database;

use ILYEUM\WhiteBooks\Models\BookClients;

class Migrates{

    public function run(){
        $driver = BookClients::getDataAdapter();
        ilm_environment()->querydebug = 1;
        // update drivers 
        $driver->sendQuery(sprintf("ALTER TABLE %s ADD COLUMN bookclients_info TEXT NULL", BookClients::table()));


        // $driver->addColumn(BookClients::table(), "bookclients_info" , []);
    }   
}