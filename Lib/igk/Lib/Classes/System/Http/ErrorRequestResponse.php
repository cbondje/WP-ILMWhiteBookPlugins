<?php


namespace IGK\System\Http;

use IGKHtmlDocTheme;
use function igk_resources_gets as __;

class ErrorRequestResponse extends RequestResponse{
    var $type = "json";
    var $code = 400;
    public function __construct($code, $message=null){
        $this->code = $code;
        $this->message = $message;
    }

    public function render(){
        igk_set_header($this->code);
       
        $obj = ["response"=>(object)[
            "code"=>$this->code,
            "status"=> $this->code== 200? 'OK' :  $this->getStatus($this->code),
            "message"=>$this->message
        ]];
        switch($this->type){
            case "json":        
            if (igk_server()->accept($this->type)){
                \igk_header_set_contenttype($this->type);
                return json_encode($obj);
            }
            break;
            default:
                igk_header_set_contenttype("html");
            break;
        }
        $doc = igk_createnode("html");        
        $doc->add("head")->addObData(function($c){
           
            $c->add("title")->Content = __("Error");
            $c->addStyle()->setAttribute("type", "text/css")->Content  = $this->getErrorStyle();

        },null);

        $doc->add('body')->setClass("error_doc")->addObData(function($c)use($obj){
            $n = $c->add("div");
            (new ResponseHtmlRenderer($n,$obj))->render();
        });
        return "<!DOCTYPE html>".$doc->render();
        
    }
    public function getErrorStyle(){        
        $theme = new IGKHtmlDocTheme(null, -1);
        $theme["*, html, body"] = "margin:0px; padding:0px;";
        $theme->bindFile(IGK_LIB_DIR."/Styles/error_request.pcss");
        return $theme->get_css_def();
    }
}