<?php

namespace IGK\System\Traits;

use Closure;

trait CreateCallable{
    public static function CreateCallable($t, $func, ...$params){
        return Closure::fromCallable(function()use($func, $params){
               $args = array_merge(func_get_args(), $params);
               return $this->$func(...$args);
        })->bindTo($t);
    }
}