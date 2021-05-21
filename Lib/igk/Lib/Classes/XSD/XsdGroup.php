<?php

namespace IGK\XSD;

class XsdGroup implements IXsdReference{
    var $name;
    var $attributes;
    
    public function getRefType(){
        return "xs:group";
    }
    public function getRef() { 
        return $this->name;
    }

}