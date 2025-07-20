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

if (!isset($_SESSION["Token"])){
    die("Error: No Token");
}

try {
    //Search if logged in as valid manager
    $Token = $_SESSION["Token"];
    $Acc = Account::searchAccount_Token($mysqli, $Token);

    if(!$Acc){
        die("Error: Not Logged In");
    }

    if($Acc->getPrivilege() !== Privilege::Manager){
        die("Error: Incorrect Permissions!");
    }
    
    if(!isset($_POST["Grade"])){
        die("Error: Improper Form submission");
    }
    
    $NewGrade = $_POST["Grade"];
    $status = Grade::addGrade($mysqli, $NewGrade);

    die (($status === true) ? "Success" : "Failed to add Grade");

} catch(mysqli_sql_exception $err){
    die($err->getMessage());
} catch (\Throwable $th) {
    die($th);
}

?>