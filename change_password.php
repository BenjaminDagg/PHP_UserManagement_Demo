<?php 
    require("./model/User.php");
    require("./Data/LoginService.php");
    require("./Data/UserRepository.php");
    require("header.php");

    $dbService = new DatabaseService();
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
                    $resultMessage = "Cannot change password to the same as your current password.";
                    $valid = false;
                }
                else{
                    try{
                        $newPassHash = password_hash($newpassword,PASSWORD_DEFAULT);
                        $user-> password = $newPassHash;

                        $userRepo->update_password($user->userName,$newPassHash);
                        $resultMessage = "";
                        $valid = true;
                    }
                    catch(Exception $e){
                        echo "Failed to update user password. " . $e->getMessage();
                        $valid = false;
                    }
                }
            }
            else{
                $resultMessage = "Incorrect current password";
                $valid = false;
            }
        }
    }
?>
<!DOCTYPE html>
<script>
    function navigateToUserList(){
        window.location.href = "list.php";
        
    }
</script>
<style>
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
    <div id="container">
    <h3>Change Password</h3>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div id="modal" style="display:<?php if($valid){echo "block";}else{echo "none";} ?>">
            <h3>Success</h3>
            <span>Successfully updated password.</span><br/>
            <button onclick="navigateToUserList()" type="button">Ok</button>
        </div>
    <table>
        <tr>
            <td>
                <label>Current Password:</label>
            </td>
            <td>
                <input type="password" name="currentpassword" value="<?php echo $password;?>"/>
            </td>
            <td>
                <span class="error"><?php echo $passwordError ?>
            </td>
        </tr>
        <tr>
            <td>
                <label>New Password:</label>
            </td>
            <td>
                <input type="password" name="newpassword" value="<?php echo $newpassword;?>"/>
            </td>
            <td>
                <span class="error"><?php echo $newpasswordError ?>
            </td>
        </tr>
        <tr>
            <td>
                <label>Confirm Password:</label>
            </td>
            <td>
                <input type="password" name="confirmpassword" value="<?php echo $confirmpassword;?>"/>
            </td>
            <td>
                <span class="error"><?php echo $confirmpasswordError ?>
            </td>
        </tr>
    </table>
    <label><span class="error"><?php echo $resultMessage ?></label></span><br/>
    <button type="button" onclick="navigateToUserList()">Back</button>
    <input type="submit" /><br/><br/>
    </form>

    </div>
    </body>
</html>