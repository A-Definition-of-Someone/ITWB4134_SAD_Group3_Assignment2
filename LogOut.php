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
                die(json_encode(array(
                    "ResponseType" => "Error",
                    "Error" => "Invalid Token"
                )));
            }

            //Remove Token
            $Acc->setToken("NULL");
            
            session_unset();
            session_destroy();
            echo(json_encode(array(
                "ResponseType" => "Location",
                "Location" => "/ITWB4134_SAD_Group3_Assignment2/"
            )));
            exit;
        }catch(mysqli_sql_exception $err){
            die(json_encode(array(
                "ResponseType" => "Error",
                "Error" => $err->getMessage()
            )));
        }
         catch (\Throwable $th) {
            die(json_encode(array(
                "ResponseType" => "Error",
                "Error" => $th
            )));
        }
    }else{
        die(json_encode(array(
            "ResponseType" => "Error",
            "Error" => "Improper Form"
        )));
    }
}else{
    die(json_encode(array(
        "ResponseType" => "Error",
        "Error" => "Session not initialized"
    )));
}
?>