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
    <h2>Register Employee</h2>
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
            elseif($_GET['error'] == 'wrongpwd'){
                echo ("The password is invalid.");
            }
            elseif($_GET['error'] == 'invalidpassword'){
                echo ('The new password does not meet requirements.<br><br>
                        Your password has to include following charactors<br><br>
                        - one number (0 - 9)<br>
                        - one uppercase letter (A - Z)<br>
                        - one lowercase letter (a - z)<br>
                        - one special charactor ( @ # \ - _ $ % ^ & + = § ! \ )<br><br>
                        The length must be from 8 to 12 letters.');

            }       
            elseif($_GET['error'] == 'emptyboss'){
                echo ("The boss name is empty.");
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
            elseif($_GET['error'] == 'alreadyexists'){
                echo ("The employee was not registered because the same employee was found in the database.");
            }
            elseif($_GET['error'] == 'failaddingemployee'){
                echo ("The system is something wrong. Contact the administrator.");
            }
            elseif($_GET['error'] == 'succeeded'){
                echo ("The registration was succeeded.");
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

<!--Employee Info-->
<section style = "text-align: center">
    <div class = "frame">
        <form action="Administrator.inc.php" method = "POST" style = "text-align: left; display: inline-block">
            New Employee's Name<br>
            <input  type = "text"     name = "employeeName"      style ="width: 350px"  placeholder = "ex. John Smith"><br><br>
            Employee's Email Address<br>
            <input  type = "text"     name = "employeeEmail"     style ="width: 350px"  placeholder = "Employee's Email Address"><br><br>
            Employee's Password<br>
            <input  type = "password" name = "employeePassword"  style ="width: 350px"  placeholder = "Password"><br><br>
            Employee's Position<br>
            <select name= "position" style ="width: 100px">
                <option value = "staff">staff</option>
                <option value = "manager">manager</option>
                <option value = "director">director</option>
                <option value = "executive">executive</option>
                <option value = "administrator">administrator</option>
            </select><br><br>
            The Name of the Employee's Boss<br>
            <input  type = "text"     name = "employeeBossName"  style ="width: 350px"  placeholder = "ex. John Smith"><br><br>
            The Email Address of the Employee's Boss<br>
            <input  type = "text"     name = "employeeBossEmail" style ="width: 350px"  placeholder = "Email of the Employee's Boss"><br><br><br>
            <button type = "submit"   name = "registerbutton"    style ="width: 358px; height: 50px">Register</button><br><br>
        </form>
    </div> 
 </section>

</body>
</html>