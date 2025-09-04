<?php require "../Database Operations/DBModel.php"; ?>
<?php

$login_state = 0;
$login_status = "";

if(!isset($_SESSION)){
    die("Error: Session not initialized!");
}

if(isset($_SESSION["Token"])){
    $Token = $_SESSION["Token"];
    $Acc = Account::TryCatch(Account::getAccount_usingToken(...), $mysqli, $Token);
    if($Acc){
        if($Acc->getPrivilege() === Privilege::Manager){
            header("Location: Manage Leave Type.php");
            exit;
        }
        header("Location: Employee Leave Request.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $login_status = "Submission Success!";
    $login_state = 1;
    if (isset($_POST["Username"]) and isset($_POST["Password"])){
        $Username = $_POST["Username"];
        $Password = $_POST["Password"];
        $Acc = Account::TryCatch(Account::getAccount(...), $mysqli, $Username, $Password);
        if ($Acc){
            $Token = implode("", $random->shuffleArray(mb_str_split("abcdefghijklmnopqrstuvwxyz0123456789🌑🌕", 1, "UTF-8")));
            if (!Account::TryCatch($Acc->setToken(...), $Token)){
                $login_state = 2;
                $login_status = "Unable to set Token!";
            }else{
                $_SESSION["Token"] = $Token;
                if($Acc->getPrivilege() === Privilege::Manager){
                    header("Location: Manage Leave Type.php");
                    exit;
                }
                header("Location: Employee Leave Request.php");
                exit;
            }
        }else{
            $login_state = 2;
            $login_status = "Invalid Credentials";
        }
    }else{
        $login_state = 2;
        $login_status = "Improper Form Submission";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Employee Page</title>
    <link rel="stylesheet" href="../Static/CommonVariables.css">
    <link rel="stylesheet" href="../Static/Menu.css">
    <link rel="stylesheet" href="../Static/Login.css">
</head>
<body>
    <form method="post">
        <fieldset>
            <legend>Employee Login</legend>
            <div class="AccountFieldRow">
                <label for="Username" class="AccountFieldLabel">Username</label>
                <input type="text" name="Username" id="Username" placeholder="Username" class="AccountTextInputField">
            </div>
            <div class="AccountFieldRow">
                <label for="Password" class="AccountFieldLabel">Password</label>
                <input type="password" name="Password" id="Password" placeholder="Password" class="AccountTextInputField">
            </div>
            <span id="Error" data-status="<?php echo $login_state; ?>">
                <?php echo $login_status; ?>
            </span>
            <input type="submit" value="Login" id="Save">
        </fieldset>
    </form>
</body>
</html>