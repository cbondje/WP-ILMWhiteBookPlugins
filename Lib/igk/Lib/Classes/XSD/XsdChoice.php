<?php

namespace IGK\XSD;

class XsdChoice implements IXsdReference{
    var $name;
    var $attributes;
    
    public function getRefType(){
        return "xs:choice";
    }
    public function getRef() { 
        return $this->name;
    }

}