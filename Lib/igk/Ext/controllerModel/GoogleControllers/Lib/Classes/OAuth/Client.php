<?php

namespace IGK\Core\Ext\Google\OAuth;

///<summary></summary>
/**
 * @desc client
 *
 * */
class Client{
    const WELLKNOW_CONFIG = "https://accounts.google.com/.well-known/openid-configuration";

    var $api_key;
    var $client_id;
    var $client_secret;
    var $scope;

    public function __construct()
    {
    }
    public function authinfo(){
        static $_auth;
        if ($_auth ===null){
            $_auth = $this->_get_wellknow_config();
        }
        return $_auth;
    }
    public function getTokenInfo($code, $redirect_uri, $grant_type="authorization_code"){
        $s  = igk_curl_post_uri($this->authinfo()->token_endpoint, [
            "code"=>$code,
            "client_id"=>$this->client_id,
            "client_secret"=>$this->client_secret,
            "grant_type"=>$grant_type,
            "redirect_uri"=>$redirect_uri,
        ], null, ["Content-Type"=>"application/x-www-form-urlencoded"]);
        if (($stat = igk_curl_info()["Status"]) !=200)
        {
            igk_ilog("failed: ".$stat);
            return null;
        }
        return json_decode($s);
    }
    public function getUserInfo($tokeninfo){
        $m = "Authorization: {$tokeninfo->token_type} {$tokeninfo->access_token}";
        $s = igk_curl_post_uri($this->authinfo()->userinfo_endpoint,null,
            ["POST"=>1], [$m]);
        if (($stat = igk_curl_info()["Status"]) !=200)
        {
            igk_ilog("failed: ".$stat);
            return null;
        }
        return json_decode($s);
    }
    private function _get_wellknow_config(){
        $f = null;
        if (function_exists("igk_google_data_dir")){
            if (file_exists($f = igk_google_data_dir()."/wellknow.config.json")){
                return json_decode(file_get_contents($f));
            }
        }
        $d = json_decode($data = igk_curl_post_uri(self::WELLKNOW_CONFIG, null,null, ["Method"=>"GET"]));
        if ($f!==null){
            igk_io_w2file($f, $data);
        }
        return $d;
    }
}