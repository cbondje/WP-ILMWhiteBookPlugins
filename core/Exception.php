<?php
namespace ILYEUM;

use Error;
use Throwable;
use \Exception as CoreException;

/**
 * default exception
 * @package ILYEUM
 */
class Exception extends CoreException{
    public function __construct($msg, $code=500, ?Throwable $throwable=null)
    {
        parent::__construct($msg, $code, $throwable);
    }
}