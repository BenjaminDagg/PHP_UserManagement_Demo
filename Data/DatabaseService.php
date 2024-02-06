<?php 

require_once("DatabaseConstants.php");

class DatabaseService {

    private $conn;

    function __construct()
    {
        $this->conn = new mysqli(
            DatabaseConstants::DB_SERVER, 
            DatabaseConstants::DB_USER, 
            DatabaseConstants::DB_PASSWORD, 
            DatabaseConstants::DB_NAME);
    }

    function __destruct()
    {
        if($this->conn){
            $this->conn->close();
        }
    }


    function query($sql){

        $result = $this->conn->query($sql);
            
        return $result;
    }

    function insert($sql){
        if($this->conn->query($sql) == TRUE){
            $last_id = $this->conn->insert_id;

            return $last_id;
        }
        else{
            echo "Error executing insert statement. " . $this->conn->error;

            return -1;
        }
    }

    function delete($sql){

        $result = $this->conn->query($sql);
            
        return $result;
    }
}
?>