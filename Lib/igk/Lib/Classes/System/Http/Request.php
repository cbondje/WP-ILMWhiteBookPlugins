<?php

namespace IGK\System\Http;

///<summary>request </summary>
class Request
{
    static $sm_instance;
    private $m_params;
    private $js_data;
    /**
     * prepared request information
     * @var mixed
     */
    private $prepared;
    public function __debugInfo()
    {
        return null;
    }
    public function getUploadedData(){
        if (!$this->prepared){
            $this->js_data = igk_io_get_uploaded_data();
        }
        return $this->js_data;
    }
    /**
     * prepare and return the updload data as json forma
     * @return mixed 
     */
    public function getJsonData(){
        $this->getUploadedData();
        if ($this->js_data !== null){
            //try to convert josn data data;
            return json_decode($this->js_data);
        } 
        return $this->js_data;
    }
  
    /**
     * set the request parameters
     */
    public function setParam($params)
    {
        $this->m_params = $params;
    }
    /**
     * get the set parameters
     */
    public function getParam($id = null, $default = null)
    {
        if ($id !== null) {
            return igk_getv($this->m_params, $id, $default);
        }
        return $this->m_params;
    }

    public static function getInstance()
    {
        if (self::$sm_instance === null)
            self::$sm_instance = new self();
        return self::$sm_instance;
    }
    private function __construct()
    {
    }
    /**
     * get the request value
     * @param mixed $name 
     * @param mixed|null $default 
     * @return mixed 
     */
    public function get($name, $default = null)
    {
        return igk_getr($name, $default);
    }
    public function getBase64($name, $tab=null){
        if ($tab === null){
            $tab = $_REQUEST;
        }
        if (key_exists($name, $tab)){
            return base64_decode($tab[$name]);
        }
        return null;
    }
    /**
     * 
     * @param mixed $name 
     * @param mixed|null $default 
     * @return mixed 
     */
    public function have($name, $default=null){
        if (key_exists($name, $_REQUEST)){
            return igk_getr($name, $_REQUEST);
        }
        return  $default;
    }
    /**
     * 
     * @param mixed $type 
     * @return mixed 
     */
    public function method($type)
    {
        return igk_server()->method($type);
    }
    /**
     * get the file
     * @return void 
     */
    public function file($name)
    {
        return igk_getv($_FILES, $name);
    }

    public function view_args($params=null, $default=null)
    {
        $t = igk_get_view_args();
        if ($params!==null) 
        {
            return igk_getv($t, $params, $default);
        }
        return $t;
    }
    public function __toString()
    {
        return json_encode($this);
    }
}
