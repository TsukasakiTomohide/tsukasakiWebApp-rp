<?php
    // Nobody can directly go to Administrator.php. Login is necessary.
    if($_SESSION['usersposition'] != 'administrator' && $_SESSION['usersposition'] != 'executive'){
        header("location: ../login.php?error=adminerror");
        exit();
     }

     $myfile = fopen("test.txt", "r") or die("Unable to open file!");
     while(!feof($myfile)) {
        echo fgets($myfile) . "<br>";
      }
     fclose($myfile);

      $myfile = fopen("test.txt", "w");
      fclose($myfile);

?>

