<?php
namespace IGK\System\Reflection;

use Exception;
use ReflectionMethod;

final class Utils{
    public static function GetModifiers($r){
        $s = "";        
        $s.= method_exists($r, "isFinal") && $r->isFinal() ? " final" : "";
        $s.= method_exists($r, "isAbstract") && $r->isAbstract() ? " asbract" : "";
        $s.= method_exists($r, "isPublic") && $r->isPublic() ? " public" : "";
        $s.= method_exists($r, "isPrivate") && $r->isPrivate() ? " private" : "";
        $s.= method_exists($r, "isStatic") && $r->isStatic() ? " static" : "";
        $s.= method_exists($r, "isProtected") && $r->isProtected() ? " protected" : "";        
        return $s;
    }
    public static function GetMethodDefinition(ReflectionMethod $v, callable $filter =null){
        $s = "(";
        $i=0;
        foreach($v->getParameters() as $p){
            if ($i)
            $s.=", ";
            if ($t = $p->getType()){
                if ($filter && method_exists($t, "getName")) $t = $filter("type", $t->getName());
                $s.= $t." ";
            }
            if ($p->isPassedByReference()){
                $s .= " & ";
            }
            if ($p->isVariadic()){
                $s .= "...";
            }
            $s.= "\$".$p->getName();
            try{
                
                if ( $p->isOptional()){
                     $t = $p->getDefaultValue();
                    if ($filter) $t = $filter("default", $t);
                    if ($t===null)
                        $t = 'null';
                  
                    $s.= " = ".$t;
                }
            } catch(Exception $ex) {
    
            }
            $i=1;
        }
    
        $s.=")";
        if ($v->hasReturnType() && ($t = $v->getReturnType())){
            if ($filter) $t = $filter("return", $t->getName());
            $s .=": ".$t;
        }
        return $s;
    }
}