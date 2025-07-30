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

if(!isset($_POST["LeaveType"]) || !isset($_POST["GradeAllocation"])){
    die("Error: Improper Form submission");
}

$_LeaveType = $_POST["LeaveType"];
$_GradeAllocation = json_decode($_POST["GradeAllocation"], true);
$_Status = false;

foreach($_GradeAllocation["GradeAllocation"] as $GA){
    $_Status = GradeAllocation::TryCatch(
        GradeAllocation::setAllocation(...), 
        $mysqli, $GA["Grade"], $_LeaveType, $GA["Allocation"]);
}

if(!$_Status){
    die("Unable to append grade allocations for " . $_LeaveType);
}

die("Success");

?>