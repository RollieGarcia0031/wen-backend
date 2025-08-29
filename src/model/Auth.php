<?php

require_once __DIR__ . '/../config/db.php';

class Auth {
    private $db;
    public string $message;
    public array $data;

    public function __construct() {
        $this->db = connection();
    }

    public function login($email, $password){
        $query = "SELECT * FROM users WHERE email = :email";
        $stment = $this->db->prepare($query);
        $stment->bindParam(':email', $email);
        $stment->execute();
        
        $row = $stment->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            $this->message = "Email not found";
            return false;
        }
        
        $hash = $row['password'];
        $verified = password_verify($password, $hash);
        
        if($verified){
            $this->message = "Login successful";

            $row['password'] = null;
            $this->data = $row;
            
            return true;
        }
        
        $this-> message = "Password incorrect";
        return false;
    }
}