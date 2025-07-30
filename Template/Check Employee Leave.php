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
    <title>Check Leave Application Status</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Check Employee Leave.css">
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
        $leaverequests = LeaveApplications::TryCatch(LeaveApplications::getLeaveApplications_withEmployeeID(...), $mysqli, $Acc->getEmployeeID());
        foreach($leaverequests as $index => $lr){
            ?>
        <div class="ListEmployeesLeave" data-mode="0" 
        data-appleaveid = "<?php echo $lr[LeaveApplications::col_LeaveApplicationID]; ?>"
        data-empID = "<?php echo $lr[Employee::col_EmployeeID]; ?>"
        data-leaveCategory = "<?php echo $lr[LeaveApplications::col_LeaveCategory]; ?>"
        >
            <span class="Num"><?php echo "#" . $index + 1; ?></span>
            <span class="LeaveType"><?php echo $lr[LeaveApplications::col_LeaveCategory]; ?></span>
            <span class="From"><?php echo $lr[LeaveApplications::col_StartDate]; ?></span>
            <span class="To"><?php echo $lr[LeaveApplications::col_EndDate]; ?></span>
            <span class="Status"><?php echo $lr[LeaveApplications::col_LeaveStatus]; ?></span>
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

    </script>
</body>
</html>