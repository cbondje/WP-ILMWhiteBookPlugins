<?php

namespace IGK\XML;

use DOMDocument;

///<summary></summary>
class XSDValidator
{
    /**
     * validate with source 
     * @param mixed $source xml source
     * @param mixed $xsd xsd source
     * @param mixed|null $error allow error
     * @return bool|void return true if succeed
     */
    public static function ValidateSource($source, $xsd, &$error = null)
    {

        $dom = new DOMDocument;
        $dom->loadXml($source);
        if ($error !== null) {
            libxml_use_internal_errors(true);
        }
        if ($success = !$dom->schemaValidateSource($xsd)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $type = "WARNING";
                        break;
                    case LIBXML_ERR_ERROR:
                        $type = "ERROR";
                        break;
                    case LIBXML_ERR_FATAL:
                        $type = "FATAL";
                        break;
                }
                $error = ["type" => $type, "message" => $error->message];
            }
        }
        return !$success;
        
    }
}
