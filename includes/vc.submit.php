<?php

require_once 'dbh.inc.php';
include_once 'vc.functions.php';
if(!isset($_SESSION)){
    session_start();
 }

if(isset($_POST["save"])){
    $countCheck = WordCount();
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

    // The numbers of texts in the boxes are over 920 words
    if ($countCheck != false){
        header($countCheck);
        exit();
    }

    if($_POST['email'] == 'Send Email'){
        $name = sendMail($conn, 'saved', $usersEmail, $year, $quarter, $phase, $calender);
    }

    $usersInfo = $year.$quarter.$vc.$usersEmail;
    header("location: ../includes/vc.php?when=$usersInfo&message=saved");
 }

elseif(isset($_POST["submit"])){    
    $countCheck = WordCount();
    $totalWeight = $_POST['Wei_1'] + $_POST['Wei_2'] + $_POST['Wei_3'] + $_POST['Wei_4'] + $_POST['Wei_5'];

    $year       = substr($_POST["submit"], 0, 4);
    $quarter    = substr($_POST["submit"], 4, 1);
    $vc         = substr($_POST["submit"], 5, 1);

    if ($totalWeight != 100){
        header("location: ../includes/vc.php?when=$year$quarter$vc&error=weightNot100");
        exit();
    }

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

    // The numbers of texts in the boxes are over 920 words
    if ($countCheck != false){
        header($countCheck);
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
    $countCheck = WordCount();
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

    // The numbers of texts in the boxes are over 920 words
    if ($countCheck != false){
        header($countCheck);
        exit();
    }    

    approveVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $_POST, $countCheck);
 }
 // Reject button was clicked
elseif(isset($_POST["Reject"])){
    $countCheck = WordCount();
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

    // The numbers of texts in the boxes are over 920 words
    if ($countCheck != false){
        header($countCheck);
        exit();
    }
    
    rejectVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $_POST, $countCheck);
 }

elseif(isset($_POST['toMain'])){
    $pos = strpos($_POST['toMain'], 'when='); //staff goes back from the boss's VC3
    if($pos != false)
    {
        $url = substr($_POST['toMain'], 0, $pos+11);
        $_SESSION['VcOwnerEmail'] = substr($_POST['toMain'], $pos+11);
        $_SESSION['vc'] = substr($_POST['toMain'], $pos+10, 1);
    }
    else
    {
        $url = $_POST['toMain'];
    }
    header("location: ".$url);
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

function rejectVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData, $countCheck){
    if($phase == 'Goals Approved' || $phase == 'Self Evaluated'){
        $Result = rejectUpdateVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
            if ($Result == false){
                header("location: ../includes/vc.php?error=stmtfailed");
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
        $state = 'returned';
    }

    // Change COLUMN `phase` in TABLE `year`
    $Result = vcUpdatePhase($conn, $year, $quarter, $phase, $usersEmail);
    if ($Result == false){
        header("location: ../includes/vc.php?error=stmtfailed");
        exit();
    }

    // Send Email
    if ($_POST['email'] != 'Not Send'){
        $Result = sendMail($conn, $state, $usersEmail, $year, $quarter, $phase, $calender); // $state is approved or rejected
        if ($Result == false){
            exit();
        }
    }

    // The numbers of text words in the boxes are over 920 words
    if ($countCheck == true){
        header("location: ../includes/vc.php?error=wordcountStaff Performance in Goal $i");
        exit();
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
function approveVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData, $countCheck){
    if($phase == 'Self Evaluated'){
        $Result = approveUpdateVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData);
            if ($Result == false){
                header("location: ../includes/vc.php?error=stmtfailed");
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
        header("location: ../includes/vc.php?error=stmtfailed");
        exit();
    }

    // The numbers of text words in the boxes are over 920 words
    if ($countCheck == true){
        header("location: ../includes/vc.php?error=wordcountStaff Performance in Goal $i");
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

function WordCount(){
    $countCheck = false;
    for ($i = 1; $i <= 5; $i++){
        $a = strlen($_POST["vc23_$i"]);
        if (isset($_POST["vc23_$i"]) && strlen($_POST["vc23_$i"]) > 920){
            $_POST["vc23_$i"] = substr($_POST["vc23_$i"], 0, 920);
            $countCheck = "location: ../includes/vc.php?error=wordcountManager/Director's Plan of Goal $i";
         }
        if (isset($_POST["Target_$i"]) && strlen($_POST["Target_$i"]) > 920){
            $_POST["Target_$i"] = substr($_POST["Target_$i"], 0, 920);
            $countCheck = "location: ../includes/vc.php?error=wordcountAnnual Target of Goal $i";
         }
        if (isset($_POST["Plan_$i"]) && strlen($_POST["Plan_$i"]) > 920){
            $_POST["Plan_$i"] = substr($_POST["Plan_$i"], 0, 920);
            $countCheck = "location: ../includes/vc.php?error=wordcountQuarter Plans of Goal $i";
         }
        if (isset($_POST["Res_$i"]) && strlen($_POST["Res_$i"]) > 920){
            $_POST["Res_$i"] = substr($_POST["Res_$i"], 0, 920);
            $countCheck = "location: ../includes/vc.php?error=wordcountQuarter Results of Goal $i";
         }
        if (isset($_POST["Per_$i"]) && strlen($_POST["Per_$i"]) > 920){
            $_POST["Per_$i"] = substr($_POST["Per_$i"], 0, 920);
            $countCheck = "location: ../includes/vc.php?error=wordcountStaff Performance of Goal $i";
         }

         return $countCheck;
    }
 }