<?php
namespace IGK\System\Http;

use IGK\System\Http\ErrorRequestResponse;

class NotAllowedRequestResponse extends ErrorRequestResponse{
    public function __construct(){
        parent::__construct(403, "Not Allowed");
    }
}