<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;

require("./model/User.php");
require("./Data/LoginService.php");
require("./Data/UserRepository.php");
require("./mail/PHPMailer.php");
require("./mail/SMTP.php");
require("./mail/Exception.php");

$dbService = new DatabaseService();
$userRepo = new UserRepository($dbService);
$loginService = new LoginService($dbService);

$email = $emailError = $resetResult = "";

function send_reset_email($email,$token){
    $mail = new PHPMailer(true);


    $mail->isSMTP();
    $mail->Host = '10.0.12.33';
    $mail->SMTPAuth = true;
    $mail->Username = 'phpdemo@test.local.com';
    $mail->Password = 'Diamond1!';
    $mail->Port = 587;

    $mail->setFrom('13ennnnnnn@gmail.com');
    $mail->addAddress($email);
    $mail->addReplyTo('13ennnnnnn@gmail.com');
    $mail->isHTML(true);
    $mail->Subject = "Reset Password";
    $mail->Body = '<p>Click the link to reset your password. <a href="http://localhost/demoapp/password_reset.php?email=' . $email . '&key=' . $token . '">Reset Password</a> </p>';
    $mail->send();


}

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $isValid = false;
    $email = $emailError = $resetResult = "";

    if(empty($_POST['email'])){
        $emailError = "Email is required.";
        $isValid = false;
    }
    else if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
        $emailError = "Invalid email format";
        $isValid = false;
    }
    else{
        $isValid = true;
        $emailError = "";
        $email = $_POST['email'];
    }

    if($isValid){
        $user = $userRepo->get_user_by_email($email);

        if(!$user || strlen($user->userName) < 1){
            $_SESSION['resetPasswordError'] = "User not found with this email.";
            $isValid = false;
            header("Location: forgot_password.php");
            exit();
        }
        else{
            $loginService->delete_reset_token($email);
            $token = $loginService->create_reset_token($email);
            send_reset_email($email,$token);
            $isValid = true;

            ?>
            
            <html>
                <head>
                    <title>Reset Password</title>
                </head>
    
                <body>
                    <div id="modal" style="display:<?php if($isValid){echo "block";}else{echo "none";} ?>">
                        <h3>Success</h3>
                        <span>A reset password email has been sent to the email associated with this account.</span><br/>
                        <button onclick="window.location.href='login.php'" type="button">Ok</button>
                    </div>
                </body>
            </html>
            <?php
        }
    }
    else{
        $_SESSION['resetPasswordError'] = $emailError;
        header("Location: forgot_password.php");
        exit();
    }
}
?>
<style>
    <?php require("./style.css") ?>

    #modal {
        position: absolute;
        margin:0 auto;
        width: 225px;
        height: 160px;
        border: 1px solid black;
        text-align: center;
        display: none;
        background-color: white;
        box-shadow: 5px 10px 18px #888888;
    }
</style>