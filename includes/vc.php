<?php
    include_once 'header.php';
    require_once 'dbh.inc.php';
    include_once 'vc.functions.php';
    //if(!isset($_SESSION)){
    //    session_start();
    // }

    /* Employee information of this vc. The info comes from the URL.
       $year, $quarter, $vc and $usersEmail */
    if(isset($_GET['when'])){
        $year       = substr($_GET['when'], 0, 4);
        $quarter    = substr($_GET['when'], 4, 1);
        $vc         = substr($_GET['when'], 5, 1);
        $usersEmail = substr($_GET['when'], 6);     // This VC's employee email
     }
    
    ////////////////////////////////////////
    //// Get this VC's employee VC data ////
    ////////////////////////////////////////
    // The VC data of this employee in a specific year, quarter and vc
    $vcThisPage = getVCdata($conn, $year, $quarter, $vc, $usersEmail);

    // VC data is not collected
    if(!$vcThisPage){
        echo ('<p>The VC data was not collected. Contact the administrator.</p>');
        exit();
     }
    
    $phase = $vcThisPage["phase"];

    ////////////////////////////////////////
    //////////// Set $vcPurpose ////////////
    ////////////////////////////////////////
    // Get the manger's VC only if the emloyee of this vc is staff //
    if($_SESSION['usersposition'] == 'staff'){ // staff
        $vcPurpose = 'write';
        // The approver's VC info is collected to enable/disable buttons, and to show if the VC3 is approved or not.
        $ApproverVC = getVCdata($conn, $year, $quarter, '3', $_SESSION['approveremail']); // Used to enter a manager's VC page
     }
    elseif($_SESSION['usersposition'] == 'manager'){ // manager
        $ApproverVC = false;
        /*  $_SESSION['approveremail'] is the login user if the position is manager, director or executive
            This VC's employee == login user means the login user changes his/her VC.
            Only managers go to this case*/
        if($_SESSION['approveremail'] == $usersEmail){
            $vcPurpose = 'write';
         }
        else{
            $vcPurpose = 'approval';
         }
     }
    else{ // director, executive and administrator
        $vcPurpose = 'approval';
        $ApproverVC = false;
     }
    // Used to go return to a previous page
    if($_SESSION['usersposition'] == 'staff' && $vc == '3'){  // staff
            $mainPage = "./vc.php?when=".$year.$quarter.'4'.$_SESSION['usersemail'];
            $mainPageName = "to VC4";
     }
    elseif ($_SESSION['usersposition'] == 'administrator'){
        $mainPage = './administrator.php';
        $mainPageName = "to Main Page";
     }
    elseif ($_SESSION['usersposition'] == 'executive' && $_SESSION['vcPurpose'] == 'administrator'){
        $mainPage = './administrator.php';
        $mainPageName = "to Main Page";
     }
    else{
        $mainPage = "./main.".$_SESSION['usersposition'].".php";
        $mainPageName = "to Main Page";
     }
    
    // Used to select an item in a select box
    $SelectSelfEval = SelectSelfEval($vcThisPage);
    $SelectEval     = SelectEval($vcThisPage);
 ?>

<style>
    .frame{
        width: 300px;
        padding: 0px;
        margin-bottom: 10px;
        border: 1px solid #333333;
        border-radius: 10px;
        display:inline-block;
     }
 </style>

<!-- Title -->
<div style = "text-align: center; width: 1500px"> <!--The alignment is center-->
    <h1><?php echo($vcThisPage["usersName"]);?><br>VC<?php echo($vc);?></h1>
 </div>
<!--Error-->
<?php
    if(isset($_GET['error']) && $_GET['error'] == 'stmtfailed'){
        echo ('<p style = "text-align: center">Something went wrong. Try again.</p>');
     }
    elseif(isset($_GET['message']) && $_GET['message'] == 'saved'){
        echo ('<p style = "text-align: center">The VC was successfully saved.</p>');
     }
    elseif(isset($_GET['error']) && $_GET['error'] == 'timestamp'){
        echo ('<p style = "text-align: center">"The next one-on-one meeting is not fixed.</p>');
     }
    else{
        echo('<p><br></p>');
    }
?>
<!-- VC3 Reference buttons -->
<?php
    if($ApproverVC != false && $_SESSION['usersposition'] == "staff" && $vc == '4'){
        $DisableVC3button = DisabledVC3Button($_SESSION['usersposition'], $vc, $ApproverVC);?>
        <div style = 'text-align: center; width: 1500px'>
            <form action = 'staff.inc.php' class ="frame" method = 'post'>
                <p>VC3<button type = 'submit' name = 'vcOpen' style = 'width:50px; height:20px' <?php echo("value = '".$year.$quarter.'3'.$_SESSION['bossemail']."' ".$DisableVC3button);?>><?php echo('Q'.$quarter);?></button></p>
                <?php
                    if($DisableVC3button == 'disabled'){
                        echo('VC3 is not approved<br><br>');
                    }
                ?>
            </form>
        </div><br>
 <?php }
    else{
        $DisableVC3button = '';
     } ?><br>

<!--Disable and Values-->
<?php
    $DisabledCalender      = DisabledCalender($_SESSION['usersposition'], $vc, $vcPurpose, $phase, $ApproverVC);
    $DisabledApproveButton = DisabledApproveButton($vcPurpose, $phase);
    $DisabledRejectButton  = DisabledRejectButton($vcPurpose, $phase);
    $DisabledSaveButton    = DisabledSaveButton($_SESSION['usersposition'], $vc, $vcPurpose, $phase, $DisableVC3button);
    $DisabledSubmitButton  = DisabledSubmitButton($_SESSION['usersposition'], $vc, $vcPurpose, $phase, $DisableVC3button);
    $DisabledGoals         = DisabledGoals($DisableVC3button, $_SESSION['usersposition'], $vc, $vcPurpose, $phase);
    $DisableWeight         = DisableWeight($DisableVC3button, $_SESSION['usersposition'], $vc, $vcPurpose, $phase);
    $DisabledResSelf       = DisabledResSelf($_SESSION['usersposition'], $vc, $vcPurpose, $phase);
    $DisabledFinalEval     = DisabledFinalEval($_SESSION['usersposition'], $vc, $vcPurpose, $phase);
    $DisabledPerformance   = DisabledPerformance($_SESSION['usersposition'], $vc, $vcPurpose, $phase);

    $vcDate = vcTimestamp($vcThisPage["OneOnOne"], $phase);
    $param = $year.$quarter.$vc.$phase."%".$usersEmail;
?>

<!--VC text boxes-->
<section style = "width: 1500px; text-align: center">
    <div style = 'text-align; center; position: relative; top: -40px'>
        <form action = 'vc.submit.php' method = 'post'> <!--Data are transferred to vc.inc.php-->
            <p style = "position: relative; left: -380px; top: 0px">Next One on One Meeting (when Goals Approved)</p>
            <input  type = 'date'   name = 'calender' value = "<?php echo($vcDate); ?>" <?php echo($DisabledCalender);?>      style = "position: relative; left: 350px;  top: -53px; width: 150px;text-align: center">
            <label                                                                                                            style = "position: relative; left: 358px;  top: -52px">Do you send notification?</label>
            <input  type = "radio"  name = 'email'    value = 'Send Email'                                                    style = "position: relative; left: 355px;  top: -50px" checked>          <label style = "position: relative; left: 355px;  top: -52px">Yes</label>
            <input  type = "radio"  name = 'email'    value = 'Not Send'                                                      style = "position: relative; left: 355px;  top: -50px">                  <label style = "position: relative; left: 355px;  top: -52px">No</label>
            <button type = 'submit' name = 'Approve'  value = "<?php echo($param); ?>"  <?php echo($DisabledApproveButton);?> style = "position: relative; left: -300px; top: 0px; width: 100px; height: 60px"><?php echo(NameApproveButton($phase));?></button>
            <button type = 'submit' name = 'Reject'   value = "<?php echo($param); ?>"  <?php echo($DisabledRejectButton);?>  style = "position: relative; left: -300px; top: 0px; width: 210px; height: 60px"><?php echo(NameRejectButton($phase));?></button>
            <button type = 'submit' name = 'save'     value = "<?php echo($param); ?>"  <?php echo($DisabledSaveButton);?>    style = "position: relative; left: -250px; top: 0px; width: 100px; height: 60px">Save</button>
            <button type = 'submit' name = 'submit'   value = "<?php echo($param); ?>"  <?php echo($DisabledSubmitButton);?>  style = "position: relative; left: -250px; top: 0px; width: 100px; height: 60px">Submit</button>
            <button type = "submit" name = "toMain"   value = "<?php echo($mainPage);?>"                                      style = "position: relative; left: -200px; top: 0px; width: 160px;height: 60px"><?php echo($mainPageName);?></button>
            
            <?php for($i = 0; $i < 5; $i++){ ?>
                <h3 style = "position: relative; top: 16px">Goal <?php echo($i+1); ?></h3>
                <input type = 'text'   name = 'vc23_<?php   echo($i+1); ?>'  value = 'VC<?php echo($vc-1);?> Solutions'             maxlength = '300' disabled                          style='position:relative;top:  0px;width:348px;left: 56px'>
                <input type = 'text'   name = 'Target_<?php echo($i+1); ?>t' value = 'Annual Target'                                maxlength = '300' disabled                          style='position:relative;top:  0px;width:348px;left: 56px'>
                <input type = 'text'   name = 'Plan_<?php   echo($i+1); ?>t' value = 'Quarter Plans'                                maxlength = '300' disabled                          style='position:relative;top:  0px;width:298px;left: 56px'>
                <input type = 'text'   name = 'Wei_<?php    echo($i+1); ?>t' value = 'Weight'                                       maxlength = '300' disabled                          style='position:relative;top:  0px;width:100px;left: 56px'>
                <input type = 'text'   name = 'Self_<?php   echo($i+1); ?>t' value = 'Self Evaluation'                              maxlength = '300' disabled                          style='position:relative;top: 82px;width:100px;left:-56px'><br>
                <textarea              name = 'vc23_<?php   echo($i+1); ?>'                                　                       maxlength = '300' <?php echo($DisabledGoals);?>     style='position:relative;top:  0px;width:350px;left:168px;height:200px;resize:none;vertical-align:top;' wrap ='hard'><?php echo($vcThisPage["vc23_".($i+1)]);?></textarea>              
                <textarea              name = 'Target_<?php echo($i+1); ?>'                                                         maxlength = '300' <?php echo($DisabledGoals);?>     style='position:relative;top:  0px;width:350px;left:168px;height:200px;resize:none;vertical-align:top;' wrap ='hard'><?php echo($vcThisPage["annualTarget_".($i+1)]);?></textarea>   
                <textarea              name = 'Plan_<?php   echo($i+1); ?>'                                                         maxlength = '300' <?php echo($DisabledGoals);?>     style='position:relative;top:  0px;width:300px;left:168px;height:200px;resize:none;vertical-align:top;' wrap ='hard'><?php echo($vcThisPage["quarterPlan_".($i+1)]);?></textarea>  
                <input type = 'number' name = 'Wei_<?php    echo($i+1); ?>'  value = '<?php echo($vcThisPage["weight_".($i+1)]);?>' maxlength = '300' <?php echo($DisableWeight);?>     style='position:relative;top:  0px;width:100px;left:168px;height: 50px;vertical-align:top;'        max='100' min='0'>
                <select                name = 'Self_<?php   echo($i+1); ?>'                                                                         <?php echo($DisabledResSelf);?>     style='position:relative;top: 82px;width:108px;left: 56px;height: 50px;vertical-align:top'>
                        <option value = 'D' <?php echo($SelectSelfEval[$i][0]); ?>>D</option>
                        <option value = 'C' <?php echo($SelectSelfEval[$i][1]); ?>>C</option>
                        <option value = 'B' <?php echo($SelectSelfEval[$i][2]); ?>>B</option>
                        <option value = 'A' <?php echo($SelectSelfEval[$i][3]); ?>>A</option>
                        <option value = ' ' <?php echo($SelectSelfEval[$i][4]); ?>> </option>
                </select>
                <input type = 'text'   name = 'Eval_<?php   echo($i+1); ?>t' value = 'Evaluation'                                 maxlength = '300' disabled                            style='position:relative;top:136px;width:100px;left:-56px'>
                <select                name = 'Eval_<?php   echo($i+1); ?>'                                                                         <?php echo($DisabledFinalEval);?>   style='position:relative;top:156px;width:108px;left:-168px;height: 50px;vertical-align:top'>
                        <option value = 'D' <?php echo($SelectEval[$i][0]); ?>>D</option>
                        <option value = 'C' <?php echo($SelectEval[$i][1]); ?>>C</option>
                        <option value = 'B' <?php echo($SelectEval[$i][2]); ?>>B</option>
                        <option value = 'A' <?php echo($SelectEval[$i][3]); ?>>A</option>
                        <option value = ' ' <?php echo($SelectEval[$i][4]); ?>> </option>
                </select><br><br>
                <input type = 'text'   name = 'Res_<?php    echo($i+1); ?>t' value = 'Results of the quarter'                     maxlength = '300' disabled                            style='position:relative;top:-12px;width:568px'>
                <input type = 'text'   name = 'Per_<?php    echo($i+1); ?>t' value = 'Performance of the staff'                   maxlength = '300' disabled                            style='position:relative;top:-12px;width:568px'>
                <textarea              name = 'Res_<?php    echo($i+1); ?>'                                                       maxlength = '300' <?php echo($DisabledResSelf);?>     style='position:relative;top:-12px;width:570px;height:240px;resize:none;vertical-align:top;' wrap ='hard'><?php echo($vcThisPage["quarterResult_".($i+1)]);?></textarea>
                <textarea              name = 'Per_<?php    echo($i+1); ?>'                                                       maxlength = '300' <?php echo($DisabledPerformance);?> style='position:relative;top:-12px;width:570px;height:240px;resize:none;vertical-align:top;' wrap ='hard'><?php echo($vcThisPage["Performance_".($i+1)]);?></textarea>
            <?php } ?>
            </form>
        </div>
</section>
</body>
</html>