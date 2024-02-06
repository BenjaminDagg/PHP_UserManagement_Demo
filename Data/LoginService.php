<?php 

require_once("DatabaseService.php");

class LoginService {

    private DatabaseService $db;

    function __construct($dbService)
    {
        $this->db = $dbService;
    }


    function validate_login($username){
    
        $pHash = "";

        $sql = "SELECT PasswordHash FROM users WHERE UserName = '$username'";

        $result = $this->db->query($sql);

        if($result->num_rows > 0){

            $row = $result->fetch_assoc();
            $pHash =  $row["PasswordHash"];
        }

        return $pHash;
    }
}

?>