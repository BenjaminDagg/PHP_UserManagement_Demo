<!DOCTYPE html>

<script>
    function navigateToUserList(){
        window.location.href = "list.php";
        
    }
</script>
<?php
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
<style>
    #modal {
        position: absolute;
        margin:0 auto;
        width: 200px;
        height: 100px;
        border: 1px solid black;
        text-align: center;
        display: none;
        background-color: white;
        box-shadow: 5px 10px 18px #888888;
    }

</style>
<html>
    <head>
        <title>Add User</title>
        <?php require("header.php") ?>
    </head>
    <body>
        
        <h3>Add User </h3>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div id="modal" style="display:<?php if($isValid){echo "block";}else{echo "none";} ?>">
                <h3>Success</h3>
                <span>Successfully added user.</span><br/>
                <button onclick="navigateToUserList()" type="button">Ok</button>
            </div>
            User Name: <input name="username" value="<?php echo $nuserName;?>"/><span><?php echo $usernameError ?><br/><br/>
            First Name: <input name="firstName" value="<?php echo $firstname;?>"/><span><?php echo $firstNameError ?><br/><br/>
            Last Name: <input name="lastName" value="<?php echo $lastname;?>"/><span><?php echo $lastNameError ?><br/><br/>
            Email: <input name="email" value="<?php echo $email;?>"/><span><?php echo $emailError ?><br/><br/>
            Password: <input type="password" name="password" value="<?php echo $password;?>"/><span><?php echo $passwordError ?><br/><br/>
            Confirm Password: <input type="password" name="passwordconfirm" value="<?php echo $confirmPassword;?>"/><span><?php echo $confirmPasswordError ?><br/><br/>
            <input type="submit" /><br/><br/>
        </form>
        <span><?php echo $validationerror ?></span>

        <button type="button" onclick="navigateToUserList()">Back</button>
    </body>
</html>