<?php



///<summary>Represent a mail </summary>
/**
* Represent a mail
*/
class IGKMail extends IGKObject implements IIGKMailAttachmentContainer {
    const CONTENT_HTML_TEXT="text/html";
    const CONTENT_IMG_PNG="image/png";
    const CONTENT_PLAIN_TEXT=IGK_CT_PLAIN_TEXT;
    const PART_ALTERNATIVE="multipart/alternative";
    const PART_MIXED="multipart/mixed";
    private $ErrorMsg;
    private $html_charset="iso-8859-1";
    private $m_files;
    private $m_from;
    private $m_htmlmsg;
    private $m_pwd;
    private $m_replyto;
    private $m_smtp_port;
    private $m_smtphost;
    private $m_socketTimeout=15;
    private $m_socketType="tls";
    private $m_textmsg;
    private $m_title;
    private $m_to;
    private $m_toBcc;
    private $m_tocc;
    private $m_useAuth;
    private $m_user;
    private $text_charset="iso-8859-1";

    ///<summary>send mail</summary>
    /**
     * send mail
     */
    public static function Mail($to, $subject, $message, $from=null, $reply=null, $attachement=null, $type="text/html", callable $init=null){
        $mail= new static();
        if ($init){
            $init($mail);
        }
        if ($message instanceof IGKHtmlItemBase){
            $opt = igk_xml_create_render_option();
            $opt->Context = "mail";
            $message = $message->render($opt); 
        }
        $mail->HtmlMsg=$message;
        $mail->Title=$subject;
        $mail->From=$from;
        $mail->HtmlCharset="utf-8";
        $mail->TextCharset="utf-8";
        $mail->addTo($to);
        if(is_array($attachement)){
            foreach($attachement as  $v){
                if(igk_reflection_class_extends($v, IGKMailAttachement::class)){
                    $mail->attach($v);
                }
                else
                    $mail->attachContent($v->Content, $v->ContentType, $v->CID);
            }
        }
        return $mail->sendMail();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        $this->ErrorMsg = "";
        $this->m_files=array();
        $this->m_to=array();
        $this->m_tocc=array();
        $this->m_toBcc=array();
        $app=igk_app();
        if($app){
            $this->m_useAuth=$app->Configs->mail_useauth;
            $this->m_smtphost=$app->Configs->mail_server;
            $this->m_user=$app->Configs->mail_user;
            $this->m_pwd=$app->Configs->mail_password;
            $this->m_smtp_port=$app->Configs->mail_port;
            $this->m_socketType=$app->Configs->mail_authtype;
        }
        // $this->HtmlCharset="utf-8";
        // $this->TextCharset="utf-8";
    }
    ///<summary>send mail with TLS by using socket</summary>
    /**
    * send mail with TLS by using socket
    */
    private function __sendMailTLS($headers, $message){
        if(!igk_network_available()){
            return 0;
        } 
        $errno=0;//IGK_STR_EMPTY;
        $errstr=IGK_STR_EMPTY;
        $host=$this->m_smtphost;
        $user=$this->m_user;
        $pass=$this->m_pwd;
        $port=$this->m_smtp_port;
        $timeout=$this->m_socketTimeout;
        $socket=@fsockopen($host, $port, $errno, $errstr, $timeout);
        if(!$socket){
            $emsg="ERROR: ".$host." ".$port." - {$errstr} ({$errno})";
            igk_debug_wln($emsg);
            $this->ErrorMSG=$emsg;
            igk_debuggerview()->add("div")->Content=$emsg;
            return false;
        }
        else{
            $recipients=$this->m_to;
            if(igk_count($this->m_tocc) > 0)
                $recipients=array_merge($recipients, $this->m_tocc);
            if(igk_count($this->m_toBcc) > 0)
                $recipients=array_merge($recipients, $this->m_toBcc);
            $subject=$this->Title;
            if(!$this->server_parse($socket, '220')){
                $this->_closeSocket($socket);
                return false;
            }
            fwrite($socket, 'EHLO '.$host.IGK_CLF);
            if(!$this->server_parse($socket, '250')){
                $this->_closeSocket($socket);
                return false;
            }
            if($this->SocketType == "tls"){
                fwrite($socket, 'STARTTLS'.IGK_CLF);
                if(!$this->server_parse($socket, '220')){
                    $this->_closeSocket($socket);
                    return false;
                }
                if(false == @stream_socket_enable_crypto($socket, true,
                 STREAM_CRYPTO_METHOD_TLS_CLIENT)){
                    $this->_closeSocket($socket);
                    igk_debug_wln("unable to start tls encryption");
                    return false;
                }
                fwrite($socket, 'HELO '.$host.IGK_CLF);
                if(!$this->server_parse($socket, '250'))
                    return false;
            } 
            igk_debug_wln("AUTH LOGIN");
            fwrite($socket, 'AUTH LOGIN'.IGK_CLF);
            if(!$this->server_parse($socket, '334')){
                $this->_closeSocket($socket);
                return false;
            }
            igk_debug_wln("AUTH USER ". $user);
            fwrite($socket, base64_encode($user).IGK_CLF);
            if(!$this->server_parse($socket, '334')){
                $this->_closeSocket($socket);
                return false;
            }
            igk_debug_wln("AUTH pass ". igk_app()->Configs->mail_password);
            fwrite($socket, base64_encode($pass).IGK_CLF);
            if(!$this->server_parse($socket, '235')){
                $this->_closeSocket($socket);
                return false;
            }
            $from = $this->FROM;
            if (!preg_match("/\<(?P<form>[^\^]+)\>/", $from)){
                $from = "<".$from.">";
            }

            igk_debug_wln("MAIL FROM: ". $from);
            fwrite($socket, 'MAIL FROM: '.$from.''.IGK_CLF);
            if(!$this->server_parse($socket, '250')){
                $this->_closeSocket($socket);
                return false;
            }
            foreach($recipients as $email){
                fwrite($socket, 'RCPT TO: <'.$email.'>'.IGK_CLF);
                if(!$this->server_parse($socket, '250')){
                    $this->_closeSocket($socket);
                    return false;
                }
            }
            fwrite($socket, 'DATA'.IGK_CLF);
            if(!$this->server_parse($socket, '354')){
                $this->_closeSocket($socket);
                return false;
            }
            fwrite($socket, 'Subject: '.$subject.IGK_CLF.'To: <'.implode('>, <', $this->m_to).'>'.IGK_CLF.$headers."\r\n\r\n".$message.IGK_CLF);
            fwrite($socket, "\r\n.\r\n");
            igk_debug_wln("SEND....Subject");
            if(!$this->server_parse($socket, '250')){
                $this->_closeSocket($socket);
                return false;
            }
            // igk_debug_wln("GOOD SENDDING --- CLOSE SOCKET");
            $this->_closeSocket($socket);
            return true;
        }
    }
    ///<summary></summary>
    ///<param name="socket"></param>
    /**
    * 
    * @param mixed $socket
    */
    private function _closeSocket($socket){
        fwrite($socket, 'QUIT'.IGK_CLF);
        fclose($socket);
    }
    ///<summary></summary>
    ///<param name="boundary"></param>
    /**
    * 
    * @param mixed $boundary
    */
    private function _getHeader($boundary){
        $header=IGK_STR_EMPTY;
        if($this->m_from)
            $header .= "From: ".$this->m_from.IGK_CLF;
        if($this->m_replyto)
            $header .= "Reply-To: ".$this->m_replyto.IGK_CLF;
        $CC=self::GetMailList($this->m_tocc);
        if(!empty($CC)){
            $header .= "Cc: <".$CC.">\r".IGK_LF;
        }
        $CC=self::GetMailList($this->m_toBcc);
        if(!empty($CC)){
            $header .= "Bcc: ".$CC.IGK_CLF;
        }
        $header .= "MIME-Version: 1.0\r".IGK_LF;
        $header .= "Content-Type: multipart/related; boundary=$boundary\r".IGK_LF;
        return $header;
    }
    ///<summary></summary>
    ///<param name="to"></param>
    /**
    * 
    * @param mixed $to
    */
    public function addTo($to){
        if(is_string($to)){
            $h=explode(",", $to);
            if(igk_count($h) > 1){
                foreach($h as $k){
                    $this->m_to[]=$k;
                }
            }
            else
                $this->m_to[]=$to;
        }
        else{
            if(is_array($to)){
                foreach($to as $k=>$v){
                    switch(strtolower($k)){
                        case "cc":
                        $this->addToCC($v);
                        break;
                        case "cci":
                        $this->addToGCC($v);
                        break;default: $this->addTo($v);
                        break;
                    }
                }
            }
            else
                $this->m_to[]=$to;
        }
    }
    ///<summary></summary>
    ///<param name="to"></param>
    /**
    * 
    * @param mixed $to
    */
    public function addToCC($to){
        if(is_string($to)){
            $h=explode(",", $to);
            if(igk_count($h) > 1){
                foreach($h as $k){
                    $this->m_tocc[]=$k;
                }
            }
            else
                $this->m_tocc[]=$to;
        }
        else
            $this->m_tocc[]=$to;
    }
    ///<summary></summary>
    ///<param name="to"></param>
    /**
    * 
    * @param mixed $to
    */
    public function addToGCC($to){
        if(is_string($to)){
            $h=explode(",", $to);
            if(igk_count($h) > 1){
                foreach($h as $k){
                    $this->m_toBcc[]=$k;
                }
            }
            else
                $this->m_toBcc[]=$to;
        }
        else
            $this->m_toBcc[]=$to;
    }
    ///<summary></summary>
    ///<param name="attachement"></param>
    /**
    * 
    * @param mixed $attachement
    */
    public function attach($attachement){
        if($attachement)
            $this->m_files[]=$attachement;
    }
    ///<summary></summary>
    ///<param name="content"></param>
    ///<param name="contentType" default="IGK_CT_PLAIN_TEXT"></param>
    ///<param name="cid" default="null"></param>
    /**
    * 
    * @param mixed $content
    * @param mixed $contentType the default value is IGK_CT_PLAIN_TEXT
    * @param mixed $cid the default value is null
    */
    public function attachContent($content, $contentType=IGK_CT_PLAIN_TEXT, $cid=null){
        $attach=new IGKMailAttachement();
        $attach->Content=$content;
        $attach->ContentType=$contentType;
        $attach->Type="Content";
        $attach->CID=$cid;
        $this->m_files[]=$attach;
        return $attach;
    }
    ///<summary></summary>
    ///<param name="file"></param>
    ///<param name="contentType" default="IGK_CT_PLAIN_TEXT"></param>
    ///<param name="cid" default="null"></param>
    /**
    * 
    * @param mixed $file
    * @param mixed $contentType the default value is IGK_CT_PLAIN_TEXT
    * @param mixed $cid the default value is null
    */
    public function attachFile($file, $contentType=IGK_CT_PLAIN_TEXT, $cid=null){
        $attach=new IGKMailAttachement();
        $attach->Link=$file;
        $attach->Content=file_exists($file) ? IGKIO::ReadAllText($file): null;
        $attach->ContentType=$contentType;
        $attach->Type="Uri";
        $attach->CID=$cid;
        $this->m_files[]=$attach;
        return $attach;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearTo(){
        $this->m_to=array();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getFrom(){
        return $this->m_from;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getHtmlCharset(){
        return $this->html_charset;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getHtmlMsg(){
        return $this->m_htmlmsg;
    }
    ///<summary></summary>
    ///<param name="tab"></param>
    /**
    * 
    * @param mixed $tab
    */
    static function GetMailList($tab){
        $o=IGK_STR_EMPTY;
        foreach($tab as $k=>$v){
            if($k > 0)
                $o .= ",";
            $o .= self::MailEntry($v);
        }
        return $o;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getPort(){
        return $this->m_smtp_port;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getPwd(){
        return $this->m_pwd;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getReplyTo(){
        return $this->m_replyto;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSmtpHost(){
        return $this->m_smtphost;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSocketTimeout(){
        return $this->m_socketTimeout;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSocketType(){
        return $this->m_socketType;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTextCharset(){
        return $this->text_charset;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTextMsg(){
        return $this->m_textmsg;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTitle(){
        return $this->m_title;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getToString(){
        return self::GetMailList($this->m_to);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUseAuth(){
        return $this->m_useAuth;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUser(){
        return $this->m_user;
    }
    ///<summary></summary>
    ///<param name="$c"></param>
    /**
    * 
    * @param mixed $c
    */
    static function MailEntry($c){
        $out=IGK_STR_EMPTY;
        if(is_numeric($c) || (is_string($c) && !empty($c))){
            $out .= $c;
        }
        else if(is_object($c) && (method_exists(get_class($c), IGK_FC_GETVALUE))){
            $out .= $c->getValue();
        }
        return $out;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function sendMail(){
        $boundary=igk_new_id();
        $to=$this->getToString();
        $title=$this->getTitle();
        $header=$this->_getHeader($boundary);
        $t=trim($to);
        if(empty($t))
            return false;
        $lf=IGK_CLF;
        $message=$lf;
        $message .= "This is a multi-part message in MIME Format.".$lf;
        $message .= "--$boundary".$lf;
        $j1=$this->TextMsg;
        $j2=$this->HtmlMsg;
        $LINE=$lf.$lf;
        if(!((empty($j1) && empty($j2)))){
            $message .= "Content-Type: multipart/alternative; boundary=sub_$boundary".$lf;
            if(!empty($j1)){
                $message .= $LINE."--sub_$boundary".$lf;
                $message .= "Content-Type: text/plain; charset=\"".$this->text_charset."\"".$LINE;
                $message .= $j1;
            }
            if(!empty($j2)){
                $message .= $LINE."--sub_$boundary".$lf;
                $message .= "Content-Type:text/html; charset=\"".$this->html_charset."\"".$LINE;
                $message .= $j2;
            }
            $message .= $LINE."--sub_$boundary--".$lf;
        }
        foreach($this->m_files as $v){
            $data=$v->getData();
            $message .= $LINE."--$boundary".$lf;
            $message .= "Content-Type: ".$v->ContentType;
            if($v->Name){
                $message .= "; name=\"".$v->Name."\"";
            }
            $message .= $lf;
            $message .= "Content-Transfer-Encoding: base64".$lf;
            if(!$v->Visible){
                $message .= "Content-Disposition: attachment".$lf;
            }
            if($v->CID){
                $message .= "Content-ID: <".$v->CID.">".$lf;
            }
            $message .= $lf.$lf.$data;
        }
        $message .= $lf."--$boundary--".$lf;
        $message .= "end of the multi-part";
        if($this->UseAuth){
            if(extension_loaded("openssl")){
                $v=$this->__sendMailTLS($header, $message);
                if (!$v){
                    igk_ilog("Mail Error:". $this->ErrorMsg); 
                }
                return $v;
            }
            else{
                igk_ilog("no openssl extension loaded", __METHOD__);
            }
            return false;
        }
        else{
            if(@mail($to, $title, $message, $header) === true){
                return true;
            }
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="socket"></param>
    ///<param name="expected_response"></param>
    /**
    * 
    * @param mixed $socket
    * @param mixed $expected_response
    */
    private function server_parse($socket, $expected_response){
        if(igk_getv(socket_get_status($socket), "eof")){
            return false;
        }      
        $server_response='';
        igk_debug_wln("Expected ".$expected_response);
        $i=1;
        while(substr($server_response, 3, 1) != ' '){
            igk_debug_wln("reponse ::::".$server_response);
            if(!($server_response=fgets($socket, 256))){
                $this->ErrorMsg=__FUNCTION__.' : Error while fetching server response codes.'."-$socket-"."{$expected_response} ".'['.$server_response.']';
                igk_debug_wln($this->ErrorMsg);
                igk_debuggerview()->add("div")->Content=$this->ErrorMsg;
                return false;
            }
        }
        igk_debug_wln('OK : "'.$server_response.'"');
        if(!(substr($server_response, 0, 3) == $expected_response)){
            $this->ErrorMsg=__FUNCTION__.' Unable to send e-mail."'.$server_response.'"';
            igk_debug_wln($this->ErrorMsg);
            return false;
        }
        return true;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setFrom($value){
        $this->m_from=$value;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setHtmlCharset($v){
        $this->html_charset=$v;
    }
    ///<summary></summary>
    ///<param name="content"></param>
    /**
    * 
    * @param mixed $content
    */
    public function setHtmlMsg($content){
        $this->m_htmlmsg=$content;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setPort($value){
        $this->m_smtp_port=$value;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setPwd($value){
        $this->m_pwd=$value;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setReplyTo($value){
        $this->m_replyto=$value;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setSmtpHost($value){
        $this->m_smtphost=$value;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setSocketTimeout($value){
        $this->m_socketTimeout=$value;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setSocketType($v){
        switch(strtolower($v)){
            case "tls":
            case "ssl":
            $this->m_socketType=strtolower($v);
            break;
        }
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setTextCharset($v){
        $this->text_charset=$v;
    }
    ///<summary></summary>
    ///<param name="content"></param>
    /**
    * 
    * @param mixed $content
    */
    public function setTextMsg($content){
        $this->m_textmsg=$content;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setTitle($value){
        $this->m_title=$value;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setUseAuth($value){
        $this->m_useAuth=$value;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setUser($value){
        $this->m_user=$value;
    }
}