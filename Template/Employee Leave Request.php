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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Type</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Employee Leave Request.css">
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
                    $Types = LeaveCategories::TryCatch(LeaveCategories::getLeaveCategories(...), $mysqli);
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
                const url = "../Database Operations/Submit Leave Application.php";
                let formData = new FormData();
                formData.append("LeaveType", LeaveType.value);
                formData.append("From", From.value);
                formData.append("To", To.value);
                formData.append("EmpID", "<?php echo $Acc->getEmployeeID() ?>");
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