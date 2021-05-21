<?php
namespace IGK\System\Http;
 

class NotAllowedRequestRequestException extends RequestException{
    public function __construct($uri=null){
        $this->code = 403;
        $this->status = "Not allowed";
    }
}