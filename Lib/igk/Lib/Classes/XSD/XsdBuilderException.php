<?php

namespace IGK\XSD;
use IGKException;

/** @package IGK\XSD */
class XsdBuilderException extends IGKException{
    public function __construct($msg)
    {
        parent::__construct($msg);
    }
}