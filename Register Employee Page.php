<?php 
require "DBModel.php";
require "NavbarLinks.php";
?>
<?php
$Username = "root";
$Database = "LeaveManagement";
$Host = "localhost";
$Password = ""; 

$mysqli = mysqli_connect($Host, $Username, $Password, $Database);

//Check connection status
if (mysqli_connect_errno()){
    die("Error connecting to DB, Error: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Employee Page</title>
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
            background-color: var(--LightGray);

            /*Grid Configuration*/
            grid-auto-rows: min-content;
            place-self: center center;
            row-gap: 1.5dvh;

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


        .AccountFieldRow{
            display: grid;
            height: min-content;
            width: min-content;

            /*Grid Configuration*/
            grid-auto-flow: column;
            grid-auto-columns: min-content;
            column-gap: 1dvw;
        }

        .AccountFieldLabel{
            display: grid;
            height: 7.4074dvh;
            width: 14.0625dvw;
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

        .AccountTextInputField{
            display: grid;
            height: 7.4074dvh;
            width: 42.1875dvw;
            background-color: black;

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: center;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
        }

        select{
            height: 7.4074dvh;
            width: 57.8dvw;
            background-color: black;

            /*Border*/
            border: var(--BorderWhite);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: left;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Padding*/
            padding-left: 0.5dvw;
            padding-right: 0.5dvw;
        }
        #Error[data-status="0"]{
            display: none;
            visibility: hidden;
            width: 0;
            height: 0;
        }

        #Error[data-status="1"]{
            display: grid;
            height: 7.4074dvh;
            width: 57.8dvw;
            background-color: var(--LightGreen);

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderDarkGreen);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: left;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Padding*/
            padding-left: 0.5dvw;
            padding-right: 0.5dvw;
        }

        #Error[data-status="2"]{
            display: grid;
            height: 7.4074dvh;
            width: 57.8dvw;
            background-color: var(--LighterOrange);

            /*Grid Config*/
            place-content: center center;

            /*Border*/
            border: var(--BorderOrange);
            border-radius: 15px 15px 15px 15px;

            /*Text*/
            color: white;
            text-align: left;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);

            /*Padding*/
            padding-left: 0.5dvw;
            padding-right: 0.5dvw;
        }

        #Save{
            display: grid;
            height: 10.1852dvh;
            width: 11.5885dvw;
            background-color: var(--DarkerBlue);

            /*Grid Configuration*/
            place-content: center center;
            place-self: center center;

            /*Text*/
            color: white;

            /*Font*/
            font-family: var(--FontFamily);
            font-size: var(--Header50PXApproximate);
            font-weight: bold;

            /*Border*/
            border-color: #5C79A3;
            border-radius: 20px 20px 20px 20px;
        }
    </style>
</head>
<body>
    <nav id="Navbar">
        <div id="LinkContainer">
            <?php echo $NavbarLinks; ?>
        </div>
        <button id="LogOut" title="For convenience, this page is accessible by anyone. Hence, no need for this button to log out">Admin</button>
    </nav>
    <form>
        <fieldset>
            <legend>Employee Registration</legend>
            <div class="AccountFieldRow">
                <label for="Username" class="AccountFieldLabel">Username</label>
                <input type="text" name="Username" id="Username" placeholder="Username" class="AccountTextInputField">
            </div>
            <div class="AccountFieldRow">
                <label for="Password" class="AccountFieldLabel">Password</label>
                <input type="password" name="Password" id="Password" placeholder="Password" class="AccountTextInputField">
            </div>
            <div class="AccountFieldRow">
                <label for="PasswordAgain" class="AccountFieldLabel">Password</label>
                <input type="password" name="PasswordAgain" id="PasswordAgain" placeholder="Confirm Password" class="AccountTextInputField">
            </div>
            <select name="Grade" id="Grade">
                <?php
                $Grade = Grade::queryGrades($mysqli);
                foreach($Grade as $grade){
                    ?>
                    <option value="<?php echo $grade; ?>"><?php echo $grade; ?></option>
                    <?php
                }
                ?>
            </select>
            <span id="Error" data-status="0">
                Error: Check Password
            </span>
            <input type="submit" value="Register" id="Save">
        </fieldset>
        <script>
            
            let form = document.querySelector("form");
            let Password = document.getElementById("Password");
            let PasswordAgain = document.getElementById("PasswordAgain");
            let ErrorOutput = document.getElementById("Error");
            let Username = document.getElementById("Username");
            let Grade = document.getElementById("Grade");

            form.addEventListener("submit", async (ev)=>{
                ev.preventDefault();
                if (Password.value === PasswordAgain.value){
                    //Now send to backend
                    const url = "/ITWB4134_SAD_Group3_Assignment2/Register Employee.php";
                    const formData = new FormData();
                    formData.append("Username", Username.value);
                    formData.append("Password", Password.value);
                    formData.append("Grade", Grade.value);
                    try {
                        let response = await fetch(url, {
                            method: "POST",
                            body: formData
                        });

                        let textObject = await response.text();
                        if(textObject === "Success: Account and Employee registration"){
                            ErrorOutput.innerText = textObject;
                            ErrorOutput.dataset.status = "1";
                        }else{
                            ErrorOutput.innerText = textObject;
                            ErrorOutput.dataset.status = "2";
                        }
                    } catch (error) {
                        ErrorOutput.innerText = error;
                        ErrorOutput.dataset.status = "2";
                    }
                }else{
                    ErrorOutput.innerText = "Error: Password fields mismatch!";
                    ErrorOutput.dataset.status = "2";
                }
            });

            
        </script>
</body>
</html>