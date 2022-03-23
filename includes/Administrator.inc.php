<?php
include_once 'vc.functions.php';
require_once 'dbh.inc.php';

//******************************************************************************//
//***************************** admin.activate.php *****************************//
//******************************************************************************//
//// Search employees' names from a part of the name ////
if(isset($_POST['searchActivate'])){
    $name = "activ".$_POST['searchedActivateName'];
    header("location: ../includes/admin.activate.php?name=".$name);
 }
elseif(isset($_POST['searchInactivate'])){
    $name = "inact".$_POST['searchedInactivateName'];
    header("location: ../includes/admin.activate.php?name=".$name);
 }

//// Change an inactive employee to to active ////
elseif(isset($_POST['Activatebutton'])){
    $position   =   strpos($_POST['employeesName'], ' (');
    $email      =   substr($_POST['employeesName'], $position + 2, strlen($_POST['employeesName']) - $position - 3);
    $Result     =   setActInactEmployee($conn, $email, 'active');
    if ($Result == false){
        header("location: ../includes/admin.activate.php?activate=failactivation");
    }
    else{
        header("location: ../includes/admin.activate.php?activate=activated");
    }
 }
//// Change an active employee to to inactive ////
elseif(isset($_POST['Inactivatebutton'])){
    $position   =   strpos($_POST['employeesName'], ' (');
    $email      =   substr($_POST['employeesName'], $position + 2, strlen($_POST['employeesName']) - $position - 3);
    $Result     =   setActInactEmployee($conn, $email, 'inactive');
    if ($Result == false){
        header("location: ../includes/admin.activate.php?activate=failinactivation");
    }
    else{
        header("location: ../includes/admin.activate.php?activate=inactivated");
    }
 }

//******************************************************************************//
//******************* Transmission from Administrator  ********************//
//******************************************************************************//
// admin.activate.php //
elseif(isset($_POST['activate'])){
    header("location: ../includes/admin.activate.php");
 }
// to admin.passwordReset.php //
elseif(isset($_POST['password'])){
    header("location: ../includes/admin.passwordReset.php");
 }
// to admin.registerEmployee.php //
elseif(isset($_POST['registerEmployee'])){
    header("location: ../includes/admin.registerEmployee.php");
 }
// to admin.reviseEmployee.php //
elseif(isset($_POST['reviseEmployee'])){
    header("location: ../includes/admin.reviseEmployee.php");
 }
// to admin.addVCspace.php //
elseif(isset($_POST['addVCspace'])){
    header("location: ../includes/admin.addVCspace.php");
 }
// to admin.backup.php //
elseif(isset($_POST['databaseBackup'])){
    $year = $_POST['year'];
    header("location: ../includes/admin.backup.php?year=$year");
 }
elseif(isset($_POST['Logout'])){
    header("location: ../login.php");
 }
// to login.php //
elseif(isset($_POST['toExecutive'])){
    header("location: ../includes/main.executive.php");
 }
//******************************************************************************//
//**************************** admin.PasswordReset *****************************//
//******************************************************************************//
// Search button
elseif(isset($_POST['searchPwdbutton'])){
    $name = $_POST['searchPwdName'];
    header("location: ../includes/admin.passwordReset.php?name=".$name);
 }
// Password Reset button
elseif(isset($_POST['passwordResetbutton'])){
    $position =     strpos($_POST['employeesName'], ' (');
    $name = substr($_POST['employeesName'], 0, $position);
    $email =        substr($_POST['employeesName'], $position + 2, strlen($_POST['employeesName']) - $position - 3);
    $newPassword =  password_hash( $_POST['newPassword'], PASSWORD_BCRYPT);

    if(!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $_POST['newPassword'])){
        header("location: ../includes/admin.passwordReset.php?error=invalidpassword");
        exit();
    }

    // Reset the employee's password
    $Result = setPwdChange($conn, $email, $newPassword);

    if($Result == false){
        header("location: ../includes/admin.passwordReset.php?error=passwordfailed");
        exit();
    }
    header("location: ../includes/admin.passwordReset.php?error=succeeded".$name);
 }

//******************************************************************************//
//********* transmission from main.executive.php to Administrator.php **********//
//******************************************************************************//
elseif(isset($_POST['toAdministrator'])){
    header("location: ../includes/Administrator.php");
 }

//******************************************************************************//
//****************************** Administrator.php *****************************//
//******************************************************************************//
elseif(isset($_POST['changeYear']) && isset($_POST['year'])){
        $year = $_POST['year'];
        header("location: ../includes/Administrator.php?year=".$year);
 }

//******************************************************************************//
//************************* admin.registerEmployee.php *************************//
//******************************************************************************//
elseif(isset($_POST['registerbutton'])){
    // Employee name is blank
    if (empty($_POST['employeeName'])){
        header("location: ../includes/admin.registerEmployee.php?error=invalidname");
        exit();
     }
    // Employee name does not have a space
    elseif (!strpos($_POST['employeeName'],' ')){
        header("location: ../includes/admin.registerEmployee.php?error=invalidname");
        exit();
     }
    // Employee email address is blank
    elseif (empty($_POST['employeeEmail'])){
        header("location: ../includes/admin.registerEmployee.php?error=wrongemail");
        exit();
     }
    // Employee email is invalid
    elseif (!filter_var($_POST['employeeEmail'],  FILTER_VALIDATE_EMAIL)){
        header("location: ../includes/admin.registerEmployee.php?error=wrongemail");
        exit();
     }
    // Password is invalid
    elseif (!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $_POST['employeePassword'])){
        header("location: ../includes/admin.registerEmployee.php?error=invalidpassword");
        exit();
     }
    // When employee's position is staff, manager or director (except executive and administrator)
    elseif ($_POST['position'] != 'executive' && $_POST['position'] != 'administrator'){
            // boss name is blank
            if (empty($_POST['employeeBossName'])){
                header("location: ../includes/admin.registerEmployee.php?error=emptyboss");
                exit();
             }
            // boss name is invalid
            elseif (!strpos($_POST['employeeBossName'],' ')){
                header("location: ../includes/admin.registerEmployee.php?error=invalidbossname");
                exit();
             }
            // boss email is blank
            elseif (empty($_POST['employeeBossEmail'])){
                header("location: ../includes/admin.registerEmployee.php?error=emptybossemail");
                exit();
             }
            // boss email is invalid
            elseif (!filter_var($_POST['employeeBossEmail'],  FILTER_VALIDATE_EMAIL)){
                header("location: ../includes/admin.registerEmployee.php?error=wrongbossmail");
                exit();
             }
     }
    // Check if the same email address is saved in the database
    $Result = checkEmailDuplicate($conn, $_POST['employeeEmail']);

    if ($Result != false){ // The same email address already exists in the database
        header("location: ../includes/admin.registerEmployee.php?error=alreadyexists");
        exit();
        }
    // Add the employee to the database
    $Result = addNewEmployee($conn, $_POST);
    if (!$Result){
        header("location: ../includes/admin.registerEmployee.php?error=failaddingemployee");
        exit();
        }
    header("location: ../includes/admin.registerEmployee.php?error=succeeded");
 }
//******************************************************************************//
//**************************** admin.addVCspace.php ****************************//
//******************************************************************************//
elseif(isset($_POST['createVCspacebutton'])){
    // Check if the table of `year` exists

    // Get EmployeeInfo from `users` table
    $allEmployees = getEmployeesInfo($conn, 0, '', 'active');

    if(!$allEmployees){
        header("location: ../includes/admin.addVCspace.php?error=stmtfailed");
        exit();
    }

    // Check if Table `year` exists
    $Result = checkVCTableExists($conn, $_POST['year']);

    // If the table does not exist, create the table.
    if (!$Result){ // Table does not exist
        // Create the table
        $Result = createNewVcTable($conn, $_POST['year']);

        if(!$Result){
            header("location: ../includes/admin.addVCspace.php?error=stmtfailed");
            exit();
        }
     }

    // In each usersEmail, search if each quarer row exists
    foreach($allEmployees as $singleEmployee){
        for($quarter = 1; $quarter <= 4; $quarter++){
            // Check if the row exists (true: exist)
            if ($singleEmployee['usersPosition'] == 'staff'){
                $Result = getQuarterExists($conn, $quarter, $_POST['year'], $singleEmployee['usersEmail'], '4');
            }
            elseif($singleEmployee['usersPosition'] == 'manager')
            {
                $Result = getQuarterExists($conn, $quarter, $_POST['year'], $singleEmployee['usersEmail'], '3');
            }
            else{
                continue;
            }

            if(!$Result){
                // Create a new row
                $Result = createNewRow($conn, $quarter, $_POST['year'], $singleEmployee);
            }
        }
    }
    header("location: ../includes/admin.addVCspace.php?error=succeeded");
 }

//******************************************************************************//
//****************************** admin.backup.php ******************************//
//******************************************************************************//
elseif(isset($_POST['Export'])){

    // Check if the folder
    if(!file_exists("..\\database_backup")){
        mkdir("..\\database_backup", 0777);
    }

    $cmd = "C:\\xampp\\mysql\\bin\\mysqldump.exe --add-drop-table --lock-all-tables -u ".$dbUsername." -p".$dbPassword." -h ".$serverName." ".$dbName." > ..\\database_backup\\".date('Y')."_".date('m')."_".date('d').".php";

    system($cmd, $output);
    if($output == 0){
        header("location: ../includes/admin.backup.php?error=exportsucceeded");
    }
    elseif($output == 2){
        header("location: ../includes/admin.backup.php?error=failedprivilege");
    }
    else{
        header("location: ../includes/admin.backup.php?error=exportfailed");
    }
 }
elseif(isset($_POST['Import'])){
    if(isset($_POST['backupFile']) && !empty($_POST['backupFile'])){
        $cmd = "C:\\xampp\\mysql\\bin\\mysql.exe -u ".$dbUsername." -p".$dbPassword." -h ".$serverName." ".$dbName." < ".$_POST['backupFile'];
        system($cmd, $output);
        if($output == 0){
            header("location: ../includes/admin.backup.php?error=importsucceeded");
        }
        elseif($output == 2){
            header("location: ../includes/admin.backup.php?error=failedprivilege");
        }
        else{
            header("location: ../includes/admin.backup.php?error=importfailed");
        }
        exit();
    }
    header("location: ../includes/admin.backup.php?error=blank");
 }
elseif(isset($_POST['Delete'])){
    if(isset($_POST['backupFile']) && !empty($_POST['backupFile'])){
        $Result = unlink($_POST['backupFile']);
        if($Result){
            header("location: ../includes/admin.backup.php?error=deletesucceeded");
        }
        else{
            header("location: ../includes/admin.backup.php?error=deletefailed");
        }
    }
    header("location: ../includes/admin.backup.php?error=blank");
 }