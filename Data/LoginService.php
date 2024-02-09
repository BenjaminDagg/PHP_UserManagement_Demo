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

    function create_reset_token($email){

        $expFormat = mktime(
            date("H")+1, date("i"), date("s"), date("m") ,date("d"), date("Y")
            );
        $expDate = date("Y-m-d H:i:s",$expFormat);
        $key = md5($expDate);
        $addKey = substr(md5(uniqid(rand(),1)),3,10);
        $key = $key . $addKey;

        $sql = "INSERT INTO password_reset (email, token, expiration_date) VALUES ('$email','$key','$expDate')";

        $this->db->insert($sql);

        return $key;
    }

    function validate_reset_token($email,$token){

        $sql = "SELECT * FROM password_reset WHERE email = '$email' AND token = '$token'";

        $result = $this->db->query($sql);

        return $result;
    }

    function delete_reset_token($email){

        $sql = "DELETE FROM password_reset WHERE email = '$email'";

        $result = $this->db->query($sql);

        return $result;
    }
}

?>