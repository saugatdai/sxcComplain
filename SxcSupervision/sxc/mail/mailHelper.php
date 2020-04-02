<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use sxc\Models\DbHelper;

//Load composer's autoloader
require 'vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/SxcSupervision/sxc/configSXC.php';

function sendEmail($to,$name,$subject,$body){
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        //Server settings
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = systemEmail;                 // SMTP username
        $mail->Password = systemEmailPassword;                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        
        //Recipients
        $mail->setFrom(systemEmail, 'SXC Supervision System');
        $mail->addAddress($to, $name);     // Add a recipient
        
        
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        echo 'Message Has Been Sent to'. $to.'<br />';
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

function sendEmailToCDS($subject, $messageBody){
    $cdsList = DbHelper::getCDSList();
    $nameList = [];
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        //Server settings
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = systemEmail;                 // SMTP username
        $mail->Password = systemEmailPassword;                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        
        //Recipients
        $mail->setFrom(systemEmail, 'SXC Supervision System');
        
        //include first cds
        $mail->addAddress($cdsList[0]->getEmail(),$cdsList[0]->getName());
        $nameList[] = $cdsList[0]->getName();
        //now include the remaining cds
        for($i=1; $i<count($cdsList);$i++){
            $mail->addCC($cdsList[$i]->getEmail(),$cdsList[$i]->getName());
            $nameList[] = $cdsList[$i]->getName(); 
        }
        
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $messageBody;
        
        $mail->send();
        echo 'Message Has Been Sent to '. implode(',',$nameList).'<br />';
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}
