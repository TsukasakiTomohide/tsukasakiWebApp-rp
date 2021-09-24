<?php
   require_once 'config.php';
   require 'vendor/autoload.php'; // If you're using Composer (recommended)
    // Comment out the above line if not using Composer
    // require("./sendgrid/sendgrid-php.php");
    // If not using Composer, uncomment the above line and
    // download sendgrid-php.zip from the latest release here,
    // replacing <PATH TO> with the path to the sendgrid-php.php file,
    // which is included in the download:
    // https://github.com/sendgrid/sendgrid-php/releases

function sendMail($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $body){

    $email = new \SendGrid\Mail\Mail(); 
    $email->setFrom($senderEmail, $senderName);
    $email->setSubject($subject);
    $email->addTo($receiverEmail, $receiverName);
    $email->addContent("text/plain", $body);

    $sendgrid = new \SendGrid(SENDGRID_API_KEY);
    try {
        $response = $sendgrid->send($email);
        //print $response->statusCode() . "\n";
        //print_r($response->headers());
        //print $response->body() . "\n";
    } catch (Exception $e) {
        echo 'Caught exception: '. $e->getMessage() ."\n";
     }
     
}