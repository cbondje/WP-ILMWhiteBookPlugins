<?php
 
namespace IGK\CGI;
use IGKHtmlReader;

class IGKCGIServer
{
    private static $smTempFile;
    private static $sm_regFileCallback;
    private static $sm_serverInfo;
    private static $sm_instance;
    public function __get($v){
        return igk_getv(self::$sm_serverInfo, $v);
    }
    private function __construct(){

    }

    private static function RegFile($file){
        if (!self::$sm_regFileCallback){            
            self::$smTempFile = [];
            self::$sm_regFileCallback = 1;
            register_shutdown_function(function(){
                foreach(self::$smTempFile as $k){
                    unlink($k);
                }
                igk_wln("script kill");
            });
        }
        self::$smTempFile[] = $file;
    }
    private static function getInstance(){
        self::$sm_instance = new IGKCGIServer();
        return self::$sm_instance;
    }
    public static function UploadData(){
        if (self::$sm_instance){
            return self::$sm_instance->UploadedData;
        }
        return null;
    }
    // prepare cgi server
    public static function Prepare(){
        
        parse_str(igk_server()->QUERY_STRING, $_GET);

        $fin=fopen("php://stdin", "r");
        if(!$fin){
            return false;
        }else {
            $_readObj = new \stdClass();
            $ctype = igk_server()->CONTENT_TYPE;
            switch($ctype){
                case "application/json":
                case "application/x-www-form-urlencoded":
                    $size = igk_server()->CONTENT_LENGTH;
                    if ($size > 0){
                        $data = fread($fin, $size);
                        $_readObj->UploadedData = $data;
                    }
                    self::$sm_serverInfo = $_readObj;
                    if (($ctype == "application/x-www-form-urlencoded")&& ($_readObj->UploadedData)){
                        $c = urldecode($_readObj->UploadedData);
                        parse_str($c, $_POST);
                    }
                    return self::getInstance();
            }

            // case multipart/formdata
            $inf = explode(";", $ctype);
            array_shift($inf);
            $_cattr=  IGKHtmlReader::ReadAttributes(implode(" ", $inf));
            $boundary = igk_getv($_cattr, "boundary"); 

            $_type =null;
            $_attr =null;
            $_dtype = null;
            $h = -1; // read mode
            $key = 0;
            $v = "";
            while($l = fgets($fin)){
                $cl = trim($l);
                if (empty($cl) && ($h==1) ){
                    // read start read value 
                    $h = 0; 
                }
                else {
                    switch($h){
                        case -1:
                         $key = $cl;
                         $h = 1;
                        break;
                        case 1:
                            $inf = explode(";", $l);
                            $def = array_shift($inf);
                            $hinfo = explode(":", $def);
                            $hvalue = trim($hinfo[1]);
                            switch($hinfo[0]){
                                case "Content-Disposition":
                                    $_type = $hvalue;
                                    $_attr=  IGKHtmlReader::ReadAttributes(implode(" ", $inf));
                                    break;
                                case "Content-Type":
                                    $_dtype = $hvalue;
                                    break;
                            }
                        break;
                        case 0:
                            // read value;
                            if ($_attr){
                                $n = $_attr["name"];
                                if (array_key_exists("filename", $_attr)){
                                    $error = 0;
                                    $cf = null;
                                    // igk_wln("in array : ".$cl);
                                    if (strpos(trim($cl), $key) !== 0){                                        
                                    
                                        $cf = igk_io_sys_tempnam("cgi");
                                        
                                        $wfile = fopen($cf,"w+");
                                        if (!$wfile) 
                                            $error = 1; // failed to create temp file
                                        else 
                                            fwrite($wfile, $l);
                                        
                                        self::RegFile($cf);
                                        while($tl = fgets($fin)){
                                            if (strpos(trim($tl), $key) === 0){
                                                break;
                                            } 
                                            if ($wfile)
                                                fwrite($wfile, $tl);
                                        }
                                        if ($wfile)
                                            fclose($wfile);

                                    } else{
                                        $error = -2;
                                    }
                                    $finfo = [
                                        "name"=>$_attr["filename"],
                                        "tmp_name"=>$cf,
                                        "type"=>$_dtype,
                                        "size"=>$error?-1: filesize($cf),
                                        "error"=>$error
                                    ];
                                    $_FILES[$n]=$finfo;
                                    // igk_wln_e("read file data:", $finfo, $_attr, file_get_contents($cf));
                                    $_type = null;
                                    $_attr = null;
                                    $_dtype = null;
                                    $v = "";
                                    $h = 1;
                                }
                                else {
                                    if (strpos($cl, $key) === 0){ 
                                        $_POST[$n] = $v;
                                        $_type = null;
                                        $_attr = null;
                                        $_dtype = null;
                                        $v = "";
                                        $h = 1;
                                    }
                                    else {
                                        $v .= $l;
                                    }
                                }
                            }
                        break;
                    }
                }
                // if (empty($cl)){
                //    // read  
                //    $h =0; 
                // }
                // else{ 
                  

                //     $h = 1;
                //     if (preg_match("/^Content-Disposition:/", $l)){ 
                //         $s = substr($inf[0], strlen("Content-Disposition:")+1);
                //         $_type = $s;
                //         array_shift($inf);
                //         $_attr=  IGKHtmlReader::ReadAttributes(implode(" ", $inf));                   
                //         // igk_wln("type= ".$_type. " attributes : ",$_attr);                    
                //     }else {
                //         if ($_type){
                //             switch($_type){
                //                case "form-data":
                //                 if (strpos($cl, $key) === 0){ 
                //                     $_POST[$_attr["name"]] = $v;
                //                     $_type = null;
                //                     $_attr = null;
                //                     $h=0;
                //                     $v = "";
                //                 } else {
                //                     $v .= $l;
                //                 } 
                //                 break;
                //                 default:
                //                     igk_wln_e("not handle:".$_type);
                //                 break;
                //             }
                //         }
                //         else { 
                //             $key = $cl; 
                //         }
                //     }
                // }
               // echo "bLine = ".$l."<br />";
            }
            //igk_wln_e("POST", $_POST);
            // $buffsize=4096;
            // $s="";
            // while(($c=fread($fin, $buffsize))){
            //     $s .= $c;
            // }
            // fclose($fin);
            // igk_wln("read data", $s, empty($s));
            // if (!empty($s)){
            //     // load 
            //     $c = ";";
            //     foreach(explode("\n", $s) as $line){
            //         echo "line:".$line ."<br />";
            //     }
            // }
            fseek($fin, 0, SEEK_SET);
            return 1;
        }
        return false;
    }
}