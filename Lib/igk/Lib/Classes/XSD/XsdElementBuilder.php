<?php

namespace IGK\XSD;

use ArrayAccess;
use IGKXmlNode;

class XsdElementBuilder extends XsdElement 
{ 
    private $m_builder;
    private $_defining; 
   
    private function __construct()
    {
    }

    /**
     * @param IGKXmlNode $node 
     * @param XsdBuilder $builder 
     * @return XsdElementBuilder 
     */
    public static function Create(IGKXmlNode $node, XsdBuilder $builder)
    {
        $n = new XsdElementBuilder;
        $n->m_node = $node;
        $n->m_builder = $builder;
        return $n;
    }

    public function setAttribute($name, $value){
        $this->m_node->setAttribute($name, $value);
        return $this;
    }
    public function setDefault($defaultvalue){
        if ($this->_defining) {
            throw new XsdBuilderException("type already defined");
        }
        if ($defaultvalue){

            $this->_defining = true;
            $this->m_node["default"] = $defaultvalue;
        }
        return $this;
    }
    public function setFixed($defaultvalue){
        if ($this->_defining) {
            throw new XsdBuilderException("type already defined");
        }
        if ($defaultvalue){
            $this->_defining = true;
            $this->m_node["fixed"] = $defaultvalue;
        }
        return $this;
    }
    /**
     * 
     * @param array $defs 
     * @param mixed|null $attributes 
     * @return void 
     * @throws XsdBuilderException 
     */
    public function addComplexType(array $defs, $attributes=null, $type="sequence", $tattributes=null)
    {
        if (!in_array($type, explode("|","choice|sequence|all"))){
            igk_wln_e("not a valid complex type : ".$type);
        }
        if ($this->_defining) {
            throw new XsdBuilderException("type already defined");
        }

        $this->_defining = true;
        $b = XsdBuilderUtility::BuildComplexType($this->m_node, $defs, "xs:".$type, $tattributes);
        
      
       
        if ($attributes && count($attributes)){
            // $seq = $e->add("xs:sequence");
            foreach($attributes as $k=>$c){
                XsdBuilderUtility::AddSequenceElement($b, $k, $c, "xs:attribute");                
            }
        }
        return $this;
    }
}
