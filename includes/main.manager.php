<?php
    include_once 'header.php';
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';

    //if(!isset($_SESSION)){
    //    session_start();
    //}

    // Nobody can directly enter this page except manager. Login is necessary.
    if($_SESSION['usersposition'] != 'manager'){
        header("location: ../login.php?error=adminerror");
        exit();
    }

    //For loop of each year iteration
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
        top: -45px;
     }
    .message{
        position:relative;
        left: 0px;
        top: -25px;
        text-align: center;
     }
    .frame{
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #333333;
        border-radius: 10px;
        display:inline-block;
     }
</style>

<!--Title-->
<div style = "text-align: center">
    <h2>Manager's Main Page</h2>
 </div>

<!--Employee's Name-->
<div style = "text-align: center">
        <!--Employee's Name-->
        <?php echo($_SESSION["usersname"]); ?>
 </div><br><br>

<!--Password Change-->
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
    <p><?php echo($_SESSION["usersname"]." => ".$_SESSION["usersboss"]);?></p>
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
    <form action = "staff.inc.php" class ="frame" method = "post">
        <?php
        // Showing user's name
        echo($_SESSION["approvername"]."<br><br>"); // It was stored on login.function.php
        
        for($year = $ac+1; $year >= 2020; $year--){

            if(!checkVCTableExists($conn, $year)){
                continue;
             }

           $subordinateVCs = staffvcInfo($conn, $year, 0, '0', $_SESSION["approveremail"]); // This function is in vc.function.php 0 means all.

            if ($subordinateVCs != false){
                echo ($year.'<br>'); // Year is displayed if data is found.

                // All buttons in the year are desplayed
                $i = 0;
                echo (' ');
                foreach($subordinateVCs as $singleVC){
                    if($i != 0){
                        if($vc == $singleVC['vc']){
                            echo (' ');
                        }
                        else{
                            echo ('<br><br>');
                        }
                    }
                    $name = $year.$singleVC['quarter'].$singleVC['vc'].$singleVC['usersEmail'];
                    buttonValueColor($singleVC['phase'], $name, $singleVC['quarter'], $singleVC['vc']);
                    $vc = $singleVC['vc'];
                    $i = $i+1;
                }
                echo ('<br>');
            }
        }
        ?>
     </form><br><br>

    <!--vc.php is shown  when a button is clicked-->
    <form action = "staff.inc.php" method = "post" style="padding: 10px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; display:inline-block;">
        <?php
        for($year = $ac+1; $year >= 2020; $year--){

            if(!checkVCTableExists($conn, $year)){ // Check if Table `year` exists
                continue;
            }
            $subordinatesVCs = managerVcInfo($conn, $year, $_SESSION["approveremail"], '4'); // This function is in vc.functions.php 0 means all.
            
            // No data in the table
            if ($subordinatesVCs == false){
                exit();
             }

           echo ($year); // Year is displayed if data is found.

           // All buttons in the year are desplayed
           $comp = "";
           foreach($subordinatesVCs as $singleVC){
            
                // If the person appears at the first time, type the name
                if($comp != $singleVC['usersName']){
                   echo ('<br><br>'.$singleVC['usersName'].'<br>');
                }

                $name = $year.$singleVC['quarter'].$singleVC['vc'].$singleVC['usersEmail'];
                buttonValueColor($singleVC['phase'], $name, $singleVC['quarter'], '4');

                echo (' ');

                $comp = $singleVC['usersName'];
           }
        }
        ?>
     </form>
 </div>

</body>
</html>