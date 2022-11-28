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

    /**********Displayed year of Select box***********/
    $ac = date('Y'); // the current year

    // Determine the year to search
    if(isset($_GET["year"]) && !empty($_GET["year"])){
        $searchedYear = $_GET["year"];
    }
    else{// URL value is blank or empty
        $searchedYear = $ac + 1;
    }
?>

<!--Position-->
<style>
    .toAdministrator{
        position:relative;
        top: -10px;
        width: 200px;
        vertical-align: middle;
     }
    .frame{
        width: 200px;
        padding: 20px;
        margin-bottom: 0px;
        border: 1px solid #333333;
        border-radius: 10px;
        text-align: center;
        display:inline-block;
     }
 </style>

<!--Title-->
<div style = "text-align: center;">
    <h2>Add VC Space</h2>
 </div>

<!--Error Display-->
<div style = "text-align: center">
    <?php
        if(isset($_GET['error']) && $_GET['error'] == 'succeeded'){
            echo ("The database was successfully updated.");
        }
        else{
            echo('<br>');
        }
    ?>
 </div><br><br>
<!--Return to Administrator.php-->
<div style = "text-align: center">
    <form action = "./Administrator.php" method = "post">
        <button class = "toAdministrator" type = "submit" name = "toAdministrator">to Administrator Page</button>
    </form>
 </div>

<!--Year Select box and Creation button-->
<div style = "text-align: center;position:relative; top: 10px;">
    <form class = "frame" action="Administrator.inc.php" method = "POST">
        <!--Select box of years-->
        <select name= "year" style = "width: 80px">
            <?php
                for($year = $ac + 1; $year >= $ac; $year--){
                    // Set the displayed select box value
                    if($year == $searchedYear){
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
         </select><br><br>
        <!--Creation button-->
        <input type="submit"name="createVCspacebutton"value="Create VC Space"/>
     </form>
 </div>

</body>
</html>