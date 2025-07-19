<?php 
require "DBModel.php";
require "NavbarLinks.php";
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
$Acc = null;
//Check if session exist first before checking session variables
if (isset($_SESSION)){
    if (isset($_SESSION["Token"])){
        //Search if logged in as valid employee or manager
        $Token = $_SESSION["Token"];
        
        try {
            $Acc = Account::searchAccount_Token($mysqli, $Token);
            if (!$Acc){
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
    
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Type</title>
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
            height: fit-content;
            max-height: 50dvh;
            background-color: #f2f2f2;

            /*Grid Configuration*/
            place-self: center center;
            row-gap: 4dvh;

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
            background-color: #04aa6d;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 25px 25px 25px 25px;

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

        #GradeRowContainer{
            display: grid;
            width: fit-content;
            height: fit-content;
            max-height: 100%;

            /*Grid Configuration*/
            grid-auto-flow: row;
            grid-auto-rows: min-content;
            row-gap: 2dvh;

            /*Scroll*/
            overflow-y: auto;
            scrollbar-width: thin;

            /*Padding*/
            padding-right: 0.5dvw;
        }

        #LeaveType{
            height: 7.4074dvh;
            width: 50dvw;

            /*Grid Configuration*/
            place-self: center center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Border*/
            border: 1pt solid #04aa6d;
        }

        .FieldRow{
            display: grid;
            height: min-content;
            width: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
        }

        .FieldLabel{
            display: grid;
            height: 7.4074dvh;
            width: 14.0625dvw;
            background-color: #04aa6d;

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: 1pt solid #04aa6d;
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
        }

        .TextInputField{
            display: grid;
            height: 7.4074dvh;
            width: 35dvw;
            
            /*Border*/
            border: 1pt solid #04aa6d;
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: black;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
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
            width: inherit;
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
            width: inherit;
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
            display: grid; /*Border*/
            border: var(--BorderWhite);
            height: 10dvh;
            width: 11dvw;
            background-color: #04aa6d;

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
            border-color: #04aa6d;
            border-radius: 20px 20px 20px 20px;

            /*Cursor*/
            cursor: pointer;
        }

        #Save:hover{
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <nav id="Navbar">
        <div id="LinkContainer">
            <?php echo $NavbarLinks_Normal; ?>
        </div>
        <button id="LogOut">Log Out</button>
    </nav>
    <form action="">
        <fieldset>
            <legend>Employee Leave Request Form</legend>
            <div id="GradeRowContainer">
                <select name="LeaveType" id="LeaveType">
                    <?php
                    $Types = LeaveType::queryLeaveType($mysqli);
                    foreach($Types as $lt){
                    ?>
                     <option value="<?php echo $lt ?>"><?php echo $lt ?></option>
                    <?php
                    }
                    ?>
                   
                </select>
                <div class="FieldRow">
                    <label for="From" class="FieldLabel">From</label>
                    <input type = "date" name="From" id="From"  class="TextInputField">
                </div>
                <div class="FieldRow">
                    <label for="To" class="FieldLabel">To</label>
                    <input type="date" name="To" id="To" class="TextInputField">
                </div>
            </div>
            <span id="Error" data-status="0">
                Test
            </span>
            <input type="submit" value="Save" id="Save">
        </fieldset>
        
    </form>
    <script>
            /**
             * Logout
             */
            let LogOut = document.getElementById("LogOut");
            LogOut.addEventListener("click", async (ev)=>{
                const url = "/ITWB4134_SAD_Group3_Assignment2/LogOut.php";
                let formData = new FormData();
                const response = await fetch(url, {
                    method: "POST",
                    body: formData
                });
                const jsonObject = await response.json();
                if(jsonObject.ResponseType === "Location"){
                    location.replace(jsonObject.Location);
                }else if(jsonObject.ResponseType === "Error"){
                    console.log("LogOut: " + jsonObject.Error);
                }

            });

            /***
             * Form
             */
            let form = document.querySelector("form");
            let LeaveType = document.getElementById("LeaveType");
            let From = document.getElementById("From");
            let To = document.getElementById("To");
            let _Error = document.getElementById("Error");
            let Save = document.getElementById("Save");

            form.addEventListener("submit",(ev)=>ev.preventDefault());
            Save.addEventListener("click", sendLeaveApplication);

            /**
             * Functions
             */
            async function sendLeaveApplication(ev){
                const url = "/ITWB4134_SAD_Group3_Assignment2/Submit Leave Application.php";
                let formData = new FormData();
                formData.append("LeaveType", LeaveType.value);
                formData.append("From", From.value);
                formData.append("To", To.value);
                formData.append("EmpID", "<?php echo $Acc->getEmployee()->getEmployeeID(); ?>");
                const response = await fetch (url,{
                    method: "POST",
                    body:formData
                });
                const textObject = await response.text();
                _Error.innerText = textObject;
                if (textObject !== "Success"){
                    _Error.dataset.status = "2";
                }else{
                    _Error.dataset.status = "1";
                }
                form.reset();
            }
    </script>
</body>
</html>
    <?php

} catch(mysqli_sql_exception $err){
    die($err->getMessage());
} catch (\Throwable $th) {
    die($th);
}
?>