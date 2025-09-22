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

    public function me($uid){
        $query = "SELECT
            id, name, email
            FROM users WHERE id = ?";
            
        $stment = $this->db->prepare($query);
        $stment->execute([$uid]);
        
        $user = $stment->fetch(PDO::FETCH_ASSOC);

        $this->data = $user;
        $this->message = "User found";
        $this->code = 200;
        
        return true;
    }

    /**
     * updates information on logged user
     */
    public function updateInfos($email, $name, $old_password, $new_password, $user_id){
        try {
            $this->db->beginTransaction();

            if(isset($email) && $email !== null){
                $q = "UPDATE users SET email = ? WHERE id = ?";
                $stment = $this->db->prepare($q);

                $stment->execute([$email, $user_id]);
            }

            if (isset($name) && $name !== null) {
                $q = "UPDATE users SET name = ? WHERE id = ?";
                $stment = $this->db->prepare($q);

                $stment->execute([$name, $user_id]);
            }

            if (isset($new_password) && $new_password !== null){
                //fetch the old password from database
                $query1 = "SELECT password FROM users WHERE id = $user_id";
                $fetched_old_password = $this->db->query($query1)->fetch(PDO::FETCH_ASSOC)['password'];

                //compare it to old password from api request
                $password_is_correct = password_verify($old_password, $fetched_old_password);

                if ($password_is_correct) {
                    $q = "UPDATE users SET password = ? WHERE id = ?";
                    $stment = $this->db->prepare($q);
                    
                    //hash the new password
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    //execute to update database
                    $stment->execute([$new_password_hash, $user_id]);
                } else {
                    throw new PDOException("Old password is incorrect");
                }
            }

            $this->db->commit();

        } catch (PDOException $e) {
            $this->db->rollBack();

            $this->code = 500;
            $this->message = $e->getMessage();
            return false;
        }

        $this->code = 200;
        $this->message = "Infos updated";
        return true;
    }
}