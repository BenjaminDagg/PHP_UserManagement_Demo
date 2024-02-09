<?php 
session_start(); 

$isLoggedIn = false;


if(!isset($_SESSION["username"]) || $_SESSION["login"] != true) {
    $isLoggedIn = false;
}
else{
    //check if session expired
    $now = time();
    $startTime = $_SESSION["start"];
    $expirationSec = (int)$_SESSION["sessionExpiration"];
    if($now - $startTime > $expirationSec){
        $isLoggedIn = false;
    }
    else{
        $isLoggedIn = true;
    }

}

?>
<!DOCTYPE html>
<script>
    function returnToLogin(){
        window.location.href = "logout.php";
        
    }
    function NavigateChangePassword(){
        
        window.location.href = "change_password.php";
        
    }
</script>
<style>
    #alert-modal {
        position: absolute;
        margin:0 auto;
        width: 200px;
        height: 150px;
        border: 1px solid black;
        text-align: center;
        display: block;
        background-color: white;
        box-shadow: 5px 10px 18px #888888;
        padding: 5px;
    }

    #block {
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: white;
        z-index: 5;
    }
</style>
<link rel="stylesheet" href="style.css"/>
<html>
    <head>
        <form action="logout.php" method="post">
            
            <?php if($isLoggedIn) :?>
                <button type="submit" name="logout" value="Logout">Logout</button>
                <button type="button" onclick="NavigateChangePassword()">Change Password</button>
                <span>Logged in as <?php
                if(isset($_SESSION["username"])){
                    $username = $_SESSION["username"];
                    echo "$username";
                }
            ?></span>
            <?php endif; ?>
        </form>
        
    </head>
    <?php if(!$isLoggedIn) :?>
        <body>
            <div id="block">
                <div id="alert-modal" style="display:<?php if(!$isLoggedIn){echo "block";}else{echo "none";} ?>">
                    <h3>Alert</h3>
                    <span>Your session has expired. Please login again.</span><br/>
                    <button type="button" onclick="returnToLogin()">Ok</button>
                </div>
            </div>
        </body>
    <?php else :?>
        <body>

        </body>
    <?php endif; ?>
</html>