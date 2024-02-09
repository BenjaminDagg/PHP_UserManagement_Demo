<!DOCTYPE html>
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

    require("./model/User.php");
    require("./Data/LoginService.php");
    require("./Data/UserRepository.php");

    $dbService = new DatabaseService();
    $userRepo = new UserRepository($dbService);
    $loginService = new LoginService($dbService);

    $password = $passwordError = $confirmPassword = $confimPasswordError = $resetResult = "";
    $token = "";
    $email = "";
    $isTokenValid = false;

    if(isset($_GET['key']) && isset($_GET['email'])){

        $isValid = false;

        $token = $_GET['key'];
        $email = $_GET['email'];

        $reset = $loginService->validate_reset_token($email,$token);

        //token and email match
        if($reset->num_rows > 0){
            $isTokenValid = true;
        }
        //token/email mismatch or not found
        else{
            $isTokenValid = false;
            $isValid = false;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $valid = true;
        $passwordError =  $confirmpasswordError = "";
        $resultMessage = "";
        $currentPassword = "";
        $isTokenValid = true;
        $email = $_POST['email'];

        if(empty($_POST["password"])){
            $passwordError = "Password field is required";
            $valid = false;
        }
        else{
            $password = $_POST["password"];
        }

        if(empty($_POST["confirmpassword"])){
            $confimPasswordError = "Confirm Password field is required";
            $valid = false;
        }
        else{
            $confirmPassword = $_POST["confirmpassword"];
        }

        if($password != $confirmPassword){
            $resetResult = "Password and Confirm password do not match.";
            $valid = false;
        }
        
        if($valid == true){
            $valid = false;
            $user = $userRepo->get_user_by_email($email);
            $currentPassHash = $user->email;

            $passwordMatch = password_verify($password,$currentPassHash);

            //trying to reset password to same as curent password
            if($passwordMatch){
                $resultMessage = "Cannot change password to the same as your current password.";
                $valid = false;
            }
            else{
                try{
                    $newPassHash = password_hash($password,PASSWORD_DEFAULT);
                    $user-> password = $newPassHash;

                    $userRepo->update_password($user->userName,$newPassHash);
                    $resultMessage = "";
                    $valid = true;

                    //delete token so user can't reset again
                    $loginService->delete_reset_token($email);
                }
                catch(Exception $e){
                    echo "Failed to update user password. " . $e->getMessage();
                    $valid = false;
                }
            }
        }
    }
    
?>
<script>
    function navigateToLogin(){
        window.location.href = "login.php";
    }
</script>
<style>
    .error {
    color: red;
}

h3 {
    color:#294d7e;
    font-family: Roboto,sans-serif;
}

body{
    font-family: Roboto,sans-serif;
}

button {
    border-radius: 4px;
    color: white;
    background-color: #294d7e;
    height: 39px;
    padding: 8px;
    font-family: Roboto,sans-serif;
    font-size: 14px;
}

input[type=submit] {
    border-radius: 4px;
    color: white;
    background-color: #294d7e;
    height: 39px;
    padding: 8px;
    font-family: Roboto,sans-serif;
    font-size: 14px;
}

#modal {
        position: absolute;
        margin:0 auto;
        width: 200px;
        height: 150px;
        border: 1px solid black;
        text-align: center;
        display: none;
        background-color: white;
        box-shadow: 5px 10px 18px #888888;
    }
</style>
<html>
                <head>
                    <title>Users</title>
                </head>
    
                <body>
                    <h3>Reset Password</h3>
                        <?php if($isTokenValid) : ?>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <div id="modal" style="display:<?php if($valid){echo "block";}else{echo "none";} ?>">
                            <h3>Success</h3>
                            <span>Successfully reset password.</span><br/>
                            <button onclick="navigateToLogin()" type="button">Ok</button>
                        </div>
                            <table>
                                <tr>
                                    <td>
                                        <label>New Password:</label>
                                    </td>
                                    <td>
                                        <input type="password" name="password" value="<?php echo $password;?>"/>
                                    </td>
                                    <td>
                                        <span class="error"><?php echo $passwordError ?>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                        <label>Confirm Password:</label>
                                    </td>
                                    <td>
                                        <input type="password" name="confirmpassword" value="<?php echo $confirmPassword;?>"/>
                                    </td>
                                    <td>
                                        <span class="error"><?php echo $confimPasswordError ?>
                                    </td>
                                 </tr>
                            </table>
                        <input hidden  name="email" value="<?php echo $email;?>"/>
                        <span class="error"><?php echo $resetResult ?></span><br/>
                        <input type="submit" /><br/><br/>
                        </form>
                        <?php else : ?>
                            <div id="modal" style="display:<?php if(!$isTokenValid){echo "block";}else{echo "none";} ?>">
                            <h3>Error</h3>
                            <span>The token is invalid or has expired. Return to login.</span><br/>
                            <button onclick="navigateToLogin()" type="button">Ok</button>
                        </div>
                        <?php endif ?>
                </body>
            </html>
