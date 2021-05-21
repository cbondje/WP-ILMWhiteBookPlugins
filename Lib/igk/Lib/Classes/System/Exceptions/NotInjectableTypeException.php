<?php

namespace IGK\System\Exceptions;

use IGKException;

class NotInjectableTypeException extends IGKException{
    public function __construct($index, $code= 404){
        parent::__construct( sprintf("Parameter not Injectable %s", $index), $code);
    }
}