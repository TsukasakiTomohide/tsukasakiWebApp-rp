<?php
    include_once 'header.php';
    require_once 'dbh.inc.php';
    include_once 'vc.functions.php';
    if(!isset($_SESSION)){
        session_start();
     }

    // Nobody can directly go to Administrator.php. Login is necessary.
    if($_SESSION['usersposition'] != 'administrator' && $_SESSION['usersposition'] != 'executive'){
        header("location: ../login.php?error=adminerror");
        exit();
     }
    // Executive changes the vcPurpose to administrator on this page.
    $_SESSION['vcPurpose'] = 'administrator'; // The users become administrator in this page.

    $ac = date('Y'); // Obraining the current year

    // Determine the year to search. This is not blank when 
    if(isset($_GET["year"]) && !empty($_GET["year"])){
        $displayedYear = $_GET["year"];
        }
    else{// URL value is blank or empty
        $displayedYear = $ac;
    }
 ?>

<style>
    .frame{
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #333333;
        border-radius: 10px;
        display:inline-block;
     }
 </style>

<!-- Title -->
<div style = "text-align: center"> <!--The alignment is center-->
        <h1>Goal Navigator</h1>
        <h2>Administrator</h2>
 </div>

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
     ?>
 </div><br>

<!--Select Box and Submit button-->
<div style = "text-align: center">
    <form action="Administrator.inc.php" method = "POST">
        <!-- Buttons to revise the database -->
        <input type="submit" name="password"         value="Password Reset"        style = "width: 150px"/>
        <input type="submit" name="activate"         value="Activate / Inactivate" style = "width: 150px"/>
        <input type="submit" name="registerEmployee" value="Register Employee"     style = "width: 150px"/>
        <input type="submit" name="reviseEmployee"   value="Revise Employee"       style = "width: 150px"/><br><br>
        <input type="submit" name="addVCspace"       value="Add VC Space"          style = "width: 150px; position: relative; left: -77px"/>
        <input type="submit" name="databaseBackup"   value="Backup"                style = "width: 150px; position: relative; left: -77px"/>
        <input type="submit" name ="Logout"          value="Logout"                style = "width: 150px; position: relative; left:  77px"/>
        <input type="submit" name ="toExecutive"     value="to Executive"          style = "width: 150px; position: relative; left:  77px" <?php if($_SESSION['usersposition'] != 'executive'){echo('disabled');} ?>/><br><br><br>

        <!--Year Select Box-->
        <select name= "year">
            <?php
                for($year = $ac+1; $year >= 2020; $year--){
                    // Set the displayed select box value
                    if($year == $displayedYear){
                        $selected = 'selected';
                     }
                    else{
                        $selected = '';
                     }
                    // Add an item in the select box
                    echo <<<__HTML__
                    <option value = "$year" $selected>$year</option>
                    __HTML__;
                 }
            ?>
         </select>
        <input type="submit"name="changeYear"value="Change Year"/><br>
    </form>
 </div>

<!--Search Active Employees-->
<?php
    // Get all employees' data
    $activeEmployees = getEmployeesInfo($conn, 0, "", 'active'); //0 excludes executive

    if ($activeEmployees == false){
        echo('<p style ="text-align:center">There are no active employees.</p>');
        exit();
    }
    if(!checkVCTableExists($conn, $displayedYear)){
        echo('<p style ="text-align:center">The table of the year does not exist in the database.</p>');
        exit();
    }
 ?>
<p style ="text-align:center">Unsubmitted -> Submitted -> Goals Approved -> Self Evaluated -> Finalized</p>

<!--Employee's VC buttons-->
<div style = "text-align: center">
    <form action = "staff.inc.php" class ="frame" method = "post">
        <?php
            // Display employees' name and buttons
            foreach($activeEmployees as $singleEmployee){ // User's Loop

                $employeeInfo = staffVcInfo($conn, $displayedYear, 0, 0, $singleEmployee['usersEmail']);// `quarter` = 0 means all quarters
                if($employeeInfo == false){
                    continue;
                }

                // If the person appears at the first time, type the name
                $comp = '';
                if($comp != $singleEmployee['usersName']){
                    echo ($singleEmployee['usersName'].'<br>');
                }
                $i = 0;
                echo (' ');
                foreach($employeeInfo as $eachEmployeeInfo){ // VC's Loop
                    if($i != 0){
                        if($vc == $eachEmployeeInfo['vc']){
                            echo (' ');
                        }
                        else{
                            echo ('<br><br>');
                        }
                    }
                    $name = $displayedYear.$eachEmployeeInfo['quarter'].$eachEmployeeInfo['vc'].$singleEmployee['usersEmail'];
                    buttonValueColor($eachEmployeeInfo['phase'], $name, $eachEmployeeInfo['quarter'], $eachEmployeeInfo['vc'], $eachEmployeeInfo['TotalEval']);
                    $vc = $eachEmployeeInfo['vc'];
                    $i = $i+1;
                }
                $comp = $eachEmployeeInfo['usersName'];

                echo ('<br><br>');
            }
         ?>
     </form>
 </div>


</body>
</html>





