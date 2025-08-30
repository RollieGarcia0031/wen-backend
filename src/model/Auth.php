<?php

require_once __DIR__ . '/AppModel.php';

class Auth extends AppModel{

    public function login($email, $password){
        $query = "SELECT * FROM users WHERE email = :email";
        $stment = $this->db->prepare($query);

        $stment->bindParam(':email', $email);
        $stment->execute();

        $row = $stment->fetch(PDO::FETCH_ASSOC);
        
        if(!$row){
            $this->message = "Email not found";
            $this->code = 404;
            return false;
        }
        
        $hash = $row['password'];
        $verified = password_verify($password, $hash);
        
        if($verified){
            $this->message = "Login successful";
            
            $row['password'] = null;
            $_SESSION['uid'] = $row['id'];

            $this->data = $row;
            $this->code = 200;
            
            return true;
        }
        
        $this->message = "Password incorrect";
        $this->code = 401;
        return false;
    }

    public function signup($email, $name, $password, $role){
        $query = "INSERT INTO users (email, name, password, role) VALUES (?, ?, ?, ?)";
        $stment = $this->db->prepare($query);

        $password = password_hash($password, PASSWORD_DEFAULT);
        
        $stment->execute([$email, $name, $password, $role]);
        $lastId = $this->db->lastInsertId();

        $this->message = "Signup successful";
        $this->code = 200;
        $this->data = ['id' => $lastId, 'email' => $email, 'name' => $name];
        return true;
    }

    public function logout(){
        session_destroy();
        $this->message = "Logout successful";
        $this->code = 200;

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