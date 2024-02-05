<?php
    require("header.php");
    require("./model/User.php");
    require("./Data/UserRepository.php");

    $nuserName = $firstname = $lastname = $email = $password = $confirmPassword = "";
    $usernameError = $firstNameError = $lastNameError = $emailError = $passwordError = $confirmPasswordError = "";
    $validationerror = "";

    $dbService = new DatabaseService();
    $userRepo = new UserRepository($dbService);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $isValid = true;
        $validationerror = "";

        if (empty($_POST["username"])) {
            $usernameError = "Username is required";
            $isValid = false;
        }
        else{
            $nuserName = cleanInput($_POST["username"]);

            if(!preg_match("/^[a-zA-z][a-zA-Z0-9]{2,8}$/",$nuserName)){
                $usernameError = "Username must be between 3 an 8 characters and start with a letter";
                $isValid = false;
            }

            $exists = $userRepo->user_exists($nuserName);

            if($exists == true){
                $usernameError = "A user already exists with this username";
                $isValid = false;
            }

        }

        if (empty($_POST["firstName"])) {
            $firstNameError = "First Name is required";
            $isValid = false;
        }
        else{
            $firstname = $_POST["firstName"];

            if(!preg_match("/^[a-zA-z][a-zA-Z]{1,7}$/",$firstname)){
                $firstNameError = "First name must be between 2 and 8 characters";
                $isValid = false;
            }
        }

        if (empty($_POST["lastName"])) {
            $lastNameError = "Last Name is required";
            $isValid = false;
        }
        else{
            $lastname = $_POST["lastName"];

            if(!preg_match("/^[a-zA-z][a-zA-Z]{1,7}$/",$lastname)){
                $lastNameError = "Last name must be between 2 and 8 characters";
                $isValid = false;
            }
        }

        if (empty($_POST["email"])) {
            $emailError = "Email is required";
            $isValid = false;
        }
        else{
            $email = $_POST["email"];

            if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
                $emailError = "Invalid email format";
                $isValid = false;
            }
        }

        if (empty($_POST["password"])) {
            $passwordError = "Please choose a password";
            $isValid = false;
        }
        else{
            $password = $_POST["password"];
        }

        if (empty($_POST["passwordconfirm"])) {
            $confirmPasswordError = "Please re-enter password";
            $isValid = false;
        }
        else{
            $confirmPassword = $_POST["passwordconfirm"];
        }

        if($password != $confirmPassword){
            $confirmPasswordError = "Password and Confirm Password do not match";
            $isValid = false;
        }

        if($isValid == true){
            $user = new User();
 
            $user->userName = $nuserName;
            $user->firstName = $firstname;
            $user->lastName = $lastname;
            $user->email = $email;
            $user->active = 1;
            $user->password = password_hash($password,PASSWORD_DEFAULT);

            try{
                $userRepo->insert_user($user);
                $isValid = true;
            }
            catch(Exception $e){
                $validationerror = "Error when creating user. " . $e->getMessage() . " <br/>";
                $isValid = false;
            }
        }
    }

    function cleanInput($data){
        $data = trim(($data));
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        return $data;
    }

    
?>
<!DOCTYPE html>
<style>
    #modal {
        position: absolute;
        margin:0 auto;
        width: 200px;
        height: 125px;
        border: 1px solid black;
        text-align: center;
        display: none;
        background-color: white;
        box-shadow: 5px 10px 18px #888888;
    }

</style>
<script>
    function navigateToUserList(){
        window.location.href = "list.php";
        
    }
</script>
<html>
    <head>
        <title>Add User</title>
    </head>
    <body>
        
        <h3>Add User </h3>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
             <div id="modal" style="display:<?php if($isValid){echo "block";}else{echo "none";} ?>">
                <h3>Success</h3>
                <span>Successfully added user.</span><br/>
                <button onclick="navigateToUserList()" type="button">Ok</button>
            </div>
            <table>
                <tr>
                    <td>
                        <label>User Name:</label>
                    </td>
                    <td>
                        <input name="username" value="<?php echo $nuserName;?>"/>
                    </td>
                    <td>
                        <span class="error"><?php echo $usernameError ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>First Name:</label>
                    </td>
                    <td>
                        <input name="firstName" value="<?php echo $firstname;?>"/>
                    </td>
                    <td>
                        <span class="error"><?php echo $firstNameError ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Last Name:</label>
                    </td>
                    <td>
                        <input name="lastName" value="<?php echo $lastname;?>"/>
                    </td>
                    <td>
                        <span class="error"><?php echo $lastNameError ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Email:</label>
                    </td>
                    <td>
                        <input name="email" value="<?php echo $email;?>"/>
                    </td>
                    <td>
                        <span class="error"><?php echo $emailError ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Password:</label>
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
                        <input type="password" name="passwordconfirm" value="<?php echo $confirmPassword;?>"/>
                    </td>
                    <td>
                        <span class="error"><?php echo $confirmPasswordError ?>
                    </td>
                </tr>
            </table>
        <span class="error"><?php echo $validationerror ?></span>
        <button type="button" onclick="navigateToUserList()">Back</button><input type="submit" />
        </form>

        
    </body>
</html>