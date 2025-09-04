<?php require "DBModel.php"; ?>
<?php
/** 
 * Check if this file is accessed through GET or POST
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION)){
    die("Error: Session or POST is not set!");
}

if (!isset($_SESSION["Token"])){
    die("Error: No Token");
}

$Token = $_SESSION["Token"];
$Acc = Account::TryCatch(Account::getAccount_usingToken(...), $mysqli, $Token);

if(!$Acc){
    die("Error: Not Logged In");
}

if(!isset($_POST["LeaveType"]) or !isset($_POST["From"]) or !isset($_POST["To"]) or !isset($_POST["EmpID"])){
    die("Error: Improper Form submission");
}

$SentLeaveType = $_POST["LeaveType"];
$From = $_POST["From"];
$To = $_POST["To"];
$EmpID = $_POST["EmpID"];

$Emp = Employee::TryCatch(Employee::getEmployee_withEmployeeID(...), $mysqli, $Acc->getEmployeeID());
if(!$Emp){
    die("Invalid Employee!");
}

if(GradeAllocation::TryCatch(GradeAllocation::getAllocation(...), $mysqli, $Emp->getEmployeeGrade(), $SentLeaveType) === 0){
    die("Error! No allocation for $SentLeaveType @ ".$Emp->getEmployeeGrade());
}

$status = LeaveApplications::TryCatch(
    LeaveApplications::createEmployeeLeaveApplication(...), 
    $mysqli, $SentLeaveType, $From, $To, $EmpID);
die (($status === true) ? "Success" : "Failed to Submit Leave Application");
?>