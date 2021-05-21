<?php
// @file: IGKGoogleCssUri.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente namespace: IGK\Core\Ext\Google</summary>
/**
* Represente IGK\Core\Ext\Google namespace
*/
namespace IGK\Core\Ext\Google;
// DIRECT RENDERINGuse IGKHtmlRelativeUriValueAttribute as IGKHtmlRelativeUriValueAttribute;
use IGKIO as IGKIO;
use \IGKHtmlRelativeUriValueAttribute;

///parse uri to local
/**
*/
class IGKGoogleCssUri{
    private $m_file;
    private $m_uri;
    ///<summary></summary>
    ///<param name="f"></param>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $f
    * @param mixed $uri
    */
    public function __construct($f, $uri){
        $this->m_file=$f;
        $this->m_uri=$uri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getValue(){
        $f=$this->m_file;
        if(empty($f))
            return null;
        if(!file_exists($f)){
            if(!igk_sys_env_production()){
                return $this->m_uri;
            }
            if(IGKIO::CreateDir($dir=dirname($f))){
                $basename=basename($f);
                igk_io_w2file($f=($dir."/notavailable.css"), "/* font not available for : {$basename} */", false);
                $this->file=$f;
            }
        }
        return (new IGKHtmlRelativeUriValueAttribute($f))->getValue();
    }
}
