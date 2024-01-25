<?php 
class User {
    //properties
    public $active;
    public $email;
    public $firstName;
    public $id;
    public $lastName;
    public $userName;
    public $password;
    public $locked;
    public int $incorrectLoginAttempts;
    public $lockoutEnd;
}
?>