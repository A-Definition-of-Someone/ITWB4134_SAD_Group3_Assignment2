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

if(!isset($_GET["LeaveType"])){
    header("Location: /ITWB4134_SAD_Group3_Assignment2/");
    exit;
}

$_LeaveType = $_GET["LeaveType"];
try {
    #Check if this leavetype exist or not
    $leavetype_isExist = LeaveType::isExistLeaveType($mysqli, $_LeaveType);

    if(!$leavetype_isExist){
        die("Error: No " . $_LeaveType . " found in database!");
    }

    $grade_allocation = Grade_Allocation::queryGradeAllocation($mysqli, $_LeaveType);

    if(!$grade_allocation){ #Initialize it
        if(!Grade_Allocation::initGradeAllocation($mysqli, $_LeaveType)){
            die("Error: Unable to initialize Grade_Allocation for leave type: " . $_LeaveType);
        }

        $grade_allocation = Grade_Allocation::queryGradeAllocation($mysqli, $_LeaveType);
    }else{ #Keep up to date with grade list
        if(!Grade_Allocation::addGradeAllocation($mysqli, $_LeaveType)){
            die("Error: Unable to add Grade_Allocation for leave type: " . $_LeaveType);
        }
        $grade_allocation = Grade_Allocation::queryGradeAllocation($mysqli, $_LeaveType);
    }
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
            background-color: var(--LightGray);

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

        .GradeRow{
            display: grid;
            height: min-content;
            width: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
        }

        .GradeName{
            display: grid;
            height: 7.4074dvh;
            width: 40.1042dvw;
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

        .GradeAllocation{
            display: grid;
            height: 7.4074dvh;
            width: 9.0365dvw;
            background-color: white;

            /*Grid Config*/
            place-content: center center;

            /*Text*/
            color: black;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header40Approximate);
            font-weight: bold;

            /*Border*/
            border: var(--BorderWhite);
            box-sizing: border-box;
        }

        .GradeAllocation::-webkit-inner-spin-button,
        .GradeAllocation::-webkit-outer-spin-button{
            opacity: 1;
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
    <nav id="Navbar">
        <div id="LinkContainer">
            <a href="/ITWB4134_SAD_Group3_Assignment2/Register%20Employee%20Page.php">Register Employee</a>
            <a href="http://">Manage Grade</a>
            <a href="/ITWB4134_SAD_Group3_Assignment2/Manage%20Leave%20Type.php">Manage Leave Type</a>
            <a href="">Manage Employee Leave</a>
        </div>
        <button id="LogOut">Log Out</button>
    </nav>
    <form action="">
        <fieldset>
            <legend>Edit <?php echo $_LeaveType; ?> Allocation</legend>
            <div id="GradeRowContainer">
                <?php
                foreach($grade_allocation as $ga){
                    ?>
                    <div class="GradeRow">
                        <span class="GradeName"><?php echo $ga["EmployeeGrade"]; ?></span>
                        <input type="number" name="" id="" class="GradeAllocation" value="<?php echo $ga["Allocations"]; ?>" min="0" step="1">
                    </div>
                    <?php
                }
                ?>
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
            form.addEventListener("submit",(ev)=>ev.preventDefault());

            /**
             * Ammend Allocations for grades
             */
            let GradeRows = Array.from(document.querySelectorAll(".GradeRow") || []);
            let Save = document.getElementById("Save");
            let _Error = document.getElementById("Error");

            Save.addEventListener("click", ammendGradesAllocations);

            /**
             * Functions
             */
            async function ammendGradesAllocations(ev){
                const url = "/ITWB4134_SAD_Group3_Assignment2/Update Grade Allocations.php";
                let formData = new FormData();
                formData.append("GradeAllocation", JSON.stringify({
                    "GradeAllocation": GradesAllocations_to_Array()
                }));
                formData.append("LeaveType", "<?php echo $_LeaveType; ?>");
                const response = await fetch(url, {
                    method: "POST",
                    body: formData
                });
                const textObject = await response.text();
                if(textObject === "Success"){
                    _Error.innerText = textObject;
                    _Error.dataset.status = "1";
                }else{
                    _Error.innerText = textObject;
                    _Error.dataset.status = "2";
                }
            }

            function GradesAllocations_to_Array(){
                let arr = [];
                GradeRows.forEach(row=>{
                    let GradeNameTXT = row.querySelector(".GradeName").innerText;
                    let GradeAllocationTXT = row.querySelector(".GradeAllocation").value;
                    arr.push({
                        "Grade": GradeNameTXT,
                        "Allocation": GradeAllocationTXT
                    });
                });
                return arr;
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
