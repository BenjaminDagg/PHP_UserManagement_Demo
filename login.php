<!DOCTYPE html>
<?php 
    require("./model/User.php");
    require("./Data/LoginService.php");
    require("./Data/UserRepository.php");

    $dbService = new DatabaseService();
    $userRepo = new UserRepository($dbService);
    $loginService = new LoginService($dbService);

    $username = $password = "";
    $usernameError = $passwordError = "";
    $loginresult = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $valid = true;
        $loginResult = "";
        $usernameError = "";
        $passwordError = "";

        if(empty($_POST["username"])){
            $usernameError = "Username field is required";
            $valid = false;
        }

        if(empty($_POST["password"])){
            $passwordError = "Password field is required";
            $valid = false;
        }

        $username = $_POST["username"];
        $password = $_POST["password"];


        //entered valid data for username and password
        if($valid == true){

            try{
                $user = $userRepo->get_user_by_Username($username);

                //user found
                if(strlen($user->userName) > 0){

                    //verify correct password
                    $hash = $user->password;
                    $loginresult = password_verify($password,$hash);

                    //correct password
                    if($loginresult){
  
                        if(!$user->active){
                            $loginresult = "User account is deactivated <br/>";
                            $valid = false;
                        }
                        elseif($user->locked){
                            $loginresult = "User is locked. Too many incorrect login attempts.. <br/>";
                            $valid = false;
                        }
                        else{
                            //create session
                            session_start();
    
                            $_SESSION['username'] = $username;
                            $_SESSION['login'] = true;
                            $_SESSION['start'] = time();
                            $_SESSION['sessionExpiration'] = 60 * 10;
    
                            //reset user incorrect login attempts
                            $user->incorrectLoginAttempts = 0;
                            $userRepo->update_user($user);
    
                            Header("Location: list.php");
                        }
                    }
                    //incorrect password
                    else{
          
                        $user->incorrectLoginAttempts = $user->incorrectLoginAttempts + 1;
                 
                        if($user->incorrectLoginAttempts >= 3){
                            $user->locked = true;
                            $loginresult = "User is locked. Too many incorrect login attempts.";
                        }
                        else{
                            $user->locked = false;
                            $loginresult = "Incorrect username or password.";
                        }

                        $valid = false;
                        $userRepo->update_user($user);
                    }
                }
                //username with that username isn't found
                else{
 
                    $loginresult = "Incorrect username or password.";
                    $valid = false;
                }
            }
            catch(Exception $e){
                $loginresult = "Login failed " . $e->getMessage();
                $valid = false;
            }
        }
    }

?>
<script>
    
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

input {
    padding: 6px;
    font-size: 14px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

input:focus {
    border: 1px solid #294d7e;
    outline: 1px solid #294d7e;
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

#reset-link {
    text-align: center;
}

#login-form{
    width: 500px;
    padding: 5px;
}

table {
    table-layout: fixed;
}

table th {
    overflow-wrap: break-word;
}

</style>
<html>
    <head>
        <title>Users</title>
    </head>
    
    <body>
    <h3>Login</h3>
    <form id="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <table style="table-layout: fixed";>
            <tr>
                <td>
                    <label>User Name:</label>
                </td>
                <td>
                    <input name="username" value="<?php echo $username;?>"/>
                </td>
                <td>
                    <span class="error"><?php echo $usernameError ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label>Password:</label>
                </td>
                <td>
                    <input type="password" name="password"/>
                </td>
                <td>
                    <span class="error"><?php echo $passwordError ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <span class="error"><?php echo $loginresult ?>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" />
                </td>
                <td>
                    <a id="reset-link" href="forgot_password.php">Reset Password</a>
                </td>
            </tr>
        </table>
    </form>
    </body>
</html>