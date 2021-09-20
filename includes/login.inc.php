<?php
require_once 'dbh.inc.php';
require_once 'login.function.php';

if(isset($_POST['submit'])){
  
    // True: either email or password is empty
    if(emptyInputLogin ($_POST['email'], $_POST['pwd']) !== false){
        header("location: ../login.php?error=emptyinput");
        exit();
    }

    // Go to the user's main page (staff, manager, executive or superuser)
    loginUser($conn, $_POST['email'], $_POST['pwd']);
}
else{
    header("location: ../login.php");
}