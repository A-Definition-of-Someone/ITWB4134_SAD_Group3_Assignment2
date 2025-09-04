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

if(!isset($_POST["LeaveType"])){
    die("Error: Improper Form submission");
}

$NewLeaveType = $_POST["LeaveType"];
$status = LeaveCategories::TryCatch(LeaveCategories::addLeaveType(...), $mysqli, $NewLeaveType, null);

die (($status === true) ? "Success" : "Failed to add Leave Type");
?>