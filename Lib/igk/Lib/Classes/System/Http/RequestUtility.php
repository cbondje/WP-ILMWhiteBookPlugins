<?php
namespace IGK\System\Http;

class RequestUtility{
    
    public static function RequestGet($paramHandler, $requestName, $paramName, $update=true){   
        $c = Request::getInstance()->have($requestName, $paramHandler->getParam($paramName));
        if ($update){
            if (!empty($c)){
                $paramHandler->setParam($paramName, $c);
            } else {
                $paramHandler->setParam($paramName, null);
            }
        }
        return $c;
    }
}