<?php

require "PHPMailer/PHPMailerAutoload.php";

$mail = new PHPMailer();
$mail->isSMTP();
$mail->CharSet = "UTF-8";
$mail->Host = "aspmx.l.google.com";
$mail->SMTPDebug = 1;
$mail->SMTPAuth = false;

$mail->From = "reviseit-notifications";
$mail->FromName = "reviseit";
$mail->addAddress("joesingo@gmail.com");

function send_email($subject, $body) {
	global $mail;

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
}

?>
