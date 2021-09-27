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
    
    /*******to Inactive********/
    //// When searcActivate and searchInactivate buttons are clicked, name is added to URL. ////
    // When name is not in the URL
    if(!isset($_GET['name']) || empty($_GET['name']) || substr($_GET['name'], 0, 5) == 'inact'){
        // Receive active employees' names and email addresses from `users` table
        $activeEmployees = getEmployeesInfo($conn, 1, "", 'active'); // 1 includes executive
     }
    // When name is in the URL
    else{
        $activeEmployees = getEmployeesInfo($conn, 1, substr($_GET['name'], 5), 'active');
     }

    /********to Active********/
    //// When searcActivate or searchInactivate buttons are clicked, name is added to URL on Administrator.inc.php. ////
    // When name is not in the URL
    if(!isset($_GET['name']) || empty($_GET['name']) || substr($_GET['name'], 0, 5) == 'activ'){
        // Receive active employees' names and email addresses from `users` table
        $inactiveEmployees = getEmployeesInfo($conn, 1, "", 'inactive'); // 1 includes executive $part == "" means all employees
     }
    else{ // Specify employees including $part, substr($_GET['name'], 5), in the name
        $inactiveEmployees = getEmployeesInfo($conn, 1, substr($_GET['name'], 5), 'inactive');
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
    <h2>Employee Activation and Inactivation</h2>
 </div><br>

<!--Message (activated or inactivated)-->
<div style = "text-align: center">
    <?php
        if(isset($_GET['activate'])){
            if($_GET['activate'] == 'activated'){
                echo ('The employee was activated.');
             }
            elseif($_GET['activate'] == 'inactivated'){
                echo ('The employee was inactivated.');
             }
            elseif($_GET['activate'] == 'failactivation'){
                echo ('The activation was failed. Contact the administrator.');
             }
            elseif($_GET['activate'] == 'failinactivation'){
                echo ('The inactivation was failed. Contact the administrator.');
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

<!--to Inactive-->
<div style = "text-align: center">
    <form class = "frame" action = "Administrator.inc.php" method = "post">
        Search
        <!-- Search text box and Search button -->
        <input  type = "text"   name = "searchedActivateName" placeholder = "Input the initial part of name...">
        <button type = "submit" name = "searchActivate">Search</button><br><br>
        Active Employees<br>
        <!-- Select box -->
        <select name= 'employeesName'>
            <?php
                // When the employees' information is received
                if($activeEmployees != false){
                    foreach($activeEmployees as $val){
                        $value = $val['usersName']." (".$val['usersEmail'].")";
                        // Add an item in the select box
                        echo <<<__HTML__
                        <option value = "$value">$value</option>
                        __HTML__;
                    }
                }
             ?>
         </select><br><br><br>
        <!-- Inactivate button-->
        <button type = "submit" name = "Inactivatebutton">Inactivate</button>
     </form>
 </div><br><br>

<!--to Active)-->
<div style = "text-align: center">
    <form class = "frame" action = "Administrator.inc.php" method = "post">
        <!-- Create a search text box and a search button -->
        Search
        <input  type = "text"   name = "searchedInactivateName" placeholder = "Input the initial part of name...">
        <button type = "submit" name = "searchInactivate">Search</button><br><br>
        <!-- Create a select box -->
        Inactive Employees<br>
        <select name= 'employeesName'>
            <?php
                if ($inactiveEmployees != false){
                    foreach($inactiveEmployees as $val){
                        $value = $val['usersName']." (".$val['usersEmail'].")";

                        // Add an item in the select box
                        echo <<<__HTML__
                        <option value = "$value">$value</option>
                        __HTML__;
                    }
                }
             ?>
         </select><br><br><br>
        <!-- Activate button-->
        <button type = "submit" name = "Activatebutton">Activate</button>
    </form>
 </div>

</body>
</html>
