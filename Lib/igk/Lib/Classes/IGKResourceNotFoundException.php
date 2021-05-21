<?php



 
///<summary>Represente class: IGKResourceNotFoundException</summary>
/**
* Represente IGKResourceNotFoundException class
*/
class IGKResourceNotFoundException extends IGKException {
    private $m_file;
    ///<summary></summary>
    ///<param name="message"></param>
    ///<param name="file"></param>
    /**
    * 
    * @param mixed $message
    * @param mixed $file
    */
    public function __construct($message, $file){
        parent::__construct($message, 500);
        $this->m_file=$file;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getResourceFile(){
        return $this->m_file;
    }
}