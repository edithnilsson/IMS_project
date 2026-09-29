<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (and all other Composer packages) via the autoloader
require_once __DIR__ . "/vendor/autoload.php";

// Enable exceptions to see if send() throws an exception or not when sending a mail
$mail = new PHPMailer(true); 

// Use SMTP (an actual mail server)
$mail->isSMTP();
$mail->SMTPAuth = true; // Gmail requires authentication to send

// Gmail's SMTP server details
$mail->Host = "smtp.gmail.com";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // encrypt connection
$mail->Port = 587; //Gmail port

// The sending account
$mail->Username = "inftraction.noreply@gmail.com";
$mail->Password = "jyxj npho juai qxja"; // Generated app password from gmail

// What recipients see as the sender
$mail->setFrom("inftraction.noreply@gmail.com", "InfTraction");

// Send the email body as HTML (so the <a> link renders as a clickable link)
$mail->isHTML(true);

//Add logo in the mail
$mail->addEmbeddedImage(__DIR__ . '/Pictures/logo_nobackground.png', 'logo');


return $mail;