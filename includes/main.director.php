<?php
    include_once 'header.php';
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';

    //if(!isset($_SESSION)){
    //    session_start();
    //}
    // Nobody can directly enter this page except director. Login is necessary.
    if($_SESSION['usersposition'] != 'director'){
        header("location: ../login.php?error=adminerror");
        exit();
    }

    $ac = date('Y'); // Obraining the year
?>
<!--Position-->
<style>
    .passwordChange{
        position:relative;
        left: -80px;
        top: -20px;
        height: 45px;
        width: 150px;
        padding: 10px;
        vertical-align: middle;
     }
    .Logout{
        position:relative;
        left: 80px;
        top: -65px;
        height: 45px;
        width: 150px;
        padding: 10px;
        vertical-align: middle;
     }
    .approvalProcess{
        position:relative;
        left: 0px;
        top: -55px;
     }
    .message{
        position:relative;
        left: 0px;
        top: -45px;
        text-align: center;
     }
 </style>

<!--Title-->
<section>
    <div style = "text-align: center">
        <h2>Director's Main Page</h2>
    </div>
 </section>

<!--Employee's Name-->
<div style = "text-align: center">
        <!--Employee's Name-->
        <?php echo($_SESSION["usersname"]); ?>
 </div><br><br>

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

<!--Approval Process-->
<div class = "approvalProcess" style = "text-align: center">
    <p>Unsubmitted -> Submitted -> Goals Approved -> Self Evaluated -> Finalized</p>
 </div>

<!--Showing a message when VC is submitted-->
<div class = "message">
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
 </div>

<!--Showing buttons at ther center-->
<div style = "text-align: center;"> 
    <!--vc.php is shown  when a button is clicked-->
    <form action = "staff.inc.php" method = "post" style="padding: 10px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; display:inline-block;">
        <?php
        for($year = $ac+1; $year >= 2020; $year--){

            if(!checkVCTableExists($conn, $year)){ // Check if Table `year` exists
                continue;
            }

            $subordinatesVCs = managerVcInfo($conn, $year, $_SESSION["approveremail"], '3'); // This function is in vc.functions.php 0 means all.
            
            // No data in the table
            if ($subordinatesVCs == false){ // The employee was not hired in $year
                continue;
            }

            echo ($year); // $year is displayed if data is found.

            // All buttons in the year are desplayed
            $comp = "";
            $vc = "";
            echo (' ');
            foreach($subordinatesVCs as $singleVC){
                // If the person appears at the first time, type the name
                if($comp != $singleVC['usersName']){
                    echo ('<br><br>'.$singleVC['usersName'].'<br>');
                }
                elseif ($vc != $singleVC['vc']){
                    echo('<br><br>');
                }

                $name = $year.$singleVC['quarter'].$singleVC['vc'].$singleVC['usersEmail'];
                buttonValueColor($singleVC['phase'], $name, $singleVC['quarter'], '3');

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