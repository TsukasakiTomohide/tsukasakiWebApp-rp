<?php
    include_once 'header.php';
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
    // Get all the files in the folder
    $allFilePaths = glob('../database_backup/*');
?>
<!--Position-->
<style>
    .toAdministrator{
        position:relative;
        top: 30px;
        width: 200px;
        vertical-align: middle;
     }
    .pos{
        position:relative;
        top: 50px;
        text-align: center;
     }
    .frame{
        width: 300px;
        padding: 20px;
        margin-bottom: 0px;
        border: 1px solid #333333;
        border-radius: 10px;
        text-align: center;
        display:inline-block;
     }
 </style>

<!--Title-->
<div style = "text-align: center">
    <h2>Backup</h2>
 </div>

<!--Error Display-->
<div style = "text-align: center">
    <?php
        if (isset($_GET['error'])){
            if($_GET['error'] == 'exportsucceeded'){
                echo ("The database was successfully exported.");
             }
            if($_GET['error'] == 'failedprivilege'){
                echo ("The database user no have a valid privilege.");
             }
            if($_GET['error'] == 'exportfailed'){
                echo ("The export of the database was failed.");
             }
            if($_GET['error'] == 'importsucceeded'){
                echo ("The database was successfully imported.");
             }
            if($_GET['error'] == 'importfailed'){
                echo ("The import of the database was failed.");
             }
            if($_GET['error'] == 'deletesucceeded'){
                echo ("The database backup file was successfully deleted.");
             }
            if($_GET['error'] == 'deletefailed'){
                echo ("The delete of the database backup file was failed.");
             }
            if($_GET['error'] == 'blank'){
                echo ("The database file was not selected.");
             }      
         }
        else{
            echo('<br>');
         }
     ?>
 </div>

<!--Return to Administrator.php-->
<div style = "text-align: center">
    <form action = "./Administrator.php" method = "post" >
        <button class = "toAdministrator" type = "submit" name = "toAdministrator">to Administrator Page</button>
     </form>
 </div>

<!--Export, Import and Delete-->
<div class ="pos">
    <form class = "frame" action = "Administrator.inc.php" method = "post">
        <button type = "submit" name = "Export">Export</button><br><br>
        <!--File Select Box-->
        <select name= "backupFile">
            <?php foreach($allFilePaths as $singleFilePath){ ?>
                     <option value = "<?php echo($singleFilePath); ?>"><?php echo(pathinfo($singleFilePath, PATHINFO_FILENAME)); ?></option>
            <?php }?>
         </select>
        <button type = "submit" name = "Import">Import</button>
        <button type = "submit" name = "Delete" style ="position:relative;top:0px;left:10px"onclick="return confirm('Do you really want to delete the backup file？')">Delete</button>
     </form>
 </div>

</body>
</html>


