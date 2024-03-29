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

            //check if email exists for another user
            $userEmail = $userRepo->get_user_by_email($email);

            if(strlen($userEmail->userName) > 0 && $userEmail->userName != $nuserName){
                $emailError = "This email exists for another user.";
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
            $currentUser->active = $isActive;
            $currentUser->lockoutEnd = $currentUser->lockoutEnd == null ? NULL : $currentUser->lockoutEnd;

            //unlock user if locked is unchecked
            if(!$isLocked){
                $currentUser->locked = false;
                $currentUser->incorrectLoginAttempts = 0;
                $currentUser->lockoutEnd = NULL;
            }
            //if user wasn't locked before but they changed the lock checkbox to true do nothing. 
            //User shouldn't be able to manually lock someone. That's what the Active feature is for
            elseif($isLocked && !$currentUser->locked){
                $currentUser->locked = false;
            }
            else{
                $currentUser->locked = $currentUser->locked;
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
        height: 125px;
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
       
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div id="modal" style="display:<?php if($isValid){echo "block";}else{echo "none";} ?>">
                <h3>Success</h3>
                <span>Successfully updated user.</span><br/>
                <button onclick="navigateToUserList()" type="button">Ok</button>
            </div>
            <input type="hidden" name="id_hidden" value="<?php echo $id;?>"/><br/><br/>
            <h3>Edit User</h3>
            <table>
                <tr>
                    <td>
                        <label>User Name:</label>
                    </td>
                    <td>
                        <input class="readonly" readonly name="username" value="<?php echo $nuserName;?>"/>
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
                        <span><?php echo $lastNameError ?>
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
                        <label>Active:</label>
                    </td>
                    <td>
                        <input type="checkbox" value="yes" name="active" <?php if($isActive){echo "checked='checked'";} ?>/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Locked:</label>
                    </td>
                    <td>
                        <input type="checkbox" value="yes" name="locked" <?php if($isLocked){echo "checked='checked'";} ?>/>
                    </td>
                </tr>
            </table>
            <span><?php echo $validationerror ?></span>
            <button type="button" onclick="navigateToUserList()">Back</button><input type="submit"/>
        </form>

    </body>
</html>