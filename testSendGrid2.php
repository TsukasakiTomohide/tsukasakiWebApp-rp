<?php
/*   require 'vendor/autoload.php'; // If you're using Composer (recommended)
// Comment out the above line if not using Composer
// require("./sendgrid/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases

$email = new \SendGrid\Mail\Mail(); 
$email->setFrom("ttsukasaki@hiokiusa.com", "Example User");
$email->setSubject("This is a test email.");
$email->addTo("tsukasaki.tomohide.com@gmail.com", "Example User");
$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
$email->addContent(
    "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
);
$a = "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";
$sendgrid = new \SendGrid($a);
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '. $e->getMessage() ."\n";
}
*/

$apiKey = "API KEY";
$client = new SendGridClient(apiKey);
$from = new EmailAddress("ttsukasaki@hiokiusa.com", "fromの名前");
$subject = "SendGridを使ったメール送信";
$to = new EmailAddress("tsukasaki.tomohide.com@gmail.com", "toの名前");
$plainTextContent = "テキストの内容です。";
$htmlContent = "<strong>HTMLの内容です。</strong>";
$msg = MailHelper.CreateSingleEmail($from, $to, $subject, $plainTextContent, $htmlContent);

$response = client.SendEmailAsync(msg).ConfigureAwait(false);
//Console.WriteLine(response.StatusCode.ToString());