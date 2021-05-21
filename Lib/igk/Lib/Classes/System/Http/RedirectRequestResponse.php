<?php

namespace IGK\System\Http;

class RedirectRequestResponse extends RequestResponse{

    public function __construct($uri=null)
    {
        if ($uri===null){
            $uri = igk_server()->HTTP_REFERER ?? igk_io_baseuri(); 
        }
        $this->code = 301;
        $this->uri = $uri;
    }
    public function render() { 
        $cp = get_called_class();
        if ($cp === __CLASS__){
            igk_navto($this->uri);
        } 
    }

}