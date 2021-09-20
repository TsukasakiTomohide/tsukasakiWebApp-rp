<?php

require_once 'dbh.inc.php';

//// Configuration for sendig email. It is the same as vc.fnctions.php ////
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
// 設置した場所のパスを指定する
require('C:\PHPMailer\src\PHPMailer.php');
require('C:\PHPMailer\src\Exception.php');
require('C:\PHPMailer\src\SMTP.php');
//////////////////////////////////////////////////////////////////////////

// Get current time //
$currentDate  = strtotime(date('Y-m-d'));
$currentYear  = date('Y');
$currentMonth = date('m');
//////////////////////

// Get usersName, usersEmail, usersBoss, bossEmail of the active employees except administrator and executive//
$allNameEmails = getNameEmails($conn);
$adminEmails   = getAdministrators($conn); // Get all admin's emal addresses

// Each year
for($year = $currentYear - 1; $year <= $currentYear; $year++){

    // Gives up when the time is too late
    if($year == ($currentYear - 1) && ($currentMonth > 4)){
        continue;
    }

    // Each employee
    foreach($allNameEmails as $singleNameEmail){
        // Get phase quarter and OneOnOne timestamp
        $quarterInfo = getYearInfo($conn, $year, $singleNameEmail['usersEmail']);

        if(!$quarterInfo){
            continue; // The employee was not employed in the year.
        }

        // Each quarter
        foreach($quarterInfo as $val){

            $OneOnOneDate = strtotime($val['OneOnOne']);
            $dateDiff = ($OneOnOneDate - $currentDate) / (60 * 60 * 24);

            $Result = Notificaion($OneOnOneDate, $dateDiff, $singleNameEmail, $val, $year);

            if (!$Result){
                // Send email to administrators
                foreach($adminEmails as $singleAdminEmail){
                    sendWarning($conn, $singleAdminEmail);
                }
                exit();
            }
        }
    }
}


function Notificaion($OneOnOneDate, $dateDiff, $singleNameEmail, $val, $year){
    if($val['phase'] == 'Goals Approved'){
        if ($OneOnOneDate == null){
            $statement = "Notification. One-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." is not set yet. Please arrange the meeting on Goal Navigator.";
        }
        elseif($dateDiff  == 2  || $dateDiff  == 5){
            $statement = "Notification. The one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." will be in ".$dateDiff." days.";
        }
        elseif($dateDiff  == 1){
            $statement = "Notification. The one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." is tomorrow. Please arrange the meeting.";
        }
        elseif($dateDiff  == 0){
            $statement = "Notification. The one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." is today. Please finish the meeting.";
        }
        elseif($dateDiff  < 0){
            $statement = "Notification. The deadline of the one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." was over. Please finish the meeting, and set the next meeting date, or change the phase";
        }
        else{
            return true;
        }
        $Result = sendNotification($singleNameEmail['usersEmail'], $singleNameEmail['bossEmail'], $val['quarter'], $year, $statement);
        return $Result;
    }
    return true;
}
function getNameEmails($conn){
    $sql = "SELECT `usersName`, `usersEmail`, `usersBoss`, `bossEmail` FROM `users` WHERE `active` = ? AND usersPosition != ? AND usersPosition != ?;";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)){
        return false;
     }
    //mysqli_stmt_bind_param($stmt);  
    $active = 'active';
    $executive = 'executive';
    $administrator = 'administrator';
    mysqli_stmt_bind_param($stmt, "sss", $active, $executive, $administrator);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($resultData)){
        $usersInfo[] = $row;
    }
    mysqli_stmt_close($stmt); 

    if(!empty($usersInfo)){
        return $usersInfo;
    }
    else{
        return false;
    }
}

function getYearInfo($conn, $year, $usersEmail){
    $sql = "SELECT `phase`, `OneOnOne`, `quarter` FROM `$year` WHERE `usersEmail` = ?;";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)){
        exit();
     }
    mysqli_stmt_bind_param($stmt, "s", $usersEmail);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($resultData)){
        $usersInfo[] = $row;
    }
    mysqli_stmt_close($stmt); 

    if(!empty($usersInfo)){
        return $usersInfo;
    }
    else{
        $result = false;
        return $result;
    }
}

function sendNotification($usersEmail, $bossEmail, $quarter, $year, $emailStatement){

    // 文字エンコードを指定
    mb_language('uni');
    mb_internal_encoding('UTF-8');

    // インスタンスを生成（true指定で例外を有効化）
    $mail = new PHPMailer(true);
    $mail->Chaset = 'utf-8';

    try {
        // デバッグ設定
        // $mail->SMTPDebug = 2; // デバッグ出力を有効化（レベルを指定）
        // $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str<br>";};
      
        // SMTPサーバの設定 (Office365用で変更しない)
        $mail->isSMTP();                               // SMTPの使用宣言
        $mail->Host       = 'smtp.office365.com';
        $mail->SMTPAuth   = true;                      // SMTP authenticationを有効化
        $mail->SMTPSecure = 'starttsl';                // 暗号化を有効（tls or ssl）無効の場合はfalse
        $mail->Port       = 587;                       // TCPポートを指定（tlsの場合は465や587）
        // SMTPサーバの設定 (HUSAの専用メールに変更する)
        $mail->Username   = 'ttsukasaki@hiokiusa.com'; // SMTPサーバーのユーザ名    (HUSAで専用を用意する)
        $mail->Password   = 'skjqtdwstzrrsllg';        // SMTPサーバーのパスワード  (App Passwordでないといけない)
      
        $mail->Subject = '[Notification] One-one-one Meeting';
        $mail->Body    = $emailStatement; 

        // 送受信先設定（第二引数は省略可）
        $mail->setFrom($usersEmail, 'Sender');       // 送信者
        $mail->addAddress($bossEmail, 'Receiver');   // 宛先

        $mail->addReplyTo($usersEmail, 'Inquirery'); // 返信先
        //$mail->addCC('ttsukasaki@hiokiusa.com', 'Receiver Name'); // CC宛先
        $mail->Sender = $usersEmail; // Return-path


        // 送信
        //$mail->send();

        $mail->setFrom($bossEmail, 'Sender');         // 送信者
        $mail->addAddress($usersEmail, 'Receiver');   // 宛先

        $mail->addReplyTo($bossEmail, 'Inquirery');   // 返信先
        //$mail->addCC('ttsukasaki@hiokiusa.com', 'Receiver Name'); // CC宛先
        $mail->Sender = $bossEmail; // Return-path

        // 送信
        //$mail->send();

        return true;

      } catch (Exception $e) {
        // エラーの場合
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
      }
}

function getAdministrators($conn){
    $sql = "SELECT `usersEmail` FROM `users` WHERE `active` = ? AND `usersPosition` = ?;";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)){
        exit();
     }
    //mysqli_stmt_bind_param($stmt);
    $active = 'active';
    $administrator = 'administrator';
    mysqli_stmt_bind_param($stmt, "ss", $active, $administrator);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($resultData)){
        $usersInfo[] = $row;
    }
    mysqli_stmt_close($stmt); 

    if(!empty($usersInfo)){
        return $usersInfo;
    }
    else{
        $result = false;
        return $result;
    }
}

function sendWarning($conn, $adminEmail){

    // 文字エンコードを指定
    mb_language('uni');
    mb_internal_encoding('UTF-8');

    // インスタンスを生成（true指定で例外を有効化）
    $mail = new PHPMailer(true);
    $mail->Chaset = 'utf-8';

    try {
        // デバッグ設定
        // $mail->SMTPDebug = 2; // デバッグ出力を有効化（レベルを指定）
        // $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str<br>";};
      
        // SMTPサーバの設定 (Office365用で変更しない)
        $mail->isSMTP();                               // SMTPの使用宣言
        $mail->Host       = 'smtp.office365.com';
        $mail->SMTPAuth   = true;                      // SMTP authenticationを有効化
        $mail->SMTPSecure = 'starttsl';                // 暗号化を有効（tls or ssl）無効の場合はfalse
        $mail->Port       = 587;                       // TCPポートを指定（tlsの場合は465や587）
        // SMTPサーバの設定 (HUSAの専用メールに変更する)
        $mail->Username   = 'ttsukasaki@hiokiusa.com'; // SMTPサーバーのユーザ名    (HUSAで専用を用意する)
        $mail->Password   = 'skjqtdwstzrrsllg';        // SMTPサーバーのパスワード  (App Passwordでないといけない)
      
        $mail->Subject = '[Warning] Scheduling Email was not sent.';
        $emailStatement = "Scheduling of one-on-one meetings was not able to send email. Please check OneOnOneReminder.php or MySQL server";
        $mail->Body    = $emailStatement; 

        // 送受信先設定（第二引数は省略可）
        $mail->setFrom($adminEmail, 'Sender');        // 送信者
        $mail->addAddress($adminEmail, 'Receiver');   // 宛先

        $mail->addReplyTo($adminEmail, 'Inquirery'); // 返信先
        //$mail->addCC('ttsukasaki@hiokiusa.com', 'Receiver Name'); // CC宛先
        $mail->Sender = $adminEmail; // Return-path


        // 送信
        //$mail->send();

        return true;

      } catch (Exception $e) {
        // エラーの場合
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
      }
}

?>