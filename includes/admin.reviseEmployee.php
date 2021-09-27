<?php
    include_once 'header.php';
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';
    //if(!isset($_SESSION)){
    //    session_start();
    //}
    // Nobody can directly go to Administrator.php. Login is necessary.
    if($_SESSION['usersposition'] != 'administrator' && $_SESSION['usersposition'] != 'executive'){
        header("location: ../login.php?error=adminerror");
        exit();
    }

    /*******Get the active employees' info*******/
    if(!isset($_GET['name']) || empty($_GET['name'])){ // $_GET['name'] value comes from the URL. It is created in reviseEmployee.inc.php.
        // Receive all active employees' names and email addresses from `users` table
        $activeEmployees = getEmployeesInfo($conn, 1, "", 'active'); // 1 includes executive // $Result is an array.
    }
    else{ // Receive active employee's names including $_GET['name'] string
        $activeEmployees = getEmployeesInfo($conn, 1, $_GET['name'], 'active');
    }

    $selectedEmployeeEmail = employeeSelection();

    if(!empty($activeEmployees) && empty($selectedEmployeeEmail)){
        $employeeInfo = $activeEmployees[0];
        $_SESSION['originalUsersEmail'] = $employeeInfo['usersEmail'];
        $Selected = positionSelection($employeeInfo['usersPosition']); // This function is at the bottom of this page
    }
?>
<!--Position-->
<style>
    .toAdministrator{
        position:relative;
        left: 160px;
        top: -10px;
        width: 200px;
        vertical-align: middle;
     }
    .frame{
        width: 500px;
        padding: 20px;
        margin-bottom: 0px;
        border: 1px solid #333333;
        border-radius: 10px;
        display:inline-block;
     }
 </style>

<!--Title-->
<div style = "text-align: center">
    <h2>Revise Employee</h2>
 </div><br>

<!--Error Display-->
<div style = "text-align: center">
    <?php
        if (isset($_GET['error'])){
            if($_GET['error'] == 'invalidname'){
                echo ("The employee's name is invalid.");
            }
            elseif($_GET['error'] == 'wrongemail'){
                echo ("The employee's email address is invalid.");
            }
            elseif($_GET['error'] == 'emptyboss'){
                echo ("The boss is empty.");
            }
            elseif($_GET['error'] == 'invalidbossname'){
                echo ("The boss's name is invalid.");
            }
            elseif($_GET['error'] == 'emptybossemail'){
                echo ("The email address of employee's boss is empty.");
            } 
            elseif($_GET['error'] == 'wrongbossmail'){
                echo ("The email address of employee's boss is wrong.");
            }
            elseif($_GET['error'] == 'failrevisingemployee'){
                echo ("The system is something wrong. Ask the administrator.");
            }
            else{
                echo("<br>");
            }
        }
        elseif (isset($_GET['message'])){
            if(strlen($_GET['message']) >= 9 && substr($_GET['message'], 0, 9) == 'succeeded'){
                echo ("The employee was successfully modified.");
            }
            elseif(strlen($_GET['message']) >= 7 && substr($_GET['message'], 0, 7) == 'deleted'){
                echo ("The employee was successfully deleted.");
            }
            else{
                echo("<br>");
            }
        }
        else{
            echo("<br>");
        }
     ?>
 </div><br>
<!--Return to Administrator.php-->
<div style = "text-align: center">
    <form action = "./Administrator.php" method = "post">
        <button class = "toAdministrator" type = "submit" name = "toAdministrator">to Administrator Page</button>
     </form>
 </div>

<!--Warning No Active Employees-->
<?php if (!$activeEmployees){
        echo('<p style ="text-align:center">There are no active employees. Activate employees.</P>');
        exit();
      } ?>

<!--Select box and Button-->
<section style = "text-align: center">
    <div class = "frame">
        <form action = "reviseEmployee.inc.php" method = "post" style = "text-align: left; display: inline-block">
            <!-- Create a search text box and a search button -->
            Search
            <input  type = "text"   name = "searchName" placeholder = "Input the initial part of name...">
            <button type = "submit" name = "searchbutton">Search</button><br><br>
            <!--Create a select box-->
            Active Employees<br>
            <select name= 'employeesName' style ="width: 410px" onchange = "submit(this.form)">
                <?php
                foreach($activeEmployees as $val){

                    $value = $val['usersName']." (".$val['usersEmail'].")";
                    
                    if($val['usersEmail'] == $selectedEmployeeEmail){
                        $selected = 'selected';
                        $employeeInfo = $val;
                        $_SESSION['originalUsersEmail'] = $employeeInfo['usersEmail'];
                        $Selected = positionSelection($employeeInfo['usersPosition']); // This function is at the bottom of this page
                    }
                    else{
                        $selected = '';
                    }
                    // Add an item in the select box
                    echo <<<__HTML__
                    <option value = "$value" $selected>$value</option>
                    __HTML__;
                }
                ?>
             </select><br><br>
            <!--Create a submit button-->
            <input  type = "submit" name = "revisebutton" value = "Revise" style = "position: relative; left:160px; width: 80px; height: 30px" /><br>
            <!--Create the employee's information boxes-->
            <p style = "text-align: left; display: inline-block">
                <!--Create Employee's name and email boxes-->
                <?php if(!empty($employeeInfo)){ ?>
                        New Employee's Name<br>
                        <input  type = "text" name = "employeeName"  value = "<?php echo($employeeInfo['usersName']);?>"  style ="width: 400px"><br><br>
                        Employee's Email Address<br>
                        <input  type = "text" name = "employeeEmail" value = "<?php echo($employeeInfo['usersEmail']);?>" style ="width: 400px"><br><br>
                <?php }
                    else{ ?>
                        New Employee's Name<br>
                        <input  type = "text" name = "employeeName" placeholder = "ex John Smith" style ="width: 400px"><br><br>
                        Employee's Email Address<br>
                        <input  type = "text" name = "employeeEmail" placeholder = "Email Address" style ="width: 400px"><br><br>
                <?php } ?>
                <!--Create the employee's position enum-->
                Employee's Position<br>
                <!--Create employee's position enum-->
                <select name= "position" style ="width: 120px">
                    <option value = "staff"         <?php echo($Selected[0]);?>>staff</option>
                    <option value = "manager"       <?php echo($Selected[1]);?>>manager</option>
                    <option value = "director"      <?php echo($Selected[2]);?>>director</option>
                    <option value = "executive"     <?php echo($Selected[3]);?>>executive</option>
                    <option value = "administrator" <?php echo($Selected[4]);?>>administrator</option>
                 </select><br><br>
                <!--Create boss's name and email text box-->
                <?php if(!empty($employeeInfo)){?>
                        The Name of the Employee's Boss<br>
                        <input  type = "text" name = "employeeBossName"  value = "<?php echo($employeeInfo['usersBoss']);?>" style ="width: 400px"><br><br>
                        The Email Address of the Employee's Boss<br>
                        <input  type = "text" name = "employeeBossEmail" value = "<?php echo($employeeInfo['bossEmail']);?>" style ="width: 400px"><br><br><br>
                 <?php }
                    else{?>
                        The Name of the Employee's Boss<br>
                        <input  type = "text" name = "employeeBossName"  placeholder = "ex. John Smith" style ="width: 400px"><br><br>
                        The Email Address of the Employee's Boss<br>
                        <input  type = "text" name = "employeeBossEmail" placeholder = "Email Address" style ="width: 400px"><br><br><br>
                 <?php }?>
            </p>
            <!--Create a delete button-->
            <p>
                <input type = "submit" name = "deletebutton" value = "Delete" style = "position: relative; left:160px; top: -20px; width: 80px; height: 30px" onclick="return confirm('Do you really want to delete the employee？')" />
             </p>
        </form>
    </div>
 </section>

<?php
function employeeSelection(){
    //This is called when a employee was selected in the select box
    if(isset($_GET['message'])){
        if(strlen($_GET['message']) >= 9 && substr($_GET['message'], 0, 9) == 'succeeded'){
            $selectedEmployeeEmail = substr($_GET['message'], 9);
        }
        elseif(strlen($_GET['message']) >= 7 && substr($_GET['message'], 0, 7) == 'changed'){
            $selectedEmployeeEmail = substr($_GET['message'], 7);
        }
        else{
            $selectedEmployeeEmail = '';
        }
    }
    else{
        $selectedEmployeeEmail = '';
    }
    return $selectedEmployeeEmail;
 }
function positionSelection($usersPosition){
    $Selected = array ("", "", "", "", "");
    if (isset($usersPosition)){
        if ($usersPosition == 'staff'){
            $Selected[0] = 'selected';
        }
        elseif ($usersPosition == 'manager'){
            $Selected[1] = 'selected';
        }
        elseif ($usersPosition == 'director'){
            $Selected[2] = 'selected';
        }
        elseif ($usersPosition == 'executive'){
            $Selected[3] = 'selected';
        }
        elseif ($usersPosition == 'administrator'){
            $Selected[4] = 'selected';
        }
    }
    return $Selected;
 }
?>

</body>
</html>