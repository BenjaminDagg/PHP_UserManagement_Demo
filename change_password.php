<!DOCTYPE html>
<style>
    
</style>
<?php 
    require("./model/User.php");
    require("./Data/LoginService.php");
    require("./Data/UserRepository.php");
    require("header.php");

    $servername = "localhost";
    $dbusername = "sa";
    $dbpassword = "Diamond1!";
    $dbname = "test";
    
    $dbService = new MyDatabaseService($servername,$dbusername,$dbpassword,$dbname);
    $loginService = new LoginService($dbService);
    $userRepo = new UserRepository($dbService);

    $user = new User();
    if(isset($_SESSION['username'])){
        $user = $userRepo->get_user_by_username($_SESSION['username']);
    }

    $password = $currentPassword = $newpassword = $confirmpassword = "";
    $passwordError = $newpasswordError = $confirmpasswordError = "";
    $resultMessage = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $valid = true;
        $passwordError = $newpasswordError = $confirmpasswordError = "";
        $resultMessage = "";
        $currentPassword = "";

        if(empty($_POST["currentpassword"])){
            $passwordError = "Password field is required";
            $valid = false;
        }
        else{
            $password = $_POST["currentpassword"];
        }

        if(empty($_POST["newpassword"])){
            $newpasswordError = "New Password field is required";
            $valid = false;
        }
        else{
            $newpassword = $_POST["newpassword"];
        }

        if(empty($_POST["confirmpassword"])){
            $confirmpasswordError = "Confirm Password field is required";
            $valid = false;
        }
        else{
            $confirmpassword = $_POST["confirmpassword"];
        }

        if($newpassword != $confirmpassword){
            $confirmpasswordError = "Password and Confirm password do not match.";
            $valid = false;
        }
        

        

        if($valid == true){
            $currentPassHash = $loginService->validate_login($user->userName);

            $passwordMatch = password_verify($password,$currentPassHash);

            if($passwordMatch){
                //test if user is trying to change their password to the same as current password
                if(password_verify($newpassword,$currentPassHash)){
                    $resultMessage = "Cannot change password to same as current password.";
                }
                else{
                    try{
                        $newPassHash = password_hash($newpassword,PASSWORD_DEFAULT);
                        $user-> password = $newPassHash;

                        $userRepo->update_password($user->userName,$newPassHash);
                        $resultMessage = "Successfully updated password.";
                    }
                    catch(Exception $e){
                        echo "Failed to update user password. " . $e->getMessage();
                    }
                }
            }
            else{
                $resultMessage = "Incorrect current password";
            }
        }
    }
?>
<script>
    function navigateToUserList(){
        window.location.href = "list.php";
        
    }
</script>
<html>
    <head>
        <title>Users</title>
    </head>
    <h3>Login</h3>
    <body>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            Current Password: <input type="password" name="currentpassword" value="<?php echo $password;?>"/><span><?php echo $passwordError ?><br/><br/>
            New Password: <input type="password" name="newpassword" value="<?php echo $newpassword;?>"/><span><?php echo $newpasswordError ?><br/><br/>
            Confirm Password: <input type="password" name="confirmpassword" value="<?php echo $confirmpassword;?>"/><span><?php echo $confirmpasswordError ?><br/><br/>
            <span><?php echo $resultMessage ?></span><br/>
            <button type="button" onclick="navigateToUserList()">Back</button><input type="submit" /><br/><br/>
        </form>
    </body>
</html>