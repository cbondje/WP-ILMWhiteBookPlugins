<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/Models/Books.php
// @desc: 
// @date: 20210517 11:40:22
namespace ILYEUM\WhiteBooks\Models;


class Books extends ModelBase{
    protected $table = "%prefix%books";
    
    protected $primaryKey = "book_id";
}