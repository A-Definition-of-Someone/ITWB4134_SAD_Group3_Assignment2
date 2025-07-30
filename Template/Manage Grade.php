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
    <title>Manage Grade</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Manage Grade.css">
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
            <legend>Manage Grade</legend>
            <div id="GradeRowContainer">
                <?php
                $_Grades = Grades::TryCatch(Grades::getGrades(...), $mysqli);
                foreach($_Grades as $Grade){
                    ?>
                    <div class="GradeRow">
                        <span class="GradeName" ><?php echo $Grade;?></span>
                        <button>🗑</button>
                    </div>
                    <?php
                }
                ?>
                <div id="AddGradeRow">
                    <span contenteditable="true">Click to name new Grade,'+' to add</span>
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
             * Grades
             */
            let GradeRowContainer = document.getElementById("GradeRowContainer");
            let GradeRows = Array.from(document.querySelectorAll(".GradeRow") || []);
            let AddGradeRow = document.getElementById("AddGradeRow");
            let BtnAddGradeRow = AddGradeRow.querySelector("button");
            let SpanAddGradeRow = AddGradeRow.querySelector("span");
            
            // For Leave Type rows defined during page load
            GradeRows.forEach(GradeRow =>{
                let BTN_DeleteGrade = GradeRow.querySelector(".GradeRow button:nth-child(2)");
                BTN_DeleteGrade.addEventListener("click", ev => DeleteGrade.call(BTN_DeleteGrade, ev, GradeRow));
            });

            // Adds new Leave Type rows and assigns listeners to buttons within them
            BtnAddGradeRow.addEventListener("click", AddGrade.bind(BtnAddGradeRow));

            /***
             * Functions
             */
            async function ManageGradeAllocation(ev, GradeRow){
                let Grade = GradeRow.querySelector("span").innerText;
                location.replace("Manage Leave Type - Grade Allocation.php?Grade=" + Grade);
            }
            async function DeleteGrade(ev, GradeRow){
                //Remove in Database first
                const url = "../Database Operations/RemoveGrade.php";
                let formData = new FormData();
                let Grade = GradeRow.querySelector("span").innerText;
                formData.append("Grade", Grade);
                const response = await fetch(url,{
                    method: "POST",
                    body: formData
                });
                //If Database alright, can update this webpage
                const textObject = await response.text();
                if(textObject === "Success"){
                    GradeRowContainer.removeChild(GradeRow);
                }else{
                    alert(textObject);
                }
            }
            async function AddGrade(ev){
                //Add in Database first
                const url = "../Database Operations/AddGrade.php";
                let formData = new FormData();
                let Grade = SpanAddGradeRow.innerText;
                formData.append("Grade", Grade);
                const response = await fetch(url,{
                    method: "POST",
                    body: formData
                });
                //If Database alright, can update this webpage
                const textObject = await response.text();
                if(textObject === "Success"){
                    SpanAddGradeRow.innerText = "Click to name new Grade,'+' to add";
                    GradeRowContainer.insertBefore(createGradeRow(Grade), AddGradeRow);
                }else{
                    alert(textObject);
                }

            }

            function createGradeRow(Grade){
                let GradeRow = document.createElement("div");
                    GradeRow.classList.add("GradeRow");
                let GradeName = document.createElement("span");
                    GradeName.classList.add("GradeName");
                    GradeName.innerText = Grade;
                let BtnDelete = document.createElement("button");
                    BtnDelete.innerText = "🗑";
                    BtnDelete.addEventListener("click", ev => DeleteGrade.call(BtnDelete, ev, GradeRow));

                GradeRow.appendChild(GradeName);
                GradeRow.appendChild(BtnDelete);

                return GradeRow;
            }

        </script>
</body>
</html>