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

$leaverequests = LeaveApplications::TryCatch(LeaveApplications::getAllPendingLeaveApplications(...), $mysqli);
$employeeallocations = EmployeeAllocation::TryCatch(EmployeeAllocation::queryAllEmployeeAllocations(...), $mysqli);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Manage Leave - Leave Requests.css">
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
        <div class="ListEmployeesLeave" data-mode="0" 
        data-appleaveid = "<?php echo $lr[LeaveApplications::col_LeaveApplicationID]; ?>"
        data-empID = "<?php echo $lr[LeaveApplications::col_EmployeeID]; ?>"
        data-leaveCategory = "<?php echo $lr[LeaveApplications::col_LeaveCategory]; ?>"
        >
            <span class="Grade"><?php echo $lr[LeaveApplications::col_EmployeeGrade]; ?></span>
            <span class="EmployeeName"><?php echo $lr[LeaveApplications::col_EmployeeName]; ?></span>
            <span class="LeaveType"><?php echo $lr[LeaveApplications::col_LeaveCategory]; ?></span>
            <span class="From"><?php echo $lr[LeaveApplications::col_StartDate]; ?></span>
            <span class="To"><?php echo $lr[LeaveApplications::col_EndDate]; ?></span>
            <span class="Status"><button>✅</button><button>❌</button></span>
        </div>
            <?php
        }
        ?>
        <?php
        foreach($employeeallocations as $ea){
            ?>
            <div class="ListEmployeesLeave" data-mode="1">
                <span class="Grade"><?php echo $ea[Employee::col_EmployeeGrade]; ?></span>
                <span class="EmployeeName"><?php echo $ea[Employee::col_EmployeeName]; ?></span>
                <span class="LeaveType"><?php echo $ea[LeaveApplications::col_LeaveCategory]; ?></span>
                <span class="From"><?php echo $ea[LeaveApplications::col_StartDate]; ?></span>
                <span class="To"><?php echo $ea[LeaveApplications::col_EndDate]; ?></span>
                <span class="Status"><button><?php echo $ea[GradeAllocation::col_Allocations]; ?></button><button><?php echo $ea[EmployeeAllocation::col_UsedAllocations]; ?></button></span>
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

                ApproveBTN.addEventListener("click", ev=>approveLeaveRequest.call(
                    Row, ev, Row.dataset.appleaveid, Row.dataset.empid, Row.dataset.leavecategory));
                RejectBTN.addEventListener("click", ev=>rejectLeaveRequest.call(
                    Row, ev, Row.dataset.appleaveid, Row.dataset.empid, Row.dataset.leavecategory));
            });

        /**Employees' Latest Approved Leave */

        /*Functions*/
        function changePage(ev) {
            LeaveContainer.dataset.mode = this.value;
            Note.dataset.hidden = NoteState[this.value];
            StatusHeader.dataset.status = this.value;
            StatusHeader.innerText = StatusState[this.value];
        }

        async function approveLeaveRequest(ev, appleaveid, empID, leaveCategory){
            const url = "../Database Operations/AcceptRejectLeaveApplication.php";
            const formData = new FormData();
            formData.append("appleaveid", appleaveid);
            formData.append("LeaveStatus", "Approved");
            formData.append("empID", empID);
            formData.append("leaveCategory", leaveCategory);
            const response = await fetch(url, {
                method: "POST",
                body: formData
            });

            const textObject = await response.text();
            if(textObject === "Success"){ //Refresh list for approved leave
                LeaveContainer.removeChild(this);
                await reloadApprovedLeaveList();
            }else{
                alert(textObject);
            }
        }

        async function rejectLeaveRequest(ev, appleaveid, empID, leaveCategory){
            const url = "../Database Operations/AcceptRejectLeaveApplication.php";
            const formData = new FormData();
            formData.append("appleaveid", appleaveid);
            formData.append("LeaveStatus", "Rejected");
            formData.append("empID", empID);
            formData.append("leaveCategory", leaveCategory);
            const response = await fetch(url, {
                method: "POST",
                body: formData
            });

            const textObject = await response.text();
            if(textObject === "Success"){ //Refresh list for approved leave
                LeaveContainer.removeChild(this);
                await reloadApprovedLeaveList();
            }else{
                alert(textObject);
            }
        }

        async function reloadApprovedLeaveList(){
            const url = "../Database Operations/Query Approved Leave List.php";
            const response = await fetch(url);

            const jsonObject = await response.json();
            
            let newApprovedRow = Array.from(jsonObject.Rows || []);
            console.log("reload: " + jsonObject.Rows[0].EmployeeGrade);
            
            if(newApprovedRow.length > 0){//Refresh list for approved leave
                let ListEmployeesLeave_Approved = Array.from(document.querySelectorAll(".ListEmployeesLeave[data-mode = '1']") || []);
                    ListEmployeesLeave_Approved.forEach(Row=>{
                        LeaveContainer.removeChild(Row);
                    });
                newApprovedRow.forEach(Row=>{
                    LeaveContainer.appendChild(ApprovedLeaveElement(Row.EmployeeGrade, 
                    Row.EmployeeName, Row.LeaveCategory, Row.StartDate, Row.EndDate, Row.Allocations, Row.UsedAllocations));
                });

            }else if (jsonObject.Status !== undefined || jsonObject.Status !== NULL){
                alert(jsonObject.Status);
            }
                
        }

        function ApprovedLeaveElement(Grade, EmployeeName, LeaveType, From, To, Allocations, UsedAllocations){
            let listemployeesleave = document.createElement("div");
                listemployeesleave.classList.add("ListEmployeesLeave");
                listemployeesleave.dataset.mode = "1";
            let grade = document.createElement("span");
                grade.classList.add("Grade");
                grade.innerText = Grade;
            let employeename = document.createElement("span");
                employeename.classList.add("EmployeeName");
                employeename.innerText = EmployeeName;
            let leavetype = document.createElement("span");
                leavetype.classList.add("LeaveType");
                leavetype.innerText = LeaveType;
            let from = document.createElement("span");
                from.classList.add("From");
                from.innerText = From;
            let to = document.createElement("span");
                to.classList.add("To");
                to.innerText = To;
            let status = document.createElement("span");
                status.classList.add("Status");
            let allocation = document.createElement("button");
                allocation.innerText = Allocations;
            let usedallocation = document.createElement("button");
                usedallocation.innerText = UsedAllocations;

                status.appendChild(allocation);
                status.appendChild(usedallocation);

                listemployeesleave.appendChild(grade);
                listemployeesleave.appendChild(employeename);
                listemployeesleave.appendChild(leavetype);
                listemployeesleave.appendChild(from);
                listemployeesleave.appendChild(to);
                listemployeesleave.appendChild(status);

                return listemployeesleave;
        }
    </script>
</body>
</html>