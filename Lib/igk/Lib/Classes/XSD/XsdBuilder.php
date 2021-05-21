<?php


namespace IGK\XSD;

use ArrayAccess;
use Exception; 

/**
 * 
 * @package IGK\XSD
 */
class XsdBuilder extends XsdElement implements ArrayAccess{
  
    const SCHEMA = "http://www.w3.org/2001/XMLSchema";
    const ANY_ATTRIBUTE = -1; //strict any attribute
    const ANY_ATTRIBUTE_LAX = -2; //strict any attribute
    const ANY_ATTRIBUTE_SKIP = -3; //strict any attribute
    private $m_notation;

    /**
     * Create a group element
     * @param mixed $name group name
     * @param mixed $items elements sequence
     * @param mixed|null $attributes for reference  
     * @return XsdGroup 
     */
    public function CreateGroup($name, $items, $attributes=null, $type="sequence"): XsdGroup{
        if (!in_array($type, explode("|", "choice|sequence|all"))){
            die("type not valie");
        } 

        $n = $this->_buildGroup($name, $items, "xs:group", "xs:all");
        
        $g = new XsdGroup($this, $n);        
        $g->name = $name;
        $g->attributes = $attributes;
        return $g;
    }
    public function CreateChoice($name, $items, $attributes=null): XsdChoice{
        $n = $this->_buildGroup($name, $items, "xs:choice");        
        $g = new XsdChoice($this, $n);        
        $g->name = $name;
        $g->attributes = $attributes;
        return $g;
    }
    public function __construct(){
        $this->m_node = igk_createxmlnode("xs:schema");
        $this->m_node["xmlns:xs"] = self::SCHEMA;
    } 
    /**
     * set the notation
     * @param mixed $appinfo 
     * @param string $documentation 
     * @return $this 
     */
    public function setNotation($appinfo, $documentation=""){

        $notation = $this->m_notation ?? $this->m_node->add("xs:annotation");
        $notation->clearChilds();
        $notation->add("xs:appinfo")->Content = $appinfo;
        $notation->add("xs:documentation")->Content = $documentation;
        $this->m_notation = $notation;
        return $this;
    }   
    /**
     * 
     * @return mixed 
     * @throws Exception 
     */
    public function render(){
        return $this->m_node->render();
    }

    /**
     * add type element
     * @return XsdElementBuilder
     */
    public function addElement($name):XsdElementBuilder{
        $n = $this->m_node->add("xs:element")->setAttribute("name", $name);
        return XsdElementBuilder::Create($n, $this);
    }
    /**
     * add a globat attribute definition
     * @param mixed $name 
     * @param mixed $type 
     * @return XsdAttributeBuilder 
     */
    public function addAttribute($name, $type):XsdAttributeBuilder{
        $n = $this->m_node->add("xs:attribute")
        ->setAttribute("name", $name)
        ->setAttribute("type", $type)
        ;
        return XsdAttributeBuilder::Create($n, $this);
    }
    private function _buildGroup($name, $items, $tag="xs:group", $itemTag="xs:sequence"){
        $e = $this->m_node->add($tag)->setAttribute("name", $name);
        if ($items){
            $t = XsdBuilderUtility::BuildSequence($e, $items, $itemTag);
        }
        return $e;
    }
    public function addGroupElement($name, $items){
        
        $this->_buildGroup($name, $items);
        return $this;
    }
    public function addGroupAttributes($name, $items){
        $e = $this->m_node->add("xs:attributeGroup")->setAttribute("name", $name);
        if ($items){
            foreach($items as $k=>$v){
                // $e->add("xs:attribute")
                // ->setAttribute("name", $k)
                // ->setAttribute("type", $v);
                XsdBuilderUtility::AddSequenceElement($e, $k, $v, "xs:attribute");  
            }
        }
        return $this;
    }

    public function addEnumElement($name, $items){
        $e = $this->m_node->add("xs:element")->setAttribute("name", $name);
        $res = $e->add("xs:simpleType")->add("xs:restriction");
        $res->setAttribute("base", XsdTypes::TSTRING);
        foreach($items as $k){
            $res->add("xs:enumeration")->setAttribute("value", $k);
        }
        return $this;
    }
    public function addEnumType($name, $items){
        $e = $this->m_node->add("xs:simpleType")->setAttribute("name", $name);
        $res = $e->add("xs:restriction");
        $res->setAttribute("base", XsdTypes::TSTRING);
        foreach($items as $k){
            $res->add("xs:enumeration")->setAttribute("value", $k);
        }
        return $this;
    }
    public function addPatternElement($name, $pattern){
        $e = $this->m_node->add("xs:element")->setAttribute("name", $name);
        $res = $e->add("xs:simpleType")->add("xs:restriction");
        $res->setAttribute("base", XsdTypes::TSTRING);        
        $res->add("xs:pattern")->setAttribute("value", $pattern);        
        return $this;
    }
    /**
     * 
     * @param mixed $name 
     * @param mixed $type white space type
     * @return $this 
     */
    public function addWhiteSpaceElement($name, $type){
        $e = $this->m_node->add("xs:element")->setAttribute("name", $name);
        $res = $e->add("xs:simpleType")->add("xs:restriction");
        $res->setAttribute("base", XsdTypes::TSTRING);        
        $res->add("xs:whiteSpace")->setAttribute("value", $type);        
        return $this;
    }
    public function addLengthRestrictionElement($name, $minLength, $maxLength){
        $e = $this->m_node->add("xs:element")->setAttribute("name", $name);
        $res = $e->add("xs:simpleType")->add("xs:restriction");
        $res->setAttribute("base", XsdTypes::TSTRING);        
        $res->add("xs:minLength")->setAttribute("value", $minLength);        
        $res->add("xs:maxLength")->setAttribute("value", $maxLength);        
        return $this;
    }

    /**
     * define complex type
     * @param mixed $name 
     * @param mixed|array $sequences 
     * @return XsdBuilder 
     */
    public function addComplexTypeElement($name, $sequences = [], $attributes =null, $ctype=
    XsdBuilderUtility::SEQUENCE): XsdBuilder{
        $e = XsdBuilderUtility::BuildComplexType($this->m_node, $sequences, $ctype);
        $e->setAttribute("name", $name);
        
        if ($attributes){
            if (!XsdBuilderUtility::BindAnyAttribute($e, $attributes)){                
                foreach($attributes as $k=>$c){
                    XsdBuilderUtility::AddSequenceElement($e, $k, $c, "xs:attribute");
                }
            }
        }
        return $this;
    }
    public function addAttributeOnlyComplexTypeElement($name, $attributes = []): XsdBuilder{
        $e = $this->m_node->add("xs:complexType")->setAttribute("name", $name);
        if ($attributes){
            if (!XsdBuilderUtility::BindAnyAttribute($e, $attributes)){                
                foreach($attributes as $k=>$c){
                    XsdBuilderUtility::AddSequenceElement($e, $k, $c, "xs:attribute");                
                }
            }
        }
        return $this;
    }
}