<?php

use IGK\System\Console\Logger;

$mail = new IGKMail();
$mail->addTo($to);
$mail->From = "cbondje@igkdev.be";
$mail->HtmlMsg= $message;
$mail->Title= $subject;

if ($mail->sendMail()){
    //Logger::success("send success: ".$to);
    return true;
}
// Logger::info("email not send to:".$to);
return false;