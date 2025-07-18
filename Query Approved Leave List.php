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
try {

    $employeeallocations = Employee_Allocation::queryAllEmployeeAllocations($mysqli);
    if(!$employeeallocations){
        die(json_encode(array(
            "Status" => "Unable to query all employee leave allocations" 
        )));
    }
    /*
    die(json_encode(array(
        "Status" => "Success",
        "Grade" => $employeeallocations[Employee_Allocation_Columns::EmployeeGrade->value],
        "Employee Name" => $employeeallocations[Employee_Allocation_Columns::EmployeeName->value],
        "Leave Type" => $employeeallocations[Employee_Allocation_Columns::LeaveCategory->value],
        "From" => $employeeallocations[Employee_Allocation_Columns::StartDate->value],
        "To" => $employeeallocations[Employee_Allocation_Columns::EndDate->value],
        "Allocations" => $employeeallocations[Employee_Allocation_Columns::Allocations->value],
        "UsedAllocations" => $employeeallocations[Employee_Allocation_Columns::UsedAllocations->value]
    )));
    */
    #die(json_encode(array("Rows" => $employeeallocations)));
    die(json_encode(array("Rows" => iterator_to_array($employeeallocations))));
    
} catch(mysqli_sql_exception $err){
    die($err->getMessage());
} catch (\Throwable $th) {
    die($th);
}
?>