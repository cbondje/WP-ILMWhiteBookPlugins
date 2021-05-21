<?php



namespace IGK\XSD;


interface IXsdReference{
    function getRefType();
    /** @return mixed  */
    function getRef();
}