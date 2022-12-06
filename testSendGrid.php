// using SendGrid's PHP Library
// https://github.com/sendgrid/sendgrid-php
require 'vendor/autoload.php';
$sendgrid = new SendGrid("SENDGRID_APIKEY");
$email    = new SendGrid\Email();

$email->addTo("tsukasaki.tomohide.com@gmail.com")
      ->setFrom("ttsukasaki@hiokiusa.com")
      ->setSubject("Sending with SendGrid is Fun")
      ->setHtml("and fast with the PHP helper library.");

$sendgrid->send($email);