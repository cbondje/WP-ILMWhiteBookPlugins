<?php
namespace IGK\System\Traits;

use ReflectionClass;

trait EnumeratesConstants{
    public static function GetConstants(){
        $ref = new ReflectionClass(static::class);
        return $ref->GetConstants();
    }
    public static function GetConstantKeys(){
        return array_keys(self::GetConstants());
    }
    public static function GetConstantValue($k){
        $ref = new ReflectionClass(static::class);
        return $ref->getConstant($k);
    }
}