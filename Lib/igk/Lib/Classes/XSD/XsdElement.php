<?php

namespace IGK\XSD;

use ArrayAccess;

abstract class XsdElement implements ArrayAccess{
    protected $m_node;

    public function getNode(){
        return $this->m_node;
    }

    public function offsetExists($offset)
    {
        return $this->m_node->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->m_node->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->m_node->offsetSet($offset, $value);
        return $this;
    }

    public function offsetUnset($offset)
    {
        return $this->m_node->offsetUnset($offset);
    }
}