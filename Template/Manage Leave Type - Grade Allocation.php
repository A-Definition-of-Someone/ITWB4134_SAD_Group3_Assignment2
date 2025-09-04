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

if(!isset($_GET["LeaveType"])){
    header("Location: Login.php"); 
    exit;
}

$_LeaveType = $_GET["LeaveType"];

if(!GradeAllocation::TryCatch(GradeAllocation::prepareLeaveCategoryGradeAllocations(...), $mysqli, $_LeaveType)){
    die("Error: Unable to initialize Grade_Allocation for Leave Category: " . $_LeaveType);
}

$grade_allocation = GradeAllocation::TryCatch(GradeAllocation::queryGradeAllocation(...), $mysqli, $_LeaveType);
      
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Type</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Manage Leave Type - Grade Allocation.css">
</head>
<body>
    <nav id="Navbar">
        <div id="LinkContainer">
            <?php echo $NavbarLinks; ?>
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
                const url = "../Database Operations/Update Grade Allocations.php";
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