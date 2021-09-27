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

    // $_GET['name'] value comes from the URL, and it is added in Administrator.inc.php
    if (!isset($_GET['name']) || empty($_GET['name'])){ // $_GET['name'] originally comes fom SearchPwdName on this page
        // Receive active employees' names and email addresses from `users` table
        $activeEmployees = getEmployeesInfo($conn, 1, "", 'active'); // 1 includes executive // $part = "" means all employees
    }
    else{
        $activeEmployees = getEmployeesInfo($conn, 1, $_GET['name'], 'active');
    }

    // This error comes from this URL error=succeeded + name + email
    // Set select when the select box is the same as name + email of the URL
    if(isset($_GET['error'])){
        $displayedEmployee = substr($_GET['error'], 9);
    }
    else{
        $displayedEmployee = '';
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
        margin-bottom: 10px;
        border: 1px solid #333333;
        border-radius: 10px;
        display:inline-block;
     }
 </style>

<!--Title-->
<div style = "text-align: center">
    <h2>Password Reset</h2>
 </div>

<!--Warning-->
<div class = "DisplayWarning" style = "text-align: center">
    <p class = "WarningItems" style = "text-align: left; display: inline-block">
        <?php
            if(isset($_GET['error']) && substr($_GET['error'], 0, 9) == 'succeeded'){
            echo ('The password of '.substr($_GET['error'], 9).' was successfully reset.');
            }
            elseif(isset($_GET['error']) && $_GET['error'] == 'passwordfailed'){
                echo ('<p>The password was not reset. The database has a trouble. Contact the administrator.</p>');
            }
            elseif(isset($_GET['error']) && $_GET['error'] == 'invalidpassword'){
                echo ('<p style = "text-align: left; display: inline-block">
                Your password has to include following charactors<br><br>
                    - one number (0 - 9)<br>
                - one uppercase letter (A - Z)<br>
                - one lowercase letter (a - z)<br>
                - one special charactor ( @ # \ - _ $ % ^ & + = § ! \ )<br>
                - The length must be from 8 to 20 letters.</p>');
            }
            else{
                echo("<br>");
            }
         ?>
     </p>
 </div>

<!--Return to Administrator.php-->
<div style = "text-align: center">
    <form action = "./Administrator.php" method = "post">
        <button class = "toAdministrator" type = "submit" name = "toAdministrator">to Administrator Page</button>
    </form>
</div>

 <!--Select box and Button-->
<div style = "text-align: center">
    <form class = "frame" action = "Administrator.inc.php" method = "post">
        <!-- Search text box and Search button -->
        Search
        <input  type = "text"   name = "searchPwdName" placeholder = "Input the initial of name...">
        <button type = "submit" name = "searchPwdbutton">Search</button><br><br>
        <!-- Create a select box -->
        Active Employees<br>
        <!--Active Employee Select Box List
            employeesName is supposed to be posted to Administrator.inc.php when Password Reset button is clicked-->
        <select name= 'employeesName'>
            <?php
                if (!$activeEmployees){
                    echo("Acive employees were not detected.");
                    exit();
                 }

                foreach($activeEmployees as $val){
                    $value = $val['usersName']." (".$val['usersEmail'].")";

                    if($value == $displayedEmployee){
                        $selected = 'selected';
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
         </select><br><br><br>
        <!-- New password text box and Change button-->
        <input  type = "password" name = "newPassword" placeholder = "Enter a new password...">
        <button type = "submit"   name = "passwordResetbutton">Password Reset</button>
     </form>
 </div>

</body>
</html>