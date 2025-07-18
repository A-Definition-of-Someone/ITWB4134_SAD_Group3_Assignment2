<?php 
require "DBModel.php";
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
mysqli_set_charset($mysqli, "utf8mb4");

//Check connection status
if (mysqli_connect_errno()){
    die("Error connecting to DB, Error: " . mysqli_connect_error());
}

//Check if session exist first before checking session variables
if (isset($_SESSION)){
    if (isset($_SESSION["Token"])){
        //Search if logged in as valid employee or manager
        $Token = $_SESSION["Token"];
        $Acc = Account::searchAccount_Token($mysqli, $Token);
        try {
            if (!$Acc){
                die("Error! You need to be logged in");
            }
            if($Acc->getPrivilege() !== Privilege::Manager){
                die("Error! Incorrect Permissions");
            }
        }catch(mysqli_sql_exception $err){
            die($err->getMessage());
        }
         catch (\Throwable $th) {
            die($th);
        }
    }else{
        die("Error! You need to be logged in");
    }
}else{
    die("Session not initialized!");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Not using POST!");
}

if(!isset($_POST["appleaveid"]) or !isset($_POST["LeaveStatus"]) or !isset($_POST["empID"]) or !isset($_POST["leaveCategory"])){
    die("Improper form provided!");
}

try {
    $appleaveid = $_POST["appleaveid"];
    $leavestatus = $_POST["LeaveStatus"];
    $empID = $_POST["empID"];
    $leaveCategory  = $_POST["leaveCategory"];

    if(!Leave_Application::setStatusEmployeeLeaveApplication($mysqli, $appleaveid, LeaveStatus::tryFrom($leavestatus))){
        die("Unable to update leave application status!");
    }

    if(LeaveStatus::Approved->value === $leavestatus)
    if(!Employee_Allocation::incrementUsedAllocations($mysqli, $empID, $leaveCategory)){
        die("Unable to update increment used allocationn for " . $empID . " in " . $leaveCategory);
    }

    die("Success");
} catch(mysqli_sql_exception $err){
    die($err->getMessage());
} catch (\Throwable $th) {
    die($th);
}
?>