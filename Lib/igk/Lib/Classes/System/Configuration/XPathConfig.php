<?php
namespace IGK\System\Configuration;

//---------------------------------------------------------------------
// XPATH: separated config entry with '/'. [index] if multiple access
//
/**
 * represent the xpath configuration
 */
class XPathConfig{
    private $m_config;
    public function __construct($config){
        $this->m_config = $config;
    }
    /**
     * get the config by XPath 
     * 
     */
    public function get($path, $default=null, $strict=0){
        return igk_conf_get($this->m_config, $path, $default, $strict);
    }
}