<?php


///<summary>only used to get global application settings</summary>
class IGKAppSetting{
    ///<summary>.ctr</summary>
    public function __construct(){        
    }
    private function & _setting(){
        $app = null;  
        static $sm_setting = null;       
        if (!isset($_SESSION)){
            if (igk_is_cmd()){

                if($sm_setting === null){
                    $sm_setting = new IGKDummySetting();
                }
                $app = & $sm_setting;
                return $app;
            }
            igk_wln_e("No Session started;");
        }
        if (isset($_SESSION[IGK_APP_SESSION_KEY])){
            $app =  & $_SESSION[IGK_APP_SESSION_KEY];
        }   
        return $app;
    }
    public function __get($n){
        if (method_exists($this, $fc = 'get'.$n)){
            return call_user_func_array([$this, $fc], []);
        }
        return igk_getv($this->_setting(), $n);
    }
    /**
     * Summary of __set
     * @param mixed $n 
     * @param mixed $v 
     * @return void
     */
    public function __set($n, $v){
        if (method_exists($this, $fc = 'set'.$n)){
            return call_user_func_array([$this, $fc], [$v]);
        }
        $setting =  & $this->_setting();
        if ($setting){
            if ($v===null){
                unset( $setting->{$n});
            }else { 
                $setting->{$n} = $v; 
            }
        } 
    }
	public function __isset($n){
		$g = $this->_setting();
		return isset($g->$n);
	}

    public function getstartAt(){
        return $this->_setting()->{IGK_CREATE_AT};
    }
    public function getSessionId(){
        return $this->_setting()->{IGK_SESSION_ID};
    }
    public function getVersion(){
        return $this->_setting()->{IGK_VERSION_ID};
    }
    ///<summary>get the current document index</summary>
    public function getCurrentDocumentIndex(){
        return $this->_setting()->{IGK_CURRENT_DOC_INDEX_ID};
    }
    public function setCurrentDocumentIndex($value){
		!is_numeric($value) && igk_die("not a numeric value ".$value);
        $this->_setting()->{IGK_CURRENT_DOC_INDEX_ID} = $value;
    }
}
