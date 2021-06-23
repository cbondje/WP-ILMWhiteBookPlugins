<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Models/BookClients.php
// @desc: 
// @date: 20210517 11:41:40
namespace ILYEUM\WhiteBooks\Models;


class BookClients extends ModelBase{
    protected $table = "%prefix%book_clients";
    protected $primaryKey = "bookclients_id";
}