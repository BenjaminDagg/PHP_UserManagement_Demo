<?php 

require_once("DbService.php");

class LoginService {
    private MyDatabaseService $db;

    function __construct($dbService)
    {
        $this->db = $dbService;
    }


    function validate_login($username){
    
        $pHash = "";

        $sql = "SELECT PasswordHash FROM users WHERE UserName = '$username'";

        $this->db->connect();
        $result = $this->db->query($sql);

        if($result->num_rows > 0){

            $row = $result->fetch_assoc();
            $pHash =  $row["PasswordHash"];
        }

        $this->db->disconnect();

        return $pHash;
    }

    function user_status($username){
    
        $active = false;

        $sql = "SELECT PasswordHash FROM users WHERE UserName = '$username' AND Active = 1";

        $this->db->connect();
        $result = $this->db->query($sql);

        if($result->num_rows > 0){

            $active = true;
        }

        $this->db->disconnect();

        return $active;
    }
}

?>