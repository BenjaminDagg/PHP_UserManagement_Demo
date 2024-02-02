<?php 

require_once("DbService.php");

class UserRepository {

    private DatabaseService $db;

    function __construct($dbService)
    {
        $this->db = $dbService;
    }

    //gets all users
    function get_users(){
        $users = array();

        $sql = "SELECT * FROM users";

        $result = $this->db->query($sql);

        if($result->num_rows > 0){

            while($row = $result->fetch_assoc()) {
                $user = new User();
    
                $user->active = $row['Active'] == "1" ? true : false;
                $user->email = $row['Email'];
                $user->firstName = $row['FirstName'];
                $user->id = $row['Id'];
                $user->lastName = $row['LastName'];
                $user->userName = $row['UserName'];
                $user->password = $row['PasswordHash'];
                $user->locked = $row['Locked'] == "1" ? true : false;
                $user->incorrectLoginAttempts = $row["IncorrectLoginAttempts"];
                $user->lockoutEnd = $row["LockoutEnd"];
    
                array_push($users,$user);
                
            }
        }

        return $users;
    }

    function get_user($id){
        $user = new User();

        $sql = "SELECT * FROM users WHERE Id = $id";

        $result = $this->db->query($sql);

        if($result->num_rows > 0){

            $row = $result->fetch_assoc();
    
                $user->active = $row['Active'] == "1" ? true : false;
                $user->email = $row['Email'];
                $user->firstName = $row['FirstName'];
                $user->id = $row['Id'];
                $user->lastName = $row['LastName'];
                $user->userName = $row['UserName'];
                $user->password = $row['PasswordHash'];
                $user->locked = $row['Locked'] == "0" ? false : true;
                $user->incorrectLoginAttempts = $row["IncorrectLoginAttempts"];
                $user->lockoutEnd = $row["LockoutEnd"];
        }

        return $user;
    }

    function get_user_by_username($username){
        $user = new User();

        $sql = "SELECT * FROM users WHERE UserName = '$username'";

        $result = $this->db->query($sql);

        if($result->num_rows > 0){

            $row = $result->fetch_assoc();
    
                $user->active = $row['Active'] == "1" ? true : false;
                $user->email = $row['Email'];
                $user->firstName = $row['FirstName'];
                $user->id = $row['Id'];
                $user->lastName = $row['LastName'];
                $user->userName = $row['UserName'];
                $user->password = $row['PasswordHash'];
                $user->locked = $row['Locked'] == "0" ? false : true;
                $user->incorrectLoginAttempts = (int)$row["IncorrectLoginAttempts"];
                $user->lockoutEnd = $row["LockoutEnd"];
        }

        return $user;
    }

    function user_exists($searchUsername){

        $exists = false;

        $sql = "SELECT * FROM users WHERE UserName = '$searchUsername'";

        $result = $this->db->query($sql);

        if($result->num_rows > 0){
            $exists = true;
        }
        else{
            $exists = false;
        }

        return $exists;
    }

    function insert_user($user){
        $sql = "INSERT INTO users (UserName, FirstName, LastName, Email, Active, PasswordHash, Locked, IncorrectLoginAttempts) VALUES ('$user->userName','$user->firstName','$user->lastName','$user->email', $user->active, '$user->password', 0, 0 )";

        $this->db->insert($sql);
    }

    function delete_user($id){
        $sql = "DELETE FROM users WHERE Id = $id";

        $this->db->delete($sql);
    }

    function update_user($user){

        $sql = "UPDATE users SET UserName = '$user->userName', FirstName = '$user->firstName', LastName = '$user->lastName', Email = '$user->email', Active = $user->active, PasswordHash = '$user->password', Locked = $user->locked, IncorrectLoginAttempts = $user->incorrectLoginAttempts, LockoutEnd = ". ($user->lockoutEnd == NULL ? "NULL" : $user->lockoutEnd) . " WHERE Id = $user->id";

        $this->db->query($sql);
    }

    function update_password($username,$passHash){
        $sql = "UPDATE users SET PasswordHash = '$passHash' WHERE UserName = '$username'";

        $this->db->query($sql);
    }
}

?>