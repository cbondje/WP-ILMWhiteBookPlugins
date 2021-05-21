<?php
namespace IGK\System\Exceptions;

use IGKException;
use Throwable;
use function igk_resources_gets as __;

class ActionNotFoundException extends IGKException{
    public function __construct($name,?Throwable $throwable=null )
    {
        parent::__construct(
            sprintf(__("Action [%s] not found"), $name),
            404, $throwable );
    }
}