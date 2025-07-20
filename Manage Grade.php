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
try{
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grade</title>
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

        #GradeRowContainer{
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

        .GradeRow button{
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

        .GradeRow button:nth-child(2){
            line-height: 1dvh;
        }

        #AddGradeRow{
            display: grid;
            height: min-content;
            width: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
        }

        #AddGradeRow span{
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

        #AddGradeRow button{
            display: grid;
            height: 7.4074dvh;
            width: 4dvw;
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
            <legend>Manage Grade</legend>
            <div id="GradeRowContainer">
                <?php
                $_Grades = Grade::queryGrades($mysqli);
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
                location.replace("/ITWB4134_SAD_Group3_Assignment2/Manage Leave Type - Grade Allocation.php?Grade=" + Grade);
            }
            async function DeleteGrade(ev, GradeRow){
                //Remove in Database first
                const url = "/ITWB4134_SAD_Group3_Assignment2/RemoveGrade.php";
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
                const url = "/ITWB4134_SAD_Group3_Assignment2/AddGrade.php";
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
<?php 
} catch(mysqli_sql_exception $err){
    die($err->getMessage());
} catch (\Throwable $th) {
    die($th);
}
?>