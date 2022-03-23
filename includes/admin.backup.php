<?php
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';
    //if(!isset($_SESSION)){
    //    session_start();
    // }
    // Nobody can directly go to Administrator.php. Login is necessary.
    if($_SESSION['usersposition'] != 'administrator' && $_SESSION['usersposition'] != 'executive'){
        header("location: ../login.php?error=adminerror");
        exit();
     }

    $year = $_GET['year'];
    
    getBackup($conn, $year);

    // *************** Download *************** //
    // FileDownload();
     
    // ************* Back to Admin ************ //
    header("location: ./backup.php");


    // *************** Sub-Function of Download *************** //
    function FileDownload(){
        // File name to be downloaded
        $filepath = 'test.txt';
          
        // File name after $filepath is downloaded
        $filename = 'test.txt';
          
        // File type
        header('Content-Type: application/force-download');
          
        // Get the file size to show the progress of download
        header('Content-Length: '.filesize($filepath));
          
        // Request download and rename
        header('Content-Disposition: attachment; filename="'.$filename.'"');
          
        // Read the file and start download
        readfile($filepath);
    
        exit;
        }
?>


