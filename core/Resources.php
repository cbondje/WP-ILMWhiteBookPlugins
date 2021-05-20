<?php
// @author: C.A.D BONDJE DOUE
// @file: Resources.php
// @desc: 
// @date: 20210517 10:35:17
namespace ILYEUM;


class Resources{
    public function gets($text){
        return ilm_getv($this->m_resources, $text, $text);
    }
    public function __construct(){
        $this->_init_resources();
    }
    private function _init_resources(){
        $l=[];
        include(ILM_WHITE_BOOK_DIR."/Configs/Lang/lang.fr.presx");
        $this->m_resources = $l;
    }
}