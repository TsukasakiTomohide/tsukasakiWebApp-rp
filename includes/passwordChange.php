<?php
    include_once 'header.php';
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';
    if(!isset($_SESSION)){
        session_start();
    }
    $mainPage = "./main.".$_SESSION['usersposition'].".php";
?>

<!--Title-->
<section class = "staff-main-form">
    <div style = "text-align: center">
        <h2>Password Change</h2>
    </div>
</section>

<!--Warning-->
<div style= "text-align: center">
    <p style= "display: inline-block; text-align: left">
        <?php
        if(isset($_GET['error'])){
            if(strlen($_GET['error']) >= 9 && substr($_GET['error'], 0, 9) == 'succeeded'){
                echo ('Password was successfully reset.');
            }
            elseif($_GET['error'] == 'invalidoldpassword'){
                echo ('The old password did not match.');
            }
            elseif($_GET['error'] == 'stmtfailed'){
                echo ('<p>Database connection is something wrong. Please contact the administrator.</p>');
            }
            elseif($_GET['error'] == 'invalidnewpassword'){
                echo ('<p style = "text-align: left; display: inline-block">
                Your password has to include following charactors<br><br>
                    - one number (0 - 9)<br>
                - one uppercase letter (A - Z)<br>
                - one lowercase letter (a - z)<br>
                - one special charactor ( @ # \ - _ $ % ^ & + = § ! \ )<br>
                - The length must be from 8 to 12 letters.</p>');
            }
        }
        else{
            echo("<br>");
        }
         ?>
     </p>
 </div>

<!--Return to main-->
<div style = "text-align: center">
    <form action = "<?php echo($mainPage);?>" method = "post">
        <button type = "submit" name = "toMain"style ="position: relative; left: 200px; top: -10px">to Main Page</button>
    </form>
 </div>

<!--Text boxes and a button-->
<div style = "text-align: center">
    <form action = "staff.inc.php" method = "post" style="width: 500px; padding: 20px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; display:inline-block;">
        <input  type = "password" name = "oldPassword" style="width: 250px" placeholder = "Enter your old password..."><br><br>
        <input  type = "password" name = "newPassword" style="width: 250px" placeholder = "Enter your new password..."><br><br>
        <button type = "submit"   name = "pswdChangebuttonATpasswordChange">Password Change</button>
    </form>
 </div>

</body>
</html>