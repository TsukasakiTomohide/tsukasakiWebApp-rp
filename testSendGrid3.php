<?php
$userid  = 'ttsukasaki@hiokiusa.com';
$password = 'MasakoShota08051019';

$sendgrid = new SendGrid($userid, $password);
$mail = new SendGridMail();
$mail->addTo('tsukasaki.tomohide.com@gmail.com')->
       setFrom('ttsukasaki@hiokiusa.com')->
       setSubject('Subject goes here')->
       setText('Hello World!')->
       setHtml('<strong>Hello World!</strong>');
$sendgrid->smtp->send($mail);
