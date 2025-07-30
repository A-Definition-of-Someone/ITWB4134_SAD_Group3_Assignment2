<?php 
require "../Database Operations/DBModel.php";
require "NavbarLinks.php";
?>
<?php
if (!isset($_SESSION)){
    die("Error: Session not initialized!");
}

if(!isset($_SESSION["Token"])){
    header("Location: Login.php"); 
    exit;
}

$Token = $_SESSION["Token"];
$Acc = Account::TryCatch(Account::getAccount_usingToken(...), $mysqli, $Token);
if(!$Acc){
    header("Location: Login.php"); 
    exit;
}

if($Acc->getPrivilege() !== Privilege::Manager){
    header("Location: Login.php"); 
    exit;
}
      
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Type</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Manage Leave Type.css">
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
                $_LeaveType = LeaveCategories::TryCatch(LeaveCategories::getLeaveCategories(...), $mysqli);
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
                const url = "../Database Operations/LogOut.php";
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

                BTN_ManageLeaveTypeAllocation.addEventListener(
                    "click", 
                    ev => ManageLeaveTypeAllocation.call(BTN_ManageLeaveTypeAllocation, ev, LeaveTypeRow)
                );
                BTN_DeleteLeaveType.addEventListener(
                    "click", 
                    ev => DeleteLeaveType.call(BTN_DeleteLeaveType, ev, LeaveTypeRow)
                );
            });

            // Adds new Leave Type rows and assigns listeners to buttons within them
            BtnAddLeaveTypeRow.addEventListener("click", AddLeaveType.bind(BtnAddLeaveTypeRow));

            /***
             * Functions
             */
            async function ManageLeaveTypeAllocation(ev, LeaveTypeRow){
                let LeaveType = LeaveTypeRow.querySelector("span").innerText;
                location.replace("Manage Leave Type - Grade Allocation.php?LeaveType=" + LeaveType);
            }
            async function DeleteLeaveType(ev, LeaveTypeRow){
                //Remove in Database first
                const url = "../Database Operations/RemoveLeaveType.php";
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
                const url = "../Database Operations/AddLeaveType.php";
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