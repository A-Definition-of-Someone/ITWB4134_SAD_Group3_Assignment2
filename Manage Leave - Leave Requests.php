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

    $leaverequests = Leave_Application::queryAllLeaveApplication($mysqli);
    $employeeallocations = Employee_Allocation::queryAllEmployeeAllocations($mysqli);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave</title>
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
            height: fit-content;
            width: fit-content;
            background-color: black;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Border*/
            border: var(--BorderWhite);

            /*Text*/
            text-align: center;
            color: white;

            /*Padding*/
            padding-top: 1dvh;
            padding-bottom: 1dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
        }

        #ContentContainer select{
            background-color: black;

            /*Border*/
            border: var(--BorderWhite);

            /*Font*/
            font-size: var(--Header40Approximate);

            /*Text*/
            color: white;

            /*Border*/
            box-sizing: border-box;

            /*Padding*/
            padding-top: 1dvh;
            padding-bottom: 1dvh;
            padding-left: 0.8dvw;
            padding-right: 0.8dvw;
        }

        #ContentContainer #Note[data-hidden = "true"]{
            display: none;
            visibility: hidden;
            height: 0;
            width: 0;
        }
        #ContentContainer #Note[data-hidden = "false"]{
            display: grid;
            height: min-content;
            width: 100%;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 10px 10px 10px 10px;

            /*Font*/
            font-size: var(--Header40Approximate);
            font-weight: bold;

            /*Text*/
            color: white;
            text-align: center;

            /*Border*/
            box-sizing: border-box;

            /*Margin*/
            margin: 0;

            /*Padding*/
            padding-top: 1dvh;
            padding-bottom: 1dvh;
            padding-right: 1dvw;
            padding-left: 1dvw;
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

        #GradeHeader{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 2.7) + 12dvw);
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

        #EmployeeNameHeader{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 6.9) + 6dvw);
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
            padding-top: 2dvh;
            padding-bottom: 2dvh;
        }

        #LeaveTypeHeader{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 5.1) + 6dvw);
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
        #StatusHeader[data-status = "1"]{
            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
            padding-right: 2dvw;
            padding-left: 2dvw;
        }
        #StatusHeader[data-status = "0"]{
            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;
            padding-right: 1dvw;
            padding-left: 1dvw;
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

        .Grade{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 2.7) + 12dvw);
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

        .EmployeeName{
            display: grid;
            height: min-content;
            min-width: min-content;
            width: calc((4.5vmin * 6.9) + 6dvw);
            background-color: var(--OceanBlue);

            /*Grid Configuration*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterOceanBlue);
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
            width: calc((4.5vmin * 5.1) + 6dvw);
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

            /*Grid Configuration*/
            grid-template-columns: 1fr 1fr;
            column-gap: 0.5dvw;

            /*Border*/
            box-sizing: border-box;

            /*Text*/
            text-wrap: nowrap;
            color: white;

            
        }
        
        .Status button{
            background-color: transparent;
            width: calc((4.5vmin * 1.2) + 1.8dvw);

            /*Grid Configuration*/
            place-self: center center;

            /*Border*/
            border-color: white;
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;

            /*Font*/
            font-size: var(--Header30Approximate);
            font-weight: bold;

            /*Text*/
            color: white;
            word-wrap: break-word;
            white-space: normal;

            /*Padding*/
            padding-top: 2dvh;
            padding-bottom: 2dvh;

            /*Cursor*/
            cursor: pointer;
        }

    </style>
</head>
<body>
    
    <nav id="Navbar">
        <div id="LinkContainer">
            <?php echo $NavbarLinks; ?>
        </div>
        <button id="LogOut">Log Out</button>
    </nav>
    
    <main id="ContentContainer">
        <span id="ManageLeave">Manage Leave</span>
        <select name="" id="ManageLeaveSelect">
            <option value="0">Leave Requests</option>
            <option value="1">Employees' Latest Approved Leave </option>
        </select>
        <pre id="Note" data-hidden = "true">A = Allocated Leave
U = Used Leave Allocation</pre>
    <div id="ListHeaders">
        <span id="GradeHeader">Grade</span>
        <span id="EmployeeNameHeader">Employee Name</span>
        <span id="LeaveTypeHeader">Leave Type</span>
        <span id="FromHeader">From</span>
        <span id="ToHeader">To</span>
        <span id="StatusHeader" data-status = "0">✅ / ❌</span>
    </div>
    <div id="LeaveContainer" data-mode="0" >
        <?php
        foreach($leaverequests as $lr){
            ?>
        <div class="ListEmployeesLeave" data-mode="0" data-appleaveid = "<?php echo $lr[Leave_Application_Columns::LeaveApplicationID->value]; ?>">
            <span class="Grade"><?php echo $lr[Leave_Application_Columns::EmployeeGrade->value]; ?></span>
            <span class="EmployeeName"><?php echo $lr[Leave_Application_Columns::EmployeeName->value]; ?></span>
            <span class="LeaveType"><?php echo $lr[Leave_Application_Columns::LeaveCategory->value]; ?></span>
            <span class="From"><?php echo $lr[Leave_Application_Columns::StartDate->value]; ?></span>
            <span class="To"><?php echo $lr[Leave_Application_Columns::EndDate->value]; ?></span>
            <span class="Status"><button>✅</button><button>❌</button></span>
        </div>
            <?php
        }
        ?>
        <?php
        foreach($employeeallocations as $ea){
            ?>
            <div class="ListEmployeesLeave" data-mode="1">
                <span class="Grade"><?php echo $ea[Employee_Allocation_Columns::EmployeeGrade->value]; ?></span>
                <span class="EmployeeName"><?php echo $ea[Employee_Allocation_Columns::EmployeeName->value]; ?></span>
                <span class="LeaveType"><?php echo $ea[Employee_Allocation_Columns::LeaveCategory->value]; ?></span>
                <span class="From"><?php echo $ea[Employee_Allocation_Columns::StartDate->value]; ?></span>
                <span class="To"><?php echo $ea[Employee_Allocation_Columns::EndDate->value]; ?></span>
                <span class="Status"><button><?php echo $ea[Employee_Allocation_Columns::Allocations->value]; ?></button><button><?php echo $ea[Employee_Allocation_Columns::UsedAllocations->value]; ?></button></span>
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


        let LeaveContainer = document.getElementById("LeaveContainer");
        let ManageLeaveSelect = document.getElementById("ManageLeaveSelect");
        let Note = document.getElementById("Note");
        let StatusHeader = document.getElementById("StatusHeader");
        let NoteState = ["true", "false"];
        let StatusState = ["✅ / ❌", "A / U "];
        ManageLeaveSelect.addEventListener("change", changePage.bind(ManageLeaveSelect));

        /**Leave Requests */
        let ListEmployeesLeave_LR = Array.from(document.querySelectorAll(".ListEmployeesLeave[data-mode = '0']") || []);
            
            ListEmployeesLeave_LR.forEach(Row=>{
                let ApproveBTN = Row.querySelector(".Status button:nth-child(1)");
                let RejectBTN = Row.querySelector(".Status button:nth-child(2)");

                ApproveBTN.addEventListener("click", ev=>alert(Row.dataset.appleaveid));
                RejectBTN.addEventListener("click", ev=>alert(Row.dataset.appleaveid));
            });

        /**Employees' Latest Approved Leave */

        /*Functions*/
        function changePage(ev) {
            LeaveContainer.dataset.mode = this.value;
            Note.dataset.hidden = NoteState[this.value];
            StatusHeader.dataset.status = this.value;
            StatusHeader.innerText = StatusState[this.value];
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
