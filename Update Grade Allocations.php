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
                header("Location: /ITWB4134_SAD_Group3_Assignment2/");
                exit;
            }
            if($Acc->getPrivilege() !== Privilege::Manager){
                header("Location: /ITWB4134_SAD_Group3_Assignment2/");
                exit;
            }
        }catch(mysqli_sql_exception $err){
            die($err->getMessage());
        }
         catch (\Throwable $th) {
            die($th);
        }
    }else{
        header("Location: /ITWB4134_SAD_Group3_Assignment2/");
        exit;
    }
}else{
    header("Location: /ITWB4134_SAD_Group3_Assignment2/");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Not using POST!");
}

if(!isset($_POST["LeaveType"]) or !isset($_POST["GradeAllocation"])){
    header("Location: /ITWB4134_SAD_Group3_Assignment2/");
    exit;
}

$_LeaveType = $_POST["LeaveType"];

$_GradeAllocation = json_decode($_POST["GradeAllocation"], true);
$_Status = false;
try {
    foreach($_GradeAllocation["GradeAllocation"] as $GA){
        $_Status = Grade_Allocation::setAllocation($mysqli, $GA["Grade"], $_LeaveType, $GA["Allocation"]);
    }

    if(!$_Status){
        die("Unable to append grade allocations for " . $_LeaveType);
    }

    die("Success");
} catch(mysqli_sql_exception $err){
    die($err->getMessage());
} catch (\Throwable $th) {
    die($th);
}

/**
 * $json = '{"Amogus":[{"VAL1":"LOL", "VAL2":"LEL"}, {"VAL3":"WHAT", "VAL4":"POG"}]}';
 * print_r(json_decode($json, true));
 */
/**
 * Array
 * (
 * [Amogus] => Array
 * (
 * [0] => Array
 * (
 * [VAL1] => LOL
 * [VAL2] => LEL
 * )
 * 
 * [1] => Array
 * (
 * [VAL3] => WHAT
 * [VAL4] => POG)
 * )
 * )

 */
?>