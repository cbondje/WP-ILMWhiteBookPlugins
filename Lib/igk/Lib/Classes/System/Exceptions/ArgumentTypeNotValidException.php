<?php

namespace IGK\System\Exceptions;

use IGKException;

class ArgumentTypeNotValidException extends IGKException{
    public function __construct($index){
        parent::__construct( sprintf("Parameter not valid %s", $index));
    }
}