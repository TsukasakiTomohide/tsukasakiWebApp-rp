<?php

require_once './includes/dbh.inc.php';
require_once './includes/vc.functions.php';

// Get current time //
$currentDate  = strtotime(date('Y-m-d'));
$currentYear  = date('Y');
$currentMonth = date('m');

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

            $Result = Notificaion($conn, $OneOnOneDate, $dateDiff, $singleNameEmail, $val, $year);

            if (!$Result && $adminEmails != null){
                // Send email to all administrators
                foreach($adminEmails as $singleAdminEmail){
                    sendWarning($conn, $singleAdminEmail);
                }
                exit();
            }
        }
    }
}

function getNameEmails($conn){
    $sql = "SELECT usersName, usersEmail, usersBoss, bossEmail FROM users WHERE active = ? AND usersPosition != ? AND usersPosition != ?;";
    
    $stmt = pdoPrepare($conn, $sql);

    pdoBind($stmt, 1, 'active', PDO::PARAM_STR);
    pdoBind($stmt, 2, 'executive', PDO::PARAM_STR);
    pdoBind($stmt, 3, 'administrator', PDO::PARAM_STR); 

    $results = pdoExecute($stmt);

    while($row = pdoFetch($stmt)){
        $allNameEmails[] = $row;
    }

    if(!empty($allNameEmails)){
        return $allNameEmails;
    }
    else{
        return false;
    }
 }
function getAdministrators($conn){
    $sql = "SELECT usersEmail FROM users WHERE active = ? AND usersPosition = ?;";

    $stmt = pdoPrepare($conn, $sql);

    pdoBind($stmt, 1, 'active', PDO::PARAM_STR);
    pdoBind($stmt, 2, 'executive', PDO::PARAM_STR);

    $results = pdoExecute($stmt);

    while($row = pdoFetch($stmt)){
        $adminEmails[] = $row;
    }

    if(!empty($adminEmails)){
        return $adminEmails;
    }
    else{
        return false;
    }
 }
function getYearInfo($conn, $year, $usersEmail){
    $sql = "SELECT phase, OneOnOne, quarter FROM [dbo].[$year] WHERE usersEmail = ?;";
    
    $stmt = pdoPrepare($conn, $sql);

    pdoBind($stmt, 1, $usersEmail, PDO::PARAM_STR);

    $results = pdoExecute($stmt);

    while($row = pdoFetch($stmt)){
        $quarterInfo[] = $row;
    }

    if(!empty($quarterInfo)){
        return $quarterInfo;
    }
    else{
        return false;
    }
 }
function Notificaion($conn, $OneOnOneDate, $dateDiff, $singleNameEmail, $val, $year){
    if($val['phase'] == 'Goals Approved'){
        if ($OneOnOneDate == null){
            $body = "Notification. One-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." is not set yet. Please arrange the meeting on Goal Navigator.";
        }
        elseif($dateDiff  == 2  || $dateDiff  == 5){
            $body = "Notification. The one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." will be in ".$dateDiff." days.";
        }
        elseif($dateDiff  == 1){
            $body = "Notification. The one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." is tomorrow. Please arrange the meeting.";
        }
        elseif($dateDiff  == 0){
            $body = "Notification. The one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." is today. Please finish the meeting.";
        }
        elseif($dateDiff  < 0){
            $body = "Notification. The deadline of the one-on-one meeting between ".$singleNameEmail['usersName']." and ".$singleNameEmail['usersBoss']." was over. Please finish the meeting, and set the next meeting date, or change the phase";
        }
        else{
            return true;
        }
        $Result = sendNotification($conn, $singleNameEmail['usersEmail'], $singleNameEmail['usersName'], $singleNameEmail['bossEmail'], $singleNameEmail['usersBoss'], $val['quarter'], $year, $body);
        return $Result;
    }
    return true;
 }

function sendNotification($conn, $usersEmail, $usersName, $bossEmail, $bossName, $quarter, $year, $body){

    $subject = '[Notification] One-one-one Meeting';

    // send
    sendMailSendGrid($conn, $bossEmail, $bossName, $subject, $body);
    sendMailSendGrid($conn, $usersEmail, $usersName, $subject, $body);

    return true;
 }

function sendWarning($conn, $adminEmail){

    $subject = '[Warning] Scheduling Email was not sent.';
    $body = "Scheduling of one-on-one meetings was not sent with email.";

    sendMailSendGrid($conn, $adminEmail, 'Admin', $subject, $body);
}

?>