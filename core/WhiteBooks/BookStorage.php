<?php
// @author: C.A.D BONDJE DOUE
// @file: WhiteBooks/BookStorage.php
// @desc: 
// @date: 20210527 13:46:00
namespace ILYEUM\WhiteBooks;
class BookStorage{
    /**
     * retreive the pour path of file 
     * @param mixed $path 
     * @return string 
     */
    public static function GetFile($path){
        return implode("/", array_filter([ilm_app()->configs->book_dir, $path]));
    }
}