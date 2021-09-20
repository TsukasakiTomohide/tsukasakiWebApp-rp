<?php
    include_once 'header.php';
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';
    if(!isset($_SESSION)){
        session_start();
    }
    // Nobody can directly enter this page except staff. Login is necessary.
    if($_SESSION['usersposition'] != 'staff'){
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
    .approvalProcess{
        position:relative;
        left: 0px;
        top: -45px;
     }
    .message{
        position:relative;
        left: 0px;
        top: -45px;
        text-align: center;
     }
 </style>

<!--Title-->
<div style = "text-align: center">
    <h2>Staff's Main Page</h2>
 </div>

<!--Employee's Name-->
<div style = "text-align: center">
        <!--Employee's Name-->
        <?php echo($_SESSION["usersname"]."<br><br>"); ?>
 </div>

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
 </div><br>

<!--Message-->
<div class = "message">
    <?php
    if(isset($_GET["message"]) && $_GET["message"] == "submitted"){
        echo ("Your VC was submitted.");
    }
    elseif(isset($_GET["message"]) && substr($_GET["message"], 0, 9) =="submitted"){
        echo ("Your VC of Q". substr($_GET["message"], 13, 1).", ".substr($_GET["message"], 9, 4)." was submitted.");
    }
    elseif(isset($_GET["error"]) && $_GET["error"] =="stmftailed"){
        echo ("The database has a trouble. Contact the administrator.");
    }
    else{
        echo('<br>');
    }
    ?>
 </div>

<!--VC4 buttons-->
<div style = "text-align: center"> <!--Transferring to staff.inc.php then vc.php when a button is clicked-->
    <form action = "staff.inc.php" method = "post">
        <?php          
            for($year = $ac; $year >= 2020; $year--){ //For loop of each year iteration
                
                // Check if the employee is employed in the year
                if(!checkVCTableExists($conn, $year)){ // Check if TABLE `year` exists
                    continue; // $Result is false if the employee is not employed in that year
                 }

                // All the VC info in that year. Only VC4 is shown.
                $staffVCs = staffVcInfo($conn, $year, 0, 4, $_SESSION["usersemail"]); // This function is in vc.functions.php. 0 means all.            
                if ($staffVCs == false){ // No data in the table
                    continue;
                 }
        
                echo ($year.'<br> '); // Year is displayed if data is found.
    
                // All the buttons of the employee in the year are desplayed including VC3 and VC4
                foreach($staffVCs as $singleVC){ // This loop is within one year
                    $name = $year.$singleVC['quarter'].$singleVC['vc'].$singleVC['usersEmail'];
                    buttonValueColor($singleVC['phase'], $name, $singleVC['quarter'], $singleVC['vc']);
                    echo (' ');
                }
                echo ('<br>');
             }
         ?>
    </form>
 </div>

</body>
</html>