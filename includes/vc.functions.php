<?php

function staffVcInfo($conn, $year, $quarter, $vc, $vcPersonEmail){

    if ($quarter == 0){ // All quarters
        if($vc == 0){ // All vc
            $sql = "SELECT * FROM [dbo].[$year] WHERE usersEmail = ? ORDER BY vc ASC, quarter ASC;";
        }
        else{ // Specific VC
            $sql = "SELECT * FROM [dbo].[$year] WHERE usersEmail = ? AND vc = ? ORDER BY quarter ASC;";
        }
    }
    else{ // Specific quarter
        if($vc == 0){
            $sql = "SELECT * FROM [dbo].[$year] WHERE usersEmail = ? AND quarter = ? ORDER BY vc;";
        }
        else{
            $sql = "SELECT * FROM [dbo].[$year] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
        }
    }

    $stmt = pdoPrepare($conn, $sql);

    // Make a MySQL statement
    pdoBind($stmt, 1, $vcPersonEmail, PDO::PARAM_STR);
    if ($quarter == 0){
        if($vc != 0){
            pdoBind($stmt, 2, $vc, PDO::PARAM_STR);
        }
    }
    else{
        if($vc == 0){
            pdoBind($stmt, 2, $quarter, PDO::PARAM_INT);
        }
        else{
            pdoBind($stmt, 2, $quarter, PDO::PARAM_STR);
            pdoBind($stmt, 3, $vc, PDO::PARAM_INT);
        }
    }

    // Send the SELECT statement //
    $results = pdoExecute($stmt);

    // Receive data //
    while ($row = pdoFetch($stmt)){
        $entries[] = $row;
    }

    if(!empty($entries)){
        return $entries;
    }
    else{
        return false;
    }
 }

function managerVcInfo($conn, $year, $approveremail, $vc){ 

    $staff = getStaffName($conn, $approveremail);

    if ($staff == false){
        exit();
        }

    foreach($staff as $val){
        if($vc == 0){ // ALl VCs
            $sql = "SELECT * FROM [dbo].[$year] WHERE usersEmail = ? ORDER BY vc ASC, quarter ASC;";
        }
        else{
            $sql = "SELECT * FROM [dbo].[$year] WHERE usersEmail = ? AND vc = ? ORDER BY quarter ASC;";
        }

        $stmt = pdoPrepare($conn, $sql);

        pdoBind($stmt, 1, $val["usersEmail"], PDO::PARAM_STR);
        if($vc != 0){
            pdoBind($stmt, 2, $vc, PDO::PARAM_INT);
        }    

        $results = pdoExecute($stmt);

        while($row = pdoFetch($stmt)){
            $entries[] = $row;
        }
    }
    
    if(!empty($entries)){
        return $entries;
    }
    else{
        return false;
    }
 }

//This function is used in managerVcInfo function.
function getStaffName($conn, $approveremail){

    $sql = "SELECT usersEmail FROM users WHERE bossEmail = ? AND active = ?;";

    $stmt = pdoPrepare($conn, $sql);

    pdoBind($stmt, 1, $approveremail, PDO::PARAM_STR);
    pdoBind($stmt, 2, 'active', PDO::PARAM_STR);  

    $results = pdoExecute($stmt);

    while($row = pdoFetch($stmt)){
        $entries[] = $row;
    }

    if(!empty($entries)){
        return $entries;
    }
    else{
        return false;
    }
 }

// When a VC form is submitted, it is sploaded to the database
function vcUpdate($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData){

    if($phase == 'Unsubmitted'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "vc23_$i = ?, annualTarget_$i = ?, quarterPlan_$i = ?, weight_$i = ?";
        }
        $sql = "Update [dbo].[$year] SET $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }
    elseif($phase == 'Submitted'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "`weight_$i`=? ";
        }
        $sql = "Update [dbo].[$year] SET OneOnOne = ?, $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }
    elseif($phase == 'Goals Approved'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "weight_$i, quarterResult_$i = ?,selfEval_$i = ? ";
        }
        $sql = "Update [dbo].[$year] SET OneOnOne = ?, $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }
    elseif($phase == 'Self Evaluated'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "finalEval_$i = ?, Performance_$i = ? ";
        }
        $sql = "Update [dbo].[$year] SET $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }

    $stmt = pdoPrepare($conn, $sql);

    // Make a MySQL statement
    if($phase == 'Unsubmitted'){
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, 4*$i+1, $vcData["vc23_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 4*$i+2, $vcData["Target_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 4*$i+3, $vcData["Plan_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 4*$i+4, $vcData["Wei_".($i+1)], PDO::PARAM_INT);
        }
        $i = 21;
    }
    elseif($phase == 'Submitted'){
        pdoBind($stmt, 1, $calender, PDO::PARAM_STR);
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, $i+2, $vcData["Wei_".($i+1)], PDO::PARAM_INT);
        }
        $i = 7;
    }
    elseif($phase == 'Goals Approved'){
        pdoBind($stmt, 1, $calender, PDO::PARAM_STR);
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, 3*$i+2, $vcData["Wei_".($i+1)], PDO::PARAM_INT);
            pdoBind($stmt, 3*$i+3, $vcData["Res_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 3*$i+4, $vcData["Self_".($i+1)], PDO::PARAM_STR);
        }
        $i = 16;
    }
    elseif($phase == 'Self Evaluated'){
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, 2*$i+1, $vcData["Eval_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 2*$i+2, $vcData["Per_".($i+1)], PDO::PARAM_STR);
        }
        $i = 11;
    }
    pdoBind($stmt, $i, $usersEmail, PDO::PARAM_STR);
    pdoBind($stmt, $i+1, $quarter, PDO::PARAM_INT);
    pdoBind($stmt, $i+2, $vc, PDO::PARAM_INT);

    $result = pdoExecute($stmt);

    // mysqli_stmt_execute did not go well
    if (!$result){
        return $result;
    }
    else{
        return true;
    }
 }
// Save VC when an approver clicked Save button
function saveVcApprover($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData){
    if($phase == 'Goals Approved'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "Performance_$i = ?";
        }
        $sql = "Update [dbo].[$year] SET OneOnOne = ?, $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }
    if($phase == 'Self Evaluated'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "finalEval_$i = ?, Performance_$i = ?";
        }
        $sql = "Update [dbo].[$year] SET $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }
    $stmt = pdoPrepare($conn, $sql);

    // Make a SQL statement
    if($phase == 'Goals Approved'){
        pdoBind($stmt, 1, date("Y-m-d", strtotime($calender)), PDO::PARAM_STR);
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, $i+2, $vcData["Per_".($i+1)], PDO::PARAM_STR);
        }
        $i = 7;
    }
    if($phase == 'Self Evaluated'){
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, 2*$i+1, $vcData["Eval_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 2*$i+2, $vcData["Per_".($i+1)], PDO::PARAM_STR);
        }
        $i = 11;
    }
    pdoBind($stmt, $i, $usersEmail, PDO::PARAM_STR);
    pdoBind($stmt, $i+1, $quarter, PDO::PARAM_INT);
    pdoBind($stmt, $i+2, $vc, PDO::PARAM_INT);

    $result = pdoExecute($stmt);

    // mysqli_stmt_execute did not go well
    if (!$result){
        return $result;
    }
    else{
        return true;
    }
 }
// Reject from Self Evaluated to Goals Approved
function rejectUpdateVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData){
    if($phase == 'Goals Approved'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "weight_$i = ?, Performance_$i = ? ";
        }
        $sql = "Update [dbo].[$year] SET $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }
    elseif($phase == 'Self Evaluated'){
        for($i = 1; $i <= 5; $i++){
            $param[$i] = "Performance_$i = ?";
        }
        $sql = "Update [dbo].[$year] SET $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";
    }

    $stmt = pdoPrepare($conn, $sql);
    if($phase == 'Goals Approved'){
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, 2*$i+1, $vcData["wei_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 2*$i+2, $vcData["Per_".($i+1)], PDO::PARAM_STR);
        }
        $i = 11;
    }
    if($phase == 'Self Evaluated'){
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, $i+1, $vcData["Per_".($i+1)], PDO::PARAM_STR);
        }
        $i = 6;
    }

    pdoBind($stmt, $i, $usersEmail, PDO::PARAM_STR);
    pdoBind($stmt, $i+1, $quarter, PDO::PARAM_INT);
    pdoBind($stmt, $i+2, $vc, PDO::PARAM_INT);

    $result = pdoExecute($stmt);

    // mysqli_stmt_execute did not go well
    if (!$result){
        return $result;
    }
    else{
        return true;
    }

 }

// Approve from Self Evaluated to Goals Approved
function approveUpdateVC($conn, $year, $quarter, $vc, $phase, $calender, $usersEmail, $vcData){
    for($i = 1; $i <= 5; $i++){

        $param[$i] = "finalEval_$i = ?, Performance_$i = ? ";
    }
    $sql = "Update [dbo].[$year] SET $param[1], $param[2], $param[3], $param[4], $param[5] WHERE usersEmail = ? AND quarter = ? AND vc = ?;";

    $stmt = pdoPrepare($conn, $sql);

    if($phase == 'Self Evaluated'){
        for($i = 0; $i < 5; $i++){
            pdoBind($stmt, 2*$i+1, $vcData["Eval_".($i+1)], PDO::PARAM_STR);
            pdoBind($stmt, 2*$i+2, $vcData["Per_".($i+1)], PDO::PARAM_STR);
        }
        $i = 11;
    }

    pdoBind($stmt, $i, $usersEmail, PDO::PARAM_STR);
    pdoBind($stmt, $i+1, $quarter, PDO::PARAM_INT);
    pdoBind($stmt, $i+2, $vc, PDO::PARAM_INT);

    $result = pdoExecute($stmt);

    // mysqli_stmt_execute did not go well
    if (!$result){
        return $result;
    }
    else{
        return true;
    }

 }

function getVCdata($conn, $year, $quarter, $vc, $usersEmail){
    // vcInfo is in staff.functions.php. $quarter is from 1 to 4.
    $row = staffVcInfo($conn, $year, $quarter, $vc, $usersEmail);

    // No data in the table
    if ($row == false){
        return false;
        }

    // The content of VC3/VC4
    for($i = 1; $i <= 5; $i++){
        if(empty($row[0]['vc23_'.$i]) || $row[0]['vc23_'.$i] == null){
            $row[0]['vc23_'.$i] = "VC2/VC3 Solutions";
        }
        if(empty($row[0]['annualTarget_'.$i]) || $row[0]['annualTarget_'.$i] == null){
            $row[0]['annualTarget_'.$i] = "Annual Goal";
        }
        if(empty($row[0]['quarterPlan_'.$i]) || $row[0]['quarterPlan_'.$i] == null){
            $row[0]['quarterPlan_'.$i] = "Goals of the quarter";
        }
        if(empty($row[0]['weight_'.$i]) || $row[0]['weight_'.$i] == null){
            $row[0]['weight_'.$i] = 0;
        }
        if(empty($row[0]['quarterResult_'.$i]) || $row[0]['quarterResult_1'] == null){
            $row[0]['quarterResult_'.$i] = "Results of the quarter";
        }
        if(empty($row[0]['selfEval_'.$i]) || $row[0]['selfEval_'.$i] == null){
            $row[0]['selfEval_'.$i] = ' ';
        }
        if(empty($row[0]['finalEval_'.$i]) || $row[0]['finalEval_'.$i] == null){
            $row[0]['finalEval_'.$i] = ' ';
        }
        if(empty($row[0]['Performance_'.$i]) || $row[0]['Performance_'.$i] == null){
            $row[0]['Performance_'.$i] = "The staff's performance of the quarter";
        }
    }

    return $row[0];
 }
function vcRemoveNullData(){
    for($i = 0;$i < 5; $i++){
    if(empty($_POST["vc23_".($i+1)]))  {$_SESSION["vc23_".($i+1)]   = "VC2/VC3 Solutions";}                      else{$_SESSION["vc23_".($i+1)]  = $_POST["vc23_".($i+1)];}
    if(empty($_POST["Target_".($i+1)])){$_SESSION["Target_".($i+1)] = "Annual Goal";}                            else{$_SESSION["Target_".($i+1)]= $_POST["Target_".($i+1)];}
    if(empty($_POST["Plan_".($i+1)]))  {$_SESSION["Plan_".($i+1)]   = "Goals of the quarter";}                   else{$_SESSION["Plan_".($i+1)]  = $_POST["Plan_".($i+1)];}
    if(empty($_POST["Wei_".($i+1)]))   {$_SESSION["Wei_".($i+1)]    = 0;}                                        else{$_SESSION["Wei_".($i+1)]   = $_POST["Wei_".($i+1)];}
    if(empty($_POST["Res_".($i+1)]))   {$_SESSION["Res_".($i+1)]    = "Results of the quarter";}                 else{$_SESSION["Res_".($i+1)]   = $_POST["Res_".($i+1)];}
    if(empty($_POST["Self_".($i+1)]))  {$_SESSION["Self_".($i+1)]   = ' ';}                                      else{$_SESSION["Self_".($i+1)]  = $_POST["Self_".($i+1)];}
    if(empty($_POST["Eval_".($i+1)]))  {$_SESSION["Eval_".($i+1)]   = ' ';}                                      else{$_SESSION["Eval_".($i+1)]  = $_POST["Eval_".($i+1)];}
    if(empty($_POST["Per_".($i+1)]))   {$_SESSION["Per_".($i+1)]    = "The staff's performance of the quarter";} else{$_SESSION["Per_".($i+1)]   = $_POST["Per_".($i+1)];}
    } 
 }

function vcUpdatePhase($conn, $year, $quarter, $phase, $usersEmail){
    try{
        $sql = "Update [dbo].[$year] SET phase = ? WHERE usersEmail = ? AND quarter = ?;";

        $stmt = pdoPrepare($conn, $sql);

        pdoBind($stmt, 1, $phase, PDO::PARAM_STR);
        pdoBind($stmt, 2, $usersEmail, PDO::PARAM_STR);
        pdoBind($stmt, 3, $quarter, PDO::PARAM_INT);

        return pdoExecute($stmt);
    }
    catch(exception $e){
        return false;
    }

 }

function sendMail($state, $usersEmail, $year, $quarter, $phase, $calender){

    //if(!isset($_SESSION)){
    //    session_start();
    // }

    try {
        require_once '../sendMail.php';

        $subject     = $usersEmail."'s VC was ".$state;

        if($phase == 'Goals Approved'){
            $body = "$usersEmail's VC of Q$quarter, $year was $state. Please check the document. The one on one meeting will be $calender.";
        }
        else{
            $body = "$usersEmail's VC of Q$quarter, $year was $state. Please check the document."; 
        }

        sendMail($usersEmail, $usersEmail, $_SESSION["approveremail"], $_SESSION["approvername"], $subject, $body);
        sendMail($_SESSION["approveremail"], $_SESSION["approvername"], $usersEmail, $usersEmail, $subject, $body);

        return true;

      } catch (Exception $e) {
        // エラーの場合
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
      }
 }

function phaseChange($conn, $year, $quarter, $vc, $phase, $usersEmail){

    if($phase == 'Unsubmitted'){
        $phase = 'Submitted';
    }
    elseif($phase == 'Goals Approved'){
        $phase = 'Self Evaluated';
    }
    else{
        return false;
        exit();
    }

    $sql = "Update [dbo].[$year] SET phase = ?  WHERE usersEmail = ? AND quarter = ? AND vc = ?;";

    $stmt = pdoPrepare($conn, $sql);

    pdoBind($stmt, 1, $phase, PDO::PARAM_STR);
    pdoBind($stmt, 2, $usersEmail, PDO::PARAM_STR);
    pdoBind($stmt, 3, $quarter, PDO::PARAM_INT);
    pdoBind($stmt, 4, $vc, PDO::PARAM_INT);

    $result = pdoExecute($stmt);

    // mysqli_stmt_execute did not go well
    if (!$result){
        return $result;
    }
    return $result;
 }

function buttonValueColor($phase, $name, $quarter, $vc){ 
    if($phase == 'Unsubmitted'){        // White
        $bcolor = "#FFFAFA";
    }
    elseif($phase == 'Submitted'){      // Red
        $bcolor = "#FFC1FF";
    }
    elseif($phase == 'Goals Approved'){       // Yello
        $bcolor = "#FFFFC1";
    }
    elseif($phase == 'Self Evaluated'){ // Green
        $bcolor = "#E0FFC1";
    }
    elseif($phase == 'Finalized'){      // Blue
        $bcolor = "#C1FFFF";
    }

    echo <<<__HTML__
    <button type = "submit" name = "vcOpen" style="width:180px;height:50px;background-color:$bcolor" value=$name>VC$vc-Q$quarter $phase</button>
    __HTML__;
 }

// Get Employees' information from `user` table except executive
// $include 0: exclude 1: include executive;  active = 'active' or 'inactive'; $part = letters for search
function getEmployeesInfo($conn, $include, $part, $active){

    if($include){
        if (empty($part)){
            $sql = "SELECT * FROM users WHERE active = ?;";
        }
        else{
            $sql = "SELECT * FROM users WHERE active = ? AND usersName LIKE ?;";
        }
    }
    else{
        if (empty($part)){
            $sql = "SELECT * FROM users WHERE usersPosition != ? AND active = ?;";
        }
        else{
            $sql = "SELECT * FROM users WHERE usersPosition != ? AND active = ? AND usersName LIKE ?;";
        }
    }

    $stmt = pdoPrepare($conn, $sql);

    // Make a SQL statement
    $partname = $part.'%';
    
    if($include){ // include exective
        if(empty($part)){
            pdoBind($stmt, 1, $active, PDO::PARAM_STR);
        }
        else{
            pdoBind($stmt, 1, $active, PDO::PARAM_STR);
            pdoBind($stmt, 2, $partname, PDO::PARAM_STR);
        }
    }
    else{ // exclude executive
        if(empty($part)){
            pdoBind($stmt, 1, 'executive', PDO::PARAM_STR);
            pdoBind($stmt, 2, $active, PDO::PARAM_STR);
        }
        else{
            pdoBind($stmt, 1, 'executive', PDO::PARAM_STR);
            pdoBind($stmt, 2, $active, PDO::PARAM_STR);
            pdoBind($stmt, 3, $partname, PDO::PARAM_STR);
        }
    }

    $results = pdoExecute($stmt);

    while($row = pdoFetch($stmt)){
        $entries[] = $row;
    }

    if(!empty($entries)){
        return $entries;
    }
    else{
        return false;
    }
 }

// setActInctEmployee is used in admin.activate.php
function setActInactEmployee($conn, $email, $act_inact){
    try{
        $sql = "UPDATE users SET active = ? WHERE usersEmail = ?;";

        $stmt = pdoPrepare($conn, $sql);

        pdoBind($stmt, 1, $act_inact, PDO::PARAM_STR);
        pdoBind($stmt, 2, $email, PDO::PARAM_STR);

        return pdoExecute($stmt);
    }
    catch(exeption $e){
            return false;
    }
 }

// *******************************************************//
// *********************** VC.php ***********************//createNewRow
// *******************************************************//
function SelectSelfEval($employeeInfo){
    $SelectSelfEval = array();
    for($i = 0; $i < 5; $i++){
        $SelectSelfEval[$i] = array_fill(0, 5, ' ');

        if($employeeInfo['selfEval_'.($i+1)] =="D"){
            $SelectSelfEval[$i][0] = 'selected';
        }
        elseif($employeeInfo['selfEval_'.($i+1)] =="C"){
            $SelectSelfEval[$i][1] = 'selected';
        }
        elseif($employeeInfo['selfEval_'.($i+1)] =="B"){
            $SelectSelfEval[$i][2] = 'selected';
        }
        elseif($employeeInfo['selfEval_'.($i+1)] =="A"){
            $SelectSelfEval[$i][3] = 'selected';
        }
        else{
            $SelectSelfEval[$i][4] = 'selected';
        }
    }
    return $SelectSelfEval;
 }
function SelectEval($employeeInfo){
    $SelectEval = array();
    for($i = 0; $i < 5; $i++){
        $SelectEval[$i] = array_fill(0, 5, ' ');

        if($employeeInfo['finalEval_'.($i+1)] =="D"){
            $SelectEval[$i][0] = 'selected';
        }
        elseif($employeeInfo['finalEval_'.($i+1)] =="C"){
            $SelectEval[$i][1] = 'selected';
        }
        elseif($employeeInfo['finalEval_'.($i+1)] =="B"){
            $SelectEval[$i][2] = 'selected';
        }
        elseif($employeeInfo['finalEval_'.($i+1)] =="A"){
            $SelectEval[$i][3] = 'selected';
        }
        else{
            $SelectEval[$i][4] = 'selected';
        }
    }
    return $SelectEval;
 }

function DisabledVC3Button($position, $vc, $ApproverVC){
    // VC3 == Submitted or Unsubmitted. Only managers write VC3.
    if ($ApproverVC['phase'] == 'Submitted' || $ApproverVC['phase'] == 'Unsubmitted'){
        return 'disabled';
    }
    else {
        return '';
    }
 }
function DisabledApproveButton($vcPurpose, $phase){
    // Enable or disable Approval button
    if ($vcPurpose == 'approval' && ($phase == 'Submitted' || $phase == 'Self Evaluated')){
        return '';
    }
    else{
        return 'disabled';}
 }
function DisabledCalender($usersposition, $vc, $phase, $ApproverVC){
    if($ApproverVC != false && $usersposition == "staff" && $vc == '3'){
        return 'disabled';
    }
    if ($phase == 'Submitted' || $phase == 'Goals Approved'){
        return '';
    }
    else{
        return 'disabled';
     }
 }
function vcTimestamp($OneOnOne, $phase){
     if($phase != 'Goals Approved'){
         return date('Y').'-'.date('m').'-'.date('d');
     }
     else{
        $Time = substr($OneOnOne, 0, 10);
         return $Time;
     }
 }
function DisabledRejectButton($vcPurpose, $phase){
        // Enable or disable Reject button
        if ($vcPurpose == 'approval'   && 
           ($phase == 'Submitted'      || 
            $phase == 'Goals Approved' || 
            $phase == 'Self Evaluated' ||
            $phase == 'Finalized'        )){
        return '';
    }
    else{
        return 'disabled';
    }  
 }
function DisabledSubmitButton($position, $vc, $vcPurpose, $phase, $ApproverVC){
    // Enable or Disable of Submit button
    // Staff
    if ($position == 'staff'){
        if ($vc == '3'){ // Staff refers to the manager's VC3
            return 'disabled';
        }
        // The position is staff, and the manager's VC3 is Submitted or Unsubmitted.
        elseif($ApproverVC != false && ($ApproverVC['phase'] == 'Submitted' || $ApproverVC['phase'] == 'Unsubmitted')){
            return 'disabled';
        }
        else{
            return '';
        }
     }
    // Manager
    elseif ($position == 'manager'){
        if ($vcPurpose == 'write' && ($phase == 'Unsubmitted' || $phase == 'Goals Approved')){
            return '';
        }
        else{
            return 'disabled';
        }
     }
    // Director, executive or administrator
    else{
        return 'disabled';
     }
 }
function DisabledSaveButton($position, $vc, $vcPurpose, $phase, $ApproverVC){
    // Enable or Disable of Submit button
    // Staff
    if ($position == 'staff'){
        if ($vc == '3'){ // Staff refers to the manager's VC3
            return 'disabled';
        }
        // The position is staff, and the manager's VC3 is Submitted or Unsubmitted.
        elseif($ApproverVC != false && ($ApproverVC['phase'] == 'Submitted' || $ApproverVC['phase'] == 'Unsubmitted')){
            return 'disabled';
        }
        else{
            return '';
        }
     }
    // Manager
    elseif ($position == 'manager'){
        if ($vcPurpose == 'approval'){
            if($phase == 'Goals Approved' || $phase == 'Self Evaluated'){
                return '';
            }
            else{
                return 'disabled';
            }
        }
        elseif ($vcPurpose == 'write' && ($phase == 'Unsubmitted' || $phase == 'Goals Approved')){ //$vcPurpose == 'write'
            return '';
        }
        else{
            return 'disabled';
        }
     }
    else{// Director, executive or administrator
            if($phase == 'Goals Approved' || $phase == 'Self Evaluated'){
                return '';
            }
            else{
                 return 'disabled';
            }
        }
 }
function DisabledGoals($DisableVC3button, $position, $vc, $vcPurpose, $phase){
    if($DisableVC3button == 'disabled'){
        return 'disabled';
    }
    if($position == 'staff' && $vc == '3'){
        return 'disabled';
    }
    elseif ($vcPurpose == 'write' && $phase == 'Unsubmitted'){
        return '';
    }
    else{
        return 'disabled';
    }
 }
function DisabledResSelf($position, $vc, $vcPurpose, $phase){
    if($position == 'staff' && $vc == '3'){
        return 'disabled';
    }
    elseif($vcPurpose == 'write' && $phase == 'Goals Approved'){
        return '';
    }
    else{
        return 'disabled';
    }
 }
function DisabledPerformance($position, $vc, $vcPurpose, $phase){
    if($position == 'staff' && $vc == '3'){
        return 'disabled';
    }
    elseif($vcPurpose == 'approval' && ($phase == 'Self Evaluated' || $phase == 'Goals Approved')){
        return '';
    }
    else{
        return 'disabled';
    }
 }
function DisabledFinalEval($position, $vc, $vcPurpose, $phase){
    if($position == 'staff' && $vc == '3'){
        return 'disabled';
    }
    elseif($vcPurpose == 'approval' && $phase == 'Self Evaluated'){
        return '';
    }
    else{
        return 'disabled';
    }
 }

function DisableWeight($DisableVC3button, $position, $vc, $vcPurpose, $phase){
    if($DisableVC3button == 'disabled'){
        return 'disabled';
    }
    if($position == 'staff' && $vc == '3'){
        return 'disabled';
    }
    elseif($vcPurpose == 'write' && ($phase == 'Unsubmitted' || $phase == 'Goals Approved')){
        return '';
    }
    else{
        return 'disabled';
    }
 }
// Change the boolean text of approval button
function NameApproveButton($phase){
    if($phase == 'Self Evaluated'){
        return 'Finalize';
    }
    else{
        return 'Approve';
    }
 }
// Change the boolean text of reject button
function NameRejectButton($phase){
    if($phase == 'Unsubmitted'){
        return 'Reject';
    }
    elseif($phase == 'Submitted' || $phase == 'Goals Approved'){
        return 'Reject to Unsubmited';
    }
    elseif($phase == 'Self Evaluated'){
        return 'Return';
    }
    elseif($phase == 'Finalized'){
        return 'Cancel Finalizing';
    }   
 }
// *******************************************************//
// ****** admin.passwordReset.php, passwordChange.php*****//
// *******************************************************//
function setPwdChange($conn, $email, $newPassword){
    try{
        $sql = "UPDATE users SET usersPwd = ? WHERE usersEmail = ?;";

        $stmt = pdoPrepare($conn, $sql);

        // Make a SQL statement
        pdoBind($stmt, 1, $newPassword, PDO::PARAM_STR);
        pdoBind($stmt, 2, $email, PDO::PARAM_STR);      

        // Send the SQL statement
        return pdoExecute($stmt);
     }
    catch(exeption $e){
        return false;
     }
 }

// passwordChange.php
function getUsersPassword($conn, $usersEmail){
    $sql = "SELECT usersPwd FROM users WHERE usersEmail = ?;";

    $stmt = pdoPrepare($conn, $sql);

    // Make a SQL statement
    pdoBind($stmt, 1, $usersEmail, PDO::PARAM_STR);      

    // Send the SQL statement'
    $results = pdoExecute($stmt);
    $row = pdoFetch($stmt);
    
    if(!empty($row)){
        return $row['usersPwd'];
    }
    else{
        return false;
    }
 }
//*******************************************************//
//*************** admin.registerEmployee.php *****************//
//*******************************************************//
function checkEmailDuplicate($conn, $usersemail){

        $sql = "SELECT usersName FROM users WHERE usersEmail = ?;";

        $stmt = pdoPrepare($conn, $sql);

        // Make a SQL statement
        pdoBind($stmt, 1, $usersEmail, PDO::PARAM_STR);   

        // Send the SQL statement
        $results = pdoExecute($stmt);
        $row = pdoFetch($stmt);

        if(empty($row)){
            return true;
        }
        else{
            return false;
        }
 }

function addNewEmployee($conn, $employeedata){
    try{
        $sql = "INSERT INTO users (usersName, active, usersEmail, usersPwd, usersPosition, usersBoss, bossEmail) VALUES (?, 'active', ?, ?, ?, ?, ?);";

        $stmt = pdoPrepare($conn, $sql);

        $newPassword =  password_hash($employeedata['employeePassword'], PASSWORD_BCRYPT);

        pdoBind($stmt, 1, $employeedata['employeeName'], PDO::PARAM_STR);
        pdoBind($stmt, 2, $employeedata['employeeEmail'], PDO::PARAM_STR);
        pdoBind($stmt, 3, $newPassword, PDO::PARAM_STR);
        pdoBind($stmt, 4, $employeedata['position'], PDO::PARAM_STR);
        pdoBind($stmt, 5, $employeedata['employeeBossName'], PDO::PARAM_STR);
        pdoBind($stmt, 6, $employeedata['employeeBossEmail'], PDO::PARAM_STR);   

        // Send the SQL statement
        return pdoExecute($stmt);
    }
    catch(exception $e){
        header("location: ../includes/admin.registerEmployee.phpp?error=stmtfailed");
        return false;
    }
 }

//********************************************************//
//*************** admin.reviseEmployee.php ***************//
//********************************************************//
function getOneEmployeeInfo($conn, $usersemail){

    $sql = "SELECT * FROM users WHERE usersEmail = ?;";

    $stmt = pdoPrepare($conn, $sql);

    // Make a SQL statement
    pdoBind($stmt, 1, $usersemail, PDO::PARAM_STR);

    // Send the SQL statement
    $results = pdoExecute($stmt);
    $row = pdoFetch($stmt);

    if(empty($row)){
        return false;
    }
    else{
        return $row;
    }
 }

// Update Employee's Info of `users` table
function updateEmployeInfo($conn, $employeeInfo){
 try{
    $sql = "UPDATE users SET usersName = ?, usersEmail = ?, usersPosition = ?, usersBoss = ?, bossEmail = ? WHERE usersEmail = ?;";

    $stmt = pdoPrepare($conn, $sql);
    
    //if(!isset($_SESSION)){
    //    session_start();
    //}

    pdoBind($stmt, 1, $employeeInfo['employeeName'], PDO::PARAM_STR);
    pdoBind($stmt, 2, $employeeInfo['employeeEmail'], PDO::PARAM_STR);
    pdoBind($stmt, 3, $employeeInfo['position'], PDO::PARAM_STR);
    pdoBind($stmt, 4, $employeeInfo['employeeBossName'], PDO::PARAM_STR);
    pdoBind($stmt, 5, $employeeInfo['employeeBossEmail'], PDO::PARAM_STR);
    pdoBind($stmt, 6, $_SESSION['originalUsersEmail'], PDO::PARAM_STR);

    // Send the SQL statement
    return pdoExecute($stmt);
 }
 catch(exception $e){
    return false;
 }
 }
//********************************************************//
//***************** admin.addVCspace.php *****************//
//********************************************************//
// Check if the table exists
function checkVCTableExists($conn, $year){
    $sql = "SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[$year]') AND type in (N'U')";

    //Prepare the SQL query pointed to by the null-terminated string query.
    $stmt = pdoPrepare($conn, $sql);
    
    // Send the SQL statement
    $results = pdoExecute($stmt);

    $exist = pdoFetch($stmt);

    if(!$exist){
        return false;
    }
    else{
        return true;
    }
 }

//********************************************************//
//******************* createVspace.php *******************//
//********************************************************//
// Create a new table
function createNewVcTable($conn, $year){

    $columnName[0] = 'usersName';
    $columnName[1] = 'usersEmail';
    $columnName[2] = 'quarter';
    $columnName[3] = 'vc';
    $columnName[4] = 'phase';
    $columnName[5] = 'OneOnOne';
    for($i = 0; $i <= 4; $i ++){
        $columnName[6 + 8 * $i]  = 'vc23_'.($i + 1);
        $columnName[7 + 8 * $i]  = 'annualTarget_'.($i + 1);
        $columnName[8 + 8 * $i]  = 'weight_'.($i + 1);
        $columnName[9 + 8 * $i]  = 'quarterPlan_'.($i + 1);
        $columnName[10 + 8 * $i] = 'quarterResult_'.($i + 1);
        $columnName[11 + 8 * $i] = 'selfEval_'.($i + 1);
        $columnName[12 + 8 * $i] = 'finalEval_'.($i + 1);
        $columnName[13 + 8 * $i] = 'Performance_'.($i + 1);
     }

    $Datatype[0] = 'varchar(50)';
    $Datatype[1] = 'varchar(50)';
    $Datatype[2] = "int"; // quarter
    $Datatype[3] = "int"; // phase
    $Datatype[4] = "varchar(14) NOT NULL DEFAULT 'Unsubmitted'";
    $Datatype[5] = 'varchar(10)';
    for($i = 0; $i <= 4; $i ++){
        $Datatype[6 + 8 * $i]  = 'varchar(100)';
        $Datatype[7 + 8 * $i]  = 'varchar(100)';
        $Datatype[8 + 8 * $i]  = 'int';
        $Datatype[9 + 8 * $i]  = 'varchar(300)';
        $Datatype[10 + 8 * $i] = 'varchar(1000)';
        $Datatype[11 + 8 * $i] = "varchar(1)";
        $Datatype[12 + 8 * $i] = "varchar(1)";
        $Datatype[13 + 8 * $i] = 'varchar(1000)';
     }
    
    $sql = "CREATE TABLE [dbo].[$year] (";

    for($i = 0; $i < count($columnName); $i++){
        if($i == 0)
        {
            $sql = $sql.$columnName[$i].' '.$Datatype[$i];
         }
        else{
            $sql = $sql.', '.$columnName[$i].' '.$Datatype[$i];
         }
    }
    $sql = $sql.');';
 
 try{
        $stmt = pdoPrepare($conn, $sql);

        // Send the SQL statement
        return pdoExecute($stmt);
    }
 catch(exception $e){
        return false;
    }
 }

function getQuarterExists($conn, $quarter, $year, $usersEmail, $vc){
    $sql = "SELECT TOP 1 1 FROM [dbo].[$year] WHERE quarter = ? AND usersEmail = ? AND vc = ?;";

    $stmt = pdoPrepare($conn, $sql);
    
    // Make a SQL statement
    pdoBind($stmt, 1, $quarter, PDO::PARAM_INT);
    pdoBind($stmt, 2, $usersEmail, PDO::PARAM_STR);
    pdoBind($stmt, 3, $vc, PDO::PARAM_INT);

    // Send the SQL statement
    $results = pdoExecute($stmt);

    $row = pdoFetch($stmt);

    if(!empty($row)){
        return true;
    }
    else{
        return false;
    }
 }

function createNewRow($conn, $quarter, $year, $val){
    $sql = "INSERT INTO [dbo].[$year] (usersName, usersEmail, vc, quarter, phase) VALUES (?, ?, ?, ?, 'Unsubmitted');";

    $stmt = pdoPrepare($conn, $sql);
    
    if($val['usersPosition'] == 'staff'){
        $vc = '4';
    }
    elseif($val['usersPosition'] == 'manager'){
        $vc = '3';
    }
    elseif($val['usersPosition'] == 'director'){
        $vc = '2';
    }
    elseif($val['usersPosition'] == 'executive'){
        $vc = '1';
    }
    elseif($val['usersPosition'] == 'administrator'){
        $vc = '';
    }
    // Make a SQL statement
    pdoBind($stmt, 1, $val['usersName'], PDO::PARAM_STR);
    pdoBind($stmt, 2, $val['usersEmail'], PDO::PARAM_STR);
    pdoBind($stmt, 3, $vc, PDO::PARAM_INT);
    pdoBind($stmt, 4, $quarter, PDO::PARAM_INT);

    // Send the SQL statement
    $results = pdoExecute($stmt);

 }

function deleteOneEmployee($conn, $usersEmail){
    try{
        $sql = "DELETE FROM users WHERE usersEmail = ?;";

        $stmt = pdoPrepare($conn, $sql);
        
        // Make a SQL statement
        pdoBind($stmt, 1, $usersEmail, PDO::PARAM_STR);

        // Send the SQL statement
        return pdoExecute($stmt);
    }
    catch(exception $e){
        return false;
    }
 }


function pdoPrepare($conn, $sql){
    return $conn->prepare($sql);
 }
function pdoBind($stmt, $num, $param, $datatype){
    return $stmt->bindValue($num,$param, $datatype);
 }
function pdoExecute($stmt){
    return $stmt->execute();
 }
function pdoFetch($stmt){
    return $stmt->fetch(PDO::FETCH_ASSOC);
 }