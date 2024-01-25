<?php 
class MyDatabaseService {
    private $serverName;
    private $username;
    private $password;
    private $dbName;
    private $conn;

    function __construct($server, $username,$password,$dbName)
    {
        $this->serverName = $server;
        $this->username = $username;
        $this->password = $password;
        $this->dbName = $dbName;
    }

    function connect(){

        $this->conn = new mysqli($this->serverName, $this->username, $this->password, $this->dbName);

        if($this->conn->connect_error){
            echo "Failed to connect to database. " . $this->conn->connect_error;
        }
    }

    function disconnect(){
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