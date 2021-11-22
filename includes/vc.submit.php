<?php

//if(!isset($_SESSION)){
//    session_start();
//}
require_once 'dbh.inc.php';
include_once 'vc.functions.php';

if(isset($_POST["save"])){
    $year       = substr($_POST["save"], 0, 4);
    $quarter    = substr($_POST["save"], 4, 1);
    $vc         = substr($_POST["save"], 5, 1);
    $pos        = strpos($_POST["save"], "%");
    $phase      = substr($_POST["save"], 6, $pos - 6);
    $usersEmail = substr($_POST["save"], $pos+1);
    
    if($phase == 'Goals Approved' && empty($_POST["calender"])){
        $usersInfo = $year.$quarter.$vc.$usersEmail;
        header("location: ../includes/vc.php?when=$usersInfo&error=timestamp");
        exit();
    }

    //Update VC data to SQL
    if(isset($_POST["calender"])){
        $calender = $_POST["calender"];
    }
    else{
        $calender = "";
    }
    $Result = SaveSubmitSQL($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $_POST);

    if ($Result == false){
        header("location: ../includes/vc.php?error=stmtfailed");
        exit();
    }

    if($_POST['email'] == 'Send Email'){
        $name = sendMail($conn, 'saved', $usersEmail, $year, $quarter, $phase, $calender);
        echo($name);
    }

    $usersInfo = $year.$quarter.$vc.$usersEmail;
    //header("location: ../includes/vc.php?when=$usersInfo&message=saved");
 }

elseif(isset($_POST["submit"])){
    $year       = substr($_POST["submit"], 0, 4);
    $quarter    = substr($_POST["submit"], 4, 1);
    $vc         = substr($_POST["submit"], 5, 1);
    $pos        = strpos($_POST["submit"], "%");
    $phase      = substr($_POST["submit"], 6, $pos - 6);
    $usersEmail = substr($_POST["submit"], $pos+1);

    //Update VC data to SQL
    if(isset($_POST["calender"])){
        $calender = $_POST["calender"];
    }
    else{
        $calender = "";
    }
    $Result = SaveSubmitSQL($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $_POST);

    if ($Result == false){
        header("location: ../includes/vc.php?error=stmtfailed");
        exit();
    }

    // COLUMN `phase` of TABLE `year` in MySQL is changed to true status
    // year is such as `2021`
    $Result = phaseChange($conn, $year, $quarter, $vc, $phase, $usersEmail);

    if ($Result == false){
        header("location: ../includes/vc.php?error=stmtfailed");
        exit();
    }

    //Send Email to the boss
    if($_POST['email'] == 'Send Email'){
        sendMail($conn, 'submitted', $usersEmail, $year, $quarter, $phase, $calender);
    }
    if ($Result == false){
        header("location: ../includes/vc.php?error=stmtfailed");
        exit();
    }

    if ($_SESSION["usersposition"] == "staff"){
        header("location: ../includes/main.staff.php?message=submitted".$year.$quarter);
    }
    elseif($_SESSION["usersposition"] == "manager"){
        header("location: ../includes/main.manager.php?message=submitted".$year.$quarter);
    }
    elseif($_SESSION["usersposition"] == "director"){
        header("location: ../includes/main.director.php?message=submitted".$year.$quarter);
    }
    elseif($_SESSION["usersposition"] == "executive"){
        if($_SESSION['vcPurpose'] = 'administrator'){
            header("location: ../includes/Administrator.php?message=submitted".$year.$quarter);
        }
        else{
            header("location: ../includes/main.executive.php?message=submitted".$year.$quarter);
        }
    }
    elseif($_SESSION["usersposition"] == "administrator"){
        header("location: ../includes/Administrator.php?message=submitted".$year.$quarter);
    }
 }

// Approve button was clicked
if(isset($_POST["Approve"])){
    $year       = substr($_POST["Approve"], 0, 4);
    $quarter    = substr($_POST["Approve"], 4, 1);
    $vc         = substr($_POST["Approve"], 5, 1);
    $pos        = strpos($_POST["Approve"], "%");
    $phase      = substr($_POST["Approve"], 6, $pos - 6);
    $usersEmail = substr($_POST["Approve"], $pos+1);

    if(isset($_POST["calender"])){
        $calender = $_POST["calender"];
    }
    else{
        $calender = "";
    }

    approveVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $_POST);
 }
// Reject button was clicked
elseif(isset($_POST["Reject"])){
    $year       = substr($_POST["Reject"], 0, 4);
    $quarter    = substr($_POST["Reject"], 4, 1);
    $vc         = substr($_POST["Reject"], 5, 1);
    $pos        = strpos($_POST["Reject"], "%");
    $phase      = substr($_POST["Reject"], 6, $pos - 6);
    $usersEmail = substr($_POST["Reject"], $pos+1);

    if(isset($_POST["calender"])){
        $calender = $_POST["calender"];
    }
    else{
        $calender = "";
    }
    rejectVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $_POST);
 }

elseif(isset($_POST['toMain'])){
    header("location: ".$_POST['toMain']);
    exit();
 }

function SaveSubmitSQL($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData){
    // Update Goals
    vcRemoveNullData();

    //Update VC data to SQL
    if ($_SESSION['usersposition'] == 'staff'){
        $Result = vcUpdate($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
     }
    elseif($_SESSION['usersposition'] == 'manager'){
        if ($usersEmail == $_SESSION['approveremail']){
            $Result = vcUpdate($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
        }
        else{
            $Result = saveVcApprover($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
        }
     }
    else{
        $Result = saveVcApprover($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
     }

    if($Result == false){
        return false;
        exit();
    }

    return $Result;
 }

function rejectVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData){
    if($phase == 'Goals Approved' || $phase == 'Self Evaluated'){
        $Result = rejectUpdateVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
            if ($Result == false){
                header("location: ../includes/vc.phpp?error=stmtfailed");
                exit();
            }
    }
    if($phase == 'Submitted' || $phase == 'Goals Approved'){
        $phase = 'Unsubmitted';
        $state = 'rejected';
    }
    elseif($phase == 'Self Evaluated'){
        $phase = 'Goals Approved';
        $state = 'returned';
    }
    elseif($phase == 'Finalized'){
        $phase = 'Self Evaluated';
        $state = 'Do not send email';
    }

    // Change COLUMN `phase` in TABLE `year`
    $Result = vcUpdatePhase($conn, $year, $quarter, $phase, $usersEmail);
    if ($Result == false){
        header("location: ../includes/vc.phpp?error=stmtfailed");
        exit();
    }

    // Send Email
    if ($_POST['email'] != 'Not Send'){
        $Result = sendMail($conn, $state, $usersEmail, $year, $quarter, $phase, $calender); // $state is approved or rejected
        if ($Result == false){
            exit();
        }
    }

    // Page Transfer
    if($_SESSION["usersposition"] == 'manager'){        // to main.manager.php page
        header("location: ../includes/main.manager.php?message=".$state.$year.$quarter);
    }
    elseif($_SESSION["usersposition"] == 'director'){   // to main.director.php page
        header("location: ../includes/main.director.php?message=".$state.$year.$quarter);
    }
    elseif($_SESSION["usersposition"] == 'executive'){
        if($_SESSION['vcPurpose'] = 'administrator'){  // to main.executive.php page
            header("location: ../includes/Administrator.php");
        }
        else{
            header("location: ../includes/main.executive.php?message=".$state.$year.$quarter);
        }
    }
    elseif($_SESSION["usersposition"] == 'administrator'){  // to Administrator.php page
        header("location: ../includes/Administrator.php");
    }
 }
function approveVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData){
    if($phase == 'Self Evaluated'){
        $Result = approveUpdateVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
            if ($Result == false){
                header("location: ../includes/vc.phpp?error=stmtfailed");
                exit();
            }
    }
    if($phase == 'Submitted'){
        $phase = 'Goals Approved';
    }
    elseif($phase == 'Self Evaluated'){
        $phase = 'Finalized';
    }
    $state = 'approved';

    // Change COLUMN `phase` in TABLE `year`
    $Result = vcUpdatePhase($conn, $year, $quarter, $phase, $usersEmail);
    if ($Result == false){
        header("location: ../includes/vc.phpp?error=stmtfailed");
        exit();
    }

    // Send Email
    if ($_POST['email'] != 'Not Send'){
        $Result = sendMail($conn, $state, $usersEmail, $year, $quarter, $phase, $calender); // $state is approved or rejected
        if ($Result == false){
            exit();
        }
    }

    // Page Transfer
    if($_SESSION["usersposition"] == 'manager'){        // to main.manager.php page
        header("location: ../includes/main.manager.php?message=".$state.$year.$quarter);
    }
    elseif($_SESSION["usersposition"] == 'director'){   // to main.director.php page
        header("location: ../includes/main.director.php?message=".$state.$year.$quarter);
    }
    elseif($_SESSION["usersposition"] == 'executive'){
        if($_SESSION['vcPurpose'] = 'administrator'){  // to main.executive.php page
            header("location: ../includes/Administrator.php");
        }
        else{
            header("location: ../includes/main.executive.php?message=".$state.$year.$quarter);
        }
    }
    elseif($_SESSION["usersposition"] == 'administrator'){  // to Administrator.php page
        header("location: ../includes/Administrator.php");
    }
 }