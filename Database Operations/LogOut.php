<?php require "DBModel.php"; ?>
<?php
if (!isset($_SESSION)){
    die(json_encode(array(
        "ResponseType" => "Error",
        "Error" => "Session not initialized!"
    )));
}

if(!isset($_SESSION["Token"])){
    die(json_encode(array(
        "ResponseType" => "Error",
        "Error" => "Improper Form, Token not provided!"
    )));
}

$Token = $_SESSION["Token"];
$Acc = Account::TryCatch(Account::getAccount_usingToken(...), $mysqli, $Token);
if(!$Acc){
    die(json_encode(array(
        "ResponseType" => "Error",
        "Error" => "Invalid Token!"
    )));
}

if(!Account::TryCatch($Acc->setToken(...), "NULL")){
    die(json_encode(array(
        "ResponseType" => "Error",
        "Error" => "Unable to Log Out, cannot set NULL to Token"
    )));
}

session_unset();
session_destroy();

die(json_encode(array(
    "ResponseType" => "Location",
    "Location" => "Login.php"
)));
      
?>