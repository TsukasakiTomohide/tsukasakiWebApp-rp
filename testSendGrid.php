// using SendGrid's PHP Library
// https://github.com/sendgrid/sendgrid-php
require 'vendor/autoload.php';

$sendgrid = new SendGrid("ttsukasaki@hiokiusa.com", "MasakoShota08051019");
$email    = new SendGrid\Email();

$email->addTo("ttsukasaki@hiokiusa.com")
      ->setFrom("ttsukasaki@hiokiusa.com")
      ->setSubject("Sending with SendGrid is Fun")
      ->setHtml("and fast with the PHP helper library.");

$sendgrid->send($email);