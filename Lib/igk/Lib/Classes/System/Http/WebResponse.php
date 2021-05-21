<?php




namespace IGK\System\Http;

/**
 * represent a web rendering result
 * @package IGK\System\Http
 */
class WebResponse extends RequestResponse{
    private $node;

    public $headers = [
        "Content-Type: text/html"
    ];

    public function __construct($node, $code=200){
        $this->code = $code; 
        $this->node = $node;
    }
    public function render() { 
        if (is_string($this->node)){
            igk_wl($this->node);
            return;
        }
        $this->node->renderAJX();
    }
}