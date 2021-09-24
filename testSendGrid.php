<?php

require_once 'sendMail.php';

$senderEmail    = 'ttsukasaki@hiokiusa.com';
$senderName     = 'Tsukasaki Sender';
$receiverEmail  = 'tsukasaki.tomohide.com@gmail.com';
$receiverName   = 'Tsukasaki Receiver';
$subject        = 'Subject: Test Email';
$body           = 'Body: This is a test email';
sendMail($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $body);