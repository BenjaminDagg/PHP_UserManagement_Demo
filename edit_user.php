<!DOCTYPE html>

<script>
    function navigateToUserList(){
        window.location.href = "list.php";
    }
</script>
<?php
    require("./model/User.php");
    require("./Data/UserRepository.php");

    $nuserName = $firstname = $lastname = $email = "";
    $usernameError = $firstNameError = $lastNameError = $emailError = "";
    $isActive = false;
    $isLocked = false;
    $validationerror = "";
    
    $dbService = new DatabaseService();
    $userRepo = new UserRepository($dbService);

    $id = 0;
    $user = new User();
    if(isset($_GET['id'])){
        
        $id = $_GET['id'];
        $user = $userRepo->get_user($id);

        $nuserName = $user->userName;
        $firstname = $user->firstName;
        $lastname = $user->lastName;
        $email = $user->email;
        $isActive = $user->active;
        $isLocked = $user->locked;
    }
   
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $isValid = true;
        $validationerror = "";

        if (empty($_POST["username"])) {
            $usernameError = "Username is required";
            $isValid = false;
        }
        else{
            $nuserName = cleanInput($_POST["username"]);
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

        if(isset($_POST["active"]) && $_POST["active"] == "yes"){
            $isActive = true;
        }
        else{
            $isActive = false;
        }

        if(isset($_POST["locked"]) && $_POST["locked"] == "yes"){
            $isLocked = true;
        }
        else{
            $isLocked = false;
        }

        if (empty($_POST["id_hidden"])) {
            $isValid = false;
        }
        else{
            $id = $_POST["id_hidden"];
        }

        if($isValid == true){
            
            $currentUser = $userRepo->get_user($id);

            $currentUser->userName = $nuserName;
            $currentUser->firstName = $firstname;
            $currentUser->lastName = $lastname;
            $currentUser->email = $email;
            $currentUser->active = $isActive ? 1 : 0;
            $currentUser->lockoutEnd = $currentUser->lockoutEnd == null ? NULL : strtotime($currentUser->lockoutEnd);

            //unlock user if locked is unchecked
            if(!$isLocked){
                $currentUser->locked = 0;
                $currentUser->incorrectLoginAttempts = 0;
                $currentUser->lockoutEnd = NULL;
            }
            //if user wasn't locked before but they changed the lock checkbox to true do nothing. 
            //User shouldn't be able to manually lock someone. That's what the Active feature is for
            elseif($isLocked && !$currentUser->locked){
                $currentUser->locked = 0;
            }
            else{
                $currentUser->locked = $currentUser->locked ? 1 : 0;
            }
            
            try{
                $userRepo->update_user($currentUser);
                $isValid = true;
            }
            catch(Exception $e){
                $validationerror = "Error when updating user. " . $e->getMessage() . " <br/>";
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
        <title>Edit User</title>
        <?php require("header.php") ?>
    </head>
    <body>
        <h3>Edit User <?php echo $user->locked ?></h3>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div id="modal" style="display:<?php if($isValid){echo "block";}else{echo "none";} ?>">
                <h3>Success</h3>
                <span>Successfully updated user.</span><br/>
                <button onclick="navigateToUserList()" type="button">Ok</button>
            </div>
            <input type="hidden" name="id_hidden" value="<?php echo $id;?>"/><br/><br/>
            User Name: <input readonly name="username" value="<?php echo $nuserName;?>"/><span><?php echo $usernameError ?><br/><br/>
            First Name: <input name="firstName" value="<?php echo $firstname;?>"/><span><?php echo $firstNameError ?><br/><br/>
            Last Name: <input name="lastName" value="<?php echo $lastname;?>"/><span><?php echo $lastNameError ?><br/><br/>
            Email: <input name="email" value="<?php echo $email;?>"/><span><?php echo $emailError ?><br/><br/>
            Active: <input type="checkbox" value="yes" name="active" <?php if($isActive){echo "checked='checked'";} ?>/><br/><br/>
            Locked: <input type="checkbox" value="yes" name="locked" <?php if($isLocked){echo "checked='checked'";} ?>/><br/><br/>
            <input type="submit"/><br/><br/>
        </form>
        <span><?php echo $validationerror ?></span>

        <button type="button" onclick="navigateToUserList()">Back</button>
    </body>
</html>