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
                            if($user->active){
                                $user->active = 1;
                            }
                            else{
                                $user->active = 0;
                            }
    
                            if($user->locked){
                                $user->locked = 1;
                            }
                            else{
                                $user->locked = 0;
                            }
    
    
                            $userRepo->update_user($user);
    
                            Header("Location: list.php");
                        }
                    }
                    //incorrect password
                    else{
          
                        $user->incorrectLoginAttempts = $user->incorrectLoginAttempts + 1;
                 
                        if($user->incorrectLoginAttempts >= 3){
                            $user->locked = 1;
                            $loginresult = "User is locked. Too many incorrect login attempts.";
                        }
                        else{
                            $user->locked = 0;
                            $loginresult = "Incorrect username or password.";
                        }

                        //reset user active flag
                        if($user->active){
                            $user->active = 1;
                        }
                        else{
                            $user->active = 0;
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
<html>
    <head>
        <title>Users</title>
    </head>
    <h3>Login</h3>
    <body>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            User Name: <input name="username" value="<?php echo $username;?>"/><span><?php echo $usernameError ?><br/><br/>
            Password: <input type="password" name="password" value="<?php echo $password;?>"/><span><?php echo $passwordError ?><br/><br/>
            <span><?php echo $loginresult ?></span><br/>
            <input type="submit" /><br/><br/>
        </form>
    </body>
</html>