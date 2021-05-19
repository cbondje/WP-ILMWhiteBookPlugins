<?php
// @author: C.A.D BONDJE DOUE
// @file: Request.php
// @desc: 
// @date: 20210517 10:55:05
namespace ILYEUM;


class Request{
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
            return ilm_getv($this->m_params, $id, $default);
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
        return ilm_getr($name, $default);
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
            return ilm_getr($name, $_REQUEST);
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
        return ilm_server()->method($type);
    }
    /**
     * get the file
     * @return void 
     */
    public function file($name)
    {
        return ilm_getv($_FILES, $name);
    }

  
    public function __toString()
    {
        return json_encode($this);
    }
}