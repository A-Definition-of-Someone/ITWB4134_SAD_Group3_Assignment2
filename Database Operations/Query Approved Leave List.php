<?php require "DBModel.php"; ?>
<?php
/** 
 * Check if this file is accessed through GET or POST
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION)){
    die(json_encode(array(
        "Status" => "Error: Session or POST is not set!"
    )));
}

if (!isset($_SESSION["Token"])){
    die(json_encode(array(
        "Status" => "Error: No Token" 
    )));
}

$Token = $_SESSION["Token"];
$Acc = Account::TryCatch(Account::getAccount_usingToken(...), $mysqli, $Token);

if(!$Acc){
    die(json_encode(array(
        "Status" => "Error: Not Logged In" 
    )));
}

$employeeallocations = EmployeeAllocation::TryCatch(EmployeeAllocation::queryAllEmployeeAllocations(...), $mysqli);
if(!$employeeallocations){
    die(json_encode(array(
        "Status" => "Unable to query all employee leave allocations" 
    )));
}

die(json_encode(array("Rows" => iterator_to_array($employeeallocations))));
?>