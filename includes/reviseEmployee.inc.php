<?php
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';

// Search button was clicked
if(isset($_POST['searchbutton'])){
    $name = $_POST['searchName'];
    header("location: ../includes/admin.reviseEmployee.php?name=".$name);
 }
// Revise button was clicked
elseif(isset($_POST['revisebutton'])){
    // Employee Name is blank
    if(empty($_POST['employeeName'])){
        header("location: ../includes/admin.reviseEmployee.php?error=invalidname");
        exit();
     }
    // Employee Name does not have a blank
    elseif(!strpos($_POST['employeeName'],' ')){
        header("location: ../includes/admin.reviseEmployee.php?error=invalidname");
        exit();
     }
    // Employee Email Address is blank
    elseif(empty($_POST['employeeEmail'])){
        header("location: ../includes/admin.reviseEmployee.php?error=wrongemail");
        exit();
     }
    // Employe Email Address is invalid
    elseif(!filter_var($_POST['employeeEmail'],  FILTER_VALIDATE_EMAIL)){
        header("location: ../includes/admin.reviseEmployee.php?error=wrongemail");
        exit();
    }
        // When employee's position is staff, manager or director (except executive and administrator)
    elseif ($_POST['position'] != 'executive' && $_POST['position'] != 'administrator'){
            // boss name is blank
            if (empty($_POST['employeeBossName'])){
                header("location: ../includes/admin.reviseEmployee.php?error=emptyboss");
                exit();
             }
            // boss name is invalid
            elseif (!strpos($_POST['employeeBossName'],' ')){
                header("location: ../includes/admin.reviseEmployee.php?error=invalidbossname");
                exit();
             }
            // boss email is blank
            elseif(empty($_POST['employeeBossEmail'])){
                header("location: ../includes/admin.reviseEmployee.php?error=emptybossemail");
                exit();
             }
            // boss email is invalid
            elseif(!filter_var($_POST['employeeBossEmail'],  FILTER_VALIDATE_EMAIL)){
                header("location: ../includes/admin.reviseEmployee.php?error=wrongbossmail");
                exit();
             }
     }

    $Result = updateEmployeInfo($conn, $_POST);
    if (!$Result){
        header("location: ../includes/admin.reviseEmployee.php?error=failrevisingemployee");
        exit();
     }
    $employeeEmail = selectBoxInfoReturn();
    header("location: ../includes/admin.reviseEmployee.php?message=succeeded".$employeeEmail);
 }

// Delete an employee
elseif(isset($_POST['deletebutton'])){
    $Result = deleteOneEmployee($conn, $_POST['employeeEmail']);

    if(!$Result){
        header("location: ../includes/admin.reviseEmployee.php?message=failrevisingemployee");
        exit();
    }
    header("location: ../includes/admin.reviseEmployee.php?message=deleted");
 }
// An employee was selected in the select box.
else{
    $employeeEmail = selectBoxInfoReturn();
    header("location: ../includes/admin.reviseEmployee.php?message=changed".$employeeEmail);
 }

// Sub-function used in this php file
function selectBoxInfoReturn(){
    $spot = strpos($_REQUEST['employeesName'], " (");
    $employeeEmail = substr($_REQUEST['employeesName'], $spot + 2, strlen($_REQUEST['employeesName']) - $spot - 3);
    return $employeeEmail;
 }