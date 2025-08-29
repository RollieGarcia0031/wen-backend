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

    public function signup($email, $name, $password){
        $query = "INSERT INTO users (email, name, password) VALUES (?, ?, ?)";
        $stment = $this->db->prepare($query);

        $password = password_hash($password, PASSWORD_DEFAULT);
        
        $stment->execute([$email, $name, $password]);
        $lastId = $this->db->lastInsertId();

        $this->message = "Signup successful";
        $this->data = ['id' => $lastId, 'email' => $email, 'name' => $name];
        return true;
    }

    public function logout(){
        session_destroy();
        $this->message = "Logout successful";
        return true;
    }

    public function update(array $new_data){
        
        if(isset($new_data['name'])){
            $q = "UPDATE users SET name = ? WHERE id = ?";
            $stment = $this->db->prepare($q);

            $stment->execute([$new_data['name'], $_SESSION['id']]);
            return true;
        }
    }
}