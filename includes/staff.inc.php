<?php
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';

    //if(!isset($_SESSION)){ // Session starts only when it is closed
    //    session_start();
    // }

    // A VC button is clicked
    if(isset($_POST['vcOpen'])){
        // $vcName includes year + quarter + vc + usersEmail
        $vcName = substr($_POST['vcOpen'], 0, 6); // It comes from buttonValueColor() of vc.functions.php
        $_SESSION['VcOwnerEmail'] = substr($_POST['vcOpen'], 6);
        header("location: ../includes/vc.php?when=$vcName");
     }

    // Password Change button on passwordChange.php
    elseif(isset($_POST['pswdChangebuttonATpasswordChange'])){
    
        $userspassword = getUsersPassword($conn, $_SESSION['usersemail']);

        // Check if the old password is valid
        if(!password_verify($_POST['oldPassword'], $userspassword)){
            header("location: ../includes/passwordChange.php?error=invalidoldpassword");
            exit();
         }

        // Check if the new password is valid
        if(!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $_POST['newPassword'])){
            header("location: ../includes/passwordChange.php?error=invalidnewpassword");
            exit();
         }
        
        // The new password is encrypted
        $newPassword =  password_hash( $_POST['newPassword'], PASSWORD_BCRYPT);

        // Change the employee's password
        $Result = setPwdChange($conn, $_SESSION['usersemail'], $newPassword);
    
        if($Result == false){
            header("location: ../includes/passwordChange.php?error=stmtfailed");
            exit();
        }
        header("location: ../includes/passwordChange.php?error=succeeded");
     }
