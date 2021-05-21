<?php

namespace IGK\XSD;

use IGKHtmlItem;
use IGKHtmlItemBase;

abstract class XsdBuilderUtility
{
    const SEQUENCE = "xs:sequence";
    public static function BuildDef($node, $defs)
    {
        $s = $node;
        foreach ($defs as $k => $v) {
            if ($v === -1) {
                $s->add("xs:any")->setAttribute("minOccurs", "0")
                    ->setAttribute("maxOccurs", "unbounded")
                    ->setAttribute("processContents", "strict");
                continue;
            }
            if ($v === -2) {
                $s->add("xs:any")->setAttribute("minOccurs", "0")
                    ->setAttribute("maxOccurs", "unbounded")
                    ->setAttribute("processContents", "skip");
                continue;
            }

            if (is_object($v) && $v instanceof IXsdReference) {

                $m = $s->add($v->getRefType())->setAttribute("ref", $v->getRef());
                if (isset($v->attributes)) {
                    $m->setAttributes($v->attributes);
                }
                continue;
            }
            if (is_object($v) && $v instanceof IGKHtmlItemBase) {
                $s->add($v);
                continue;
            }
            XsdBuilderUtility::AddSequenceElement($s, $k, $v);
        }
        return $s;
    }
    public static function BuildSequence($node, $defs, $ctype = self::SEQUENCE)
    {
        $s = $node->add($ctype);
        self::BuildDef($s, $defs);       
        return $s;
    }
    public static function BuildComplexType($node, $defs, $ctype = "xs:sequence", $tattributes = null)
    {
        $b = $node->add("xs:complexType");
        if ($defs && (count($defs) > 0)) {
            $s = $b->add($ctype);
            if ($tattributes)
                $s->setAttributes($tattributes);

            self::BuildDef($s, $defs);

            // foreach ($defs as $k => $v) {
            //     if ($v === -1) {
            //         $s->add("xs:any")->setAttribute("minOccurs", "0")
            //             ->setAttribute("maxOccurs", "unbounded")
            //             ->setAttribute("processContents", "strict");
            //         continue;
            //     }
            //     if ($v === -2) {
            //         $s->add("xs:any")->setAttribute("minOccurs", "0")
            //             ->setAttribute("maxOccurs", "unbounded")
            //             ->setAttribute("processContents", "skip");
            //         continue;
            //     }

            //     if (is_object($v) && $v instanceof IXsdReference) {

            //         $m = $s->add($v->getRefType())->setAttribute("ref", $v->getRef());
            //         if (isset($v->attributes)) {
            //             $m->setAttributes($v->attributes);
            //         }
            //         continue;
            //     }
            //     XsdBuilderUtility::AddSequenceElement($s, $k, $v);
            // }
        }
        return $b;
    }
    public static function BindAnyAttribute($node, $attributes)
    {
        if (is_integer($attributes)) {
            $type = "";
            switch ($attributes) {
                case XsdBuilder::ANY_ATTRIBUTE:
                    break;
                case XsdBuilder::ANY_ATTRIBUTE_LAX:
                    $type = "lax";
                    break;
                case XsdBuilder::ANY_ATTRIBUTE_SKIP:
                    $type = "skip";
                    break;
                default:
                    throw new XsdBuilderException("attribute value not allowed");
            }
            $node->add("xs:anyAttribute")->setAttribute("processContents", $type);
            return true;
        }
        return false;
    }
    public static function AddSequenceElement($node, $name, $value, $tag = "xs:element")
    {
        $e = $node->add($tag);
        $e->setAttribute("name", $name);
        if (is_array($value)) {
            $o = igk_createobj_filter($value, [
                "type" => XsdTypes::TSTRING, "maxOccurs" => null,
                "minOccurs" => null,
                "require" => null,
                "default" => null
            ]);
            $e->setAttribute("type", $o->type);
            if ($o->maxOccurs !== null) {
                $e->setAttribute("maxOccurs", $o->maxOccurs);
            }
            if ($o->minOccurs !== null) {
                $e->setAttribute("minOccurs", $o->minOccurs);
            }
            if ($o->require !== null) {
                $e->setAttribute("use", "required");
            }
            if ($o->default !== null) {
                $e->setAttribute("default", $o->default);
            }
        } else {

            $e->setAttribute("type", $value);
        }
    }
}
