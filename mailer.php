<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

use PHPMailer\PHPMailer\Exception;
include './vendor/autoload.php';
$mail= new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPAuth=true;
$mail->Host="smtp.example.com";
$mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;
$mail->port= 587;
$mail->username="your_user@example.com";
$mail->password="your_password";
$mail->isHTML(true);
return $mail;

?>