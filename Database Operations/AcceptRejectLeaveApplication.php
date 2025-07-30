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

if($Acc->getPrivilege() !== Privilege::Manager){
    die("Error: Incorrect Permissions!");
}

if(!isset($_POST["appleaveid"]) or !isset($_POST["LeaveStatus"]) or !isset($_POST["empID"]) or !isset($_POST["leaveCategory"])){
    die("Improper form provided!");
}

$appleaveid = $_POST["appleaveid"];
$leavestatus = $_POST["LeaveStatus"];
$empID = $_POST["empID"];
$leaveCategory  = $_POST["leaveCategory"];

if(!LeaveApplications::TryCatch(LeaveApplications::setStatusEmployeeLeaveApplication(...), $mysqli, $appleaveid, $leavestatus)){
    die("Unable to update leave application status!");
}

if(!EmployeeAllocation::TryCatch(EmployeeAllocation::incrementUsedAllocations(...), $mysqli, $empID, $leaveCategory)){
    die("Unable to update increment used allocation for " . $empID . " in " . $leaveCategory);
}

die("Success");
?>