<?php


namespace IGK\System\Http;

abstract class RequestResponse{
 
    /**
     * return code
     */
    var $code = 200;
    /**
     * additinal header
     */
    var $headers; 

    protected function getStatus($code){
        return igk_getv(
            [
                "404"=>"Page Not found", 
                "400"=>"Bad request!!!", 
            ],
            $code
        );
    }
    /**
     * output the current response
     * @return void 
     */
    public function output(){ 
        igk_set_header($this->code, $this->getStatus($this->code), $this->headers); // "testing base", $headers);
        igk_wl($this->render());
        igk_exit();
    }
    abstract function render();

    public function cache_output($second){
        $ts=gmdate("D, d M Y H:i:s", time() + $second). " GMT";
        $this->headers[] = ("Expires: {$ts}");
        $this->headers[] = ("Pragma: cache");
        $this->headers[] = ("Cache-Control: max-age={$second}, public");
    }
    public function clear_headers(){
        $this->headers = [];
    }
}