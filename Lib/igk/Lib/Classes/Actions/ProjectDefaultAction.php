<?php

namespace IGK\Actions;

use IGK\System\Http\RequestException;
use IGK\System\Http\ResponseHtmlRenderer;
use IGK\System\Http\WebResponse;
use IGK\System\IO\MimeType;

abstract class ProjectDefaultAction extends \IGKActionBase{
    /**
     * manifest cache output
     * @var mixed
     */
    protected $manifest_cache;

    public function logout(){
        $this->ctrl->logout(1);
    }
    /**
     * resolve internal asset 
     * @return mixed 
     */
    public function assets(){

        $path = implode("/", func_get_args());



        if ($content = $this->ctrl::asset_content($path)){
            $response = new WebResponse($content);
            $mime = igk_getv(igk_header_mime(), igk_io_path_ext($path), "text/plain");
            $response->headers=[
                "Content-Type:{$mime}",
                "Content-Length:".strlen($content)
            ]; 
            $response->cache_output(2500);  
            $response->output();
        } 
        throw new  RequestException(404, "resource not found: $path");
    }
    protected function manifest_json(){
        $dir = $this->ctrl->getDeclaredDir();
        if (file_exists($fname = ($dir."/manifest.json"))){ 
            $response = new WebResponse($str=file_get_contents($fname)); 
            $response->headers = [
                "Content-Type:".MimeType::FromExtension("json"),
                "Content-Length:".strlen($str)
            ]; 
            if ($this->manifest_cache>0){
                $response->cache_output($this->manifest_cache);
            }
            igk_do_response($response);
        } else {  
            throw new RequestException(404);
       }
    }
    public function __call($name, $args){
        if (preg_match("/^manifest(\.json)?$/", $name)){
            return $this->manifest_json(...$args);
        } 
        return parent::__call($name, $args);
    }
}