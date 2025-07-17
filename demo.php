<?php
require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com ';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'freelancinggurus0@gmail.com';                 // SMTP username
$mail->Password = 'dybh ixsw dxyi vekv';                           // SMTP password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

// recipients
$mail->setFrom('freelancinggurus0@gmail.com', 'Freelancing Gurus');
$mail->addAddress('amolsolse2127@gmail.com', 'Amol Solse');     // Add a recipient

$mail->isHTML(true);                                  // Set email format to HTML
$mail->Subject = 'Job application';
$mail->Body    = 'Registration Successful';   // HTML message body
$mail->send();
echo 'Message has been sent'; 