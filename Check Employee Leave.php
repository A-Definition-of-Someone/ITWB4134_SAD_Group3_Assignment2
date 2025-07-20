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
        $Acc = Account::searchAccount_Token($mysqli, $Token);
        try {
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
    $emp = $Acc->getEmployee();
    $leaverequests = Leave_Application::querySpecificEmployeeLeaveApplication($mysqli, $emp->getEmployeeID());
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Leave Application Status</title>
    <link rel="stylesheet" href="CommonVariables.css">
    <link rel="stylesheet" href="Menu.css">
    <style>
        body{
            /*Grid Configuration*/
            row-gap: 2dvh;

            /*Scroll*/
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
        }

        #ContentContainer{
            display: grid;
            height: min-content;
            width: inherit;

            /*Grid Config*/
            grid-auto-flow: row;
            grid-auto-rows: min-content;
            row-gap: 2dvh;

            /*Border*/
            box-sizing: border-box;

            /*Padding*/
            padding-left: 0.5dvw;
            padding-right: 0.5dvw;
        }

        #ManageLeave{
            display: grid;
            height: fit-content;
            width: fit-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            column-gap: 1dvw;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            

            /*Text*/
            text-align: center;
            color: white;

            /*Padding*/
            padding-bottom: 2dvh;
        }

        #ManageLeave span:nth-child(1){
            display: grid;
            height: min-content;
            background-color: black;

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderWhite);
            box-sizing: border-box;
            
            /*Padding*/
            padding-top: 1dvh;
            padding-bottom: 1dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
        }

        #ManageLeave span:nth-child(2){
            display: grid;
            height: min-content;
            min-width: min-content;
            width: min-content;
            background-color: var(--OceanBlue);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterOceanBlue);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: nowrap;

            /*Padding*/
            padding-top: 1dvh;
            padding-bottom: 1dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
        }


        #ListHeaders{
            display: grid;
            width: min-content;
            height: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
            place-self: center center;

            /*Font*/
            font-size: var(--Header40Approximate);
            font-weight: bold;
        }

        #NumHeader{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 2.7) + 2dvw);
            background-color: var(--LightGreen);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterGreen);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: nowrap;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
        }


        #LeaveTypeHeader{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 9) + 6dvw);
            background-color: var(--DarkPurple);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterDarkPurple);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: nowrap;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
        }

        #FromHeader, #ToHeader{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 3.2) + 6dvw);
            background-color: var(--Orange);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterOrange);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: nowrap;
            color: white;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
        }

        #StatusHeader{
            display: grid;
            height: min-content;
            width: calc((4.5vmin * 4.7) + 4dvw);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: nowrap;
            color: white;

            
        }
        #StatusHeader{
            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
        }
        
        
        #LeaveContainer{
            display: grid;
            height: min-content;
            max-height: 56dvh;
            width: 100%;

            /*Grid Configuration*/
            row-gap: 1dvh;

            /*Border*/
            box-sizing: border-box;

            /*Padding*/
            padding-top: 1dvh;

            /*Scroll*/
            overflow-y: scroll;
            scrollbar-width: none;
        }

        #LeaveContainer[data-mode="0"] .ListEmployeesLeave[data-mode="0"], #LeaveContainer[data-mode="1"] .ListEmployeesLeave[data-mode="1"]{
            display: grid;
            width: min-content;
            height: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
            place-self: center center;

            /*Font*/
            font-size: var(--Header40Approximate);
            font-weight: bold;
        }

        #LeaveContainer[data-mode="0"] .ListEmployeesLeave[data-mode="1"], #LeaveContainer[data-mode="1"] .ListEmployeesLeave[data-mode="0"]{
            display: none;
            visibility: hidden;
            height: 0;
            width: 0;
        }

        .Num{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 2.7) + 2dvw);
            background-color: var(--LightGreen);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterGreen);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: wrap;
            text-align: center;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
        }


        .LeaveType{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 9) + 6dvw);
            background-color: var(--DarkPurple);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterDarkPurple);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: wrap;
            text-align: center;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
        }

        .From, .To{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 3.2) + 6dvw);
            background-color: var(--Orange);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterOrange);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: wrap;
            text-align: center;
            color: white;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
        }


        .Status{
            display: grid;
            height: min-content;
            width: calc((4.5vmin * 4.7) + 4dvw);

            /*Grid Configuration*/
            grid-template-rows: 100%;
            grid-template-columns: 100%;
            place-content: center center;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Text*/
            text-wrap: nowrap;
            text-align: center;
            color: white;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
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
    
    <main id="ContentContainer">
        <span id="ManageLeave"><span>Leave Request Status</span><span><?php echo $Acc->getUsername(); ?></span></span>
        <div id="ListHeaders">
            <span id="NumHeader">Num</span>
            <span id="LeaveTypeHeader">Leave Type</span>
            <span id="FromHeader">From</span>
            <span id="ToHeader">To</span>
            <span id="StatusHeader">✅ / ❌</span>
        </div>
    <div id="LeaveContainer" data-mode="0" >
        <?php
        foreach($leaverequests as $index => $lr){
            ?>
        <div class="ListEmployeesLeave" data-mode="0" 
        data-appleaveid = "<?php echo $lr[Leave_Application_Columns::LeaveApplicationID->value]; ?>"
        data-empID = "<?php echo $lr[Leave_Application_Columns::EmployeeID->value]; ?>"
        data-leaveCategory = "<?php echo $lr[Leave_Application_Columns::LeaveCategory->value]; ?>"
        >
            <span class="Num"><?php echo "#" . $index + 1; ?></span>
            <span class="LeaveType"><?php echo $lr[Leave_Application_Columns::LeaveCategory->value]; ?></span>
            <span class="From"><?php echo $lr[Leave_Application_Columns::StartDate->value]; ?></span>
            <span class="To"><?php echo $lr[Leave_Application_Columns::EndDate->value]; ?></span>
            <span class="Status"><?php echo $lr[Leave_Application_Columns::LeaveStatus->value]; ?></span>
        </div>
            <?php
        }
        ?>
        
        
    </div>
    </main>
    
    
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