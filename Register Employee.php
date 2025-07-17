<?php 
require "DBModel.php";
use Random\Randomizer; //php 8.2+
?>
<?php
// Start the session
session_start();
?>
<?php
$Username = "root";
$Database = "LeaveManagement";
$Host = "localhost";
$Password = ""; 

$mysqli = mysqli_connect($Host, $Username, $Password, $Database);

//Check connection status
if (mysqli_connect_errno()){
    die("Error connecting to DB, Error: " . mysqli_connect_error());
}

//Check if session exist first before checking session variables
if (isset($_SESSION) === false and isset($_POST) === false){
    die("Error: Session or POST is not set!");
}

if(!isset($_POST["Username"]) || !isset($_POST["Password"]) || !isset($_POST["Grade"])){
    die("Error: Improper Form submission");
}

$random = new Randomizer();
$Username = $_POST["Username"];
$Password = $_POST["Password"];
$Grade = $_POST["Grade"];

#echo ($Username . " " . $Password . " " . $Grade); //POST DATA WORKS

#$length = 50;
#$Token = substr(bin2hex(random_bytes($length / 2)), 0, $length);





$Privilege = Privilege::Normal;

if($Grade === Privilege::Manager->value){
    $Privilege = Privilege::Manager;
}

try {
    $Acc = Account::createAccount($mysqli, $Username, $Password, "", $Privilege);
    
    if($Acc === null){
        die("Failure: Account registration");
    }
    
    $EmployeeID = implode("", $random->shuffleArray(str_split("abcdefghijklmnopqrstuvwxyz0123456789")));
    $Emp = Employee::createEmployee($mysqli, $EmployeeID, $Username, $Grade); //All good

    if($Emp === null){
        die("Failure: Employee registration");
    }

    if (!$Acc->setEmployee($Emp)){
        die("Fail to attach Employee to Account");
    }

}catch(mysqli_sql_exception $err){
    die($err->getMessage());
}catch (\Throwable $th) {
    die($th);
}

die("Success: Account and Employee registration");
?>
