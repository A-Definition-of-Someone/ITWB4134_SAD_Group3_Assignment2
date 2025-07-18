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
            height: 38.8889dvh;
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

        #LeaveTypeRowContainer{
            display: grid;
            width: 100%;
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

        .LeaveTypeRow{
            display: grid;
            height: min-content;
            width: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
        }

        .LeaveTypeName{
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

        .LeaveTypeRow button{
            display: grid;
            height: 7.4074dvh;
            width: 4dvw;
            background-color: black;

            /*Grid config*/
            place-content: center center;

            /*Text*/
            text-align: center;
            color: red;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header45PXApproximate);
        }

        .LeaveTypeRow button:nth-child(3){
            line-height: 1dvh;
        }

        #AddLeaveTypeRow{
            display: grid;
            height: min-content;
            width: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
        }

        #AddLeaveTypeRow span{
            display: grid;
            height: 7.4074dvh;
            width: 40.1042dvw;
            background-color: var(--DarkerBlue);

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderLighterDarkerBlue);

            /*Text*/
            color: white;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
        }

        #AddLeaveTypeRow button{
            display: grid;
            height: 7.4074dvh;
            width: 9dvw;
            background-color: black;

            /*Grid config*/
            place-content: center center;

            /*Text*/
            text-align: center;
            color: var(--DarkGreen);

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header60PXApproximate);
            font-weight: bold;
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
    <form>
        <fieldset>
            <legend>Manage Leave Type</legend>
            <div id="LeaveTypeRowContainer">
                <?php
                $_LeaveType = LeaveType::queryLeaveType($mysqli);
                foreach($_LeaveType as $LT){
                    ?>
                    <div class="LeaveTypeRow">
                        <span class="LeaveTypeName" ><?php echo $LT;?></span>
                        <button>✏️</button>
                        <button>🗑</button>
                    </div>
                    <?php
                }
                ?>
                <div id="AddLeaveTypeRow">
                    <span contenteditable="true">Add Leave Type</span>
                    <button>+</button>
                </div>
            </div>
        </fieldset>
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

            /***
             * LeaveTypes
             */
            let LeaveTypeRowContainer = document.getElementById("LeaveTypeRowContainer");
            let LeaveTypeRows = Array.from(document.querySelectorAll(".LeaveTypeRow") || []);
            let AddLeaveTypeRow = document.getElementById("AddLeaveTypeRow");
            let BtnAddLeaveTypeRow = AddLeaveTypeRow.querySelector("button");
            let SpanAddLeaveTypeRow = AddLeaveTypeRow.querySelector("span");
            
            // For Leave Type rows defined during page load
            LeaveTypeRows.forEach(LeaveTypeRow =>{
                let BTN_ManageLeaveTypeAllocation = LeaveTypeRow.querySelector(".LeaveTypeRow button:nth-child(2)");
                let BTN_DeleteLeaveType = LeaveTypeRow.querySelector(".LeaveTypeRow button:nth-child(3)");

                BTN_ManageLeaveTypeAllocation.addEventListener("click", ev => ManageLeaveTypeAllocation.call(BTN_ManageLeaveTypeAllocation, ev, LeaveTypeRow));
                BTN_DeleteLeaveType.addEventListener("click", ev => DeleteLeaveType.call(BTN_DeleteLeaveType, ev, LeaveTypeRow));
            });

            // Adds new Leave Type rows and assigns listeners to buttons within them
            BtnAddLeaveTypeRow.addEventListener("click", AddLeaveType.bind(BtnAddLeaveTypeRow));

            /***
             * Functions
             */
            async function ManageLeaveTypeAllocation(ev, LeaveTypeRow){
                let LeaveType = LeaveTypeRow.querySelector("span").innerText;
                location.replace("/ITWB4134_SAD_Group3_Assignment2/Manage Leave Type - Grade Allocation.php?LeaveType=" + LeaveType);
            }
            async function DeleteLeaveType(ev, LeaveTypeRow){
                //Remove in Database first
                const url = "/ITWB4134_SAD_Group3_Assignment2/RemoveLeaveType.php";
                let formData = new FormData();
                let LeaveType = LeaveTypeRow.querySelector("span").innerText;
                formData.append("LeaveType", LeaveType);
                const response = await fetch(url,{
                    method: "POST",
                    body: formData
                });
                //If Database alright, can update this webpage
                const textObject = await response.text();
                if(textObject === "Success"){
                    LeaveTypeRowContainer.removeChild(LeaveTypeRow);
                }else{
                    alert(textObject);
                }
            }
            async function AddLeaveType(ev){
                //Add in Database first
                const url = "/ITWB4134_SAD_Group3_Assignment2/AddLeaveType.php";
                let formData = new FormData();
                let LeaveType = SpanAddLeaveTypeRow.innerText;
                formData.append("LeaveType", LeaveType);
                const response = await fetch(url,{
                    method: "POST",
                    body: formData
                });
                //If Database alright, can update this webpage
                const textObject = await response.text();
                if(textObject === "Success"){
                    SpanAddLeaveTypeRow.innerText = "Add Leave Type";
                    LeaveTypeRowContainer.insertBefore(createLeaveTypeRow(LeaveType), AddLeaveTypeRow);
                }else{
                    alert(textObject);
                }

            }

            function createLeaveTypeRow(LeaveType){
                let LeaveTypeRow = document.createElement("div");
                    LeaveTypeRow.classList.add("LeaveTypeRow");
                let LeaveTypeName = document.createElement("span");
                    LeaveTypeName.classList.add("LeaveTypeName");
                    LeaveTypeName.innerText = LeaveType;
                let BtnEdit = document.createElement("button");
                    BtnEdit.innerText = "✏️";
                    BtnEdit.addEventListener("click", ev => ManageLeaveTypeAllocation.call(BtnEdit, ev, LeaveTypeRow));
                let BtnDelete = document.createElement("button");
                    BtnDelete.innerText = "🗑";
                    BtnDelete.addEventListener("click", ev => DeleteLeaveType.call(BtnDelete, ev, LeaveTypeRow));

                LeaveTypeRow.appendChild(LeaveTypeName);
                LeaveTypeRow.appendChild(BtnEdit);
                LeaveTypeRow.appendChild(BtnDelete);

                return LeaveTypeRow;
            }

        </script>
</body>
</html>