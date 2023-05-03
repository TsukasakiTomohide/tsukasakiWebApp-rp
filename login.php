<?php
    include_once 'includes/header.php';
    if(!isset($_SESSION)){
        session_start();
     }
     
    $_SESSION['usersposition'] = '';
?>

<section class = "login-form">
    <!--Title-->
    <div style = "text-align: center">
        <h1>Goal Navigator</h1>
        <h2>Login</h2>
    </div>
    <!--Login data-->
    <div class = "login-form-form" style = "text-align: center">
        <form action = "includes/login.inc.php" method = "post"> <!--Data are transferred to login.inc.php-->
            <input  type = "text"     size = "35" name = "email" placeholder = "Email..."><br><br><br>
            <input  type = "password" size = "35" name = "pwd"   placeholder = "Password..."><br><br><br>
            <button type = "submit"   style="height:50px; width:150px" name = "submit">Log In</button>
        </form>
    </div>
</section>

<!--Error message-->
<div style = "text-align: center">
<?php
    if(isset($_GET['error']) && $_GET['error'] == 'emptyinput'){
        echo ('<p>Fill in all fields.</p>');
    }

    if(isset($_GET['error']) && $_GET['error'] == 'stmtfailed'){
        echo ('<p>Database connection is something wrong. Please contact the administrator.</p>');
    }

    if(isset($_GET['error']) && $_GET['error'] == 'emailinvalid'){
        echo ('<p>Email does not exist.</p>');
    }

    if(isset($_GET['error']) && $_GET['error'] == 'wrongpwd'){
        echo ('<p>Password does not match.</p>');
    }
    if(isset($_GET['error']) && $_GET['error'] == 'adminerror'){
        echo ('<p>Unauthorized Access was performed.</p>');
    }
?>
</div>

</body>
</html>