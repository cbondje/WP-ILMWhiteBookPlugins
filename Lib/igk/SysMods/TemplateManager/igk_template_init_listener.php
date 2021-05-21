<?php
// @file: igk_template_init_listener.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: igk_template_init_listener</summary>
/**
* Represente igk_template_init_listener class
*/
class igk_template_init_listener implements IIGKControllerInitListener{
    private $m_zip;
    ///<summary></summary>
    ///<param name="zip"></param>
    /**
    * 
    * @param mixed $zip
    */
    public function __construct($zip){
        $this->m_zip=$zip;
    }
    ///<summary></summary>
    ///<param name="dir"></param>
    /**
    * 
    * @param mixed $dir
    */
    public function addDir($dir){
        $this->m_zip->addEmptyDir("src/".$dir);
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="content"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $content
    */
    public function addSource($name, $content){
        $this->m_zip->addFromString("src/".$name, $content);
    }
}
?>