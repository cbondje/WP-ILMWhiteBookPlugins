<?php



///<summary>represent server management </summary>
/**
* represent server management
*/
final class IGKServer{
    private $data;
    private static $sm_server;
    ///<summary></summary>
    /**
    * 
    */
    private function __construct(){
        $this->prepareServerInfo();
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function __get($n){
        if(isset($this->data[$n]))
            return $this->data[$n];
        return null;
    }
    /**
     * get encoding support
     */
    public function accepts($list){
        $accept = $this->HTTP_ACCEPT_ENCODING;
        if (is_array($list)){
            foreach($list as $k){
                if (strstr($accept, $k)){
                    return true;
                }
            }
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    public function __isset($n){
        return isset($this->data[$n]);
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $v
    */
    public function __set($n, $v){
        if($v === null){
            unset($this->data[$n]);
        }
        else
            $this->data[$n]=$v;
    }
    ///<summary>return if server accept return type</summary>
    public function accept($type="html"){
        static $accept_type= null;
        if ($accept_type===null){
            $accept_type = [
                "html"=>"text/html",
                "json"=>"application/json"
            ];
        }
        $a = explode(",", $this->HTTP_ACCEPT);
        if (in_array("*/*", $a)){
            return true;
        }
        $mtype = igk_getv($accept_type, $type, null);
        return $mtype && in_array($mtype, explode(",", $this->HTTP_ACCEPT));
    }

    public function get($name, $default=null){
        return igk_getv($this->data, $name, $default);
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function getInstance(){
        $r=& self::$sm_server;
        return igk_create_instance(__CLASS__, $r, function($s){
            return new $s();
        });
    }
    ///<summary></summary>
    ///<param name="file"></param>
    /**
    * 
    * @param mixed $file
    */
    public function IsEntryFile($file){
        return $file === realpath($this->SCRIPT_FILENAME);
    }
    ///<summary>check if this request is POST</summary>
    /**
    * check if this request is POST
    */
    public function ispost(){
        return $this->REQUEST_METHOD == "POST";
    }
    ///<summary>check for method. if type is null return the REQUEST_METHOD</summary>
    /**
    * check for method
    */
    public function method($type=null){
			if ($type===null)
				return $this->REQUEST_METHOD;
        return $this->REQUEST_METHOD == $type;
    }
    public function isMultipartFormData(){
        return strpos($this->CONTENT_TYPE, "multipart/form-data") === 0;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function prepareServerInfo(){
        $this->data=array();
        foreach($_SERVER as $k=>$v){
            $this->data[$k]=$v;
        }
        $this->IGK_SCRIPT_FILENAME=igk_html_uri(realpath($this->SCRIPT_FILENAME));
        $this->IGK_DOCUMENT_ROOT= igk_html_uri(realpath($this->DOCUMENT_ROOT))."/";
        $sym_root=$this->IGK_DOCUMENT_ROOT !== $this->DOCUMENT_ROOT;
        $c_script=$this->IGK_SCRIPT_FILENAME;
        if(!$sym_root)
            $c_script=$this->SCRIPT_FILENAME;
        if(!empty($doc_root=$this->IGK_DOCUMENT_ROOT)){
            $doc_root=str_replace("\\", "/", realpath($doc_root));
            $self=substr($c_script, strlen($doc_root));
            if((strlen($self) > 0) && ($self[0] == "/"))
                $self=substr($self, 1);
            $basedir=str_replace("\\", "/", dirname($doc_root."/".$self));
            $this->IGK_BASEDIR=$basedir;
            $uri=$this->REQUEST_SCHEME."://".$this->HTTP_HOST;
            $query=substr($basedir, strlen($doc_root) + 1);
            if(!empty($query))
                $query .= "/";
            $baseuri=$uri."/".$query;
            $this->IGK_BASEURI=$baseuri;
        }
        $this->IGK_CONTEXT=($t_=isset($this->HTTP_HOST)) ? "html": "cmd";
        $this->LF=$t_ ? "\n": "<br />";
        if(!empty($env=$this->ENVIRONMENT)){
            $this->ENVIRONMENT=defined('IGK_ENV_PRODUCTION') ? "production": $env;
        }
        else{
            $this->ENVIRONMENT=defined('IGK_ENV_PRODUCTION') ? "production": "development";
        }
        if(!isset($this->WINDIR)){
            $this->WINDIR=($this->OS == "Windows_NT");
        }
        if(isset($_SERVER['REDIRECT_STATUS']) && isset($_GET["__c"])){
            $_get=array_slice($_GET, 0);
            $this->REDIRECT_CODE=$_get["__c"];
            $this->REDIRECT_OPT=array();
            unset($_get["__c"]);
            $_SERVER["QUERY_STRING"]=http_build_query($_get);
        }
        $this->REQUEST_PATH=explode("?", $this->REQUEST_URI)[0];
    }
    ///<summary></summary>
    /**
    * 
    */
    public function toArray(){
        return $this->data;
    }
}
