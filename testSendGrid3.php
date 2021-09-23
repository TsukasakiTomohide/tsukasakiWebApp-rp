<?php

$userid   = 'ttsukasaki@hiokiusa.com';
$password = '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';

$sendgrid = new SendGrid($userid, $password);
$mail = new SendGridMail();
$mail->addTo('tsukasaki.tomohide.com@gmail.com')->
       setFrom('ttsukasaki@hiokiusa.com')->
       setSubject('Subject goes here')->
       setText('Hello World!')->
       setHtml('<strong>Hello World!</strong>');
$sendgrid->smtp->send($mail);