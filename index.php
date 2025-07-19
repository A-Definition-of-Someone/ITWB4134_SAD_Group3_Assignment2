<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
try {
$mysqli = mysqli_connect($Host, $Username, $Password, $Database);
mysqli_set_charset($mysqli, "utf8mb4");

//Check connection status
if (mysqli_connect_errno()){
    die("Error connecting to DB, Error: " . mysqli_connect_error());
}

//Check if session exist first before checking session variables
$login_state = 0;
$login_status = "";

if (isset($_SESSION)){
    if (isset($_SESSION["Token"])){
        //Search if logged in as valid employee or manager
        $Token = $_SESSION["Token"];
        try {
            $Acc = Account::searchAccount_Token($mysqli, $Token);
            if ($Acc){
                if($Acc->getPrivilege() === Privilege::Manager){
                    header("Location: /ITWB4134_SAD_Group3_Assignment2/Manage Leave Type.php"); #Manager / HR first landing page
                    exit;
                }
                header("Location: /ITWB4134_SAD_Group3_Assignment2/Employee Leave Request.php");
                exit;
            }
        }catch(mysqli_sql_exception $err){
            die($err->getMessage());
        }
         catch (\Throwable $th) {
            die($th);
        }
          
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $login_status = "Submission Success!";
    $login_state = 1;
    if (isset($_POST["Username"]) and isset($_POST["Password"])){
        try {
            $Username = $_POST["Username"];
            $Password = $_POST["Password"];
            $Acc = Account::searchAccount_UsernamePassword($mysqli, $Username, $Password);
            if ($Acc){
                $random = new Randomizer();
                $Token = implode("", $random->shuffleArray(mb_str_split("abcdefghijklmnopqrstuvwxyz0123456789🌑🌕", 1, "UTF-8")));
                $Acc->setToken($Token);
                $_SESSION["Token"] = $Token;
                if($Acc->getPrivilege() === Privilege::Manager){
                    header("Location: /ITWB4134_SAD_Group3_Assignment2/Manage Leave Type.php"); #Manager / HR first landing page
                    exit;
                }
                header("Location: /ITWB4134_SAD_Group3_Assignment2/Employee Leave Request.php");
                exit;
            }else{
                $login_state = 2;
                $login_status = "Invalid Credentials";
            }
        }catch(mysqli_sql_exception $err){
            die("SQL: " . $err->getMessage());
        }
         catch (\Throwable $th) {
            die("Other error: " . $th);
        }
    }else{
        $login_state = 2;
        $login_status = "Improper Form Submission";
    }
}
} catch(mysqli_sql_exception $err){
    die($err->getMessage());
} catch (\Throwable $th) {
    die($th);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Employee Page</title>
    <link rel="stylesheet" href="CommonVariables.css">
    <link rel="stylesheet" href="Menu.css">
    <style>
        form{
            display: grid;
            height: 90.7408dvh;
            width: inherit;
        }
        form fieldset{
            display: grid;
            background-color: var(--LightGray);

            /*Grid Configuration*/
            grid-auto-rows: min-content;
            place-self: center center;
            row-gap: 1.5dvh;

            /*Border*/
            border: var(--BorderLighterLightGray);
            border-radius: 25px 25px 25px 25px;

            /*Padding*/
            padding-top: 3dvh;
            padding-bottom: 2dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
        }
        fieldset legend{
            background-color: black;

            /*Border*/
            border: var(--BorderWhite);

            /*Text*/
            color: white;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Padding*/
            padding-top: 1dvh;
            padding-bottom: 1dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
        }


        .AccountFieldRow{
            display: grid;
            height: min-content;
            width: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
        }

        .AccountFieldLabel{
            display: grid;
            height: 7.4074dvh;
            width: 14.0625dvw;
            background-color: black;

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderWhite);

            /*Text*/
            color: white;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
        }

        .AccountTextInputField{
            display: grid;
            height: 7.4074dvh;
            width: 42.1875dvw;
            background-color: black;

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
        }

        select{
            height: 7.4074dvh;
            width: 57.8dvw;
            background-color: black;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: left;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Padding*/
            padding-left: 0.5dvw;
            padding-right: 0.5dvw;
        }
        #Error[data-status="0"]{
            display: none;
            visibility: hidden;
            width: 0;
            height: 0;
        }

        #Error[data-status="1"]{
            display: grid;
            height: 7.4074dvh;
            width: 57.8dvw;
            background-color: var(--LightGreen);

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderDarkGreen);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: left;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Padding*/
            padding-left: 0.5dvw;
            padding-right: 0.5dvw;
        }

        #Error[data-status="2"]{
            display: grid;
            height: 7.4074dvh;
            width: 57.8dvw;
            background-color: var(--LighterOrange);

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderOrange);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: left;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Padding*/
            padding-left: 0.5dvw;
            padding-right: 0.5dvw;
        }

        #Save{
            display: grid;
            height: 10.1852dvh;
            width: 11.5885dvw;
            background-color: var(--DarkerBlue);

            /*Grid Configuration*/
            place-content: center center;
            place-self: center center;

            /*Text*/
            color: white;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
            font-weight: bold;

            /*Border*/
            border-color: #5C79A3;
            border-radius: 20px 20px 20px 20px;
        }
    </style>
</head>
<body>
    <form method="post">
        <fieldset>
            <legend>Employee Login</legend>
            <div class="AccountFieldRow">
                <label for="Username" class="AccountFieldLabel">Username</label>
                <input type="text" name="Username" id="Username" placeholder="Username" class="AccountTextInputField">
            </div>
            <div class="AccountFieldRow">
                <label for="Password" class="AccountFieldLabel">Password</label>
                <input type="password" name="Password" id="Password" placeholder="Password" class="AccountTextInputField">
            </div>
            <span id="Error" data-status="<?php echo $login_state; ?>">
                <?php echo $login_status; ?>
            </span>
            <input type="submit" value="Login" id="Save">
        </fieldset>
    </form>
</body>
</html>