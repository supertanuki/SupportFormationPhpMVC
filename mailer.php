<?php
require_once('PHPMailer_5.2.0/class.phpmailer.php');
$mail = new PHPMailer();

$body = '<p>Hello <b>world</b></p>';

$mail->SetFrom('joyeux@noel.com', 'Joyeux Noel');


// multiples destinataires
$mail->AddAddress('supertanuki@gmail.com', "JEAN");
$mail->AddAddress('supertanuki@gmail.com', "JEAN");
$mail->AddAddress('supertanuki@gmail.com', "JEAN");
$mail->AddAddress('supertanuki@gmail.com', "JEAN");

// en copie
$mail->AddCC('supertanuki@gmail.com', "JEAN");

// en copie cachée
$mail->AddBCC('supertanuki@gmail.com', "JEAN");



$mail->Subject    = "Test du mail";

$mail->AltBody    = strip_tags( $body );

$mail->MsgHTML($body);

$mail->AddAttachment("upload/Formation-PHP.ppt");      // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}