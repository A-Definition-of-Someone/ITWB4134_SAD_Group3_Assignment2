<?php require "DBModel.php"; ?>
<?php
/** 
 * Check if this file is accessed through GET or POST
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION)){
    die("Error: Session or POST is not set!");
}

if(!isset($_POST["Username"]) || !isset($_POST["Password"]) || !isset($_POST["Grade"]) || !isset($_POST["Privilege"])){
    die("Error: Improper Form submission");
}

$Username = $_POST["Username"];
$Password = $_POST["Password"];
$Grade = $_POST["Grade"];
$Privilege = $_POST["Privilege"];

$Emp = Employee::TryCatch(Employee::createEmployee(...), $mysqli, $Username, $Grade);
$Acc = Account::TryCatch(Account::createAccount(...), $mysqli, $Username, $Password, $Privilege, $Emp->getEmployeeID());

if($Emp === null || $Acc === null){
    die("Failure: Employee registration");
}

die("Success: Account and Employee registration");
?>