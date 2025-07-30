<?php 
require "../Database Operations/DBModel.php";
require "NavbarLinks.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Employee Page</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Register Employee.css">
</head>
<body>
    <nav id="Navbar">
        <div id="LinkContainer">
            <?php echo $NavbarLinks_Admin; ?>
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
                $Grades = Grades::TryCatch(Grades::getGrades(...), $mysqli);
                foreach($Grades as $grade){
                    ?>
                    <option value="<?php echo $grade; ?>"><?php echo $grade; ?></option>
                    <?php
                }
                ?>
            </select>
            <select name="Privilege" id="Privilege">
                <option value="Normal">Normal</option>
                <option value="Manager">Manager</option>
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
    let Privilege = document.getElementById("Privilege");
    
    form.addEventListener("submit", async (ev)=>{
        ev.preventDefault();
        if (Password.value === PasswordAgain.value){
        /*Now send to backend*/
        const url = "../Database Operations/Register Employee.php";
        const formData = new FormData();
        formData.append("Username", Username.value);
        formData.append("Password", Password.value);
        formData.append("Grade", Grade.value);
        formData.append("Privilege", Privilege.value);
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