<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';
function sendMail($from, $fromName, $to, $toName, $ccRecipient, $subJect, $content){
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = true;
    $mail->Debugoutput = "error_log";
    $mail->IsSMTP();
    $mail->Mailer = "smtp";
    $mail->SMTPDebug  = 1;  
    $mail->SMTPAuth   = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;
    $mail->Host       = "mocha3032.mochahost.com"; //SMTP Host
    $mail->Username   = "system@traveylo.com"; // Username
    $mail->Password   = "Aj@[6o{Bc)[1"; // Pass

    //Attachments
   // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
   // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
    
    $mail->IsHTML(true);
    $mail->AddAddress($to, $toName);
    $mail->SetFrom($from, $fromName);
    //$mail->AddReplyTo($replyTo, $replyToName);
    $mail->AddCC($ccRecipient, "");
    $mail->Subject = $subJect;
    $content = $content;
    $mail->MsgHTML($content); 
    if(!$mail->Send()) {
        echo "<script>console.log('Mail not send')</script>";
        //echo "Error while sending Email.";
        //var_dump($mail);
    } else {
        //echo "Email sent successfully";
    }
}
?>