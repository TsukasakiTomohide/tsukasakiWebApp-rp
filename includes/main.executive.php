<?php

    include_once 'header.php';
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';
    if(!isset($_SESSION)){
        session_start();
     }

    // Nobody can directly enter this page except executive. Login is necessary.
    if($_SESSION['usersposition'] != 'executive'){
        header("location: ../login.php?error=adminerror");
        exit();
    }
    
    $_SESSION['vcPurpose'] = 'approval';
    $ac = date('Y'); // Obraining the year
?>

<style>
.passwordChange{
    position:relative;
    left: -80px;
    top: 0px;
    height: 45px;
    width: 150px;
    padding: 10px;
    vertical-align: middle;
 }
.Logout{
    position:relative;
    left: 80px;
    top: -45px;
    height: 45px;
    width: 150px;
    padding: 10px;
    vertical-align: middle;
 }
</style>

<!--Showing the page title at the center-->
<section>
    <div style = "text-align: center">
        <h2>Executive's Main Page</h2>
    </div>
 </section>

<!--Showing a message when VC is submitted-->
<div style = "text-align: center">
    <?php
    if(isset($_GET["message"])){
        if($_GET["message"] == "submitted"){
            echo ("Your VC was submitted.");
        }
        elseif(substr($_GET["message"], 0, 9) =="submitted"){
            echo ("Your VC of Q". substr($_GET["message"], 13, 1).", ".substr($_GET["message"], 9, 4)." was submitted.");
        }
        elseif(substr($_GET["message"], 0, 8) =="approved"){
            echo ("The VC of Q". substr($_GET["message"], 12, 1).", ".substr($_GET["message"], 8, 4)." was approved.");
        }
        elseif(substr($_GET["message"], 0, 8) =="rejected" || substr($_GET["message"], 0, 8) =="returned"){
            echo ("The VC of Q". substr($_GET["message"], 12, 1).", ".substr($_GET["message"], 8, 4)." was ".substr($_GET["message"], 0, 8).".");
        }
    }
    else{
        echo('<br>');
    }
    ?>
 </div><br>
<!--Password Change button-->
<div style = "text-align: center">
    <form action = "passwordChange.php" method = "post">
        <button class = "passwordChange" type = "submit" name = "pswdChangebuttonATmain">Password Change</button>
    </form>
 </div>
<!--Return to Login-->
<div style = "text-align: center">
    <form action = "../login.php" method = "post">
        <button class = "Logout" type = "submit" name = "Logout">Logout</button>
     </form>
 </div>

<!--Button to Administrator.php-->
<div style = "text-align: center">
    <form  action="Administrator.inc.php" method = "POST">
        <input type="submit" name="toAdministrator"value="Administrator Page"/>
    </form><br><br>
 </div>

<!--Showing buttons at ther center-->
<div style = "text-align: center;"> 
    <!--vc.php is shown  when a button is clicked-->
    <form action = "staff.inc.php" method = "post" style="display:inline-block;">
        <?php
        $i = 0;
        for($year = $ac; $year >= 2020; $year--){

            if(!checkVCTableExists($conn, $year)){ // Check if Table `year` exists
                continue;
            }
            $subordinatesVCs = managerVcInfo($conn, $year, $_SESSION["approveremail"], 0); // $vc == 0 means all VCs
            
            // No data in the table
            if ($subordinatesVCs == false){
                continue;
             }
            
            // Approval Flow
            if ($i == 0){
                echo("Unsubmitted -> Submitted -> Goals Approved -> Self Evaluated -> Finalized<br><br>");
                $i = $i + 1;
            }

            echo ($year); // Year is displayed if data is found.

            // All buttons in the year are desplayed
            $comp = "";
            $vc = "";
            echo (' ');
            foreach($subordinatesVCs as $singleVC){
                // This place is for a manager's staff.
                // Eliminate the manager's name and buttons.
                if ($_SESSION['approvername'] == $singleVC['usersName']){
                    continue;
                 }
            
                // If the person appears at the first time, type the name
                if($comp != $singleVC['usersName']){
                   echo ('<br><br>'.$singleVC['usersName'].'<br>');
                 }
                elseif ($vc != $singleVC['vc']){
                    echo('<br><br>');
                }

                // Create a button, its color and letters in it
                $name = $year.$singleVC['quarter'].$singleVC['vc'].$singleVC['usersEmail'];
                buttonValueColor($singleVC['phase'], $name, $singleVC['quarter'], $singleVC['vc']);

                echo (' ');
                $vc = $singleVC['vc'];
                $comp = $singleVC['usersName'];
             }
         }
        ?>
     </form>
 </div>
</body>
</html>